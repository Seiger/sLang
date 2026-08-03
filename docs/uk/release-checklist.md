# Release Checklist

Використовуйте цей checklist перед публікацією нового sLang build або перед передачею задач QA.

## Документація

- Кожна підтримана locale має `README.md`, `getting-started.md`, `user-guide.md`, `management-tabs.md`, `configuration.md`, `use-in-blade.md`, `developer-guide.md`, `reference.md`, `frontend-guide.md`, `resource-bridge.md`, `troubleshooting.md` і `release-checklist.md`.
- `docs/README.md` веде на всі мовні entrypoints.
- `docs/checks/docs-check.php` проходить.
- Task artifacts не лежать у `docs/tasks`.
- Українська документація використовує `docs/uk`, не `docs/ua`.

## Manager UI

- Словник і Конфігурація рендеряться через Livewire і evo-ui.
- Словник і Конфігурація не підключають `assets/css/manager.css` і не використовують локальні `slang-settings__*` layout classes.
- Колонка ключа не редагується після створення.
- Синхронізація працює через Livewire і не перезавантажує iframe.
- Працюють переклади одного рядка і всієї порожньої колонки.
- Кнопка збереження в Конфігурації вимкнена, поки форма не dirty.
- Мови фронтенду обмежені вибраними мовами сайту.

## Resource Editor

- Загальні поля, Template Variables і settings tabs показуються для кожної мови сайту.
- Синхронізація `form#mutate` працює з TinyMCE, CodeMirror і textarea fallback.
- Compatibility styles вкладок ресурсу залишаються під `.slang-resource-tab-page`.

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

Очікуваний sLang conformance result: score `100`, `drift: []`, а exceptions тільки в `site-content-resource-editor`.
