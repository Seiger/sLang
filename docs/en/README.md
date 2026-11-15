---
slug: /
sidebar_position: 1
---

# sLang Documentation

[![Latest stable version](https://img.shields.io/packagist/v/seiger/slang?label=version)](https://packagist.org/packages/seiger/slang)
[![Evolution CMS version requirement](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FSeiger%2FsLang%2F2.x%2Fcomposer.json&query=%24.require%5B%27evolution-cms%2Fevolution%27%5D&label=CMS&prefix=Evolution%20&color=brightgreen)](https://github.com/evolution-cms/evolution)
[![PHP 8.4, 8.5 and 8.6](https://img.shields.io/badge/PHP-8.4_%7C_8.5_%7C_8.6-777BB4?logo=php&logoColor=white)](getting-started.md)
[![License](https://img.shields.io/packagist/l/seiger/slang)](https://packagist.org/packages/seiger/slang)
[![Open issues](https://img.shields.io/github/issues/Seiger/slang)](https://github.com/Seiger/slang/issues)
[![GitHub stars](https://img.shields.io/packagist/stars/Seiger/slang)](https://github.com/Seiger/sLang)
[![Total downloads](https://img.shields.io/packagist/dt/seiger/slang)](https://packagist.org/packages/seiger/slang)
![Made in Ukraine](https://img.shields.io/badge/made_in-Ukraine-ffd700.svg?labelColor=0057b7)

[★ Star on GitHub](https://github.com/Seiger/sLang)

**sLang** is a multilingual management module for Evolution CMS. It helps manage translation phrases, frontend languages, multilingual resource tabs, URL language segments, automatic translation helpers, and locale-aware content APIs.

## Screenshots

![Translation dictionary](../assets/images/slang-dictionary.png)

![Language configuration](../assets/images/slang-configuration.png)

![Resource editor with the Ukrainian tab selected](../assets/images/slang-resource-editor.png)

## Pages

- [Getting Started](getting-started.md)
- [User Guide](user-guide.md)
- [Management tabs](management-tabs.md)
- [Configuration](configuration.md)
- [Use in Blade](use-in-blade.md)
- [Developer Guide](developer-guide.md)
- [Reference](reference.md)
- [Frontend Guide](frontend-guide.md)
- [Resource Bridge](resource-bridge.md)
- [Troubleshooting](troubleshooting.md)
- [Release Checklist](release-checklist.md)

## Features

- Automatic phrase translation through Google or custom translation providers.
- Automatic translation lookup in templates.
- Multilingual resource tabs.
- Unlimited translation language support.
- Multilingual SEO support.

## Locale-aware content model

- `Seiger\sLang\Models\sLangContent` respects the current locale by default through `evo()->getLocale()`.
- Explicit locale selection is available through the `lang()` scope.
- Template variables can be appended with `withTVs()`.
- `langAndTvs()` still works for existing code. Use `lang()` and `withTVs()` for new queries.
