# Changelog

This changelog documents only the two most recent published GitHub releases. Each entry describes verified changes between its release tag and the immediately preceding published release. Later changes on `master` are excluded.

## 1.1.3 - 2026-10-15

### Changed

- Updated the GitHub Actions CI PostgreSQL service from version 16 to 17.
- Updated the MariaDB CI service from version 10 to 11.4, revising startup environment variables and health-check configuration.
- Added the plugin icon at `pix/icon.png` and reorganized the SVG logo asset as `pix/monologo.svg`.
- Updated the plugin release identifier from `1.1.2` to `1.1.3` and its build number from `2026101200` to `2026101500`.

No functional changes to the Oral Assessment activity were identified in this tag comparison.

[Compare 1.1.2...1.1.3](https://github.com/EduardoKrausME/moodle-mod_oralassessment/compare/1.1.2...1.1.3)

## 1.1.2 - 2026-10-12

### Fixed

- Hardened the release workflow against shell injection by passing the head commit message through the `RELEASE_MESSAGE` environment variable instead of interpolating it directly into shell commands.
- Updated the CI dependency setup to check out `EduardoKrausME/moodle-local_ai_bridge` from `master` into `extra-plugins/ai_bridge` and install it using `moodle-plugin-ci install --extra-plugins ./extra-plugins`, replacing the previous `add-plugin` command.

### Changed

- Updated the PHP 8.3 CI matrix from Moodle 5.2 (`MOODLE_502_STABLE`) to Moodle 5.3 (`MOODLE_503_STABLE`), retaining Moodle 4.5 coverage.
- Removed the release-workflow step that created a ZIP archive named `local_ai_bridge_<version>.zip`, and removed that ZIP from GitHub Release attachments. The previous packaging path was named `ai_bridge`, not `oralassessment`.
- Standardized GNU GPL header links in PHP source, test and language files; added a GPL/file header to the activity's JavaScript source and GPL headers to its Mustache templates. These are documentation/header changes, not changes to their activity logic.
- Updated the plugin release identifier from `1.1.1` to `1.1.2` and its build number from `2026100500` to `2026101200`.

[Compare 1.1.1...1.1.2](https://github.com/EduardoKrausME/moodle-mod_oralassessment/compare/1.1.1...1.1.2)
