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
 * Core callbacks for mod_oralassessment.
 *
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_oralassessment\manager;

/**
 * Supported Moodle features.
 *
 * @param string $feature Feature constant.
 * @return mixed
 */
function oralassessment_supports($feature) {
    if (defined('FEATURE_MOD_PURPOSE') && $feature === FEATURE_MOD_PURPOSE) {
        return MOD_PURPOSE_ASSESSMENT;
    }

    switch ($feature) {
        case FEATURE_MOD_INTRO:
        case FEATURE_SHOW_DESCRIPTION:
        case FEATURE_COMPLETION_TRACKS_VIEWS:
        case FEATURE_COMPLETION_HAS_RULES:
        case FEATURE_GRADE_HAS_GRADE:
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_OTHER;
        default:
            return null;
    }
}

/**
 * Add activity instance.
 *
 * @param stdClass $data Form data.
 * @param moodleform|null $mform Form instance.
 * @return int
 */
function oralassessment_add_instance($data, $mform = null) {
    return manager::add_instance($data);
}

/**
 * Update activity instance.
 *
 * @param stdClass $data Form data.
 * @param moodleform|null $mform Form instance.
 * @return bool
 */
function oralassessment_update_instance($data, $mform = null) {
    return manager::update_instance($data);
}

/**
 * Delete activity instance.
 *
 * @param int $id Instance id.
 * @return bool
 */
function oralassessment_delete_instance($id) {
    return manager::delete_instance($id);
}

/**
 * Return cached course module data, including custom completion rules.
 *
 * @param stdClass $coursemodule Course module record.
 * @return cached_cm_info|false
 */
function oralassessment_get_coursemodule_info($coursemodule) {
    global $DB;

    $activity = $DB->get_record('oralassessment', ['id' => $coursemodule->instance],
        'id,name,intro,introformat,completionattempt,completionreviewed');
    if (!$activity) {
        return false;
    }

    $info = new cached_cm_info();
    $info->name = $activity->name;
    if ($coursemodule->showdescription) {
        $info->content = format_module_intro('oralassessment', $activity, $coursemodule->id, false);
    }
    if ($coursemodule->completion == COMPLETION_TRACKING_AUTOMATIC) {
        $info->customdata['customcompletionrules']['completionattempt'] = (int)$activity->completionattempt;
        $info->customdata['customcompletionrules']['completionreviewed'] = (int)$activity->completionreviewed;
    }
    return $info;
}

/**
 * Human-readable active completion rules.
 *
 * @param cm_info|stdClass $cm Course module info.
 * @return array
 */
function mod_oralassessment_get_completion_active_rule_descriptions($cm) {
    if (empty($cm->customdata['customcompletionrules']) || $cm->completion != COMPLETION_TRACKING_AUTOMATIC) {
        return [];
    }
    $descriptions = [];
    if (!empty($cm->customdata['customcompletionrules']['completionattempt'])) {
        $descriptions[] = get_string('completiondetail:attempt', 'mod_oralassessment');
    }
    if (!empty($cm->customdata['customcompletionrules']['completionreviewed'])) {
        $descriptions[] = get_string('completiondetail:reviewed', 'mod_oralassessment');
    }
    return $descriptions;
}

/**
 * Serve intro and optional attempt audio files.
 *
 * @param stdClass $course Course.
 * @param stdClass $cm Course module.
 * @param context $context Module context.
 * @param string $filearea File area.
 * @param array $args File path args.
 * @param bool $forcedownload Whether to force download.
 * @param array $options Additional options.
 * @return bool
 */
function oralassessment_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    global $DB, $USER;

    if ($context->contextlevel !== CONTEXT_MODULE) {
        return false;
    }
    require_login($course, true, $cm);
    require_capability('mod/oralassessment:view', $context);

    if (!$args) {
        return false;
    }
    $itemid = (int)array_shift($args);
    if ($filearea === 'intro') {
        if ($itemid !== 0) {
            return false;
        }
    } else if ($filearea === 'attemptaudio') {
        $turn = $DB->get_record('oralassessment_turns', ['id' => $itemid], 'id,attemptid,role', IGNORE_MISSING);
        if (!$turn || $turn->role !== 'user') {
            return false;
        }
        $attempt = $DB->get_record('oralassessment_attempts', ['id' => $turn->attemptid], 'id,userid', MUST_EXIST);
        if ((int)$attempt->userid !== (int)$USER->id && !has_capability('mod/oralassessment:reviewattempts', $context)) {
            return false;
        }
    } else {
        return false;
    }

    $relativepath = implode('/', $args);
    $fullpath = "/{$context->id}/mod_oralassessment/{$filearea}/{$itemid}/{$relativepath}";
    $fs = get_file_storage();
    $file = $fs->get_file_by_hash(sha1($fullpath));
    if (!$file || $file->is_directory()) {
        return false;
    }
    send_stored_file($file, 0, 0, $forcedownload, $options);
}

/**
 * Update the gradebook item.
 *
 * @param stdClass $oralassessment Activity instance.
 * @param array|stdClass|null $grades Grade data.
 * @return int
 */
function oralassessment_grade_item_update($oralassessment, $grades = null) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    $params = [
        'itemname' => $oralassessment->name,
        'idnumber' => $oralassessment->cmidnumber ?? null,
    ];
    if ((int)$oralassessment->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax'] = (float)$oralassessment->grade;
        $params['grademin'] = 0;
    } else {
        $params['gradetype'] = GRADE_TYPE_NONE;
    }

    return grade_update('mod/oralassessment', $oralassessment->course, 'mod', 'oralassessment',
        $oralassessment->id, 0, $grades, $params);
}

/**
 * Push reviewed grades to gradebook.
 *
 * @param stdClass $oralassessment Activity instance.
 * @param int $userid Optional user id.
 * @param bool $nullifnone Whether to send null grade if absent.
 */
function oralassessment_update_grades($oralassessment, $userid = 0, $nullifnone = true) {
    global $DB;

    $params = ['oralassessmentid' => $oralassessment->id, 'status' => manager::STATUS_REVIEWED];
    if ($userid) {
        $params['userid'] = $userid;
    }
    $attempts = $DB->get_records('oralassessment_attempts', $params);
    $grades = [];
    foreach ($attempts as $attempt) {
        if ($attempt->grade === null && !$nullifnone) {
            continue;
        }
        $grades[$attempt->userid] = (object)[
            'userid' => $attempt->userid,
            'rawgrade' => $attempt->grade === null ? null : (float)$attempt->grade,
            'dategraded' => $attempt->timereviewed,
            'datesubmitted' => $attempt->timesubmitted,
        ];
    }
    oralassessment_grade_item_update($oralassessment, $grades ?: null);
}


/**
 * Return reviewed grades for Moodle gradebook synchronisation.
 *
 * @param stdClass $oralassessment Activity instance.
 * @param int $userid Optional user id.
 * @return array
 */
function oralassessment_get_user_grades($oralassessment, $userid = 0) {
    global $DB;

    $params = [
        'oralassessmentid' => $oralassessment->id,
        'status' => manager::STATUS_REVIEWED,
    ];
    if ($userid) {
        $params['userid'] = $userid;
    }
    $attempts = $DB->get_records('oralassessment_attempts', $params);
    $grades = [];
    foreach ($attempts as $attempt) {
        $grades[(int)$attempt->userid] = (object)[
            'userid' => (int)$attempt->userid,
            'rawgrade' => $attempt->grade === null ? null : (float)$attempt->grade,
            'dategraded' => (int)$attempt->timereviewed,
            'datesubmitted' => (int)$attempt->timesubmitted,
        ];
    }
    return $grades;
}
