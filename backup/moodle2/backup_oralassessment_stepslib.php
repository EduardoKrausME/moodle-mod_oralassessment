<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Backup structure for mod_oralassessment.
 *
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Activity structure backup step.
 */
class backup_oralassessment_activity_structure_step extends backup_activity_structure_step {
    /**
     * Define the activity backup tree.
     *
     * @return backup_nested_element
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value('userinfo');

        $activity = new backup_nested_element('oralassessment', ['id'], [
            'name', 'intro', 'introformat', 'objectives', 'criteria', 'initialquestions', 'rounds', 'duration',
            'allowfollowup', 'rubric', 'requirereview', 'storeaudio', 'transcriptionmode', 'grade',
            'completionattempt', 'completionreviewed', 'timecreated', 'timemodified',
        ]);
        $attempts = new backup_nested_element('attempts');
        $attempt = new backup_nested_element('attempt', ['id'], [
            'userid', 'status', 'round', 'timestarted', 'timesubmitted', 'timereviewed', 'reviewedby',
            'aisummary', 'aievidence', 'aireviewpoints', 'aierror', 'grade', 'feedback', 'feedbackformat', 'timemodified',
        ]);
        $turns = new backup_nested_element('turns');
        $turn = new backup_nested_element('turn', ['id'], [
            'turnnumber', 'role', 'question', 'transcript', 'transcriptionmethod', 'aijson', 'timecreated',
        ]);

        $activity->add_child($attempts);
        $attempts->add_child($attempt);
        $attempt->add_child($turns);
        $turns->add_child($turn);

        $activity->set_source_table('oralassessment', ['id' => backup::VAR_ACTIVITYID]);
        if ($userinfo) {
            $attempt->set_source_table('oralassessment_attempts', ['oralassessmentid' => backup::VAR_PARENTID]);
            $turn->set_source_table('oralassessment_turns', ['attemptid' => backup::VAR_PARENTID]);
            $attempt->annotate_ids('user', 'userid');
            $attempt->annotate_ids('user', 'reviewedby');
            $turn->annotate_files('mod_oralassessment', 'attemptaudio', 'id');
        }

        $activity->annotate_files('mod_oralassessment', 'intro', null);
        return $this->prepare_activity_structure($activity);
    }
}
