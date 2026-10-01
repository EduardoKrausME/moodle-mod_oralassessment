# Moodle mod_oralassessment

`mod_oralassessment` is a formative oral-assessment activity for Moodle. A learner answers a sequence of spoken or typed questions, while AI can adapt the next question and organise transcript evidence for a teacher to review.

The activity is intentionally designed around a human-review boundary: AI may ask questions, summarise transcript evidence and point out items that deserve attention, but it never calculates, recommends or applies the final grade. A grade reaches the Moodle gradebook only when a user with `mod/oralassessment:reviewattempts` explicitly saves the human review form.

## No parallel audio provider

The current `local_ai_bridge` release used by this plugin is a text-generation bridge. `mod_oralassessment` therefore does **not** call OpenAI Whisper, OpenAI audio APIs, Google Speech, Anthropic, or any other external transcription/provider API directly.

This first release keeps transcription as a separate layer:

- browser speech recognition can fill the transcript when the browser exposes the Web Speech API;
- the learner can always type or edit the transcript before submission;
- audio can optionally be recorded with `MediaRecorder` and stored in Moodle;
- an `engine_interface` exists for future Moodle/core/local transcription mechanisms, but no external network transcription provider is bundled;
- when `local_ai_bridge` gains an appropriate multimodal/audio capability, integration should be added through the bridge instead of creating a second provider path in this activity.

Browser speech recognition is not the same thing as local Moodle transcription. Depending on the browser/operating system, recognition can use a service supplied by that vendor. The activity tells the learner this and keeps typed input available as the fallback.

## Teacher configuration

The teacher can configure:

- learning objectives that define the allowed scope;
- assessment criteria;
- optional initial questions, one per line;
- maximum number of rounds;
- maximum duration;
- whether AI can create adaptive follow-up questions;
- an optional rubric used only to organise evidence;
- whether a submitted attempt should be marked as awaiting teacher review;
- whether Moodle stores audio or only the transcript;
- browser-assisted or typed-only transcription mode;
- a maximum grade, including `0` for an ungraded formative activity;
- completion on submission and/or completion after teacher review.

## Dialogue and AI boundary

For each turn the AI request contains the configured objectives, criteria and rubric plus the conversation information needed for the dialogue. Instructions explicitly require the model to remain inside the teacher-provided scope and to treat transcript content as assessment evidence rather than instructions.

The expected structured result contains:

- the next question, when another round is required;
- evidence mapped to teacher criteria;
- a formative summary;
- points that deserve teacher review;
- a completion flag.

The response parser accepts only the expected JSON structure and limits the size/count of returned fields. Raw provider-specific APIs, API keys, endpoints and model settings do not exist in this plugin.

## Safety and assessment limits

The plugin instructs AI not to infer or diagnose intelligence, personality, emotion, medical conditions, identity, or biometric traits from a response. Audio is not sent through `local_ai_bridge` in the plugin; only text is used for AI dialogue.

This is a formative evidence tool, not an autonomous examiner. Generated summaries and evidence are visibly marked as advisory. Teacher feedback and any grade are human actions.

## Attempts and review

The the plugin supports one attempt per learner per activity. A learner can resume an in-progress attempt. Each question and learner transcript is stored as a turn, which keeps the assessment auditable without asking AI to reconstruct what happened.

If AI generation fails after a learner submits a response, the transcript is saved first. On the last round the attempt can still be submitted for human review even when the AI service is unavailable.

## Audio and privacy

The activity can run in either of these modes:

1. **Transcript only** — Moodle stores submitted text and no activity audio file.
2. **Store audio** — after explicit browser microphone permission and a learner action to record, Moodle stores the audio with the corresponding learner turn.

The Privacy API declares and handles:

- attempt ownership and timestamps;
- questions and transcripts;
- AI-generated review aids;
- human reviewer identity, grade and feedback;
- optional audio files;
- the textual data handed to the `local_ai_bridge` path.

Privacy deletion removes learner turns and associated audio files. When a user appears only as a reviewer, deletion anonymises the reviewer reference rather than deleting another learner's attempt.

## Capabilities

- `mod/oralassessment:addinstance`
- `mod/oralassessment:view`
- `mod/oralassessment:reviewattempts`
