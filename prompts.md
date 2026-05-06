# Create new file prompts.md

Created `prompts.md` and started tracking prompts for this session. Future prompts will be appended here with a header and a short result excerpt.

# You're project tech lead / system architecture. We have to create set of rules for this project, so any new developer can open it using their AI tool could work with codebase. We have to create set of rules for Cursor, but user with any other AI model should also attach it and use rules for main work. Let's start with a plan, what should be in these rules.

Outlined a practical rule-system plan with layered rule categories, portability guidance for non-Cursor tools, an authoring template, rollout order, and governance to keep rules clear and enforceable.

# Mixed. Some rules will be global, some file-pattern / stack-dependent specific.

Confirmed mixed strategy: keep a small always-on global rule core and add focused file-pattern/stack-specific rules that activate only for relevant code areas.

# Create skeleton files: * canonical rules, * main directories (or set of directories) and their purpose * main entities and terms

Created initial skeleton documentation under `ai-rules/` with three files covering canonical AI rules, key directory purpose mapping, and shared entities/terms glossary for onboarding and cross-tool AI usage.

# No need to waste tokens on this yet. We'll create *.mdc adapters when all rules will be ready.

Deferred Cursor `.mdc` adapter generation; continuing with canonical, tool-agnostic rules first until the full ruleset is finalized.

# Detect style guides for php files (PSR version), .js files (detect main libraries which have main helpers for js code creation), .css files (which breakpoints are used), etc. Find as much data as possible to collect main rules for lintering.

Scanned repository configs, CI, and source patterns to detect practical linting baselines: PHP style compatibility (non-PSR12 legacy mix), JS library stack and helper usage, CSS breakpoint families, and recommended phased lint-rule adoption.

# Add md file for restictions. * Do not create new SOAP endpoints (it's legacy functionality, will be deprecated in the future) * Always check SQLi / XSS / AI Prompt injection * Check for SSRF * Use OWASP for reference of varies security issues * Do not limit tests creation with positive scenarios only: always ask for negative scenarios until developer will confirm that it's ok. * Do not allow big images files (more than 100Kb) into git (add pre-commit check)

Added a dedicated restrictions document capturing API deprecation constraints, mandatory OWASP-aligned security checks (SQLi/XSS/prompt injection/SSRF), negative-test expectations, and a required pre-commit guard for image files over 100 KB.

# Add rule about open questions. If there is some uncertainty, ALWAYS ask / propose plan creation instead of immediate implementation.

Added an always-applied Cursor rule to require clarification or plan confirmation whenever requirements are uncertain before implementation starts.

# add cursor follow-up rule: after reply, list all of md rules which were used to generate your response.

Added an always-applied Cursor rule requiring each reply to include a follow-up line listing used `.md` rules, or `none` when not used.

# ok. now let's move to project add/remove/refactor rules (still not for cursor-specific, but for all AI models/agents). create additional folder for these rules.

Created model-agnostic `ai-rules/` with baseline `add.md`, `remove.md`, `refactor.md`, and an index README.

# why you've splitted ai-generated content for docs and ai-rules into different folder. docs/ai-rules also should be in root ai-rules directory, isn't it?

Verified there was a split and proposed consolidation steps to move `docs/ai-rules` into root `ai-rules`.

# there is no existing project "docs". as you can see, this folder was created just an hour ago =)

Consolidated all rules into root `ai-rules`, removed `docs/ai-rules` markdown files, and updated stale path references.

# move action rules (add/remove/refactor) to ai-rules/actions directory to not mess this directory. rules started with digits move to folder main, use this folder md files for all requests.

Reorganized rules into `ai-rules/actions` and `ai-rules/main`, updated README semantics, and removed old root-level duplicates.

# move README.md to actions folder.

Moved `ai-rules/README.md` to `ai-rules/actions/README.md`.

# let's create ai-rules/scenarios folder. add these three scenarios: * create new endpoint (use api/rest/restcore) *  add new command (analyze core/commands and collect common questions which can be used for command generation) * add new helper (analyze helper_api.php and collect common data, which can be used to solve the task)

Created `ai-rules/scenarios` and added scenario playbooks for new endpoint, new command, and new helper based on repository patterns.
Added `ai-rules/scenarios/README.md` with a quick scenario selection decision map.

# add scenario: create test for new controller

Added `ai-rules/scenarios/create-test-for-new-controller.md` and updated the scenario decision map to include controller-test selection.

# now convert all created rules into cursor-specific

Converted all created `ai-rules/**/*.md` documents into Cursor `.mdc` adapters under `.cursor/rules` with scenario activation guidance.

# now let's check how these rules are working. task 1: create new controller for project state update (use ProjectAddCommand as a reference).

Paused for clarification due to ambiguity, then implemented a dedicated command and REST controller route for project `view_state` updates.

# 1. view_state 2. dedicated command is ok. 3. yes 4. yes PATCH /project/{id}/state is ok.

Implemented `PATCH /projects/{id}/state` with `ProjectViewStateUpdateCommand`, validation, permission checks, and updated project response.

# task 2: add tests for this controller

Added REST tests covering success and negative paths for project state updates, including anonymous access denial.

# task 3: add helper to get_percentage_by_status_and_project (use get_percentage_by_status as a reference)

Added `get_percentage_by_status_and_project()` in `core/helper_api.php` and refactored `get_percentage_by_status()` to delegate to it.

# update prompts.md file - copy all prompts from this session with small exceprt sinto it (I've switched to another session by mistake)

Appended this session's prompts and short result excerpts to `prompts.md` for continuity across sessions.
