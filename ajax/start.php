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
 * Start or resume an attempt.
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
    $cmid = required_param('cmid', PARAM_INT);
    $cm = get_coursemodule_from_id('oralassessment', $cmid, 0, false, MUST_EXIST);
    $course = get_course($cm->course);
    $activity = $DB->get_record('oralassessment', ['id' => $cm->instance], '*', MUST_EXIST);
    $context = context_module::instance($cm->id);
    require_login($course, true, $cm);
    require_capability('mod/oralassessment:view', $context);

    [$attempt, $question] = manager::start_attempt($activity, $cm, $USER->id, new dialogue_service());
    echo json_encode([
        'success' => true,
        'attemptid' => (int)$attempt->id,
        'started' => (int)$attempt->timestarted,
        'status' => $attempt->status,
        'question' => $question ? (string)$question->question : null,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => get_string('aifailed', 'mod_oralassessment')], JSON_UNESCAPED_UNICODE);
}
