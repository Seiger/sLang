# Utilisation dans Blade

## Langue courante

```php
{{ evo()->getLocale() }}
```

ou

```php
{{ evo()->getConfig('lang') }}
```

## Langue par défaut

```php
{{ evo()->getConfig('s_lang_default') }}
```

## Liste des langues frontend séparées par des virgules

```php
{{ evo()->getConfig('s_lang_front') }}
```

## Traduction des phrases

```php
In Blade:
@lang('phrase')

In Controller:
__('phrase')
```

## Versions localisées de la page pour Google hreflang

```php
{!! sLang::hreflang() !!}
```

## Sélecteur de langue

Afficher la langue courante avec son nom ou son nom court:

```php
{{ Str::upper(sLang::langSwitcher()[evo()->getConfig('lang')]['short']) }}
```

Sélecteur de langue dans un template Blade:

```php
@foreach(sLang::langSwitcher() as $lang)
    <a href="{{ $lang['link'] }}">{{ Str::upper($lang['short']) }}</a>
@endforeach
```

## Liste de menu

Par défaut, sLang propose 2 zones de menu: **Main Menu** et **Footer Menu**. Elles sont basées sur les paramètres TV **menu_main** et **menu_footer** et apparaissent dans l'onglet des paramètres de ressource.

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

Sortie dans Blade:

```php
@foreach($mainMenu as $menu)
    <a href="{{ $menu->fullLink }}" {!! $menu->linkAttributes !!}>{{ $menu->menutitle }}</a>
@endforeach
```

## Variables TV

Le scope `withTVs()` facilite la récupération des paramètres TV associés à une ressource.

```php
$resource = sLangContent::withTVs(['tv_image'])->active()->first();
```

Affichage dans le template:

```php
{{ $resource->tv_image }}
```

Filtrer par valeur TV:

```php
$resources = sLangContent::withTVs(['tv_image'])->whereTv('tv_image', '!=', '')->get();
```

> **Deprecated:** Le helper `langAndTvs()` est déprécié depuis `1.0.8` et sera supprimé en `v1.2`. Remplacez-le par `lang()` et `withTVs()`.

## Champs de ressource dans l'admin

Vous pouvez contrôler l'affichage des champs de ressource dans les onglets généraux avec l'événement `sLangDocFormFieldRender`.

```php
Event::listen('evolution.sLangDocFormFieldRender', function($params) {
    if ($params['content']['template'] == 7 && $params['name'] == 'introtext') {
        return view('slang.introtext', $params)->render();
    }
});
```
