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
 * Presentation helpers.
 *
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_oralassessment;

use context_module;
use moodle_url;
use stdClass;

/**
 * Build Mustache-friendly data without mixing domain logic into pages.
 */
class presenter {
    /**
     * Build an attempt transcript for learner or reviewer output.
     *
     * @param stdClass $attempt Attempt.
     * @param context_module $context Module context.
     * @return array
     */
    public static function attempt(stdClass $attempt, context_module $context): array {
        global $DB;
        $turns = $DB->get_records('oralassessment_turns', ['attemptid' => $attempt->id], 'id ASC');
        $items = [];
        foreach ($turns as $turn) {
            if ($turn->role === 'assistant') {
                $items[] = [
                    'isquestion' => true,
                    'isresponse' => false,
                    'text' => format_string((string)$turn->question),
                    'turnnumber' => (int)$turn->turnnumber,
                ];
                continue;
            }
            $audio = null;
            $fs = get_file_storage();
            $files = $fs->get_area_files($context->id, 'mod_oralassessment', 'attemptaudio', $turn->id,
                'id ASC', false);
            if ($files) {
                $file = reset($files);
                $audio = moodle_url::make_pluginfile_url(
                    $context->id,
                    'mod_oralassessment',
                    'attemptaudio',
                    $turn->id,
                    $file->get_filepath(),
                    $file->get_filename()
                )->out(false);
            }
            $items[] = [
                'isquestion' => false,
                'isresponse' => true,
                'text' => (string)$turn->transcript,
                'turnnumber' => (int)$turn->turnnumber,
                'method' => (string)$turn->transcriptionmethod,
                'audio' => $audio,
            ];
        }

        $evidence = json_decode((string)$attempt->aievidence, true);
        $reviewpoints = json_decode((string)$attempt->aireviewpoints, true);
        return [
            'id' => (int)$attempt->id,
            'status' => (string)$attempt->status,
            'submitted' => !empty($attempt->timesubmitted) ? userdate($attempt->timesubmitted) : '',
            'reviewed' => !empty($attempt->timereviewed) ? userdate($attempt->timereviewed) : '',
            'summary' => (string)$attempt->aisummary,
            'hassummary' => trim((string)$attempt->aisummary) !== '',
            'evidence' => self::evidence($evidence),
            'hasevidence' => !empty($evidence),
            'reviewpoints' => self::review_points($reviewpoints),
            'hasreviewpoints' => !empty($reviewpoints),
            'aierror' => (string)$attempt->aierror,
            'hasaierror' => trim((string)$attempt->aierror) !== '',
            'feedback' => format_text((string)$attempt->feedback, (int)$attempt->feedbackformat),
            'hasfeedback' => trim((string)$attempt->feedback) !== '',
            'grade' => $attempt->grade === null ? '' : format_float((float)$attempt->grade),
            'hasgrade' => $attempt->grade !== null,
            'turns' => $items,
        ];
    }

    /**
     * Normalize evidence items.
     *
     * @param mixed $items Decoded JSON.
     * @return array
     */
    private static function evidence($items): array {
        if (!is_array($items)) {
            return [];
        }
        $result = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $result[] = [
                'criterion' => (string)($item['criterion'] ?? ''),
                'evidence' => (string)($item['evidence'] ?? ''),
                'turn' => (int)($item['turn'] ?? 0),
            ];
        }
        return $result;
    }

    /**
     * Normalize review point strings.
     *
     * @param mixed $items Decoded JSON.
     * @return array
     */
    private static function review_points($items): array {
        if (!is_array($items)) {
            return [];
        }
        return array_values(array_map(static fn($item) => ['text' => (string)$item], $items));
    }
}
