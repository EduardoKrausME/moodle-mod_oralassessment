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
 * Transcription text normalization.
 *
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_oralassessment\transcription;

use moodle_exception;

/**
 * Server-side boundary for transcript handling.
 */
class transcription_service {
    /** @var string */
    public const METHOD_BROWSER = 'browser';

    /** @var string */
    public const METHOD_MANUAL = 'manual';

    /** @var int */
    public const MAX_CHARS = 20000;

    /**
     * Validate and normalize a transcript generated in the browser or typed by the learner.
     *
     * @param string $transcript Transcript.
     * @param string $method Method identifier.
     * @return string
     */
    public static function normalise(string $transcript, string $method): string {
        self::normalise_method($method);
        $transcript = trim(clean_param($transcript, PARAM_TEXT));
        if ($transcript === '') {
            throw new moodle_exception('emptytranscript', 'mod_oralassessment');
        }
        if (\core_text::strlen($transcript) > self::MAX_CHARS) {
            throw new moodle_exception('transcripttoolong', 'mod_oralassessment');
        }
        return $transcript;
    }
    /**
     * Canonicalise the reported transcription origin.
     *
     * @param string $method Method identifier.
     * @return string
     */
    public static function normalise_method(string $method): string {
        if (!in_array($method, [self::METHOD_BROWSER, self::METHOD_MANUAL], true)) {
            return self::METHOD_MANUAL;
        }
        return $method;
    }

}
