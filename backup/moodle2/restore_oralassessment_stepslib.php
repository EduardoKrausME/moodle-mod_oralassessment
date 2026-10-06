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
 * Restore structure for mod_oralassessment.
 *
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Activity structure restore step.
 */
class restore_oralassessment_activity_structure_step extends restore_activity_structure_step {
    /**
     * Define restore paths.
     *
     * @return array
     */
    protected function define_structure() {
        $paths = [new restore_path_element('oralassessment', '/activity/oralassessment')];
        if ($this->get_setting_value('userinfo')) {
            $paths[] = new restore_path_element('oralassessment_attempt', '/activity/oralassessment/attempts/attempt');
            $paths[] = new restore_path_element(
                'oralassessment_turn', '/activity/oralassessment/attempts/attempt/turns/turn');
        }
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Restore activity settings.
     *
     * @param array|stdClass $data Data.
     */
    protected function process_oralassessment($data) {
        global $DB;
        $data = (object)$data;
        $data->course = $this->get_courseid();
        $newitemid = $DB->insert_record('oralassessment', $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Restore a learner attempt.
     *
     * @param array|stdClass $data Data.
     */
    protected function process_oralassessment_attempt($data) {
        global $DB;
        $data = (object)$data;
        $data->oralassessmentid = $this->get_new_parentid('oralassessment');
        $data->userid = $this->get_mappingid('user', $data->userid, 0);
        if (!empty($data->reviewedby)) {
            $data->reviewedby = $this->get_mappingid('user', $data->reviewedby, 0);
        }
        $newitemid = $DB->insert_record('oralassessment_attempts', $data);
        $this->set_mapping('oralassessment_attempt', $data->id, $newitemid);
    }

    /**
     * Restore a conversation turn.
     *
     * @param array|stdClass $data Data.
     */
    protected function process_oralassessment_turn($data) {
        global $DB;
        $data = (object)$data;
        $data->attemptid = $this->get_new_parentid('oralassessment_attempt');
        $oldid = $data->id;
        $newitemid = $DB->insert_record('oralassessment_turns', $data);
        $this->set_mapping('oralassessment_turn', $oldid, $newitemid, true);
    }

    /**
     * Restore related files after all records and mappings exist.
     */
    protected function after_execute() {
        $this->add_related_files('mod_oralassessment', 'intro', null);
        $this->add_related_files('mod_oralassessment', 'attemptaudio', 'oralassessment_turn');
    }
}
