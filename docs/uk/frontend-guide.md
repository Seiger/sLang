# Frontend-гайд

Цей гайд описує використання sLang у шаблонах і межі manager UI.

## Blade

Основні приклади знаходяться у [Використанні в Blade](use-in-blade.md).

```blade
{{ sLang::langDefault() }}
{{ sLang::hreflang() }}
@lang('global.save')
```

## Екрани модуля

Словник і Конфігурація є повноцінними evo-ui module screens. Вони мають використовувати shared evo-ui table, choices, buttons, modal, form, dirty-state, pagination і row-action primitives. Не додавайте package stylesheet для цих екранів і не стилізуйте глобальні manager selectors із sLang.

Module shell підключає evo-ui assets і тільки маленький `assets/js/manager.js` synchronizer для title/icon. Якщо Словнику або Конфігурації потрібна нова візуальна поведінка, її спочатку треба додати в evo-ui, а потім використати в sLang.

## Вкладки ресурсу

Вкладки ресурсу вбудовані в Evolution editor. Використовуйте `data-slang-*` markers і scoped `.slang-resource-tab-page` styles.

## Assets

```text
assets/js/manager.js
```

Для manager module surface навмисно немає `assets/css/manager.css`. Локальні стилі допускаються тільки для resource editor bridge всередині native Evolution resource tab boundary.

## UI Safety Rules

- Не додавайте inline scripts у Словник або Конфігурацію.
- Не стилізуйте глобальні `.evo-ui-*` селектори з sLang.
- Не повертайте локальні manager layout classes для Словника або Конфігурації.
- Compatibility styles вкладок ресурсу тримайте під `.slang-resource-tab-page`.
- Іконки модуля і верхні tabs мають бути вирівняні зі стандартом sArticles/evo-ui.
- Для кнопок, choices, модалок, пагінації і таблиць використовуйте evo-ui components.
