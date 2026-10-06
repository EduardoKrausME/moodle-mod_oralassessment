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

namespace mod_oralassessment;

use mod_oralassessment\ai\dialogue_service;

/**
 * Attempt lifecycle tests.
 *
 * @coversNothing
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class attempts_test extends \advanced_testcase {
    /**
     * A learner can start, answer and submit without losing the transcript.
     */
    public function test_attempt_lifecycle(): void {
        global $DB;
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $activity = $this->getDataGenerator()->create_module('oralassessment', [
            'course' => $course->id,
            'initialquestions' => "First question?\nSecond question?",
            'rounds' => 2,
            'allowfollowup' => 0,
        ]);
        $cm = get_coursemodule_from_instance('oralassessment', $activity->id, $course->id, false, MUST_EXIST);
        $ai = new dialogue_service(static function(array $messages): string {
            $payload = json_decode($messages[1]['content'], true);
            return json_encode([
                'next_question' => !empty($payload['final_turn']) ? null : 'Generated question?',
                'evidence' => [['criterion' => 'Accuracy', 'evidence' => 'Evidence from transcript', 'turn' => 1]],
                'summary' => 'Formative summary',
                'review_points' => ['Check the explanation.'],
                'complete' => !empty($payload['final_turn']),
            ]);
        });

        [$attempt, $question] = manager::start_attempt($activity, $cm, $user->id, $ai);
        $this->assertSame(manager::STATUS_INPROGRESS, $attempt->status);
        $this->assertSame('First question?', $question->question);

        $result = manager::submit_response($activity, $cm, $attempt, $user->id,
            'My first spoken response.', 'manual', $ai);
        $this->assertFalse($result['finished']);
        $this->assertSame('Second question?', $result['question']);

        $attempt = $DB->get_record('oralassessment_attempts', ['id' => $attempt->id], '*', MUST_EXIST);
        $result = manager::submit_response($activity, $cm, $attempt, $user->id,
            'My second spoken response.', 'browser', $ai);
        $this->assertTrue($result['finished']);

        $attempt = $DB->get_record('oralassessment_attempts', ['id' => $attempt->id], '*', MUST_EXIST);
        $this->assertSame(manager::STATUS_SUBMITTED, $attempt->status);
        $this->assertSame(2, (int)$attempt->round);
        $this->assertSame(2, $DB->count_records('oralassessment_turns', [
            'attemptid' => $attempt->id,
            'role' => 'user',
        ]));
        $this->assertNotEmpty($attempt->aisummary);
    }
}
