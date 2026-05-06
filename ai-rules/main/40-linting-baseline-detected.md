# Linting Baseline (Detected From Repository)

## Purpose

Capture detected style and stack conventions from the current codebase to drive
canonical linting rules (without introducing speculative standards).

## Detection Scope

- No dedicated lint config files found (`.editorconfig`, `phpcs.xml`, `.eslintrc*`,
  `.stylelintrc*`, `.php-cs-fixer*`, `package.json`).
- Conventions inferred from source code, CI workflow, and build scripts.

## PHP

### Runtime and ecosystem

- Minimum supported runtime in Composer is PHP `^8.1` (`composer.json`).
- CI runs tests on PHP `8.1` through `8.5` (`.github/workflows/mantisbt.yml`).
- `composer.json` uses PSR-4 autoloading namespace `Mantis\\`.

### Detected style signals

- Predominantly tab-indented code in core and entry-point files.
- Extensive historical use of `array(...)`; short arrays `[]` are also present.
- Predominantly K&R-style braces for functions/control structures.
- Spacing style commonly follows `if( ... )` and `function name( ... )` (no space
  before `(`).
- Legacy inline comments often use `#` in PHP files.
- `declare(strict_types=1)` appears in tests but is not generally used in app code.

### PSR compatibility assessment

- Codebase is not fully PSR-12 compliant today (tabs, `#` comments, mixed array
  syntax, function-call spacing style).
- Most compatible practical target for existing code is a **project profile**
  loosely based on PSR-2/PSR-12 where safe, with migration-only rules for new code.

## JavaScript

### Main libraries and helpers detected

- jQuery `3.7.1`
- Bootstrap JS `3.4.1`
- Typeahead `1.3.4`
- List.js `2.3.1`
- Moment.js `2.29.4`
- Eonasdan Bootstrap Datetimepicker `4.17.49`
- Dropzone `5.5.0`
- Ace admin framework integration (`ace.min.js`, `ace.click_event` usage)
- Chart.js stack used in plugin (`plugins/MantisGraph`)

(Versions are defined in `core/constant_inc.php` and loaded via `core/html_api.php`,
`core/layout_api.php`, `core/datetimepicker_api.php`, and `core/dropzone_api.php`.)

### Detected style signals

- `jshint` pragmas are present in maintained custom JS files.
- Heavy jQuery event-driven imperative style.
- Mixed ES levels:
  - ES6 (`/* jshint esversion: 6 */`, `let`)
  - ES8 in proxy modules (`/* jshint esversion: 8 */`)
- Tab indentation is common in local JS files.

### Practical lint baseline

- Prefer JSHint-compatible rules initially for minimal disruption.
- Enforce consistency on touched files (spacing, semicolons, strict equality).
- Avoid broad rewrites to modern framework patterns unless requested.

## CSS

### Framework and structure signals

- Bootstrap 3.4.1 is present and drives much of responsive behavior.
- Main customized styles appear in `css/ace*.css`, `css/default.css`,
  `css/ace-mantis.css`.

### Breakpoint values detected in media queries

- Core Bootstrap-aligned breakpoints: `768`, `992`, `1200`
- Common max-width cutoffs: `991`, `767`, `479`, `480`, `320`
- Additional project-specific cutoffs: `240`, `319`, `360`, `540`, `550`, `600`,
  `1199`

### Notes for linting rules

- Keep Bootstrap-3-era responsive model as canonical baseline.
- Normalize around existing breakpoint families; discourage adding new arbitrary
  widths unless justified.
- Maintain existing RTL and print media behavior.

## CI / quality gates currently detected

- Automated CI currently runs PHPUnit only; no JS/CSS/PHP style gate exists in CI.

## Recommended canonical linting strategy

1. Start with **non-disruptive** rules matching existing style.
2. Add per-stack rules (PHP, JS, CSS) as warnings first.
3. Upgrade to blocking checks only after baseline cleanup.
4. Separate rules for:
   - legacy files (compatibility mode),
   - new files/modules (stricter mode).

## Open items before final lint rule lock

- Confirm whether we keep tabs as canonical indentation for legacy PHP/JS.
- Decide if new PHP code should require short arrays `[]` only.
- Decide JSHint-only vs ESLint migration path for custom JS.
- Select authoritative breakpoint set for new CSS contributions.
