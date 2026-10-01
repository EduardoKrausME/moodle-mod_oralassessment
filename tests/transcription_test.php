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
 * Transcription boundary tests.
 *
 * @coversNothing
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class transcription_test extends \advanced_testcase {
    /**
     * Supported transcript origins are normalised to safe internal values.
     */
    public function test_browser_and_manual_transcripts_are_normalised(): void {
        $this->assertSame('A spoken response', transcription_service::normalise('  A spoken response  ', 'browser'));
        $this->assertSame('Typed response', transcription_service::normalise('Typed response', 'manual'));
        $this->assertSame('Fallback', transcription_service::normalise('Fallback', 'unknown-engine'));
        $this->assertSame(transcription_service::METHOD_MANUAL,
            transcription_service::normalise_method('unknown-engine'));
    }

    /**
     * Empty transcripts are rejected.
     */
    public function test_empty_transcript_is_rejected(): void {
        $this->expectException(\moodle_exception::class);
        transcription_service::normalise('   ', 'manual');
    }

    /**
     * Oversized transcripts are rejected before persistence or AI use.
     */
    public function test_excessively_long_transcript_is_rejected(): void {
        $this->expectException(\moodle_exception::class);
        transcription_service::normalise(str_repeat('x', transcription_service::MAX_CHARS + 1), 'manual');
    }
}
