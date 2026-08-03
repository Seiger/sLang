# Konfiguracja

Ta referencja opisuje główne ustawienia.

## Ustawienia

| Klucz | Cel |
| --- | --- |
| `s_lang_default` | Język domyślny. |
| `s_lang_default_show` | Pokazuj język domyślny w URL. |
| `s_lang_config` | Języki strony utrzymywane w managerze. |
| `s_lang_front` | Publiczne języki frontendu. |
| `s_lang_url_map` | Własne segmenty URL. |
| `s_lang_tvs` | Wielojęzyczne Template Variables. |
| `check_sLang` | Compatibility check toggle. |

## Pliki

| Plik | Cel |
| --- | --- |
| `config/lang-list.php` | Lista języków. |
| `config/coutry-lang.php` | Mapowanie język-kraj. |
| `config/translates/table.php` | Tabela słownika evo-ui. |

## Locale

Ukraińska dokumentacja używa `uk`, nie `ua`.

## Reguły Walidacji

- `s_lang_default` musi istnieć w `s_lang_config`.
- `s_lang_front` musi być podzbiorem `s_lang_config`.
- Segmenty URL powinny być unikalne po usunięciu slashy.
- Nowy język wymaga modyfikacji tabeli przed zapisem inline.
- Po usunięciu języka strony sprawdź języki frontendu i URL map.

## Akcje Utrzymaniowe

Dictionary synchronization dodaje klucze znalezione w kodzie. Obsolete cleanup usuwa klucze, których już nie znaleziono i które wyglądają na wygenerowane albo nieużywane.
