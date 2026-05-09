---
layout: page
title: Getting started
description: Getting started with sLang
permalink: /getting-started/
---

### Requirements

- Evolution CMS **3.3+**
- PHP **8.3+**
- Composer **2.2+**
- One of: **MySQL 8.0+** / **MariaDB 10.5+** / **PostgreSQL 10+** / **SQLite 3.25+**

## Install by artisan package installer

Go to You /core/ folder:

```console
cd path/to/your/evolution/cms/core
```

Run php artisan command

```console
php artisan package:installrequire seiger/slang "*"
```

```console
php artisan vendor:publish --provider="Seiger\sLang\sLangServiceProvider"
```

Run make DB structure with command:

```console
php artisan migrate
```

## Management

After installing the module, you can use it immediately. Path to the module in the
administrator panel **Admin Panel -> Modules -> Multilingual**.

The resource includes tabs for each language separately.

![Multilingual tabs](https://github.com/Seiger/slang/releases/download/v1.0.0/sLang.png)

[Management tabs]({{site.baseurl}}/management/){: .btn .btn-sky}

## Extra

If you write your own code that can integrate with the sLang module,
you can check the presence of this module in the system through a configuration variable.

```php
if (evo()->getConfig('check_sLang', false)) {
    // You code
}
```

If the plugin is installed, the result of ```evo()->getConfig('check_sLang', false)```
will always be ```true```. Otherwise, you will get an ```false```.

### Working with localized content

Use the `sLangContent` model to fetch translated resources:

```php
use Seiger\sLang\Models\sLangContent;

$items = sLangContent::active()->get(); // locale resolved automatically
$itemsEn = sLangContent::lang('en')->withTVs(['preview'])->get();
```

> **Deprecated:** The `langAndTvs()` scope is deprecated since `1.0.8` and will be removed in `v1.2`. Replace it with `lang()` and `withTVs()`.
