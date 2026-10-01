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
 * Human review of one oral assessment attempt.
 *
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

use mod_oralassessment\form\review_form;
use mod_oralassessment\manager;
use mod_oralassessment\presenter;

$id = required_param('id', PARAM_INT);
$attemptid = required_param('attemptid', PARAM_INT);
$cm = get_coursemodule_from_id('oralassessment', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('oralassessment', ['id' => $cm->instance], '*', MUST_EXIST);
$context = context_module::instance($cm->id);
$attempt = $DB->get_record('oralassessment_attempts', [
    'id' => $attemptid,
    'oralassessmentid' => $activity->id,
], '*', MUST_EXIST);
$user = $DB->get_record('user', ['id' => $attempt->userid], '*', MUST_EXIST);

require_login($course, true, $cm);
require_capability('mod/oralassessment:reviewattempts', $context);

$PAGE->set_url('/mod/oralassessment/review_attempt.php', ['id' => $cm->id, 'attemptid' => $attempt->id]);
$PAGE->set_title(get_string('reviewattempt', 'mod_oralassessment'));
$PAGE->set_heading(format_string($course->fullname));

$form = new review_form(null, ['maxgrade' => (float)$activity->grade]);
$form->set_data((object)[
    'id' => $cm->id,
    'attemptid' => $attempt->id,
    'grade' => $attempt->grade,
    'feedback_editor' => [
        'text' => (string)$attempt->feedback,
        'format' => (int)$attempt->feedbackformat,
    ],
]);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/mod/oralassessment/review.php', ['id' => $cm->id]));
} elseif ($data = $form->get_data()) {
    $rawgrade = $data->grade ?? '';
    $grade = ($rawgrade === '' || $rawgrade === null) ? null : (float)$rawgrade;
    $feedback = $data->feedback_editor['text'] ?? '';
    manager::review_attempt($activity, $cm, $attempt->id, $USER->id, $grade, $feedback);
    redirect(new moodle_url('/mod/oralassessment/review_attempt.php', [
        'id' => $cm->id,
        'attemptid' => $attempt->id,
    ]), get_string('reviewed', 'mod_oralassessment'));
}

$attempt = $DB->get_record('oralassessment_attempts', ['id' => $attempt->id], '*', MUST_EXIST);
$view = presenter::attempt($attempt, $context);
$view['student'] = fullname($user);
$view['aigeneratednotice'] = get_string('aigeneratednotice', 'mod_oralassessment');

echo $OUTPUT->header();
echo $OUTPUT->heading(fullname($user));
echo $OUTPUT->render_from_template('mod_oralassessment/review_attempt', $view);
$form->display();
echo $OUTPUT->footer();
