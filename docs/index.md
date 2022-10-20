# sLang for Evolution CMS 3
![sLang](https://github.com/Seiger/slang/releases/download/v1.0.0/sLang.jpg)
[![Latest Stable Version](https://img.shields.io/packagist/v/seiger/slang?label=version)](https://packagist.org/packages/seiger/slang)
[![CMS Evolution](https://img.shields.io/badge/CMS-Evolution-brightgreen.svg)](https://github.com/evolution-cms/evolution)
![PHP version](https://img.shields.io/packagist/php-v/seiger/slang)
[![License](https://img.shields.io/packagist/l/seiger/slang)](https://packagist.org/packages/seiger/slang)
[![Issues](https://img.shields.io/github/issues/Seiger/slang)](https://github.com/Seiger/slang/issues)
[![Stars](https://img.shields.io/packagist/stars/Seiger/slang)](https://packagist.org/packages/seiger/slang)
[![Total Downloads](https://img.shields.io/packagist/dt/seiger/slang)](https://packagist.org/packages/seiger/slang)

**sLang** Seiger Lang multi language Management Module for Evolution CMS admin panel.

The work of the module is based on the use of the standard Laravel functionality for multilingualism.

## Features

- [x] Automatic translation of phrases through Google.
- [x] Automatic search for translations in templates.
- [x] Multilingual tabs in resource.
- [x] Unlimited translation languages.

![Multilingual tabs](https://github.com/Seiger/slang/releases/download/v1.0.0/sLang.png)

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
```blade
[(lang)]
or
{{evo()->getConfig('lang')}}
or
{{evo()->getLocale()}}
```

Default language:
```php
[(s_lang_default)]
or
{{evo()->getConfig('s_lang_default')}}
```

List of frontend languages by comma:
```php
[(s_lang_front)]
or
{{evo()->getConfig('s_lang_default')}}
```

Translation of phrases:
```php
@lang('phrase')
```

Localized versions of your page for Google hreflang
```php
@php($sLang = new sLang())
{!!$sLang->hrefLang()!!}
```

## Content management

Implementing a Language Switcher
```php
@foreach(sLang::langSwitcher() as $lang)
    <a href="{{$lang['link']}}">{{Str::upper($lang['ISO 639-1'])}}</a>
@endforeach
```

Example returns langSwitcher
```php
^ array:2 [▼
  "uk" => array:6 [▼
    "name" => "Українська"
    "short" => "Укр"
    "ISO 639-1" => "uk"
    "ISO 639-3" => "ukr"
    "country" => "Ukraine"
    "link" => "https://example.com/"
  ]
  "en" => array:6 [▼
    "name" => "English"
    "short" => "Eng"
    "ISO 639-1" => "en"
    "ISO 639-3" => "eng"
    "country" => "English"
    "link" => "https://example.com/en/"
  ]
]
```

Get resources with translations for the current language.
```php
@foreach(\sLang\Models\sLangContent::langAndTvs(evo()->getConfig('lang'))->whereParent(11)->get() as $content)
    <li class="brands__item">
        <a class="text__mini" href="@makeUrl($content->id)">{{$content->menutitle}}</a>
    </li>
@endforeach
```

Get resources with TV parameters and filtering by TV parameter.
```php
$mainMenu = sLangContent::langAndTvs(evo()->getConfig('lang'), ['tv_image'])
    ->active()
    ->whereTv('tv_main_menu', 1)
    ->orderBy('menuindex')
    ->get();
```