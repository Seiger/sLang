# sLang Documentation

sLang is the multilingual layer for Evolution CMS manager screens, resource edit tabs, dictionary phrases, URL language segments, and Blade/runtime helpers.

## Languages

| Locale | Entry point | Status |
| --- | --- | --- |
| `uk` | [Українська](uk/README.md) | complete |
| `en` | [English](en/README.md) | complete |
| `de` | [Deutsch](de/README.md) | needs-review |
| `fr` | [Français](fr/README.md) | needs-review |
| `pl` | [Polski](pl/README.md) | needs-review |

## Read First

- [User Guide](en/user-guide.md) for manager workflows.
- [Developer Guide](en/developer-guide.md) for package architecture and release checks.
- [Configuration](en/configuration.md) for settings and language keys.
- [Resource Bridge](en/resource-bridge.md) for the embedded Evolution resource tab boundary.

## Documentation Contract

The public docs live in this `docs/` folder so dDocs can index them from the filesystem. Old GitHub Pages material stays archived in `old_docs/` outside this tree. Ukrainian docs use `uk`; do not create a `ua` docs folder.

Screenshots and diagrams belong in `assets/`. Implementation task artifacts belong in the dIssues artifact store, not in `docs/tasks`.

## Release Gates

Each locale includes a `release-checklist.md` page with the manager UI, resource editor, documentation, and automated checks that must pass before publishing sLang.
