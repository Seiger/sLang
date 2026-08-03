# Référence

Lookup rapide des APIs et classes sLang.

## Helpers

| API | Rôle |
| --- | --- |
| `sLang::langDefault()` | Langue par défaut. |
| `sLang::langConfig()` | Langues du site. |
| `sLang::langFront()` | Langues frontend. |
| `sLang::langSwitcher()` | Données du sélecteur de langue. |
| `sLang::hreflang()` | Alternatives localisées. |

## Classes

| Classe | Rôle |
| --- | --- |
| `Seiger\sLang\Models\sLangTranslate` | Modèle du dictionnaire. |
| `Seiger\sLang\Models\sLangContent` | Contenu locale-aware. |
| `Seiger\sLang\Support\LanguageBridge` | Pont des langues. |
| `Seiger\sLang\Livewire\SettingsPanel` | Formulaire de configuration. |
| `Seiger\sLang\Tables\TranslatesTableData` | Provider du dictionnaire. |

## Entrypoints De Test

| Commande | Rôle |
| --- | --- |
| `php tests/run.php` | Contrats statiques du package. |
| `php docs/checks/docs-check.php` | Structure dDocs, liens, headings et code fences. |
| `php tests/regression/slang-demo-regression.php demo/core` | Régression base de données Dictionary et Settings. |
| `php tests/demo-smoke.php demo/core --url=http://127.0.0.1:8788` | Smoke HTTP du module et des resource tabs. |
