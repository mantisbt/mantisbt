# Remove Rules

Use this checklist when deleting code, files, dependencies, or behavior.

1. Confirm that removal scope is explicit and approved.
2. Identify and update all call sites and references.
3. Remove dead tests and add regression tests when risk exists.
4. Keep backward compatibility unless a breaking change is intentional.
5. Remove related docs/config entries that become obsolete.
6. Verify that no hidden runtime paths still depend on removed logic.
