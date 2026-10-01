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
 * Submit one transcript turn.
 *
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
require('../../../config.php');

use mod_oralassessment\ai\dialogue_service;
use mod_oralassessment\manager;

header('Content-Type: application/json; charset=utf-8');
try {
    require_sesskey();
    $attemptid = required_param('attemptid', PARAM_INT);
    $transcript = required_param('transcript', PARAM_RAW_TRIMMED);
    $method = optional_param('method', 'manual', PARAM_ALPHANUMEXT);
    $attempt = $DB->get_record('oralassessment_attempts', ['id' => $attemptid], '*', MUST_EXIST);
    $activity = $DB->get_record('oralassessment', ['id' => $attempt->oralassessmentid], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('oralassessment', $activity->id, $activity->course, false, MUST_EXIST);
    $course = get_course($cm->course);
    $context = context_module::instance($cm->id);
    require_login($course, true, $cm);
    require_capability('mod/oralassessment:view', $context);

    $result = manager::submit_response($activity, $cm, $attempt, $USER->id, $transcript, $method,
        new dialogue_service());
    $result['success'] = true;
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    $message = $e instanceof moodle_exception ? $e->getMessage() : get_string('aifailed', 'mod_oralassessment');
    echo json_encode(['success' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
}
