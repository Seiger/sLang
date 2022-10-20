## Welcome to sLang

![slang](https://user-images.githubusercontent.com/12029039/167660172-9596574a-47ae-4304-a389-814bfa4c9e87.png)
[![GitHub version](https://img.shields.io/badge/version-v.1.1.1-blue)](https://github.com/Seiger/seigerlang/releases)
[![CMS Evolution](https://img.shields.io/badge/CMS-Evolution-brightgreen.svg)](https://github.com/evolution-cms/evolution)
![PHP version](https://img.shields.io/badge/PHP->=v7.4-red.svg?php=7.4)

Seiger Lang multi language Management Module for Evolution CMS admin panel.

The work of the module is based on the use of the standard Laravel functionality for multilingualism.

## Features
- [x] Based on **templatesEdit3** plugin.
- [x] Automatic translation of phrases through Google
- [x] Automatic search for translations in templates
- [x] Unlimited translation languages

## Requirements
Before installing the module, make sure you have the templatesEdit3 plugin installed.

## Use in controllers
For using this module on front pages your need add few includes to base controller
```php
require_once MODX_BASE_PATH . 'assets/modules/seigerlang/sLang.class.php';
```

## Use in templates
Current language:
```php
[(lang)]
```

Translation of phrases:
```php
@lang('phrase')
```

Default language:
```php
[(s_lang_default)]
```

List of frontend languages by comma:
```php
[(s_lang_front)]
```

Multilingual link:
```php
[~~[(catalog_root)]~~]
```

Localized versions of your page for Google hreflang
```php
@php($sLang = new sLang())
{!!$sLang->hrefLang()!!}
```

## Content management

Implementing a Language Switcher
```php
@foreach($sLang->langSwitcher() as $lang => $link)
    <a href="{{$link}}">{{Str::ucfirst($lang)}}</a>
@endforeach
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

## Setting
This module uses the **templatesEdit3** plugin to display multilingual content fields in the site's admin area.

If, after setting up the module, the multilingual fields are not displayed on the resource editing tab, then you need to check the file *MODX_BASE_PATH.'assets/plugins/templatesedit/configs/custom_fields.php'*
```php
<?php global $_lang, $modx; 
return [
	'en_pagetitle' => [
		'title' => $_lang['resource_title'].' (EN)',
		'help' => $_lang['resource_title_help'],
		'default' => '',
		'save' => '',
	],
	'en_longtitle' => [
		'title' => $_lang['long_title'].' (EN)',
		'help' => $_lang['resource_long_title_help'],
		'default' => '',
		'save' => '',
	],
	'en_description' => [
		'title' => $_lang['resource_description'].' (EN)',
		'help' => $_lang['resource_description_help'],
		'default' => '',
		'save' => '',
	],
	'en_introtext' => [
		'title' => $_lang['resource_summary'].' (EN)',
		'help' => $_lang['resource_summary_help'],
		'default' => '',
		'save' => '',
	],
	'en_content' => [
		'title' => $_lang['resource_content'].' (EN)',
		'default' => '',
		'save' => '',
	],
	'en_menutitle' => [
		'title' => $_lang['resource_opt_menu_title'].' (EN)',
		'help' => $_lang['resource_opt_menu_title_help'],
		'default' => '',
		'save' => '',
	],
	'en_seotitle' => [
		'title' => $_lang['resource_title'].' SEO (EN)',
		'default' => '',
		'save' => '',
	],
	'en_seodescription' => [
		'title' => $_lang['resource_description'].' SEO (EN)',
		'default' => '',
		'save' => '',
	],
];
```

To enable a text editor for a content field, you must select ***Type: Rich Text*** for the field when setting the template fields in templatesEdit3.