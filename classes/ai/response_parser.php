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
 * Parser for structured AI dialogue responses.
 *
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_oralassessment\ai;

use moodle_exception;

/**
 * Normalize the model response into a safe, predictable structure.
 */
class response_parser {
    /**
     * Parse JSON returned by the AI bridge.
     *
     * @param string $text Model response text.
     * @param bool $questionrequired Whether a next question is required.
     * @return array
     */
    public static function parse(string $text, bool $questionrequired): array {
        $text = trim($text);
        if (preg_match('/^```(?:json)?\s*(.*?)\s*```$/s', $text, $matches)) {
            $text = trim($matches[1]);
        }
        $data = json_decode($text, true);
        if (!is_array($data)) {
            throw new moodle_exception('aifailed', 'mod_oralassessment');
        }

        $nextquestion = $data['next_question'] ?? null;
        if ($nextquestion !== null) {
            $nextquestion = trim(clean_param((string)$nextquestion, PARAM_TEXT));
            if ($nextquestion === '') {
                $nextquestion = null;
            }
            if ($nextquestion !== null) {
                $nextquestion = shorten_text($nextquestion, 2000);
            }
        }
        if ($questionrequired && !$nextquestion) {
            throw new moodle_exception('aifailed', 'mod_oralassessment');
        }

        $summary = clean_param((string)($data['summary'] ?? ''), PARAM_TEXT);
        $summary = shorten_text($summary, 10000);

        $evidence = [];
        foreach (($data['evidence'] ?? []) as $item) {
            if (!is_array($item)) {
                continue;
            }
            $criterion = trim(clean_param((string)($item['criterion'] ?? ''), PARAM_TEXT));
            $evidencetext = trim(clean_param((string)($item['evidence'] ?? ''), PARAM_TEXT));
            if ($criterion === '' && $evidencetext === '') {
                continue;
            }
            $evidence[] = [
                'criterion' => shorten_text($criterion, 1000),
                'evidence' => shorten_text($evidencetext, 4000),
                'turn' => max(0, (int)($item['turn'] ?? 0)),
            ];
            if (count($evidence) >= 100) {
                break;
            }
        }

        $reviewpoints = [];
        foreach (($data['review_points'] ?? []) as $point) {
            if (!is_scalar($point)) {
                continue;
            }
            $point = trim(clean_param((string)$point, PARAM_TEXT));
            if ($point !== '') {
                $reviewpoints[] = shorten_text($point, 2000);
            }
            if (count($reviewpoints) >= 50) {
                break;
            }
        }

        return [
            'next_question' => $nextquestion,
            'evidence' => $evidence,
            'summary' => $summary,
            'review_points' => $reviewpoints,
            'complete' => !empty($data['complete']),
        ];
    }
}
