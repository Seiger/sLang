# sLang for Evolution CMS 3
![sLang](https://github.com/Seiger/slang/releases/download/v1.0.0/sLang.jpg)
[![Latest Stable Version](https://img.shields.io/packagist/v/seiger/slang?label=version)](https://packagist.org/packages/seiger/slang)
[![CMS Evolution](https://img.shields.io/badge/CMS-Evolution-brightgreen.svg)](https://github.com/evolution-cms/evolution)
![PHP version](https://img.shields.io/packagist/php-v/seiger/slang)
[![License](https://img.shields.io/packagist/l/seiger/slang)](https://packagist.org/packages/seiger/slang)
[![Issues](https://img.shields.io/github/issues/Seiger/slang)](https://github.com/Seiger/slang/issues)
[![Stars](https://img.shields.io/packagist/stars/Seiger/slang)](https://packagist.org/packages/seiger/slang)
[![Total Downloads](https://img.shields.io/packagist/dt/seiger/slang)](https://packagist.org/packages/seiger/slang)

**sLang** is a robust multilingual Management Module meticulously crafted for the Evolution CMS
admin panel. This dynamic package empowers users to seamlessly implement and manage
multilingual tools within the Evolution CMS environment. By utilizing Evolution CMS
as its platform, sLang offers a streamlined solution for users seeking efficient and
intuitive ways to handle diverse language content, making it an indispensable asset
for administrators and developers navigating the intricacies of multilingual website
management.

The work of the module is based on the use of the standard Laravel functionality for
multilingualism. This foundation ensures a reliable and well-established framework for
managing multilingual aspects, enhancing the module's performance and aligning it with
industry best practices. With its focus on simplicity and integration, sLang emerges as
an essential companion for those aiming to enhance the linguistic versatility of their
Evolution CMS-powered websites.

## Features

- [x] Automatic Phrase Translation through Google or Custom.
- [x] Automatic search for translations in templates.
- [x] Multilingual tabs in resource.
- [x] Unlimited Translation Language Support.
- [x] Multilingual SEO Support.

![Multilingual tabs](https://github.com/Seiger/slang/releases/download/v1.0.0/sLang.png)

### Requirements

- Evolution CMS **3.3+**
- PHP **8.3+**
- Composer **2.2+**
- One of: **MySQL 8.0+** / **MariaDB 10.5+** / **PostgreSQL 10+** / **SQLite 3.25+**

## Install by artisan package installer

Go to You /core/ folder:

```console
cd core
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

## Usage in blade
Current language:
```php
{{evo()->getConfig('lang')}}
or
{{evo()->getLocale()}}
```

Default language:
```php
{{evo()->getConfig('s_lang_default')}}
```

List of frontend languages by comma:
```php
{{evo()->getConfig('s_lang_front')}}
```

Translation of phrases:
```php
@lang('phrase')
```

Localized versions of your page for Google hreflang
```php
{!!sLang::hreflang()!!}
```

## Content management

Show current language anywhere with name or shortname
```php
{{Str::upper(sLang::langSwitcher()[evo()->getConfig('lang')]['short'])}}
```

Implementing a Language Switcher
```php
@foreach(sLang::langSwitcher() as $lang)
    <a href="{{$lang['link']}}">{{Str::upper($lang['ISO 639-1'])}}</a>
@endforeach
```

## Working with localized content (Eloquent)

The `Seiger\sLang\Models\sLangContent` model now applies the current locale automatically.

- Fetch translated rows for the active locale:
  ```php
  use Seiger\sLang\Models\sLangContent;

  $items = sLangContent::active()->get(); // locale resolved via evo()->getLocale()
  ```
- Select a specific locale explicitly:
  ```php
  $items = sLangContent::lang('en')->get();
  ```
- Include template variables while keeping the locale filtering:
  ```php
  $items = sLangContent::withTVs(['color', 'price'])->get();
  ```
- Combine multiple helpers:
  ```php
  $items = sLangContent::lang('uk')
      ->withTVs(['color', 'price'])
      ->whereParent($parentId)
      ->active()
      ->get();
  ```
- Legacy helper is preserved for backward compatibility:
  ```php
  $items = sLangContent::langAndTvs('en', ['color', 'price'])->get();
  ```
  > **Deprecated:** `langAndTvs()` is deprecated since `1.0.8` and is scheduled for removal in `v1.2`. Use `lang()->withTVs()` instead.

[See full documentation here](https://seiger.github.io/sLang/)