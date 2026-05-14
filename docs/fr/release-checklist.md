# Release Checklist

Utilisez cette checklist avant une nouvelle release sLang ou avant le passage en QA.

## Documentation

- Chaque locale supportée contient `README.md`, `getting-started.md`, `user-guide.md`, `management-tabs.md`, `configuration.md`, `use-in-blade.md`, `developer-guide.md`, `reference.md`, `frontend-guide.md`, `resource-bridge.md`, `troubleshooting.md` et `release-checklist.md`.
- `docs/README.md` relie toutes les pages d'accueil de locale.
- `docs/checks/docs-check.php` passe.
- Aucun artefact de tâche ne se trouve dans `docs/tasks`.
- La documentation ukrainienne utilise `docs/uk`, pas `docs/ua`.

## Manager UI

- Dictionary et Configuration sont rendus par Livewire et evo-ui.
- Dictionary et Configuration ne chargent pas `assets/css/manager.css` et n'utilisent pas de classes layout locales `slang-settings__*`.
- La colonne key n'est pas éditable après création.
- Synchronize passe par Livewire et ne recharge pas l'iframe.
- Les actions de traduction par ligne et par colonne fonctionnent.
- Le bouton Save dans Configuration est désactivé tant que le formulaire n'est pas dirty.
- Les langues frontend sont limitées aux langues du site sélectionnées.

## Resource Editor

- Les champs généraux, Template Variables et settings tabs apparaissent pour chaque langue du site.
- La synchronisation `form#mutate` fonctionne avec TinyMCE, CodeMirror et textarea fallback.
- La compatibilité resource-tab reste scopée sous `.slang-resource-tab-page`.

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

Résultat attendu pour la conformance sLang : score `100`, `drift: []` et exceptions uniquement sous `site-content-resource-editor`.
