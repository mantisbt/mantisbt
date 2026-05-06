# Scenario: Add New Helper

## Scope

Use this when adding utility logic to `core/helper_api.php` or a helper-adjacent API.

## Primary Locations

- `core/helper_api.php`
- Call sites in REST handlers, commands, and page flows

## Existing Helper Patterns (Observed)

- Parsing/validation helpers throw `ClientException` (`helper_parse_id`, `helper_parse_view_state`).
- Text length guard helpers centralize constraints (`helper_ensure_longtext_length_valid`).
- URL/link helpers encapsulate formatting/security behavior (`helper_url_combine`, `helper_get_link_attributes`, `helper_is_link_external`).
- Project context helpers centralize project selection/cookie handling (`helper_get_current_project`, `helper_set_current_project`).

## Common Questions For Helper Generation

1. Is this truly cross-cutting reusable logic, not feature-specific business logic?
2. Does a similar helper already exist that should be reused or extended?
3. What are strict input types and accepted value ranges?
4. Should invalid input throw `ClientException` or return fallback values?
5. Is the helper pure (no side effects), or does it intentionally touch session/cookies/config?
6. Which configs or constants does it depend on?
7. Is this helper safe for both web and REST contexts?
8. Are there security implications (URL handling, user input parsing, external links)?
9. Which current call sites should migrate to the new helper?
10. What failure/edge cases must be covered (empty, malformed, out-of-range)?

## Helper Authoring Checklist

1. Add helper function with clear docblock and stable naming (`helper_*` where appropriate).
2. Reuse existing error codes and exception style for consistency.
3. Keep behavior deterministic; avoid hidden side effects unless explicitly required.
4. Update callers to use helper instead of duplicating logic.
5. Add or update tests around both happy path and failure paths.
