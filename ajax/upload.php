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
 * Store optional learner audio. No transcription or AI provider call occurs here.
 *
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
require('../../../config.php');

header('Content-Type: application/json; charset=utf-8');
try {
    require_sesskey();
    $attemptid = required_param('attemptid', PARAM_INT);
    $turnid = required_param('turnid', PARAM_INT);
    $consent = required_param('consent', PARAM_BOOL);
    $attempt = $DB->get_record('oralassessment_attempts', ['id' => $attemptid], '*', MUST_EXIST);
    $activity = $DB->get_record('oralassessment', ['id' => $attempt->oralassessmentid], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('oralassessment', $activity->id, $activity->course, false, MUST_EXIST);
    $course = get_course($cm->course);
    $context = context_module::instance($cm->id);
    require_login($course, true, $cm);
    require_capability('mod/oralassessment:view', $context);

    if ((int)$attempt->userid !== (int)$USER->id || empty($activity->storeaudio) || !$consent) {
        throw new moodle_exception('nopermissions', 'error');
    }
    $turn = $DB->get_record('oralassessment_turns', [
        'id' => $turnid,
        'attemptid' => $attempt->id,
        'role' => 'user',
    ], '*', MUST_EXIST);
    if (empty($_FILES['audio']) || !is_uploaded_file($_FILES['audio']['tmp_name'])) {
        throw new moodle_exception('audiouploadfailed', 'mod_oralassessment');
    }
    if ((int)$_FILES['audio']['size'] > 20 * 1024 * 1024) {
        throw new moodle_exception('audiouploadfailed', 'mod_oralassessment');
    }
    $allowed = [
        'audio/webm' => 'webm',
        'video/webm' => 'webm',
        'audio/ogg' => 'ogg',
        'audio/mp4' => 'm4a',
        'video/mp4' => 'm4a',
        'audio/mpeg' => 'mp3',
        'audio/wav' => 'wav',
        'audio/x-wav' => 'wav',
    ];
    if (!function_exists('finfo_open')) {
        throw new moodle_exception('audiouploadfailed', 'mod_oralassessment');
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimetype = $finfo ? (string)finfo_file($finfo, $_FILES['audio']['tmp_name']) : '';
    if ($finfo) {
        finfo_close($finfo);
    }
    if (!array_key_exists($mimetype, $allowed)) {
        throw new moodle_exception('audiouploadfailed', 'mod_oralassessment');
    }

    $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'mod_oralassessment', 'attemptaudio', $turn->id);
    $filename = 'response.' . $allowed[$mimetype];
    $fileinfo = [
        'contextid' => $context->id,
        'component' => 'mod_oralassessment',
        'filearea' => 'attemptaudio',
        'itemid' => $turn->id,
        'filepath' => '/',
        'filename' => $filename,
        'userid' => $USER->id,
    ];
    $fs->create_file_from_pathname($fileinfo, $_FILES['audio']['tmp_name']);
    echo json_encode(['success' => true, 'message' => get_string('audiostored', 'mod_oralassessment')]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => get_string('audiouploadfailed', 'mod_oralassessment')]);
}
