# Contributing to MantisBT

First of all, we would like to thank you for considering helping us improve
MantisBT. We welcome contributions from anyone, and look forward to receiving
yours!

This document briefly describes our expectations with regards to your
submissions.


## Issue Tracker

We naturally use [MantisBT](https://mantisbt.org/) to track bugs and
feature requests, GitHub Issues are disabled.

Please report Issues at https://mantisbt.org/bugs.


## Pull Requests

We ❤️ Pull Requests! And even more so when they **respect the following rules**:

- The PR MUST target the **master** branch.
  We do not accept new features on stable releases.
  > [!NOTE]
  > When fixing a _confirmed_ Issue in the stable release, the PR can be
  > based on and target the corresponding branch (i.e. *master-X.Y*).
- Your feature branch must be up-to-date with the target branch
- The PR's description MUST **reference an existing issue** in our
  [Tracker](https://mantisbt.org/bugs/);
  please [create one](https://mantisbt.org/bugs/bug_report_page.php) if needed.
- Submissions MUST follow our
  **[coding guidelines](https://mantisbt.org/wiki/doku.php/mantisbt:coding_guidelines)**
- Commit messages SHOULD follow our [checklist](https://mantisbt.org/wiki/doku.php/mantisbt:git_commit_messages)

If updates to the target branch introduce merge conflicts in the PR, those
should genrerally be fixed by **rebasing** the feature branch, and then
**force-pushing** it, prior to merge.

Avoid force-pushing while code review is in progress.

> [!TIP]
> Do not hesitate to contact the MantisBT core team should you have any questions.
>
> [![Gitter](https://img.shields.io/gitter/room/mantisbt/mantisbt.svg?logo=gitter)](https://gitter.im/mantisbt/mantisbt)


## Further reading and references

- [Developers Guide](https://mantisbt.org/docs/master/en-US/Developers_Guide/html-desktop/)
- [MantisBT Wiki](https://mantisbt.org/wiki/doku.php)
