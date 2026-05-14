# Release Checklist

Użyj tej checklisty przed nowym wydaniem sLang albo przed przekazaniem do QA.

## Dokumentacja

- Każda wspierana locale zawiera `README.md`, `getting-started.md`, `user-guide.md`, `management-tabs.md`, `configuration.md`, `use-in-blade.md`, `developer-guide.md`, `reference.md`, `frontend-guide.md`, `resource-bridge.md`, `troubleshooting.md` oraz `release-checklist.md`.
- `docs/README.md` linkuje wszystkie wejścia językowe.
- `docs/checks/docs-check.php` przechodzi.
- Artefakty zadań nie znajdują się w `docs/tasks`.
- Ukraińska dokumentacja używa `docs/uk`, nie `docs/ua`.

## Manager UI

- Dictionary i Configuration renderują się przez Livewire i evo-ui.
- Dictionary i Configuration nie ładują `assets/css/manager.css` i nie używają lokalnych klas layoutu `slang-settings__*`.
- Kolumna key nie jest edytowalna po utworzeniu.
- Synchronize działa przez Livewire i nie przeładowuje iframe.
- Działają akcje tłumaczenia wiersza i pustej kolumny.
- Przycisk Save w Configuration jest wyłączony, dopóki formularz nie jest dirty.
- Języki frontendu są ograniczone do wybranych języków strony.

## Resource Editor

- Pola ogólne, Template Variables i settings tabs pojawiają się dla każdego języka strony.
- Synchronizacja `form#mutate` działa z TinyMCE, CodeMirror i textarea fallback.
- Kompatybilność resource-tab pozostaje pod `.slang-resource-tab-page`.

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

Oczekiwany wynik conformance dla sLang: score `100`, `drift: []` oraz exceptions tylko pod `site-content-resource-editor`.
