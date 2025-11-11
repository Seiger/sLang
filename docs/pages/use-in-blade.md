---
layout: page
title: Use in Blade
description: Use sLang code in Blade layouts
permalink: /use-in-blade/
---

## Current language:

```php
{% raw %}{{evo()->getLocale()}}{% endraw %}
or
{% raw %}{{evo()->getConfig('lang')}}{% endraw %}
```

## Default language:
```php
{% raw %}{{evo()->getConfig('s_lang_default')}}{% endraw %}
```

## List of frontend languages by comma:
```php
{% raw %}{{evo()->getConfig('s_lang_front')}}{% endraw %}
```

## Translation of phrases:
```php
In Blade:
@lang('phrase')

In Controller:
__('phrase')
```

## Localized versions of your page for Google hreflang
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
    $this->data['mainMenu'] = sLangContent::withTVs(['menu_main'])
        ->whereTv('menu_main', 1)
        ->where('hidemenu', 0)
        ->orderBy('menuindex')
        ->active()
        ->get();

    $this->data['footerMenu'] = sLangContent::withTVs(['menu_footer'])
        ->whereTv('menu_footer', 1)
        ->where('hidemenu', 0)
        ->orderBy('menuindex')
        ->active()
        ->get();
}
```

Output in the Blade template
```php
@if($mainMenu)
    <ul>
        @foreach($mainMenu as $menu)
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

The `withTVs()` scope makes it easy to retrieve TV parameters associated with a resource. For example, the **tv_image** parameter.

Get in the controller.
```php
$resource = sLangContent::withTVs(['tv_image'])->active()->first();
```

Display in the template.
```php
{% raw %}{{$resource->tv_image}}{% endraw %}
```

The `whereTv()` method allows you to use a filter based on the value of the TV parameter if necessary.
```php
$resource = sLangContent::withTVs(['tv_image'])->whereTv('tv_image', '!=', '')->get();
```

> **Deprecated:** The `langAndTvs()` helper is deprecated since `1.0.8` and will be removed in `v1.2`. Replace it with the `lang()` and `withTVs()` scopes.

## Resource fields in Admin panel

You can control the display of resource fields on general tabs through an event ```sLangDocFormFieldRender```.

Usage example from your plugin:
```php
Event::listen('evolution.sLangDocFormFieldRender', function($params) {
    if ($params['content']['template'] == 7) {
        if ($params['name'] == 'introtext') {
            return view('slang.introtext', $params)->render();
        }
    }
});
```
