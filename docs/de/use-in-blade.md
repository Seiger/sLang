# Verwendung in Blade

## Aktuelle Sprache

```php
{{ evo()->getLocale() }}
```

oder

```php
{{ evo()->getConfig('lang') }}
```

## Standardsprache

```php
{{ evo()->getConfig('s_lang_default') }}
```

## Liste der Frontend-Sprachen durch Komma getrennt

```php
{{ evo()->getConfig('s_lang_front') }}
```

## Übersetzung von Phrasen

```php
In Blade:
@lang('phrase')

In Controller:
__('phrase')
```

## Lokalisierte Seitenversionen für Google hreflang

```php
{!! sLang::hreflang() !!}
```

## Sprachumschalter

Aktuelle Sprache mit Name oder Kurzname anzeigen:

```php
{{ Str::upper(sLang::langSwitcher()[evo()->getConfig('lang')]['short']) }}
```

Sprachumschalter im Blade-Template:

```php
@foreach(sLang::langSwitcher() as $lang)
    <a href="{{ $lang['link'] }}">{{ Str::upper($lang['short']) }}</a>
@endforeach
```

## Menüliste

Standardmäßig bietet sLang 2 Menübereiche: **Main Menu** und **Footer Menu**. Diese Bereiche basieren auf den TV-Parametern **menu_main** und **menu_footer** und werden im Ressourcen-Einstellungs-Tab angezeigt.

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

Ausgabe im Blade-Template:

```php
@foreach($mainMenu as $menu)
    <a href="{{ $menu->fullLink }}" {!! $menu->linkAttributes !!}>{{ $menu->menutitle }}</a>
@endforeach
```

## TV-Variablen

Der Scope `withTVs()` erleichtert das Laden von TV-Parametern einer Ressource.

```php
$resource = sLangContent::withTVs(['tv_image'])->active()->first();
```

Anzeige im Template:

```php
{{ $resource->tv_image }}
```

Filterung nach TV-Wert:

```php
$resources = sLangContent::withTVs(['tv_image'])->whereTv('tv_image', '!=', '')->get();
```

> **Deprecated:** Der Helper `langAndTvs()` ist seit `1.0.8` veraltet und wird in `v1.2` entfernt. Ersetzen Sie ihn durch `lang()` und `withTVs()`.

## Ressourcenfelder im Admin-Panel

Die Anzeige von Ressourcenfeldern in allgemeinen Tabs kann über das Event `sLangDocFormFieldRender` gesteuert werden.

```php
Event::listen('evolution.sLangDocFormFieldRender', function($params) {
    if ($params['content']['template'] == 7 && $params['name'] == 'introtext') {
        return view('slang.introtext', $params)->render();
    }
});
```
