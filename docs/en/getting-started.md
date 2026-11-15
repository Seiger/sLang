---
sidebar_position: 2
---

# Getting Started

## Requirements

- Evolution CMS **^3.5**. The minimum patch version can depend on the resolved EvoUI version.
- PHP **^8.4**
- Composer **2.2+**
- One of: **MySQL 8.0+** / **MariaDB 10.5+** / **PostgreSQL 10+** / **SQLite 3.25+**

## Install by artisan package installer

Go to your `core` folder:

```console
cd path/to/your/evolution/cms/core
```

Run the artisan command:

The command below installs the stable 2.x release once it is available on Packagist.

```console
php artisan package:installrequire seiger/slang "^2.0"
```

Publish package files:

```console
php artisan vendor:publish --provider="Seiger\sLang\sLangServiceProvider"
```

Create the database structure:

```console
php artisan migrate
```

## Management

After installing the module, you can use it immediately. Path to the module in the administrator panel: **Admin Panel -> Modules -> Multilingual**.

The resource includes tabs for each language separately.

![Resource editor with the Ukrainian tab selected](../assets/images/slang-resource-editor.png)

[Management tabs](management-tabs.md)

## Extra

If you write your own code that can integrate with the sLang module, check the module presence through a configuration variable.

```php
if (evo()->getConfig('check_sLang', false)) {
    // Your code
}
```

If the plugin is installed, `evo()->getConfig('check_sLang', false)` always returns `true`. Otherwise, it returns `false`.

## Working with localized content

Use the `sLangContent` model to fetch translated resources:

```php
use Seiger\sLang\Models\sLangContent;

$items = sLangContent::active()->get(); // locale resolved automatically
$itemsEn = sLangContent::lang('en')->withTVs(['preview'])->get();
```

> **Deprecated:** `langAndTvs()` still works for existing code. Use `lang()` and `withTVs()` for new queries.
