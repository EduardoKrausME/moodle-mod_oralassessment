<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Test data generator for mod_oralassessment.
 *
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Oral assessment test generator.
 */
class mod_oralassessment_generator extends testing_module_generator {
    /**
     * Create an activity instance with useful defaults.
     *
     * @param array|stdClass|null $record Activity data.
     * @param array|null $options Course module options.
     * @return stdClass
     */
    public function create_instance($record = null, ?array $options = null) {
        $record = (object)(array)$record;
        $defaults = [
            'name' => 'Oral assessment',
            'intro' => '',
            'introformat' => FORMAT_HTML,
            'objectives' => 'Explain the configured subject accurately.',
            'criteria' => 'Accuracy and reasoning.',
            'initialquestions' => 'Explain the main idea.',
            'rounds' => 1,
            'duration' => 600,
            'allowfollowup' => 1,
            'rubric' => 'Use transcript evidence only.',
            'requirereview' => 1,
            'storeaudio' => 0,
            'transcriptionmode' => 'manual',
            'grade' => 100,
            'completionattempt' => 0,
            'completionreviewed' => 0,
        ];
        foreach ($defaults as $field => $value) {
            if (!property_exists($record, $field)) {
                $record->{$field} = $value;
            }
        }
        return parent::create_instance($record, $options);
    }
}
