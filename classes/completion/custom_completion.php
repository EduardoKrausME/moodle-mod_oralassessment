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
 * Custom completion rules for mod_oralassessment.
 *
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace mod_oralassessment\completion;

use core_completion\activity_custom_completion;
use mod_oralassessment\manager;

/**
 * Activity completion implementation.
 */
class custom_completion extends activity_custom_completion {
    /**
     * Return state for a custom rule.
     *
     * @param string $rule Rule name.
     * @return int
     */
    public function get_state(string $rule): int {
        global $DB;
        $this->validate_rule($rule);
        $attempt = $DB->get_record('oralassessment_attempts', [
            'oralassessmentid' => $this->cm->instance,
            'userid' => $this->userid,
        ], 'id,status', IGNORE_MISSING);

        if ($rule === 'completionattempt') {
            $complete = $attempt && in_array($attempt->status,
                [manager::STATUS_SUBMITTED, manager::STATUS_REVIEWED], true);
            return $complete ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
        }
        if ($rule === 'completionreviewed') {
            $complete = $attempt && $attempt->status === manager::STATUS_REVIEWED;
            return $complete ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
        }
        return COMPLETION_UNKNOWN;
    }

    /**
     * Defined custom rules.
     *
     * @return array
     */
    public static function get_defined_custom_rules(): array {
        return ['completionattempt', 'completionreviewed'];
    }

    /**
     * Descriptions for completion badges.
     *
     * @return array
     */
    public function get_custom_rule_descriptions(): array {
        return [
            'completionattempt' => get_string('completiondetail:attempt', 'mod_oralassessment'),
            'completionreviewed' => get_string('completiondetail:reviewed', 'mod_oralassessment'),
        ];
    }

    /**
     * Sort order including core rules.
     *
     * @return array
     */
    public function get_sort_order(): array {
        return ['completionview', 'completionattempt', 'completionreviewed', 'completionusegrade', 'completionpassgrade'];
    }
}
