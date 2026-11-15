# sLang for Evolution CMS 3

![sLang dictionary with English, Ukrainian, and German translations](docs/assets/images/slang-dictionary.png)

[![Latest Stable Version](https://img.shields.io/packagist/v/seiger/slang?label=version)](https://packagist.org/packages/seiger/slang)
[![Evolution CMS version requirement](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FSeiger%2FsLang%2F2.x%2Fcomposer.json&query=%24.require%5B%27evolution-cms%2Fevolution%27%5D&label=CMS&prefix=Evolution%20&color=brightgreen)](https://github.com/evolution-cms/evolution)
[![PHP: 8.4, 8.5 and 8.6](https://img.shields.io/badge/PHP-8.4_%7C_8.5_%7C_8.6-777BB4?logo=php&logoColor=white)](#compatibility)
[![License](https://img.shields.io/packagist/l/seiger/slang)](https://packagist.org/packages/seiger/slang)
[![Issues](https://img.shields.io/github/issues/Seiger/slang)](https://github.com/Seiger/slang/issues)
[![Stars](https://img.shields.io/packagist/stars/Seiger/slang)](https://packagist.org/packages/seiger/slang)
[![Total Downloads](https://img.shields.io/packagist/dt/seiger/slang)](https://packagist.org/packages/seiger/slang)
![Made in Ukraine](https://img.shields.io/badge/made_in-Ukraine-ffd700.svg?labelColor=0057b7)

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

<details>
<summary>More screenshots</summary>

![Language configuration](docs/assets/images/slang-configuration.png)

![Resource editor with the Ukrainian translation tab selected](docs/assets/images/slang-resource-editor.png)

</details>

## Install by artisan package installer

Go to You /core/ folder:

```console
cd core
```

Install the stable 2.x release once it is available on Packagist.

```console
php artisan package:installrequire seiger/slang "^2.0"
```

```console
php artisan vendor:publish --provider="Seiger\sLang\sLangServiceProvider"
```

Run make DB structure with command:

```console
php artisan migrate
```

## Compatibility

Composer requires PHP ^8.4 and Evolution CMS ^3.5. The installed EvoUI version can require a newer Evolution CMS patch. Manager access was verified on Evolution CMS 3.5.9. Package checks passed on PHP 8.4, 8.5 and the PHP 8.6.0 RC5 preview.

## Local shared demo workflow

This repo is developed inside the shared Extras demo:

```console
cd /path/to/Extras/sArticles/demo/core
composer update seiger/slang --with-all-dependencies
php artisan package:discover
php artisan migrate --force
```

The shared demo also loads `sArticles`, `dmi3yy/dissues`, `sSeo`, `evo-ui`,
`dTui-editor`, and `eTinyMCE`. Before writing to dIssues task data, create a DB
backup:

```console
php /path/to/MiddleDuck/skills/dissues-demo-ops/scripts/dissues-demo.php backup
```

Run the sLang smoke check from this repo:

```console
php tests/demo-smoke.php --url=http://127.0.0.1:8791
```

Run the targeted regression test for Dictionary CRUD and Settings state:

```console
php tests/regression/slang-demo-regression.php
```

The manager module shell now uses evo-ui for Dictionary and Settings. Resource
edit multilingual tabs remain legacy-compatible and keep the existing
Evolution mutate form contract.

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

## Documentation

- [English](docs/en/README.md)
- [Українська](docs/uk/README.md)
- [Deutsch](docs/de/README.md)
- [Français](docs/fr/README.md)
- [Polski](docs/pl/README.md)

See the [documentation site](https://seiger.github.io/sLang/) and [GitHub Releases](https://github.com/Seiger/sLang/releases).
