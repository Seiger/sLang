# Getting Started

## Requirements

- Evolution CMS **3.3+**
- PHP **8.3+**
- Composer **2.2+**
- One of: **MySQL 8.0+** / **MariaDB 10.5+** / **PostgreSQL 10+** / **SQLite 3.25+**

## Install by artisan package installer

Go to your `core` folder:

```console
cd path/to/your/evolution/cms/core
```

Run the artisan command:

```console
php artisan package:installrequire seiger/slang "*"
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

![Multilingual tabs](https://github.com/Seiger/slang/releases/download/v1.0.0/sLang.png)

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

> **Deprecated:** The `langAndTvs()` scope is deprecated since `1.0.8` and will be removed in `v1.2`. Replace it with `lang()` and `withTVs()`.
