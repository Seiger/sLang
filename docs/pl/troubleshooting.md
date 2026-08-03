# Rozwiązywanie problemów

Typowe problemy w sLang.

## Choices Pokazują Blade

Wyczyść compiled views i sprawdź, czy Configuration renderuje się przez Livewire.

## Za Dużo Języków Frontendu

`s_lang_front` musi być podzbiorem `s_lang_config`.

## Brak Kolumny Locale

Po dodaniu języka uruchom synchronizację albo modyfikację tabel.

## Zasób Się Nie Zapisuje

Sprawdź synchronizację z `form#mutate`, szczególnie przy TinyMCE lub CodeMirror.

## Kontrola Docs

```bash
php docs/checks/docs-check.php
```

## Release Checklist Nie Przechodzi

Jeśli docs coverage jest poniżej 100%, sprawdź canonical pages w każdej locale oraz sygnały: `SettingsPanel`, `TranslatesTableData`, `LanguageBridge`, `s_lang_config`, `config/translates/table.php`.

Jeśli EvoUI conformance pokazuje drift, oddziel prawdziwy problem module screen od udokumentowanego embedded resource bridge. Drift modułu naprawiamy; bridge musi zostać wąski i opisany.
