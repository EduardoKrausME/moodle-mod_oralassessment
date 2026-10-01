<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace mod_oralassessment;

/**
 * Teacher review and gradebook tests.
 *
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class teacher_review_test extends \advanced_testcase {
    /**
     * Load the activity gradebook callbacks used by the review flow.
     */
    public static function setUpBeforeClass(): void {
        global $CFG;
        require_once($CFG->dirroot . '/mod/oralassessment/lib.php');
        parent::setUpBeforeClass();
    }

    /**
     * Gradebook updates happen only after an explicit teacher review.
     */
    public function test_grade_is_applied_only_by_explicit_human_review(): void {
        global $DB;
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();
        $teacher = $this->getDataGenerator()->create_user();
        $activity = $this->getDataGenerator()->create_module('oralassessment', [
            'course' => $course->id,
            'grade' => 100,
        ]);
        $cm = get_coursemodule_from_instance('oralassessment', $activity->id, $course->id, false, MUST_EXIST);
        $attemptid = $DB->insert_record('oralassessment_attempts', (object)[
            'oralassessmentid' => $activity->id,
            'userid' => $student->id,
            'status' => manager::STATUS_SUBMITTED,
            'round' => 1,
            'timestarted' => time() - 120,
            'timesubmitted' => time() - 30,
            'timereviewed' => 0,
            'reviewedby' => 0,
            'grade' => null,
            'feedback' => null,
            'feedbackformat' => FORMAT_HTML,
            'timemodified' => time(),
        ]);

        $gradeitem = $DB->get_record('grade_items', [
            'courseid' => $course->id,
            'itemmodule' => 'oralassessment',
            'iteminstance' => $activity->id,
        ], '*', MUST_EXIST);
        $this->assertFalse($DB->record_exists('grade_grades', [
            'itemid' => $gradeitem->id,
            'userid' => $student->id,
        ]));

        manager::review_attempt($activity, $cm, $attemptid, $teacher->id, 82.5, '<p>Human feedback</p>');

        $attempt = $DB->get_record('oralassessment_attempts', ['id' => $attemptid], '*', MUST_EXIST);
        $this->assertSame(manager::STATUS_REVIEWED, $attempt->status);
        $this->assertSame($teacher->id, (int)$attempt->reviewedby);
        $this->assertEquals(82.5, (float)$attempt->grade);

        $grade = $DB->get_record('grade_grades', [
            'itemid' => $gradeitem->id,
            'userid' => $student->id,
        ], '*', MUST_EXIST);
        $this->assertEquals(82.5, (float)$grade->rawgrade);
    }
}
