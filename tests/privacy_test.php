<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace mod_oralassessment;

use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use mod_oralassessment\privacy\provider;

/**
 * Privacy API tests.
 *
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class privacy_test extends \advanced_testcase {
    /**
     * Privacy discovery includes learners/reviewers and learner deletion removes stored audio.
     */
    public function test_context_discovery_userlist_and_deletion_remove_audio(): void {
        global $DB;
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $learner = $this->getDataGenerator()->create_user();
        $reviewer = $this->getDataGenerator()->create_user();
        $activity = $this->getDataGenerator()->create_module('oralassessment', [
            'course' => $course->id,
            'storeaudio' => 1,
        ]);
        $context = \context_module::instance($activity->cmid);
        $attemptid = $DB->insert_record('oralassessment_attempts', (object)[
            'oralassessmentid' => $activity->id,
            'userid' => $learner->id,
            'status' => manager::STATUS_REVIEWED,
            'round' => 1,
            'timestarted' => time() - 120,
            'timesubmitted' => time() - 60,
            'timereviewed' => time(),
            'reviewedby' => $reviewer->id,
            'aisummary' => 'Summary',
            'aievidence' => '[]',
            'aireviewpoints' => '[]',
            'grade' => 70,
            'feedback' => 'Reviewed',
            'feedbackformat' => FORMAT_PLAIN,
            'timemodified' => time(),
        ]);
        $turnid = $DB->insert_record('oralassessment_turns', (object)[
            'attemptid' => $attemptid,
            'turnnumber' => 1,
            'role' => 'user',
            'question' => null,
            'transcript' => 'Personal transcript',
            'transcriptionmethod' => 'browser',
            'aijson' => null,
            'timecreated' => time(),
        ]);
        $fs = get_file_storage();
        $fs->create_file_from_string([
            'contextid' => $context->id,
            'component' => 'mod_oralassessment',
            'filearea' => 'attemptaudio',
            'itemid' => $turnid,
            'filepath' => '/',
            'filename' => 'response.webm',
        ], 'fake audio bytes');

        $contexts = provider::get_contexts_for_userid($learner->id);
        $this->assertContains($context->id, $contexts->get_contextids());
        $reviewcontexts = provider::get_contexts_for_userid($reviewer->id);
        $this->assertContains($context->id, $reviewcontexts->get_contextids());

        $userlist = new userlist($context, 'mod_oralassessment');
        provider::get_users_in_context($userlist);
        $userids = $userlist->get_userids();
        $this->assertContains($learner->id, $userids);
        $this->assertContains($reviewer->id, $userids);

        $approved = new approved_contextlist($learner, 'mod_oralassessment', [$context->id]);
        provider::delete_data_for_user($approved);
        $this->assertFalse($DB->record_exists('oralassessment_attempts', ['id' => $attemptid]));
        $this->assertFalse($DB->record_exists('oralassessment_turns', ['id' => $turnid]));
        $this->assertEmpty($fs->get_area_files($context->id, 'mod_oralassessment', 'attemptaudio', $turnid,
            'id ASC', false));
    }

    /**
     * Reviewer deletion anonymises the reviewer reference without deleting learner evidence.
     */
    public function test_reviewer_deletion_anonymises_review_without_deleting_learner_attempt(): void {
        global $DB;
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $learner = $this->getDataGenerator()->create_user();
        $reviewer = $this->getDataGenerator()->create_user();
        $activity = $this->getDataGenerator()->create_module('oralassessment', ['course' => $course->id]);
        $context = \context_module::instance($activity->cmid);
        $attemptid = $DB->insert_record('oralassessment_attempts', (object)[
            'oralassessmentid' => $activity->id,
            'userid' => $learner->id,
            'status' => manager::STATUS_REVIEWED,
            'round' => 1,
            'timestarted' => time() - 120,
            'timesubmitted' => time() - 60,
            'timereviewed' => time(),
            'reviewedby' => $reviewer->id,
            'timemodified' => time(),
        ]);

        $approved = new approved_userlist($context, 'mod_oralassessment', [$reviewer->id]);
        provider::delete_data_for_users($approved);
        $attempt = $DB->get_record('oralassessment_attempts', ['id' => $attemptid], '*', MUST_EXIST);
        $this->assertSame(0, (int)$attempt->reviewedby);
        $this->assertSame($learner->id, (int)$attempt->userid);
    }
}
