# Fehlerbehebung

Typische Probleme in sLang.

## Choices Zeigen Blade Text

Leeren Sie compiled views und prüfen Sie, dass die Configuration über Livewire gerendert wird.

## Zu Viele Frontend-Sprachen

`s_lang_front` muss eine Teilmenge von `s_lang_config` sein.

## Fehlende Locale-Spalte

Nach dem Hinzufügen einer Sprache Synchronisierung oder Tabellenanpassung ausführen.

## Resource Speichert Nicht

Prüfen Sie die Synchronisierung mit `form#mutate`, besonders bei TinyMCE oder CodeMirror.

## Docs Check

```bash
php docs/checks/docs-check.php
```

## Release Checklist Schlägt Fehl

Wenn docs coverage unter 100% liegt, prüfen Sie die canonical pages in jeder Locale und echte Signale wie `SettingsPanel`, `TranslatesTableData`, `LanguageBridge`, `s_lang_config` und `config/translates/table.php`.

Wenn EvoUI conformance Drift meldet, trennen Sie echte Module-Screen-Probleme von der dokumentierten embedded resource bridge. Module-Screen-Drift wird behoben; der Bridge muss eng und dokumentiert bleiben.
