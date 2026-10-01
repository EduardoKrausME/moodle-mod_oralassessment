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
 * Contract for future transcription engines.
 *
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_oralassessment\transcription;

/**
 * Future server-side transcription engines implement this interface.
 *
 * The initial release intentionally ships no external provider implementation. Implementations must remain inside
 * Moodle's approved transcription architecture and must not call Whisper, OpenAI, Gemini, Claude, or any other
 * external provider directly. Future provider-backed audio support belongs behind local_ai_bridge.
 */
interface engine_interface {
    /**
     * Whether this engine is currently available.
     *
     * @return bool
     */
    public function is_available(): bool;

    /**
     * Transcribe local audio bytes without bypassing the configured Moodle/bridge architecture.
     *
     * Implementations must not create an independent external AI/transcription provider path.
     *
     * @param string $pathname Local pathname.
     * @param string $mimetype MIME type.
     * @return string
     */
    public function transcribe(string $pathname, string $mimetype): string;
}
