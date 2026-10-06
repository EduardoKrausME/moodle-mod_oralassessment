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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Privacy provider for mod_oralassessment.
 *
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_oralassessment\privacy;

use context;
use context_module;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Implements Moodle's Privacy API for attempts, transcripts, reviews and optional audio.
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider,
        \core_privacy\local\request\core_userlist_provider {

    /**
     * Describe stored and transmitted data.
     *
     * @param collection $collection Metadata collection.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('oralassessment_attempts', [
            'userid' => 'privacy:metadata:attempts:userid',
            'status' => 'privacy:metadata:attempts:status',
            'timestarted' => 'privacy:metadata:attempts:timestarted',
            'timesubmitted' => 'privacy:metadata:attempts:timesubmitted',
            'timereviewed' => 'privacy:metadata:attempts:timereviewed',
            'reviewedby' => 'privacy:metadata:attempts:reviewedby',
            'aisummary' => 'privacy:metadata:attempts:aisummary',
            'aievidence' => 'privacy:metadata:attempts:aievidence',
            'aireviewpoints' => 'privacy:metadata:attempts:aireviewpoints',
            'aierror' => 'privacy:metadata:attempts:aierror',
            'grade' => 'privacy:metadata:attempts:grade',
            'feedback' => 'privacy:metadata:attempts:feedback',
            'timemodified' => 'privacy:metadata:attempts:timemodified',
        ], 'privacy:metadata:attempts');

        $collection->add_database_table('oralassessment_turns', [
            'role' => 'privacy:metadata:turns:role',
            'question' => 'privacy:metadata:turns:question',
            'transcript' => 'privacy:metadata:turns:transcript',
            'transcriptionmethod' => 'privacy:metadata:turns:transcriptionmethod',
            'aijson' => 'privacy:metadata:turns:aijson',
            'timecreated' => 'privacy:metadata:turns:timecreated',
        ], 'privacy:metadata:turns');

        $collection->add_subsystem_link('core_files', [], 'privacy:metadata:audio');
        $collection->add_external_location_link('ai_bridge', [
            'objectives' => 'privacy:metadata:aibridge:objectives',
            'criteria' => 'privacy:metadata:aibridge:criteria',
            'rubric' => 'privacy:metadata:aibridge:rubric',
            'conversation' => 'privacy:metadata:aibridge:conversation',
            'previousquestion' => 'privacy:metadata:aibridge:previousquestion',
        ], 'privacy:metadata:aibridge');

        return $collection;
    }

    /**
     * Return activity contexts containing this user's data.
     *
     * @param int $userid User id.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $sql = "SELECT DISTINCT ctx.id
                  FROM {context} ctx
                  JOIN {course_modules} cm
                    ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextlevel
                  JOIN {modules} m
                    ON m.id = cm.module AND m.name = :modname
                  JOIN {oralassessment_attempts} a
                    ON a.oralassessmentid = cm.instance
                 WHERE a.userid = :learnerid OR a.reviewedby = :reviewerid";
        $contextlist->add_from_sql($sql, [
            'contextlevel' => CONTEXT_MODULE,
            'modname' => 'oralassessment',
            'learnerid' => $userid,
            'reviewerid' => $userid,
        ]);
        return $contextlist;
    }

    /**
     * Populate users that have data in a module context.
     *
     * @param userlist $userlist User list.
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();
        if (!$context instanceof context_module) {
            return;
        }
        $cm = get_coursemodule_from_id('oralassessment', $context->instanceid);
        if (!$cm) {
            return;
        }
        $userlist->add_from_sql('userid',
            'SELECT userid FROM {oralassessment_attempts} WHERE oralassessmentid = :activityid',
            ['activityid' => $cm->instance]);
        $userlist->add_from_sql(
            'userid',
            'SELECT reviewedby AS userid
               FROM {oralassessment_attempts}
              WHERE oralassessmentid = :activityid AND reviewedby <> 0',
            ['activityid' => $cm->instance]
        );
    }

    /**
     * Export data for a user.
     *
     * Learner transcripts are exported only to that learner. A reviewer receives only metadata about reviews they
     * performed; another learner's transcript is never exported as the reviewer's personal data.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $userid = (int)$contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_module) {
                continue;
            }
            $cm = get_coursemodule_from_id('oralassessment', $context->instanceid);
            if (!$cm) {
                continue;
            }

            $attempt = $DB->get_record('oralassessment_attempts', [
                'oralassessmentid' => $cm->instance,
                'userid' => $userid,
            ]);
            if ($attempt) {
                $turns = $DB->get_records('oralassessment_turns', ['attemptid' => $attempt->id], 'id ASC');
                $turnexport = [];
                foreach ($turns as $turn) {
                    $turnexport[] = [
                        'turnnumber' => (int)$turn->turnnumber,
                        'role' => (string)$turn->role,
                        'question' => (string)$turn->question,
                        'transcript' => (string)$turn->transcript,
                        'transcriptionmethod' => (string)$turn->transcriptionmethod,
                        'timecreated' => transform::datetime($turn->timecreated),
                    ];
                    if ($turn->role === 'user') {
                        writer::with_context($context)->export_area_files(
                            [
                                get_string('pluginname', 'mod_oralassessment'),
                                get_string('attempt', 'mod_oralassessment'),
                                get_string('audio', 'mod_oralassessment') . '-' . (int)$turn->turnnumber,
                            ],
                            'mod_oralassessment',
                            'attemptaudio',
                            $turn->id
                        );
                    }
                }

                $data = (object)[
                    'status' => (string)$attempt->status,
                    'timestarted' => transform::datetime($attempt->timestarted),
                    'timesubmitted' => $attempt->timesubmitted ? transform::datetime($attempt->timesubmitted) : null,
                    'timereviewed' => $attempt->timereviewed ? transform::datetime($attempt->timereviewed) : null,
                    'aisummary' => (string)$attempt->aisummary,
                    'aievidence' => (string)$attempt->aievidence,
                    'aireviewpoints' => (string)$attempt->aireviewpoints,
                    'aierror' => (string)$attempt->aierror,
                    'grade' => $attempt->grade,
                    'feedback' => (string)$attempt->feedback,
                    'turns' => $turnexport,
                ];
                writer::with_context($context)->export_data(
                    [get_string('pluginname', 'mod_oralassessment'), get_string('attempt', 'mod_oralassessment')],
                    $data
                );
            }

            $reviews = $DB->get_records('oralassessment_attempts', [
                'oralassessmentid' => $cm->instance,
                'reviewedby' => $userid,
            ], 'timereviewed ASC', 'id,timereviewed');
            if ($reviews) {
                $reviewexport = [];
                foreach ($reviews as $review) {
                    $reviewexport[] = [
                        'attemptid' => (int)$review->id,
                        'timereviewed' => transform::datetime($review->timereviewed),
                    ];
                }
                writer::with_context($context)->export_data(
                    [get_string('pluginname', 'mod_oralassessment'), get_string('reviewsperformed', 'mod_oralassessment')],
                    (object)['reviews' => $reviewexport]
                );
            }
        }
    }

    /**
     * Delete all user data in an activity context.
     *
     * @param context $context Context.
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        global $DB;
        if (!$context instanceof context_module) {
            return;
        }
        $cm = get_coursemodule_from_id('oralassessment', $context->instanceid);
        if (!$cm) {
            return;
        }
        self::delete_attempts($context, ['oralassessmentid' => $cm->instance]);
    }

    /**
     * Delete approved user's data.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;
        $userid = (int)$contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_module) {
                continue;
            }
            $cm = get_coursemodule_from_id('oralassessment', $context->instanceid);
            if (!$cm) {
                continue;
            }
            self::delete_attempts($context, ['oralassessmentid' => $cm->instance, 'userid' => $userid]);
            $DB->set_field_select('oralassessment_attempts', 'reviewedby', 0,
                'oralassessmentid = :activityid AND reviewedby = :reviewerid',
                ['activityid' => $cm->instance, 'reviewerid' => $userid]);
        }
    }

    /**
     * Delete data for a list of users in one activity context.
     *
     * @param approved_userlist $userlist Approved user list.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;
        $context = $userlist->get_context();
        if (!$context instanceof context_module) {
            return;
        }
        $cm = get_coursemodule_from_id('oralassessment', $context->instanceid);
        if (!$cm) {
            return;
        }
        $userids = array_map('intval', $userlist->get_userids());
        if (!$userids) {
            return;
        }
        [$insql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'uid');
        $params['activityid'] = $cm->instance;
        $attempts = $DB->get_records_select('oralassessment_attempts',
            "oralassessmentid = :activityid AND userid {$insql}", $params);
        self::delete_attempt_records($context, $attempts);

        [$reviewsql, $reviewparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'rid');
        $reviewparams['activityid'] = $cm->instance;
        $DB->set_field_select('oralassessment_attempts', 'reviewedby', 0,
            "oralassessmentid = :activityid AND reviewedby {$reviewsql}", $reviewparams);
    }

    /**
     * Delete attempts matching simple record conditions.
     *
     * @param context_module $context Context.
     * @param array $conditions Conditions.
     */
    private static function delete_attempts(context_module $context, array $conditions): void {
        global $DB;
        $attempts = $DB->get_records('oralassessment_attempts', $conditions);
        self::delete_attempt_records($context, $attempts);
    }

    /**
     * Delete attempts, dependent turns and their optional audio files.
     *
     * @param context_module $context Context.
     * @param array $attempts Attempt records.
     */
    private static function delete_attempt_records(context_module $context, array $attempts): void {
        global $DB;
        if (!$attempts) {
            return;
        }
        $fs = get_file_storage();
        foreach ($attempts as $attempt) {
            $turnids = $DB->get_fieldset_select('oralassessment_turns', 'id', 'attemptid = ?', [$attempt->id]);
            foreach ($turnids as $turnid) {
                $fs->delete_area_files($context->id, 'mod_oralassessment', 'attemptaudio', (int)$turnid);
            }
            $DB->delete_records('oralassessment_turns', ['attemptid' => $attempt->id]);
            $DB->delete_records('oralassessment_attempts', ['id' => $attempt->id]);
        }
    }
}
