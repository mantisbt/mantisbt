# Scenarios Decision Map

Use this folder when the request matches a repeatable implementation pattern.

## Which scenario to use

- Use `create-new-endpoint.md` when adding or extending a REST route in `api/rest/restcore`.
- Use `add-new-command.md` when introducing a new command in `core/commands` (or changing command-level behavior).
- Use `add-new-helper.md` when adding reusable utility/parsing/helper logic in `core/helper_api.php` or helper-adjacent APIs.
- Use `create-test-for-new-controller.md` when creating tests for new controller/endpoint behavior.

## Selection questions

Ask these quickly before starting:

1. Is the change primarily a transport/API surface change? -> endpoint scenario.
2. Is the change primarily business action orchestration/validation? -> command scenario.
3. Is the change primarily reusable utility logic used by multiple callers? -> helper scenario.
4. Is the change primarily validating a new controller contract? -> controller-test scenario.

If uncertain, propose a short plan first and confirm scenario choice before implementation.
