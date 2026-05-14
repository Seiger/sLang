# Guide frontend

Ce guide décrit l'usage dans les templates et la limite de l'UI manager.

## Blade

Voir [Utilisation dans Blade](use-in-blade.md).

```blade
{{ sLang::langDefault() }}
{{ sLang::hreflang() }}
@lang('global.save')
```

## Module Manager

Dictionary et Configuration sont des écrans module evo-ui complets. Ils doivent utiliser les primitives partagées evo-ui pour tables, choices, boutons, modales, formulaires, dirty state, pagination et actions de ligne. N'ajoutez pas de stylesheet package pour ces écrans et ne stylez pas les sélecteurs manager globaux depuis sLang.

Le shell du module charge les assets evo-ui et seulement le petit synchroniseur `assets/js/manager.js` pour le titre et l'icône. Si Dictionary ou Configuration a besoin d'un nouveau comportement visuel, ajoutez d'abord la primitive dans evo-ui, puis consommez-la depuis sLang.

## Onglets De Ressource

Les onglets de ressource utilisent les marqueurs `data-slang-*` et les styles `.slang-resource-tab-page`, car ils vivent dans l'éditeur Evolution natif.

## Assets

```text
assets/js/manager.js
```

Il n'y a volontairement pas de `assets/css/manager.css` pour la surface manager du module. Les styles locaux restent autorisés uniquement pour la passerelle resource editor dans les onglets natifs Evolution.

## Règles UI

- Ne pas ajouter de scripts inline dans Dictionary ou Configuration.
- Ne pas styler les sélecteurs globaux `.evo-ui-*` depuis sLang.
- Ne pas réintroduire de classes layout manager locales pour Dictionary ou Configuration.
- Garder les styles de compatibilité des resource tabs sous `.slang-resource-tab-page`.
- Aligner les icônes du module et les top tabs avec le standard sArticles/evo-ui.
- Utiliser les composants evo-ui pour boutons, choices, modales, pagination et tables.
