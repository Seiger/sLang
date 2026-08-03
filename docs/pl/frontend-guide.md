# Przewodnik frontend

Ten przewodnik opisuje użycie w szablonach i granice UI managera.

## Blade

Zobacz [Użycie w Blade](use-in-blade.md).

```blade
{{ sLang::langDefault() }}
{{ sLang::hreflang() }}
@lang('global.save')
```

## Ekrany Modułu

Dictionary i Configuration są pełnymi ekranami modułu evo-ui. Muszą używać wspólnych primitives evo-ui dla tabel, choices, przycisków, modali, formularzy, dirty state, paginacji i akcji wiersza. Nie dodawaj stylesheetu pakietu dla tych ekranów i nie styluj globalnych selektorów managera z sLang.

Shell modułu ładuje assety evo-ui oraz tylko mały synchronizer `assets/js/manager.js` dla tytułu i ikony. Jeśli Dictionary lub Configuration potrzebuje nowego zachowania wizualnego, dodaj primitive najpierw w evo-ui, a potem użyj go w sLang.

## Zakładki Zasobu

Zakładki zasobu używają markerów `data-slang-*` i stylów `.slang-resource-tab-page`, ponieważ działają w natywnym edytorze Evolution.

## Assets

```text
assets/js/manager.js
```

Dla manager module surface celowo nie ma `assets/css/manager.css`. Lokalne style są dozwolone tylko dla resource editor bridge w natywnych zakładkach zasobu Evolution.

## UI Safety Rules

- Nie dodawaj inline scripts w Dictionary lub Configuration.
- Nie styluj globalnych selektorów `.evo-ui-*` z sLang.
- Nie przywracaj lokalnych klas layoutu managera dla Dictionary lub Configuration.
- Style kompatybilności resource tabs trzymaj pod `.slang-resource-tab-page`.
- Ikony modułu i top tabs wyrównuj ze standardem sArticles/evo-ui.
- Dla przycisków, choices, modali, paginacji i tabel używaj komponentów evo-ui.
