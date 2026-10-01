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
 * Capability tests.
 *
 * @coversNothing
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_oralassessment;
final class permissions_test extends \advanced_testcase {
    /**
     * Default role archetypes receive only the expected activity capabilities.
     */
    public function test_student_and_teacher_archetypes(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $activity = $this->getDataGenerator()->create_module('oralassessment', ['course' => $course->id]);
        $context = \context_module::instance($activity->cmid);
        $coursecontext = \context_course::instance($course->id);

        $this->assertTrue(has_capability('mod/oralassessment:view', $context, $student));
        $this->assertFalse(has_capability('mod/oralassessment:reviewattempts', $context, $student));
        $this->assertTrue(has_capability('mod/oralassessment:view', $context, $teacher));
        $this->assertTrue(has_capability('mod/oralassessment:reviewattempts', $context, $teacher));
        $this->assertTrue(has_capability('mod/oralassessment:addinstance', $coursecontext, $teacher));
    }
}
