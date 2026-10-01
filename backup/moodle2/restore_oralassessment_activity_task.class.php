<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Restore task for mod_oralassessment.
 *
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/restore_oralassessment_stepslib.php');

/**
 * Oral assessment restore task.
 */
class restore_oralassessment_activity_task extends restore_activity_task {
    /** Define task settings. */
    protected function define_my_settings() {
    }

    /** Define task steps. */
    protected function define_my_steps() {
        $this->add_step(new restore_oralassessment_activity_structure_step(
            'oralassessment_structure', 'oralassessment.xml'));
    }

    /** @return array */
    public static function define_decode_contents() {
        return [];
    }

    /** @return array */
    public static function define_decode_rules() {
        return [];
    }
}
