# Konfiguration

Diese Referenz beschreibt die wichtigsten Einstellungen.

## Einstellungen

| Schlüssel | Zweck |
| --- | --- |
| `s_lang_default` | Standardsprache der Website. |
| `s_lang_default_show` | Standardsprache in URL anzeigen. |
| `s_lang_config` | Im Manager gepflegte Website-Sprachen. |
| `s_lang_front` | Öffentliche Frontend-Sprachen. |
| `s_lang_url_map` | Eigene URL-Segmente pro Sprache. |
| `s_lang_tvs` | Mehrsprachige Template Variables. |
| `check_sLang` | Compatibility check toggle. |

## Dateien

| Datei | Zweck |
| --- | --- |
| `config/lang-list.php` | Sprachliste. |
| `config/coutry-lang.php` | Sprach-Land-Mapping. |
| `config/translates/table.php` | evo-ui Wörterbuch-Tabelle. |

## Locale

Ukrainische Dokumentation verwendet `uk`, nicht `ua`.

## Validierungsregeln

- `s_lang_default` muss in `s_lang_config` vorhanden sein.
- `s_lang_front` muss eine Teilmenge von `s_lang_config` sein.
- URL-Segmente sollten nach dem Trimmen von Slashes eindeutig sein.
- Neue Sprachen brauchen eine Tabellenanpassung, bevor Werte gespeichert werden.
- Nach dem Entfernen einer Sprache Frontend-Sprachen und URL map prüfen.

## Wartungsaktionen

Dictionary synchronization fügt im Code gefundene Schlüssel hinzu. Obsolete cleanup entfernt Einträge, die nicht mehr gefunden werden und wie generierte oder ungenutzte Schlüssel aussehen.
