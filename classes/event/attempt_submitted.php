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
 * Attempt Submitted event.
 *
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_oralassessment\event;

/**
 * Event fired when an oral assessment attempt changes state.
 */
class attempt_submitted extends \core\event\base {
    /**
     * Initialize event metadata.
     */
    protected function init(): void {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'oralassessment_attempts';
    }

    /**
     * Event name.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('eventattemptsubmitted', 'mod_oralassessment');
    }

    /**
     * Event description.
     *
     * @return string
     */
    public function get_description(): string {
        return 'The user with id ' . $this->userid . ' submitted oral assessment attempt ' . $this->objectid . '.';
    }
}
