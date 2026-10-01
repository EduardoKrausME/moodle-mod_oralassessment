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
 * Human review form.
 *
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_oralassessment\form;

use moodleform;

defined('MOODLE_INTERNAL') || die;

require_once($GLOBALS['CFG']->libdir . '/formslib.php');

/**
 * Explicit human review and grade form.
 */
class review_form extends moodleform {
    /**
     * Define form.
     */
    protected function definition() {
        $mform = $this->_form;
        $maxgrade = (float)$this->_customdata['maxgrade'];

        $mform->addElement('text', 'grade', get_string('grade', 'mod_oralassessment'));
        $mform->setType('grade', PARAM_FLOAT);
        if ($maxgrade <= 0) {
            $mform->freeze('grade');
            $mform->setDefault('grade', '');
        }

        $mform->addElement('editor', 'feedback_editor', get_string('feedback', 'mod_oralassessment'), null,
            ['maxfiles' => 0, 'maxbytes' => 0]);
        $mform->addElement('hidden', 'attemptid');
        $mform->setType('attemptid', PARAM_INT);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $this->add_action_buttons(true, get_string('savereview', 'mod_oralassessment'));
    }

    /**
     * Validate grade range.
     *
     * @param array $data Data.
     * @param array $files Files.
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $maxgrade = (float)$this->_customdata['maxgrade'];
        $grade = $data['grade'] ?? '';
        if ($grade !== '' && ($maxgrade <= 0 || (float)$grade < 0 || (float)$grade > $maxgrade)) {
            $errors['grade'] = get_string('invalidgrade', 'grades');
        }
        return $errors;
    }
}
