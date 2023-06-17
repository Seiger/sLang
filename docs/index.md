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
```php
{% raw %}{{evo()->getLocale()}}{% endraw %}
or
{% raw %}{{evo()->getConfig('lang')}}{% endraw %}
```

Default language:
```php
{% raw %}{{evo()->getConfig('s_lang_default')}}{% endraw %}
```

List of frontend languages by comma:
```php
{% raw %}{{evo()->getConfig('s_lang_front')}}{% endraw %}
```

Translation of phrases:
```php
In Blade:
@lang('phrase')

In Controller:
__('phrase')
```

Localized versions of your page for Google hreflang
```php
{!!sLang::hreflang()!!}
```

## Language Switcher

Show current language anywhere with name or shortname
```php
{% raw %}{{Str::upper(sLang::langSwitcher()[evo()->getConfig('lang')]['short'])}}{% endraw %}
```

Implementing a Language Switcher in Blade template
```php
@foreach(sLang::langSwitcher() as $lang)
    <a href="{% raw %}{{$lang['link']}}{% endraw %}">{% raw %}{{Str::upper($lang['short'])}}{% endraw %}</a>
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

## Menu list

By default, sLang offers 2 menu areas. This is the **Main Menu** and the **Footer Menu**. These areas are built on TV **menu_main** and **menu_footer** parameters and displayed in the resource settings tab.

Data preparation in BaseController.php
```php
use Seiger\sLang\Models\sLangContent;

... 

public function globalElements()
{
    $this->data['menu_main'] = sLangContent::langAndTvs(evo()->getConfig('lang', 'uk'))
        ->whereTv('menu_main', 1)
        ->where('hidemenu', 0)
        ->orderBy('menuindex')
        ->active()
        ->get();

    $this->data['menu_footer'] = sLangContent::langAndTvs(evo()->getConfig('lang', 'uk'))
        ->whereTv('menu_footer', 1)
        ->where('hidemenu', 0)
        ->orderBy('menuindex')
        ->active()
        ->get();
}
```

Output in the Blade template
```php
@if($menu_main)
    <ul>
        @foreach($menu_main as $menu)
            @if($menu->id == evo()->documentObject['id'])
            <li class="active">
                <a>{% raw %}{{$menu->menutitle}}{% endraw %}</a>
            </li>
            @else
                <li>
                    <a href="@makeUrl($menu->id)">{% raw %}{{$menu->menutitle}}{% endraw %}</a>
                </li>
            @endif
        @endforeach
    </ul>
@endif
```

## TV variables

The ```langAndTvs()``` method makes it quite easy to get TV parameters associated with a resource. For example, the **tv_image** parameter.

Get in the controller.
```php
$resource = sLangContent::langAndTvs(evo()->getConfig('lang'), ['tv_image'])->active()->first();
```

Display in the template.
```php
{% raw %}{{$resource->tv_image}}{% endraw %}
```

The ```whereTv()``` method allows you to use a filter based on the value of the TV parameter if necessary.
```php
$resource = sLangContent::langAndTvs(evo()->getConfig('lang'), ['tv_image'])->whereTv('tv_image', '!=', '')->get();
```