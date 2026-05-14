# User Guide

This guide explains the daily manager workflows in sLang: maintaining the dictionary, configuring site languages, and using multilingual resource tabs.

## Open The Module

Open **Modules** and choose **Multilanguage**. The module has two workspaces:

- **Dictionary** for phrase keys and translations.
- **Configuration** for default language, frontend languages, URL segments, multilingual TVs, and maintenance actions.

## Maintain Dictionary Phrases

Use **Synchronize** to scan templates and package views for translation keys. The action reuses the existing backend parser and adds missing keys to the database without reloading the manager iframe.

Use the green add button when a key is known but not yet present in code. sLang opens a create modal where you enter the key and values for the currently configured site languages. The key column is read-only after creation so templates keep a stable lookup string.

Use the translate buttons in a language column to fill empty values from the default language. Row translate buttons fill one phrase; the header translate button fills all empty values in that language.

Use the delete action only for keys that are no longer used. For keys created by scanning, prefer the cleanup action in Configuration so obsolete records are confirmed in one place.

## Configure Languages

In **Configuration**, choose the default language first. The **Use in URL** checkbox controls whether the default language also appears as a URL prefix.

Select **Site languages** before selecting **Frontend languages**. Frontend language choices are limited to the selected site languages, so the public site cannot expose a locale that the manager does not maintain.

Set **Language folder names** for every selected site language. Keep the locale code when the public URL segment should match the locale; override it only when the site needs a custom segment.

Select **Multilingual parameters** for Template Variables that should be copied into localized resource tabs.

## Clean Obsolete Keys

Configuration can show obsolete dictionary keys found by comparing the database against scanned templates. Use **Cleanup obsolete translations** only after reviewing the count and confirmation message.

## Edit Resources

When editing a resource, sLang adds language-specific tabs for general fields, Template Variables, and settings. These tabs are embedded in the native Evolution resource form, so save the resource with the normal Evolution save buttons.

## Recommended Work Order

For a new multilingual site, configure languages before filling the dictionary:

1. Choose the default language.
2. Select all site languages.
3. Select frontend languages from the site language subset.
4. Review URL folder names.
5. Select multilingual Template Variables.
6. Save Configuration.
7. Run Dictionary synchronization.
8. Fill missing translations or use the per-language bulk translate action.

## What To Check After Changes

After changing languages, open a resource and verify that the general fields, Template Variables, and settings tabs show the same language set. Then open the public site in each frontend language and confirm that URL prefixes and language switcher links match the Configuration screen.
