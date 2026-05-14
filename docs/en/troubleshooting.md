# Troubleshooting

Use this guide when sLang behaves unexpectedly in the manager or templates.

## Choices Show Raw Blade Text

Clear compiled views and verify the choices component is rendered by Livewire, not copied as plain Blade text. The Configuration screen should show choice pills, not template code.

## Frontend Language Shows Too Many Options

Check `s_lang_config` first. `s_lang_front` must contain only languages selected in `s_lang_config`. Remove stale frontend locales and save Configuration again.

## SQL Error For Missing Locale Column

Run dictionary synchronization or the table modification routine after adding a language. sLang stores translation values in dynamic locale columns, so the database must be updated before saving values for a new locale.

## Resource Content Does Not Save

Resource tabs depend on the native `form#mutate` save. Verify the default content field is synchronized before submit, especially when TinyMCE or CodeMirror is active.

## Documentation Does Not Appear In dDocs

Run:

```bash
php docs/checks/docs-check.php
```

Then confirm the package has `docs/README.md`, locale `README.md` files, and no `docs/ua` or `docs/tasks` folder.

## Release Checklist Fails

If coverage is below 100%, check that every locale has the canonical pages and that the docs mention real package signals such as `SettingsPanel`, `TranslatesTableData`, `LanguageBridge`, `s_lang_config`, and `config/translates/table.php`.

If EvoUI conformance reports drift, check whether the finding is a real module-screen issue or the documented embedded resource bridge. Module-screen drift should be fixed; resource bridge findings must stay narrow and documented.
