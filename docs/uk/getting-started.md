# Початок роботи

## Вимоги

- Evolution CMS **3.3+**
- PHP **8.3+**
- Composer **2.2+**
- Одна з баз даних: **MySQL 8.0+** / **MariaDB 10.5+** / **PostgreSQL 10+** / **SQLite 3.25+**

## Встановлення через artisan package installer

Перейдіть у папку `core` вашого Evolution CMS:

```console
cd path/to/your/evolution/cms/core
```

Виконайте artisan-команду:

```console
php artisan package:installrequire seiger/slang "*"
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

![Multilingual tabs](https://github.com/Seiger/slang/releases/download/v1.0.0/sLang.png)

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

> **Deprecated:** scope `langAndTvs()` застарів з версії `1.0.8` і буде видалений у `v1.2`. Використовуйте `lang()` і `withTVs()`.
