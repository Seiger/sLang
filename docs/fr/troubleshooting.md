# Dépannage

Problèmes fréquents dans sLang.

## Choices Affiche Du Blade

Videz les vues compilées et vérifiez que Configuration est rendue par Livewire.

## Trop De Langues Frontend

`s_lang_front` doit être un sous-ensemble de `s_lang_config`.

## Colonne Locale Manquante

Après l'ajout d'une langue, exécutez la synchronisation ou la modification de table.

## Ressource Non Sauvegardée

Vérifiez la synchronisation avec `form#mutate`, surtout avec TinyMCE ou CodeMirror.

## Vérification Docs

```bash
php docs/checks/docs-check.php
```

## Checklist Release En Échec

Si docs coverage est sous 100%, vérifiez les canonical pages dans chaque locale et les signaux réels: `SettingsPanel`, `TranslatesTableData`, `LanguageBridge`, `s_lang_config`, `config/translates/table.php`.

Si EvoUI conformance signale du drift, séparez les vrais problèmes de module screen et le embedded resource bridge documenté. Le drift du module doit être corrigé; le bridge reste étroit et documenté.
