---
sidebar_position: 2
---

# Початок роботи

## Вимоги

- Evolution CMS **^3.5**. Мінімальна версія може залежати від версії EvoUI, яку вибере Composer.
- PHP **^8.4**
- Composer **2.2+**
- Одна з баз даних: **MySQL 8.0+** / **MariaDB 10.5+** / **PostgreSQL 10+** / **SQLite 3.25+**

## Встановлення через artisan package installer

Перейдіть у папку `core` вашого Evolution CMS:

```console
cd path/to/your/evolution/cms/core
```

Виконайте artisan-команду:

Після публікації на Packagist ця команда встановить стабільний випуск 2.x.

```console
php artisan package:installrequire seiger/slang "^2.0"
```

Опублікуйте файли пакета:

```console
php artisan vendor:publish --provider="Seiger\sLang\sLangServiceProvider"
```

Створіть структуру бази даних:

```console
php artisan migrate
```

## Керування

Після встановлення модуль можна використовувати одразу. Шлях у панелі адміністратора: **Admin Panel -> Modules -> Multilingual**.

У ресурсі з'являються окремі вкладки для кожної мови.

![Редактор ресурсу з відкритою українською вкладкою](../assets/images/slang-resource-editor.png)

[Вкладки керування](management-tabs.md)

## Додатково

Якщо ви пишете власний код, який інтегрується з sLang, перевіряйте наявність модуля через конфігураційну змінну.

```php
if (evo()->getConfig('check_sLang', false)) {
    // Your code
}
```

Якщо плагін встановлено, `evo()->getConfig('check_sLang', false)` завжди повертає `true`. Інакше повертається `false`.

## Робота з локалізованим контентом

Використовуйте модель `sLangContent`, щоб отримувати перекладені ресурси:

```php
use Seiger\sLang\Models\sLangContent;

$items = sLangContent::active()->get(); // locale resolved automatically
$itemsEn = sLangContent::lang('en')->withTVs(['preview'])->get();
```

> **Deprecated:** `langAndTvs()` працює в наявному коді. Для нових запитів використовуйте `lang()` і `withTVs()`.
