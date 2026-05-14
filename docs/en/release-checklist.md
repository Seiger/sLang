# Release Checklist

Use this checklist before publishing a new sLang package build or before asking QA to close the migration tasks.

## Documentation

- Every supported locale has `README.md`, `getting-started.md`, `user-guide.md`, `management-tabs.md`, `configuration.md`, `use-in-blade.md`, `developer-guide.md`, `reference.md`, `frontend-guide.md`, `resource-bridge.md`, `troubleshooting.md`, and `release-checklist.md`.
- `docs/README.md` links the supported locale entrypoints.
- `docs/checks/docs-check.php` passes.
- No task artifacts exist under `docs/tasks`.
- No Ukrainian docs folder exists as `docs/ua`; use `docs/uk`.

## Manager UI

- Dictionary and Configuration render through Livewire and evo-ui.
- Dictionary and Configuration do not load `assets/css/manager.css` and do not use local `slang-settings__*` layout classes.
- Dictionary key column is not editable after creation.
- Synchronize runs through Livewire and does not reload the iframe.
- Per-row and per-column translation actions work.
- Save button in Configuration is disabled until the form is dirty.
- Frontend language choices are limited to selected site languages.

## Resource Editor

- General fields, Template Variables, and settings tabs appear for each configured site language.
- `form#mutate` synchronization works with TinyMCE, CodeMirror, and native textarea fallback.
- Resource-tab compatibility remains scoped under `.slang-resource-tab-page`.

## Automated Checks

```bash
php docs/checks/docs-check.php
php tests/run.php
php tests/regression/slang-demo-regression.php demo/core
php tests/demo-smoke.php demo/core --url=http://127.0.0.1:8788
```

## MiddleDuck Gates

```bash
php DuckBook/scripts/extras-doc-coverage.php --extras=/path/to/Extras --modules=sLang
php skills/evo-ui-consumer-conformance/scripts/evo-ui-consumer-conformance.php --extras=/path/to/Extras --modules=sLang
```

Expected sLang conformance result: score `100`, `drift: []`, and exceptions only under `site-content-resource-editor`.
