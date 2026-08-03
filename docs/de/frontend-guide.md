# Frontend-Leitfaden

Dieser Leitfaden beschreibt Template-Nutzung und Manager-UI-Grenzen.

## Blade

Siehe [Verwendung in Blade](use-in-blade.md).

```blade
{{ sLang::langDefault() }}
{{ sLang::hreflang() }}
@lang('global.save')
```

## Modulbildschirme

Dictionary und Configuration sind vollständige evo-ui Modulbildschirme. Sie müssen gemeinsame evo-ui Tabellen-, Choices-, Button-, Modal-, Form-, Dirty-State-, Pagination- und Row-Action-Primitives verwenden. Füge für diese Screens kein Package-Stylesheet hinzu und style keine globalen Manager-Selektoren aus sLang.

Die Modul-Shell lädt evo-ui Assets und nur den kleinen `assets/js/manager.js` Synchronizer für Titel/Icon. Wenn Dictionary oder Configuration neues visuelles Verhalten braucht, füge zuerst ein Primitive in evo-ui hinzu und nutze es danach in sLang.

## Resource-Tabs

Resource-Tabs nutzen `data-slang-*` Marker und scoped `.slang-resource-tab-page` Styles, weil sie im nativen Evolution Editor laufen.

## Assets

```text
assets/js/manager.js
```

Für die Manager-Module-Surface gibt es absichtlich kein `assets/css/manager.css`. Lokale Styles bleiben nur für die Resource-Editor-Bridge innerhalb der nativen Evolution Resource-Tabs erlaubt.

## UI Safety Rules

- Keine inline scripts in Dictionary oder Configuration hinzufügen.
- Keine globalen `.evo-ui-*` Selektoren aus sLang stylen.
- Keine lokalen Manager-Layout-Klassen für Dictionary oder Configuration wieder einführen.
- Resource-Tab-Kompatibilitätsstyles unter `.slang-resource-tab-page` halten.
- Modul-Icons und Top-Tabs am sArticles/evo-ui Standard ausrichten.
- Für Buttons, Choices, Modals, Pagination und Tabellen evo-ui Komponenten verwenden.
