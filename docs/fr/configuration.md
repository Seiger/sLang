# Configuration

Cette référence liste les paramètres principaux.

## Paramètres

| Clé | Rôle |
| --- | --- |
| `s_lang_default` | Langue par défaut. |
| `s_lang_default_show` | Afficher la langue par défaut dans l'URL. |
| `s_lang_config` | Langues du site maintenues dans le manager. |
| `s_lang_front` | Langues publiques du frontend. |
| `s_lang_url_map` | Segments URL personnalisés. |
| `s_lang_tvs` | Template Variables multilingues. |
| `check_sLang` | Compatibility check toggle. |

## Fichiers

| Fichier | Rôle |
| --- | --- |
| `config/lang-list.php` | Liste des langues. |
| `config/coutry-lang.php` | Mapping langue-pays. |
| `config/translates/table.php` | Table evo-ui du dictionnaire. |

## Locale

La documentation ukrainienne utilise `uk`, jamais `ua`.

## Règles De Validation

- `s_lang_default` doit exister dans `s_lang_config`.
- `s_lang_front` doit être un sous-ensemble de `s_lang_config`.
- Les segments URL doivent rester uniques après suppression des slashs.
- Une nouvelle langue nécessite une modification de table avant la sauvegarde inline.
- Après suppression d'une langue du site, vérifier les langues frontend et la URL map.

## Actions De Maintenance

Dictionary synchronization ajoute les clés trouvées dans le code. Obsolete cleanup supprime les clés qui ne sont plus trouvées et qui ressemblent à des entrées générées ou inutilisées.
