# Użycie w Blade

## Aktualny język

```php
{{ evo()->getLocale() }}
```

albo

```php
{{ evo()->getConfig('lang') }}
```

## Język domyślny

```php
{{ evo()->getConfig('s_lang_default') }}
```

## Lista języków frontendu rozdzielona przecinkami

```php
{{ evo()->getConfig('s_lang_front') }}
```

## Tłumaczenie fraz

```php
In Blade:
@lang('phrase')

In Controller:
__('phrase')
```

## Lokalne wersje strony dla Google hreflang

```php
{!! sLang::hreflang() !!}
```

## Przełącznik języka

Pokaż aktualny język nazwą albo skrótem:

```php
{{ Str::upper(sLang::langSwitcher()[evo()->getConfig('lang')]['short']) }}
```

Przełącznik języka w szablonie Blade:

```php
@foreach(sLang::langSwitcher() as $lang)
    <a href="{{ $lang['link'] }}">{{ Str::upper($lang['short']) }}</a>
@endforeach
```

## Lista menu

Domyślnie sLang oferuje 2 obszary menu: **Main Menu** i **Footer Menu**. Są one oparte na parametrach TV **menu_main** i **menu_footer** oraz widoczne w zakładce ustawień zasobu.

```php
use Seiger\sLang\Models\sLangContent;

$this->data['mainMenu'] = sLangContent::withTVs(['tv_image'])
    ->where('hidemenu', 0)
    ->whereTv('menu_main', 1)
    ->orderBy('parent_id')
    ->orderBy('menuindex')
    ->active()
    ->get()
    ->toTreeParent(0);
```

Wyjście w Blade:

```php
@foreach($mainMenu as $menu)
    <a href="{{ $menu->fullLink }}" {!! $menu->linkAttributes !!}>{{ $menu->menutitle }}</a>
@endforeach
```

## Zmienne TV

Scope `withTVs()` ułatwia pobieranie parametrów TV powiązanych z zasobem.

```php
$resource = sLangContent::withTVs(['tv_image'])->active()->first();
```

Wyświetlenie w szablonie:

```php
{{ $resource->tv_image }}
```

Filtrowanie według wartości TV:

```php
$resources = sLangContent::withTVs(['tv_image'])->whereTv('tv_image', '!=', '')->get();
```

> **Deprecated:** helper `langAndTvs()` jest przestarzały od `1.0.8` i zostanie usunięty w `v1.2`. Zastąp go przez `lang()` i `withTVs()`.

## Pola zasobu w panelu admina

Wyświetlaniem pól zasobu w ogólnych zakładkach można sterować przez event `sLangDocFormFieldRender`.

```php
Event::listen('evolution.sLangDocFormFieldRender', function($params) {
    if ($params['content']['template'] == 7 && $params['name'] == 'introtext') {
        return view('slang.introtext', $params)->render();
    }
});
```
