---
layout: page
title: Getting started
description: Getting started with sLang
permalink: /getting-started/
---

## Minimum requirements

- Evolution CMS 3.2.0
- PHP 8.1.0
- Composer 2.2.0
- PostgreSQL 10.23.0
- MySQL 8.0.3
- MariaDB 10.5.2
- SQLite 3.25.0

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
