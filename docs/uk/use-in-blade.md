# Використання в Blade

## Поточна мова

```php
{{ evo()->getLocale() }}
```

або

```php
{{ evo()->getConfig('lang') }}
```

## Мова за замовчуванням

```php
{{ evo()->getConfig('s_lang_default') }}
```

## Список мов фронтенду через кому

```php
{{ evo()->getConfig('s_lang_front') }}
```

## Переклад фраз

```php
In Blade:
@lang('phrase')

In Controller:
__('phrase')
```

## Локалізовані версії сторінки для Google hreflang

```php
{!! sLang::hreflang() !!}
```

## Перемикач мов

Показати поточну мову за назвою або короткою назвою:

```php
{{ Str::upper(sLang::langSwitcher()[evo()->getConfig('lang')]['short']) }}
```

Реалізація перемикача мов у Blade-шаблоні:

```php
@foreach(sLang::langSwitcher() as $lang)
    <a href="{{ $lang['link'] }}">{{ Str::upper($lang['short']) }}</a>
@endforeach
```

## Список меню

За замовчуванням sLang пропонує 2 області меню: **Main Menu** і **Footer Menu**. Ці області побудовані на TV-параметрах **menu_main** і **menu_footer** та відображаються у вкладці налаштувань ресурсу.

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

Вивід у Blade-шаблоні:

```php
@if($mainMenu)
    <ul>
        @foreach($mainMenu as $menu)
            <li>
                <a href="{{ $menu->fullLink }}" {!! $menu->linkAttributes !!}>{{ $menu->menutitle }}</a>
            </li>
        @endforeach
    </ul>
@endif
```

## TV-змінні

Scope `withTVs()` спрощує отримання TV-параметрів, пов'язаних із ресурсом.

```php
$resource = sLangContent::withTVs(['tv_image'])->active()->first();
```

Вивід у шаблоні:

```php
{{ $resource->tv_image }}
```

Фільтрація за значенням TV-параметра:

```php
$resources = sLangContent::withTVs(['tv_image'])->whereTv('tv_image', '!=', '')->get();
```

> **Deprecated:** helper `langAndTvs()` застарів з версії `1.0.8` і буде видалений у `v1.2`. Замініть його на `lang()` і `withTVs()`.

## Поля ресурсу в адмін-панелі

Ви можете керувати відображенням полів ресурсу на загальних вкладках через подію `sLangDocFormFieldRender`.

```php
Event::listen('evolution.sLangDocFormFieldRender', function($params) {
    if ($params['content']['template'] == 7 && $params['name'] == 'introtext') {
        return view('slang.introtext', $params)->render();
    }
});
```
