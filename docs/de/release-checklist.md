# Release Checklist

Diese Checkliste wird vor einem neuen sLang Release oder vor der Übergabe an QA genutzt.

## Dokumentation

- Jede unterstützte Locale enthält `README.md`, `getting-started.md`, `user-guide.md`, `management-tabs.md`, `configuration.md`, `use-in-blade.md`, `developer-guide.md`, `reference.md`, `frontend-guide.md`, `resource-bridge.md`, `troubleshooting.md` und `release-checklist.md`.
- `docs/README.md` verlinkt alle Locale-Startseiten.
- `docs/checks/docs-check.php` läuft erfolgreich.
- Keine Task-Artefakte liegen unter `docs/tasks`.
- Ukrainische Dokumentation nutzt `docs/uk`, nicht `docs/ua`.

## Manager UI

- Dictionary und Configuration rendern über Livewire und evo-ui.
- Dictionary und Configuration laden kein `assets/css/manager.css` und verwenden keine lokalen `slang-settings__*` Layout-Klassen.
- Die Schlüsselspalte ist nach dem Erstellen nicht editierbar.
- Synchronize läuft über Livewire und lädt den iframe nicht neu.
- Row- und Spalten-Übersetzungsaktionen funktionieren.
- Der Save-Button in Configuration ist deaktiviert, bis das Formular dirty ist.
- Frontend-Sprachen sind auf ausgewählte Website-Sprachen begrenzt.

## Resource Editor

- Allgemeine Felder, Template Variables und Settings Tabs erscheinen für jede Website-Sprache.
- `form#mutate` Synchronisierung funktioniert mit TinyMCE, CodeMirror und textarea fallback.
- Resource-tab compatibility bleibt unter `.slang-resource-tab-page` gescoped.

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

Erwartetes sLang Conformance-Ergebnis: Score `100`, `drift: []` und Exceptions nur unter `site-content-resource-editor`.
