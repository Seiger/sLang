# Frontend Guide

This guide describes how sLang output is used in templates and how manager UI code must stay scoped.

## Blade Usage

Use the helpers from [Use in Blade](use-in-blade.md) for current language, default language, translation phrases, hreflang links, menus, and Template Variables.

```blade
{{ sLang::langDefault() }}
{{ sLang::hreflang() }}
@lang('global.save')
```

## Manager Module Screens

Dictionary and Configuration are evo-ui module screens. They must use shared evo-ui table, choices, buttons, modal, form, dirty-state, pagination, and row-action primitives. Do not add a package stylesheet for these screens, and do not style global manager selectors from sLang.

The manager module shell loads evo-ui assets and the small `assets/js/manager.js` title/icon synchronizer only. If Dictionary or Configuration needs a visual behavior that evo-ui does not expose yet, add the primitive to evo-ui first and then consume it from sLang.

## Resource Tabs

Resource tabs are not standalone module screens. They are injected into Evolution's resource editor and must keep the native save lifecycle. Use `data-slang-*` markers for compatibility behavior and keep styles scoped under `.slang-resource-tab-page`.

## Assets

The manager module may load:

```text
assets/js/manager.js
```

There is intentionally no `assets/css/manager.css` for the manager module surface. The only local styles that remain are the resource editor bridge styles inside the native Evolution resource tab boundary.

## UI Safety Rules

- Do not add inline scripts to Dictionary or Configuration screens.
- Do not style global `.evo-ui-*` selectors from sLang.
- Do not reintroduce manager-local layout classes for Dictionary or Configuration.
- Keep resource-tab compatibility styles under `.slang-resource-tab-page`.
- Keep manager module icons and top tabs aligned with sArticles and other evo-ui consumers.
- Prefer evo-ui components for buttons, forms, choices, modals, pagination, and table actions.
