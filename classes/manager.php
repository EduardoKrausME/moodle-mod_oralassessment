<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Activity and attempt manager.
 *
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_oralassessment;

use completion_info;
use context_module;
use mod_oralassessment\ai\dialogue_service;
use mod_oralassessment\transcription\transcription_service;
use moodle_exception;
use stdClass;

/**
 * Central domain service for the activity.
 */
class manager {
    public const STATUS_INPROGRESS = 'inprogress';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_REVIEWED = 'reviewed';

    /**
     * Add activity instance.
     *
     * @param stdClass $data Form data.
     * @return int
     */
    public static function add_instance(stdClass $data): int {
        global $DB;
        $now = time();
        $data->timecreated = $now;
        $data->timemodified = $now;
        $id = $DB->insert_record('oralassessment', $data);
        $data->id = $id;
        \oralassessment_grade_item_update($data);
        return $id;
    }

    /**
     * Update activity instance.
     *
     * @param stdClass $data Form data.
     * @return bool
     */
    public static function update_instance(stdClass $data): bool {
        global $DB;
        $data->id = $data->instance;
        $data->timemodified = time();
        $result = $DB->update_record('oralassessment', $data);
        \oralassessment_grade_item_update($data);
        return $result;
    }

    /**
     * Delete an activity and its dependent records.
     *
     * @param int $id Activity id.
     * @return bool
     */
    public static function delete_instance(int $id): bool {
        global $CFG, $DB;
        $activity = $DB->get_record('oralassessment', ['id' => $id]);
        if (!$activity) {
            return false;
        }
        $cm = get_coursemodule_from_instance('oralassessment', $id, $activity->course, false, IGNORE_MISSING);
        $attemptids = $DB->get_fieldset_select('oralassessment_attempts', 'id', 'oralassessmentid = ?', [$id]);
        if ($attemptids) {
            [$insql, $params] = $DB->get_in_or_equal($attemptids, SQL_PARAMS_QM);
            if ($cm) {
                $context = context_module::instance($cm->id);
                $turnids = $DB->get_fieldset_select('oralassessment_turns', 'id', "attemptid {$insql}", $params);
                $fs = get_file_storage();
                foreach ($turnids as $turnid) {
                    $fs->delete_area_files($context->id, 'mod_oralassessment', 'attemptaudio', (int)$turnid);
                }
            }
            $DB->delete_records_select('oralassessment_turns', "attemptid {$insql}", $params);
        }
        $DB->delete_records('oralassessment_attempts', ['oralassessmentid' => $id]);
        $DB->delete_records('oralassessment', ['id' => $id]);
        require_once($CFG->libdir . '/gradelib.php');
        \grade_update('mod/oralassessment', $activity->course, 'mod', 'oralassessment', $id, 0, null,
            ['deleted' => 1]);
        return true;
    }

    /**
     * Parse configured initial questions.
     *
     * @param stdClass $activity Activity.
     * @return array
     */
    public static function initial_questions(stdClass $activity): array {
        $lines = preg_split('/\R/u', (string)$activity->initialquestions) ?: [];
        return array_values(array_filter(array_map('trim', $lines), static fn($value) => $value !== ''));
    }

    /**
     * Get or create the user's single attempt.
     *
     * @param stdClass $activity Activity.
     * @param stdClass $cm Course module.
     * @param int $userid User id.
     * @param dialogue_service $ai AI service.
     * @return array Attempt and current question.
     */
    public static function start_attempt(stdClass $activity, stdClass $cm, int $userid, dialogue_service $ai): array {
        global $DB;

        $attempt = $DB->get_record('oralassessment_attempts', [
            'oralassessmentid' => $activity->id,
            'userid' => $userid,
        ]);
        if (!$attempt) {
            $attempt = (object)[
                'oralassessmentid' => $activity->id,
                'userid' => $userid,
                'status' => self::STATUS_INPROGRESS,
                'round' => 0,
                'timestarted' => time(),
                'timesubmitted' => 0,
                'timereviewed' => 0,
                'reviewedby' => 0,
                'timemodified' => time(),
            ];
            $attempt->id = $DB->insert_record('oralassessment_attempts', $attempt);
            $event = event\attempt_started::create([
                'objectid' => $attempt->id,
                'context' => context_module::instance($cm->id),
                'other' => ['oralassessmentid' => $activity->id],
            ]);
            $event->trigger();
        }

        $current = self::current_question($attempt->id);
        if ($attempt->status === self::STATUS_INPROGRESS && !$current) {
            $questions = self::initial_questions($activity);
            if ($questions) {
                $question = $questions[0];
                self::insert_question($attempt->id, 1, $question, null);
            } else {
                try {
                    $result = $ai->first_question($activity);
                    $question = $result['next_question'];
                    self::insert_question($attempt->id, 1, $question, json_encode($result, JSON_UNESCAPED_UNICODE));
                } catch (\Throwable $e) {
                    $DB->set_field('oralassessment_attempts', 'aierror', get_string('aifailed', 'mod_oralassessment'),
                        ['id' => $attempt->id]);
                    throw new moodle_exception('aifailed', 'mod_oralassessment');
                }
            }
            $current = self::current_question($attempt->id);
        }

        return [$attempt, $current];
    }

    /**
     * Submit a learner response and optionally generate the next question.
     *
     * @param stdClass $activity Activity.
     * @param stdClass $cm Course module.
     * @param stdClass $attempt Attempt.
     * @param int $userid User id.
     * @param string $transcript Transcript.
     * @param string $method Transcription method.
     * @param dialogue_service $ai AI service.
     * @return array Result payload.
     */
    public static function submit_response(stdClass $activity, stdClass $cm, stdClass $attempt, int $userid,
            string $transcript, string $method, dialogue_service $ai): array {
        global $DB;

        if ((int)$attempt->userid !== $userid || $attempt->status !== self::STATUS_INPROGRESS) {
            throw new moodle_exception('attemptclosed', 'mod_oralassessment');
        }

        $method = transcription_service::normalise_method($method);
        $transcript = transcription_service::normalise($transcript, $method);
        $questionturn = self::current_question($attempt->id);
        if (!$questionturn) {
            throw new moodle_exception('invalidattempt', 'mod_oralassessment');
        }

        $turnnumber = (int)$attempt->round + 1;
        $userturn = (object)[
            'attemptid' => $attempt->id,
            'turnnumber' => $turnnumber,
            'role' => 'user',
            'question' => null,
            'transcript' => $transcript,
            'transcriptionmethod' => $method,
            'aijson' => null,
            'timecreated' => time(),
        ];
        $userturn->id = $DB->insert_record('oralassessment_turns', $userturn);

        $attempt->round = $turnnumber;
        $attempt->timemodified = time();
        $DB->update_record('oralassessment_attempts', $attempt);

        $expired = ((int)$activity->duration > 0 && time() >= ((int)$attempt->timestarted + (int)$activity->duration));
        $final = $turnnumber >= (int)$activity->rounds || $expired;
        $result = null;
        $aierror = null;
        try {
            $history = self::conversation_history($attempt->id);
            $result = $ai->after_response($activity, $history, (string)$questionturn->question, $transcript, $final);
            self::store_ai_result($attempt->id, $result);
        } catch (\Throwable $e) {
            $aierror = get_string('aifailed', 'mod_oralassessment');
            $DB->set_field('oralassessment_attempts', 'aierror', $aierror, ['id' => $attempt->id]);
        }

        if ($final) {
            self::finish_attempt($activity, $cm, $attempt->id);
            return [
                'finished' => true,
                'userturnid' => $userturn->id,
                'question' => null,
                'aierror' => $aierror,
                'result' => $result,
            ];
        }

        $questions = self::initial_questions($activity);
        $nextquestion = null;
        if (empty($activity->allowfollowup) && isset($questions[$turnnumber])) {
            $nextquestion = $questions[$turnnumber];
        } elseif ($result && !empty($result['next_question'])) {
            $nextquestion = $result['next_question'];
        }

        if (!$nextquestion && empty($activity->allowfollowup) && !isset($questions[$turnnumber])) {
            try {
                $history = self::conversation_history($attempt->id);
                $independent = $ai->next_independent_question($activity, $history);
                $nextquestion = $independent['next_question'];
                $result = $independent;
                self::store_ai_result($attempt->id, $independent);
            } catch (\Throwable $e) {
                $aierror = get_string('aifailed', 'mod_oralassessment');
                $DB->set_field('oralassessment_attempts', 'aierror', $aierror, ['id' => $attempt->id]);
            }
        }

        if (!$nextquestion) {
            self::finish_attempt($activity, $cm, $attempt->id);
            return [
                'finished' => true,
                'userturnid' => $userturn->id,
                'question' => null,
                'aierror' => $aierror,
                'result' => $result,
            ];
        }

        self::insert_question($attempt->id, $turnnumber + 1, $nextquestion,
            $result ? json_encode($result, JSON_UNESCAPED_UNICODE) : null);

        return [
            'finished' => false,
            'userturnid' => $userturn->id,
            'question' => $nextquestion,
            'aierror' => $aierror,
            'result' => $result,
        ];
    }

    /**
     * Mark an attempt submitted and update completion.
     *
     * @param stdClass $activity Activity.
     * @param stdClass $cm Course module.
     * @param int $attemptid Attempt id.
     */
    public static function finish_attempt(stdClass $activity, stdClass $cm, int $attemptid): void {
        global $DB;
        $now = time();
        $DB->set_field('oralassessment_attempts', 'status', self::STATUS_SUBMITTED, ['id' => $attemptid]);
        $DB->set_field('oralassessment_attempts', 'timesubmitted', $now, ['id' => $attemptid]);
        $DB->set_field('oralassessment_attempts', 'timemodified', $now, ['id' => $attemptid]);

        $attempt = $DB->get_record('oralassessment_attempts', ['id' => $attemptid], '*', MUST_EXIST);
        $event = event\attempt_submitted::create([
            'objectid' => $attemptid,
            'context' => context_module::instance($cm->id),
            'other' => ['oralassessmentid' => $activity->id, 'userid' => $attempt->userid],
        ]);
        $event->trigger();
        self::update_completion($activity, $cm, (int)$attempt->userid);
    }

    /**
     * Save explicit human review and apply a grade if one is supplied.
     *
     * @param stdClass $activity Activity.
     * @param stdClass $cm Course module.
     * @param int $attemptid Attempt id.
     * @param int $reviewerid Reviewer id.
     * @param float|null $grade Grade or null.
     * @param string $feedback Feedback.
     */
    public static function review_attempt(stdClass $activity, stdClass $cm, int $attemptid, int $reviewerid,
            ?float $grade, string $feedback): void {
        global $DB;

        $attempt = $DB->get_record('oralassessment_attempts', [
            'id' => $attemptid,
            'oralassessmentid' => $activity->id,
        ], '*', MUST_EXIST);
        if ($attempt->status === self::STATUS_INPROGRESS) {
            throw new moodle_exception('invalidattempt', 'mod_oralassessment');
        }
        if ($grade !== null && ((int)$activity->grade <= 0 || $grade < 0 || $grade > (float)$activity->grade)) {
            throw new moodle_exception('invalidgrade', 'grades');
        }

        $attempt->status = self::STATUS_REVIEWED;
        $attempt->reviewedby = $reviewerid;
        $attempt->timereviewed = time();
        $attempt->grade = $grade;
        $attempt->feedback = $feedback;
        $attempt->feedbackformat = FORMAT_HTML;
        $attempt->timemodified = time();
        $DB->update_record('oralassessment_attempts', $attempt);

        $event = event\attempt_reviewed::create([
            'objectid' => $attemptid,
            'context' => context_module::instance($cm->id),
            'relateduserid' => (int)$attempt->userid,
            'other' => ['oralassessmentid' => $activity->id],
        ]);
        $event->trigger();

        \oralassessment_update_grades($activity, (int)$attempt->userid, true);
        self::update_completion($activity, $cm, (int)$attempt->userid);
    }

    /**
     * Return ordered conversation history.
     *
     * @param int $attemptid Attempt id.
     * @return array
     */
    public static function conversation_history(int $attemptid): array {
        global $DB;
        $records = $DB->get_records('oralassessment_turns', ['attemptid' => $attemptid], 'id ASC');
        $history = [];
        foreach ($records as $turn) {
            if ($turn->role === 'assistant' && $turn->question !== null) {
                $history[] = ['role' => 'assistant', 'content' => (string)$turn->question];
            } elseif ($turn->role === 'user' && $turn->transcript !== null) {
                $history[] = ['role' => 'user', 'content' => (string)$turn->transcript];
            }
        }
        return $history;
    }

    /**
     * Current unanswered question.
     *
     * @param int $attemptid Attempt id.
     * @return stdClass|null
     */
    public static function current_question(int $attemptid): ?stdClass {
        global $DB;
        $sql = "SELECT t.*
                  FROM {oralassessment_turns} t
                 WHERE t.attemptid = :attemptid
                   AND t.role = :role
              ORDER BY t.id DESC";
        $record = $DB->get_record_sql($sql, ['attemptid' => $attemptid, 'role' => 'assistant'], IGNORE_MULTIPLE);
        return $record ?: null;
    }

    /**
     * Insert a question turn.
     *
     * @param int $attemptid Attempt id.
     * @param int $turnnumber Turn number.
     * @param string $question Question.
     * @param string|null $aijson Raw normalized AI JSON.
     */
    private static function insert_question(int $attemptid, int $turnnumber, string $question, ?string $aijson): void {
        global $DB;
        $DB->insert_record('oralassessment_turns', (object)[
            'attemptid' => $attemptid,
            'turnnumber' => $turnnumber,
            'role' => 'assistant',
            'question' => $question,
            'transcript' => null,
            'transcriptionmethod' => null,
            'aijson' => $aijson,
            'timecreated' => time(),
        ]);
    }

    /**
     * Persist advisory AI review aids.
     *
     * @param int $attemptid Attempt id.
     * @param array $result Normalized AI result.
     */
    private static function store_ai_result(int $attemptid, array $result): void {
        global $DB;
        $record = (object)[
            'id' => $attemptid,
            'aisummary' => (string)($result['summary'] ?? ''),
            'aievidence' => json_encode($result['evidence'] ?? [], JSON_UNESCAPED_UNICODE),
            'aireviewpoints' => json_encode($result['review_points'] ?? [], JSON_UNESCAPED_UNICODE),
            'aierror' => null,
            'timemodified' => time(),
        ];
        $DB->update_record('oralassessment_attempts', $record);
    }

    /**
     * Update Moodle activity completion state.
     *
     * @param stdClass $activity Activity.
     * @param stdClass $cm Course module.
     * @param int $userid User id.
     */
    public static function update_completion(stdClass $activity, stdClass $cm, int $userid): void {
        $course = get_course($activity->course);
        $completion = new completion_info($course);
        if ($completion->is_enabled($cm)) {
            $completion->update_state($cm, COMPLETION_UNKNOWN, $userid);
        }
    }
}
