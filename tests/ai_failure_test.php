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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * AI failure handling tests.
 *
 * @coversNothing
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_oralassessment;

use mod_oralassessment\ai\dialogue_service;
final class ai_failure_test extends \advanced_testcase {
    /**
     * AI failures retain the learner transcript and submit the attempt for review.
     */
    public function test_ai_failure_does_not_lose_final_transcript(): void {
        global $DB;
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $activity = $this->getDataGenerator()->create_module('oralassessment', [
            'course' => $course->id,
            'initialquestions' => 'Explain it.',
            'rounds' => 1,
        ]);
        $cm = get_coursemodule_from_instance('oralassessment', $activity->id, $course->id, false, MUST_EXIST);
        $ai = new dialogue_service(static function(): string {
            throw new \moodle_exception('aifailed', 'mod_oralassessment');
        });

        [$attempt] = manager::start_attempt($activity, $cm, $user->id, $ai);
        $result = manager::submit_response($activity, $cm, $attempt, $user->id,
            'This transcript must survive the AI outage.', 'manual', $ai);

        $this->assertTrue($result['finished']);
        $this->assertNotEmpty($result['aierror']);
        $saved = $DB->get_record('oralassessment_turns', [
            'attemptid' => $attempt->id,
            'role' => 'user',
        ], '*', MUST_EXIST);
        $this->assertSame('This transcript must survive the AI outage.', $saved->transcript);
        $attempt = $DB->get_record('oralassessment_attempts', ['id' => $attempt->id], '*', MUST_EXIST);
        $this->assertSame(manager::STATUS_SUBMITTED, $attempt->status);
        $this->assertNotEmpty($attempt->aierror);
    }
}
