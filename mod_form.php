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
 * Activity configuration form.
 *
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Oral assessment settings form.
 */
class mod_oralassessment_mod_form extends moodleform_mod {
    /**
     * Define the form.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('oralassessmentname', 'mod_oralassessment'), ['size' => 64]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $this->standard_intro_elements();

        $mform->addElement('textarea', 'objectives', get_string('objectives', 'mod_oralassessment'),
            ['rows' => 7, 'cols' => 70]);
        $mform->setType('objectives', PARAM_RAW_TRIMMED);
        $mform->addRule('objectives', null, 'required', null, 'client');
        $mform->addHelpButton('objectives', 'objectives', 'mod_oralassessment');

        $mform->addElement('textarea', 'criteria', get_string('criteria', 'mod_oralassessment'),
            ['rows' => 6, 'cols' => 70]);
        $mform->setType('criteria', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('criteria', 'criteria', 'mod_oralassessment');

        $mform->addElement('textarea', 'initialquestions', get_string('initialquestions', 'mod_oralassessment'),
            ['rows' => 6, 'cols' => 70]);
        $mform->setType('initialquestions', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('initialquestions', 'initialquestions', 'mod_oralassessment');

        $mform->addElement('text', 'rounds', get_string('rounds', 'mod_oralassessment'), ['size' => 5]);
        $mform->setType('rounds', PARAM_INT);
        $mform->setDefault('rounds', 3);

        $mform->addElement('duration', 'duration', get_string('duration', 'mod_oralassessment'), ['optional' => false]);
        $mform->setDefault('duration', 600);

        $mform->addElement('selectyesno', 'allowfollowup', get_string('allowfollowup', 'mod_oralassessment'));
        $mform->setDefault('allowfollowup', 1);

        $mform->addElement('textarea', 'rubric', get_string('rubric', 'mod_oralassessment'),
            ['rows' => 8, 'cols' => 70]);
        $mform->setType('rubric', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('rubric', 'rubric', 'mod_oralassessment');

        $mform->addElement('header', 'privacyheader', get_string('privacywarning', 'mod_oralassessment'));
        $mform->addElement('selectyesno', 'storeaudio', get_string('storeaudio', 'mod_oralassessment'));
        $mform->setDefault('storeaudio', 0);
        $mform->addHelpButton('storeaudio', 'storeaudio', 'mod_oralassessment');

        $transcriptionoptions = [
            'browser' => get_string('transcriptionbrowser', 'mod_oralassessment'),
            'manual' => get_string('transcriptionmanual', 'mod_oralassessment'),
        ];
        $mform->addElement('select', 'transcriptionmode', get_string('transcriptionmode', 'mod_oralassessment'),
            $transcriptionoptions);
        $mform->setDefault('transcriptionmode', 'browser');

        $mform->addElement('header', 'reviewheader', get_string('reviewattempts', 'mod_oralassessment'));
        $mform->addElement('selectyesno', 'requirereview', get_string('requirereview', 'mod_oralassessment'));
        $mform->setDefault('requirereview', 1);
        $mform->addHelpButton('requirereview', 'requirereview', 'mod_oralassessment');

        $mform->addElement('text', 'grade', get_string('maxgrade', 'mod_oralassessment'), ['size' => 6]);
        $mform->setType('grade', PARAM_INT);
        $mform->setDefault('grade', 100);

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Add custom completion rules.
     *
     * @return array
     */
    public function add_completion_rules() {
        $mform = $this->_form;
        $suffix = $this->get_suffix();

        $attempt = 'completionattempt' . $suffix;
        $reviewed = 'completionreviewed' . $suffix;
        $mform->addElement('checkbox', $attempt, '', get_string('completionattempt', 'mod_oralassessment'));
        $mform->addElement('checkbox', $reviewed, '', get_string('completionreviewed', 'mod_oralassessment'));

        return [$attempt, $reviewed];
    }

    /**
     * Whether any module completion rule is enabled.
     *
     * @param array $data Submitted form data.
     * @return bool
     */
    public function completion_rule_enabled($data) {
        $suffix = $this->get_suffix();
        return !empty($data['completionattempt' . $suffix]) || !empty($data['completionreviewed' . $suffix]);
    }

    /**
     * Clear custom completion values when automatic completion is not active.
     *
     * @param stdClass $data Form data.
     */
    public function data_postprocessing($data) {
        parent::data_postprocessing($data);
        if (!empty($data->completionunlocked)) {
            $suffix = $this->get_suffix();
            $completionfield = 'completion' . $suffix;
            $autocompletion = !empty($data->{$completionfield})
                && (int)$data->{$completionfield} === COMPLETION_TRACKING_AUTOMATIC;
            if (!$autocompletion) {
                $data->{'completionattempt' . $suffix} = 0;
                $data->{'completionreviewed' . $suffix} = 0;
            }
        }
    }

    /**
     * Validate form values.
     *
     * @param array $data Submitted values.
     * @param array $files Submitted files.
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if ((int)($data['rounds'] ?? 0) < 1 || (int)$data['rounds'] > 20) {
            $errors['rounds'] = get_string('errorrounds', 'mod_oralassessment');
        }
        if ((int)($data['duration'] ?? 0) < 30 || (int)$data['duration'] > 7200) {
            $errors['duration'] = get_string('errorduration', 'mod_oralassessment');
        }
        if ((int)($data['grade'] ?? 0) < 0 || (int)$data['grade'] > 1000) {
            $errors['grade'] = get_string('errorgrade', 'mod_oralassessment');
        }
        return $errors;
    }
}
