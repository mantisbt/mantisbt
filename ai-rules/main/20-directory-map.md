# Main Directories And Purpose (Skeleton)

## Purpose

This map helps developers and AI agents understand where changes belong.

## Core Application Areas

- `core/` - Main backend business logic, APIs, utilities, and shared internals.
- `config/` - Configuration files and setup-related assets.
- `api/` - Public API entry points and API-related integration surface.
- `plugins/` - Optional extensions and plugin modules.
- `lang/` - Localization resources and language strings.
- `js/` - Frontend JavaScript assets.
- `css/` - Frontend stylesheet assets.
- `images/` - UI image assets.
- `fonts/` - Font resources used by UI.

## Quality And Tooling

- `tests/` - Automated test suites (core, REST, SOAP, and helpers).
- `scripts/` - Operational and maintenance scripts.
- `build/` - Build-time or packaging-related resources.
- `vendor/` - Third-party dependencies (treat as external unless instructed).

## Docs And Reference

- `doc/` - Project documentation.
- `docbook/` - Documentation source material and publishing artifacts.

## Conventions For Placement

- Put business logic in `core/` and call it from entry-point scripts.
- Keep integration-facing behavior in `api/` or plugin extension points.
- Place reusable test helpers under `tests/core/`.
- Avoid adding new root-level scripts when a domain folder exists.

## Open Questions

- Which root-level PHP pages are legacy entry points vs actively maintained flows?
- Should some subdirectories be marked read-only for routine feature work?
