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
 * Main activity page.
 *
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once(__DIR__ . '/lib.php');

use mod_oralassessment\event\course_module_viewed;
use mod_oralassessment\manager;
use mod_oralassessment\presenter;

$id = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('oralassessment', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('oralassessment', ['id' => $cm->instance], '*', MUST_EXIST);
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability('mod/oralassessment:view', $context);

$PAGE->set_url('/mod/oralassessment/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($activity->name));
$PAGE->set_heading(format_string($course->fullname));

$completion = new completion_info($course);
$completion->set_module_viewed($cm);
$event = course_module_viewed::create([
    'objectid' => $activity->id,
    'context' => $context,
]);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('oralassessment', $activity);
$event->trigger();

$attempt = $DB->get_record('oralassessment_attempts', [
    'oralassessmentid' => $activity->id,
    'userid' => $USER->id,
]);

$data = [
    'name' => format_string($activity->name),
    'intro' => format_module_intro('oralassessment', $activity, $cm->id),
    'privacywarning' => get_string('privacywarning', 'mod_oralassessment'),
    'storeaudio' => !empty($activity->storeaudio),
    'recordingnotice' => !empty($activity->storeaudio)
        ? get_string('recordingnotice', 'mod_oralassessment')
        : get_string('transcriptonlynotice', 'mod_oralassessment'),
    'canreview' => has_capability('mod/oralassessment:reviewattempts', $context),
    'reviewurl' => (new moodle_url('/mod/oralassessment/review.php', ['id' => $cm->id]))->out(false),
];

if ($attempt && $attempt->status !== manager::STATUS_INPROGRESS) {
    $data['finished'] = true;
    $data['attempt'] = presenter::attempt($attempt, $context);
    $data['awaitingreview'] = !empty($activity->requirereview) && $attempt->status === manager::STATUS_SUBMITTED;
    $data['reviewoptional'] = empty($activity->requirereview) && $attempt->status === manager::STATUS_SUBMITTED;
} else {
    $data['finished'] = false;
    $data['attemptid'] = $attempt ? (int)$attempt->id : 0;
    $data['currentquestion'] = '';
    $data['hascurrentquestion'] = false;
    $data['showrecordbutton'] = !empty($activity->storeaudio) || $activity->transcriptionmode === 'browser';
    if ($attempt) {
        $question = manager::current_question($attempt->id);
        $data['currentquestion'] = $question ? (string)$question->question : '';
        $data['hascurrentquestion'] = $question !== null;
        $data['started'] = (int)$attempt->timestarted;
    }
    $PAGE->requires->js_call_amd('mod_oralassessment/oralassessment', 'init', [[
        'cmid' => (int)$cm->id,
        'attemptid' => $attempt ? (int)$attempt->id : 0,
        'started' => $attempt ? (int)$attempt->timestarted : 0,
        'duration' => (int)$activity->duration,
        'storeaudio' => !empty($activity->storeaudio),
        'transcriptionmode' => (string)$activity->transcriptionmode,
        'starturl' => (new moodle_url('/mod/oralassessment/ajax/start.php'))->out(false),
        'submiturl' => (new moodle_url('/mod/oralassessment/ajax/submit.php'))->out(false),
        'uploadurl' => (new moodle_url('/mod/oralassessment/ajax/upload.php'))->out(false),
        'sesskey' => sesskey(),
        'emptytranscript' => get_string('emptytranscript', 'mod_oralassessment'),
        'strings' => [
            'record' => get_string('record', 'mod_oralassessment'),
            'stop' => get_string('stoprecording', 'mod_oralassessment'),
            'finished' => get_string('assessmentfinished', 'mod_oralassessment'),
            'aierror' => get_string('aifailed', 'mod_oralassessment'),
            'consentrequired' => get_string('consentrequired', 'mod_oralassessment'),
        ],
    ]]);
}

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_oralassessment/student', $data);
echo $OUTPUT->footer();
