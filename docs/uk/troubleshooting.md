# Діагностика

Ця сторінка допомагає швидко знайти типові проблеми sLang.

## Choices показують Blade

Очистіть compiled views і перевірте, що Конфігурація рендериться Livewire-компонентом, а не plain Blade-текстом.

## У фронтенді зайві мови

Перевірте `s_lang_config`. `s_lang_front` має бути підмножиною мов сайту.

## SQL помилка через відсутню колонку

Після додавання нової мови запустіть синхронізацію або table modification routine, щоб у словнику з'явилася колонка locale.

## Ресурс не зберігає локалізований контент

Перевірте синхронізацію з `form#mutate`, особливо коли активний TinyMCE або CodeMirror.

## Документація не відкривається в dDocs

```bash
php docs/checks/docs-check.php
```

## Release Checklist Падає

Якщо docs coverage нижче 100%, перевірте наявність canonical pages у кожній locale і signals: `SettingsPanel`, `TranslatesTableData`, `LanguageBridge`, `s_lang_config`, `config/translates/table.php`.

Якщо EvoUI conformance показує drift, розділіть реальний module-screen drift і задокументований embedded resource bridge. Module-screen drift виправляємо, bridge має залишатися вузьким і описаним.
