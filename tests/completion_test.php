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
 * Custom completion tests.
 *
 * @coversNothing
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_oralassessment;

use mod_oralassessment\completion\custom_completion;
final class completion_test extends \advanced_testcase {
    /**
     * Attempt submission and teacher review independently satisfy their completion rules.
     */
    public function test_attempt_and_review_completion_rules(): void {
        global $DB, $CFG;
        $this->resetAfterTest();
        $CFG->enablecompletion = 1;
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $user = $this->getDataGenerator()->create_user();
        $activity = $this->getDataGenerator()->create_module('oralassessment', [
            'course' => $course->id,
            'completionattempt' => 1,
            'completionreviewed' => 1,
        ], [
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
        ]);

        $cm = get_fast_modinfo($course, $user->id)->get_cm($activity->cmid);
        $completion = new custom_completion($cm, $user->id);
        $this->assertSame(COMPLETION_INCOMPLETE, $completion->get_state('completionattempt'));
        $this->assertSame(COMPLETION_INCOMPLETE, $completion->get_state('completionreviewed'));

        $attemptid = $DB->insert_record('oralassessment_attempts', (object)[
            'oralassessmentid' => $activity->id,
            'userid' => $user->id,
            'status' => manager::STATUS_SUBMITTED,
            'round' => 1,
            'timestarted' => time() - 60,
            'timesubmitted' => time(),
            'timereviewed' => 0,
            'reviewedby' => 0,
            'timemodified' => time(),
        ]);
        $this->assertSame(COMPLETION_COMPLETE, $completion->get_state('completionattempt'));
        $this->assertSame(COMPLETION_INCOMPLETE, $completion->get_state('completionreviewed'));

        $DB->set_field('oralassessment_attempts', 'status', manager::STATUS_REVIEWED, ['id' => $attemptid]);
        $this->assertSame(COMPLETION_COMPLETE, $completion->get_state('completionreviewed'));
    }
}
