# Use in Blade

## Current language

```php
{{ evo()->getLocale() }}
```

or

```php
{{ evo()->getConfig('lang') }}
```

## Default language

```php
{{ evo()->getConfig('s_lang_default') }}
```

## List of frontend languages by comma

```php
{{ evo()->getConfig('s_lang_front') }}
```

## Translation of phrases

```php
In Blade:
@lang('phrase')

In Controller:
__('phrase')
```

## Localized versions of your page for Google hreflang

```php
{!! sLang::hreflang() !!}
```

## Language Switcher

Show current language anywhere with name or short name:

```php
{{ Str::upper(sLang::langSwitcher()[evo()->getConfig('lang')]['short']) }}
```

Implement a language switcher in a Blade template:

```php
@foreach(sLang::langSwitcher() as $lang)
    <a href="{{ $lang['link'] }}">{{ Str::upper($lang['short']) }}</a>
@endforeach
```

Example `langSwitcher` result:

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

By default, sLang offers 2 menu areas: **Main Menu** and **Footer Menu**. These areas are built on TV **menu_main** and **menu_footer** parameters and displayed in the resource settings tab.

Data preparation in `BaseController.php`:

```php
use Seiger\sLang\Models\sLangContent;

...

public function globalElements()
{
    // Tree menu
    $this->data['mainMenu'] = sLangContent::withTVs(['tv_image'])
        ->where('hidemenu', 0)
        ->whereTv('menu_main', 1)
        ->orderBy('parent_id')
        ->orderBy('menuindex')
        ->active()
        ->get()
        ->toTreeParent(0);

    // Simple menu
    $this->data['mainMenu'] = sLangContent::withTVs(['tv_image'])
        ->whereTv('menu_main', 1)
        ->where('hidemenu', 0)
        ->orderBy('menuindex')
        ->active()
        ->get();

    $this->data['footerMenu'] = sLangContent::whereTv('menu_footer', 1)
        ->where('hidemenu', 0)
        ->orderBy('menuindex')
        ->active()
        ->get();
}
```

Output in the Blade template:

```php
@if($mainMenu)
    <ul>
        @foreach($mainMenu as $menu)
            <li>
                @if($menu->id == evo()->documentObject['id'])
                    <a>{{ $menu->menutitle }}</a>
                @else
                    <a href="{{ $menu->fullLink }}" {!! $menu->linkAttributes !!}>{{ $menu->menutitle }}</a>
                @endif
            </li>
        @endforeach
    </ul>
@endif
```

For unlimited nesting, move the menu item into a recursive partial.

## TV variables

The `withTVs()` scope makes it easy to retrieve TV parameters associated with a resource.

```php
$resource = sLangContent::withTVs(['tv_image'])->active()->first();
```

Display in the template:

```php
{{ $resource->tv_image }}
```

The `whereTv()` method allows filtering by TV parameter value:

```php
$resources = sLangContent::withTVs(['tv_image'])->whereTv('tv_image', '!=', '')->get();
```

Combine multiple helpers:

```php
$resources = sLangContent::lang('uk')->withTVs(['color', 'price'])->whereParent($parentId)->active()->get();
```

> **Deprecated:** The `langAndTvs()` helper is deprecated since `1.0.8` and will be removed in `v1.2`. Replace it with the `lang()` and `withTVs()` scopes.

## Resource fields in Admin panel

You can control the display of resource fields on general tabs through the `sLangDocFormFieldRender` event.

```php
Event::listen('evolution.sLangDocFormFieldRender', function($params) {
    if ($params['content']['template'] == 7) {
        if ($params['name'] == 'introtext') {
            return view('slang.introtext', $params)->render();
        }
    }
});
```
