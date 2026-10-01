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
 * English strings for mod_oralassessment.
 *
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Oral assessment';
$string['modulename'] = 'Oral assessment';
$string['modulenameplural'] = 'Oral assessments';
$string['oralassessment:addinstance'] = 'Add a new oral assessment';
$string['oralassessment:view'] = 'View oral assessment';
$string['oralassessment:reviewattempts'] = 'Review oral assessment attempts';
$string['oralassessmentname'] = 'Name';
$string['objectives'] = 'Learning objectives';
$string['objectives_help'] = 'Define the knowledge or skills that the oral conversation must stay within.';
$string['criteria'] = 'Assessment criteria';
$string['criteria_help'] = 'Criteria used to organize evidence for teacher review. The AI does not award the final grade.';
$string['initialquestions'] = 'Optional initial questions';
$string['initialquestions_help'] = 'Enter one question per line. If empty, the AI creates the first question from the objectives.';
$string['rounds'] = 'Maximum rounds';
$string['duration'] = 'Maximum duration';
$string['allowfollowup'] = 'Allow adaptive follow-up questions';
$string['rubric'] = 'Rubric';
$string['rubric_help'] = 'Optional rubric text sent to the AI only to organize evidence. It is not used to automatically grade.';
$string['requirereview'] = 'Require teacher review';
$string['requirereview_help'] = 'When enabled, a submitted attempt is shown as awaiting human review. Moodle activity ' .
    'completion remains controlled separately by the completion rules.';
$string['storeaudio'] = 'Store audio recordings';
$string['storeaudio_help'] = 'When enabled, audio captured in the browser may be stored in Moodle. The AI bridge receives ' .
    'text only in this version.';
$string['transcriptionmode'] = 'Transcription mode';
$string['transcriptionbrowser'] = 'Browser speech recognition when available, with typed fallback';
$string['transcriptionmanual'] = 'Typed transcript only';
$string['maxgrade'] = 'Maximum grade';
$string['privacywarning'] = 'This activity can process a spoken response. Depending on the activity settings, Moodle ' .
    'stores either the transcript only or the transcript plus the recorded audio.';
$string['recordingnotice'] = 'Audio recording is enabled for this activity. Recording starts only after you press the ' .
    'record button and your browser grants microphone access.';
$string['transcriptonlynotice'] = 'Audio is not stored. Browser speech recognition may be used when available, and you can ' .
    'always type or edit the transcript before submitting it.';
$string['startassessment'] = 'Start assessment';
$string['record'] = 'Record';
$string['stoprecording'] = 'Stop recording';
$string['submitresponse'] = 'Submit response';
$string['transcript'] = 'Transcript / typed response';
$string['currentquestion'] = 'Current question';
$string['timeleft'] = 'Time remaining';
$string['assessmentfinished'] = 'Assessment submitted';
$string['aigeneratednotice'] = 'AI-generated evidence is advisory and must be interpreted by a human reviewer.';
$string['summary'] = 'AI summary for review';
$string['evidence'] = 'Evidence related to criteria';
$string['reviewpoints'] = 'Points that deserve teacher review';
$string['aifailed'] = 'The AI service was unavailable. Your transcript was saved and remains available for teacher review.';
$string['reviewattempts'] = 'Review attempts';
$string['reviewattempt'] = 'Review attempt';
$string['student'] = 'Student';
$string['status'] = 'Status';
$string['started'] = 'Started';
$string['submitted'] = 'Submitted';
$string['reviewed'] = 'Reviewed';
$string['inprogress'] = 'In progress';
$string['notgraded'] = 'Not graded';
$string['grade'] = 'Grade';
$string['feedback'] = 'Teacher feedback';
$string['savereview'] = 'Save human review and apply grade';
$string['reviewedby'] = 'Reviewed by';
$string['questionsandresponses'] = 'Questions and responses';
$string['noattempts'] = 'No attempts yet.';
$string['completionattempt'] = 'Student must submit the oral assessment';
$string['completionreviewed'] = 'A teacher must review the attempt';
$string['completiondetail:attempt'] = 'Submit an oral assessment attempt';
$string['completiondetail:reviewed'] = 'Have the oral assessment reviewed by a teacher';
$string['invalidattempt'] = 'Invalid oral assessment attempt.';
$string['attemptclosed'] = 'This attempt is already closed.';
$string['timeexpired'] = 'The configured assessment time has expired.';
$string['emptytranscript'] = 'Enter or capture a transcript before submitting.';
$string['transcripttoolong'] = 'The transcript is too long.';
$string['audiouploadfailed'] = 'The audio could not be stored.';
$string['audiostored'] = 'Audio stored.';
$string['audio'] = 'Audio';
$string['purpose'] = 'AI purpose: oralassessment-dialogue';
$string['privacy:metadata:attempts'] = 'Stores oral assessment attempt state, AI review aids, teacher review, and grade.';
$string['privacy:metadata:attempts:userid'] = 'The learner user ID.';
$string['privacy:metadata:attempts:status'] = 'Attempt status.';
$string['privacy:metadata:attempts:reviewedby'] = 'The reviewer user ID.';
$string['privacy:metadata:attempts:aisummary'] = 'AI-generated summary for review.';
$string['privacy:metadata:attempts:aievidence'] = 'AI-organized evidence related to criteria.';
$string['privacy:metadata:attempts:aireviewpoints'] = 'AI-generated review points.';
$string['privacy:metadata:attempts:grade'] = 'Grade applied by a human reviewer.';
$string['privacy:metadata:attempts:feedback'] = 'Teacher feedback.';
$string['privacy:metadata:turns'] = 'Stores the questions and learner transcripts that form the oral assessment conversation.';
$string['privacy:metadata:turns:question'] = 'Question shown to the learner.';
$string['privacy:metadata:turns:transcript'] = 'Transcript or typed response submitted by the learner.';
$string['privacy:metadata:turns:transcriptionmethod'] = 'How the transcript was produced.';
$string['privacy:metadata:audio'] = 'Optional stored audio recordings for learner responses.';
$string['privacy:metadata:aibridge'] = 'Text from this activity is sent through local_ai_bridge under purpose ' .
    'oralassessment-dialogue. This plugin does not call an external AI provider directly.';
$string['attempt'] = 'Attempt';
$string['reviewsperformed'] = 'Reviews performed';
$string['browserprivacy'] = 'Browser speech recognition can be implemented by the browser or operating system and may use ' .
    'its own speech service. Moodle receives the resulting transcript; you can use the typed ' .
    'fallback instead.';
$string['errorrounds'] = 'Use a value between 1 and 20.';
$string['errorduration'] = 'Use a duration between 30 seconds and 2 hours.';
$string['errorgrade'] = 'Use a maximum grade between 0 and 1000.';
$string['privacy:metadata:attempts:timestarted'] = 'When the attempt started.';
$string['privacy:metadata:attempts:timesubmitted'] = 'When the learner submitted the attempt.';
$string['privacy:metadata:attempts:timereviewed'] = 'When the teacher reviewed the attempt.';
$string['privacy:metadata:attempts:aierror'] = 'A non-sensitive operational message if AI generation failed.';
$string['privacy:metadata:attempts:timemodified'] = 'When the attempt record was last modified.';
$string['privacy:metadata:turns:role'] = 'Whether the turn is an assistant question or a learner response.';
$string['privacy:metadata:turns:aijson'] = 'Structured AI output associated with a generated question.';
$string['privacy:metadata:turns:timecreated'] = 'When the conversation turn was created.';
$string['privacy:metadata:aibridge:objectives'] = 'Teacher-provided learning objectives that constrain the dialogue.';
$string['privacy:metadata:aibridge:criteria'] = 'Teacher-provided assessment criteria.';
$string['privacy:metadata:aibridge:rubric'] = 'Teacher-provided rubric text, when configured.';
$string['privacy:metadata:aibridge:conversation'] = 'Questions and learner response transcripts needed for the dialogue.';
$string['privacy:metadata:aibridge:previousquestion'] = 'The previous question, used to generate a scoped follow-up.';

$string['awaitingreview'] = 'This formative attempt is awaiting teacher review.';
$string['reviewoptional'] = 'The formative attempt is complete. Teacher review is optional for this activity.';

$string['recordingconsent'] = 'I understand that if I record audio, the recording will be stored in Moodle with this attempt.';
$string['consentrequired'] = 'Confirm the audio storage notice before starting a recording.';

$string['eventattemptstarted'] = 'Oral assessment attempt started';
$string['eventattemptsubmitted'] = 'Oral assessment attempt submitted';
$string['eventattemptreviewed'] = 'Oral assessment attempt reviewed';
