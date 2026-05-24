# Test Set

Blijwin Content CMS uses the same test-profile idea as Blijwin OS: run the smallest reliable profile while developing, then broaden before commit, push or deploy.

## Profiles

```bash
composer test:dev
composer test:smoke
composer test:fast
composer test:architecture
composer test:cms
composer test:content
composer test:downloads
composer test:faq
composer test:tracking
composer test:full
```

## When To Use What

- `test:dev`: default local loop. Without selectors it runs the smoke set.
- `test:dev -- Downloads`: run one selector by feature or unit directory name.
- `test:smoke`: fast CMS rendering and Markdown checks.
- `test:fast`: all unit and feature tests.
- `test:architecture`: non-database architecture/policy tests when present.
- `test:cms`: CMS unit and feature tests.
- `test:content`: CMS, FAQ and downloads content behavior.
- `test:downloads`: downloads rendering, delivery and secure request flow.
- `test:faq`: FAQ rendering and import behavior.
- `test:tracking`: tracking endpoints and persistence behavior.
- `test:full`: the full Laravel test suite.

## Rules

- Run at least `composer test:dev -- <selector>` while editing a focused area.
- Run `composer test:fast` before normal commits.
- Run `composer test:full` before deployment or broad architecture/database changes.
- Pass normal Laravel test options after the profile, for example:

```bash
composer test:downloads -- --filter=secure
composer test:full -- --compact
```

The runner clears Laravel config before each profile so settings changes are picked up consistently.
