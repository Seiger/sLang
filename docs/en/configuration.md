# Configuration

This reference lists the settings and files that control sLang behavior.

## Manager Settings

| Setting | Purpose | Notes |
| --- | --- | --- |
| `s_lang_default` | Default site language. | Must be present in the site language list. |
| `s_lang_default_show` | Show default language in URLs. | Disabled means default language URLs stay unprefixed. |
| `s_lang_config` | Site languages maintained by the manager. | Stored as locale codes such as `uk`, `en`, `de`. |
| `s_lang_front` | Public frontend languages. | Must be a subset of `s_lang_config`. |
| `s_lang_url_map` | Custom URL folder segment per language. | Empty or locale value keeps the default segment. |
| `s_lang_tvs` | Multilingual Template Variables. | Selected TVs are rendered in localized resource tabs. |
| `check_sLang` | Runtime check toggle. | Used by legacy compatibility checks. |

## Package Config Files

| File | Purpose |
| --- | --- |
| `config/lang-list.php` | Full language list shown in choices. |
| `config/coutry-lang.php` | Language-to-country mapping kept for compatibility. |
| `config/sLangAlias.php` | Facade alias registration. |
| `config/sLangCheck.php` | Install/runtime check definitions. |
| `config/translates/table.php` | evo-ui table preset for the dictionary. |

## Locale Rules

Use `uk` for Ukrainian docs and manager language values. Do not create a `ua` docs folder. Frontend URL segments can still be customized through `s_lang_url_map` when a site needs a different public path.

## Validation Rules

- `s_lang_default` must exist in `s_lang_config`.
- `s_lang_front` must be a subset of `s_lang_config`.
- URL folder names should be unique after trimming slashes.
- New languages require dictionary table modification before inline values can be saved.
- Removing a site language should be followed by a review of frontend languages and URL segments.

## Maintenance Actions

Dictionary synchronization adds discovered keys. Obsolete cleanup removes keys that are no longer found in code and still look like generated or unused entries. Both actions should be run from the manager, then verified with the regression suite before release.
