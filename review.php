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
 * Teacher attempt list.
 *
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

$id = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('oralassessment', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('oralassessment', ['id' => $cm->instance], '*', MUST_EXIST);
$context = context_module::instance($cm->id);
require_login($course, true, $cm);
require_capability('mod/oralassessment:reviewattempts', $context);

$PAGE->set_url('/mod/oralassessment/review.php', ['id' => $cm->id]);
$PAGE->set_title(get_string('reviewattempts', 'mod_oralassessment'));
$PAGE->set_heading(format_string($course->fullname));

$sql = "SELECT a.*, u.firstname, u.lastname
          FROM {oralassessment_attempts} a
          JOIN {user} u ON u.id = a.userid
         WHERE a.oralassessmentid = :activityid
      ORDER BY a.timemodified DESC";
$attempts = $DB->get_records_sql($sql, ['activityid' => $activity->id]);
$data = ['attempts' => [], 'hasattempts' => !empty($attempts)];
foreach ($attempts as $attempt) {
    $data['attempts'][] = [
        'student' => fullname($attempt),
        'status' => get_string($attempt->status, 'mod_oralassessment'),
        'started' => userdate($attempt->timestarted),
        'submitted' => $attempt->timesubmitted ? userdate($attempt->timesubmitted) : '-',
        'grade' => $attempt->grade === null ? get_string('notgraded', 'mod_oralassessment') : format_float($attempt->grade),
        'url' => (new moodle_url('/mod/oralassessment/review_attempt.php', [
            'id' => $cm->id,
            'attemptid' => $attempt->id,
        ]))->out(false),
    ];
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('reviewattempts', 'mod_oralassessment'));
echo $OUTPUT->render_from_template('mod_oralassessment/review_list', $data);
echo $OUTPUT->footer();
