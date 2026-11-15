---
slug: /
sidebar_position: 1
---

# Документація sLang

[![Остання стабільна версія](https://img.shields.io/packagist/v/seiger/slang?label=version)](https://packagist.org/packages/seiger/slang)
[![Вимога до версії Evolution CMS](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FSeiger%2FsLang%2F2.x%2Fcomposer.json&query=%24.require%5B%27evolution-cms%2Fevolution%27%5D&label=CMS&prefix=Evolution%20&color=brightgreen)](https://github.com/evolution-cms/evolution)
[![PHP 8.4, 8.5 і 8.6](https://img.shields.io/badge/PHP-8.4_%7C_8.5_%7C_8.6-777BB4?logo=php&logoColor=white)](getting-started.md)
[![Ліцензія](https://img.shields.io/packagist/l/seiger/slang)](https://packagist.org/packages/seiger/slang)
[![Відкриті задачі](https://img.shields.io/github/issues/Seiger/slang)](https://github.com/Seiger/slang/issues)
[![Зірочки на GitHub](https://img.shields.io/packagist/stars/Seiger/slang)](https://github.com/Seiger/sLang)
[![Загальна кількість завантажень](https://img.shields.io/packagist/dt/seiger/slang)](https://packagist.org/packages/seiger/slang)
![Зроблено в Україні](https://img.shields.io/badge/made_in-Ukraine-ffd700.svg?labelColor=0057b7)

[★ Поставити зірочку на GitHub](https://github.com/Seiger/sLang)

**sLang** — модуль керування мультимовністю для Evolution CMS. Він допомагає працювати зі словником перекладів, мовами фронтенду, мультимовними вкладками ресурсів, мовними сегментами URL, автоматичним перекладом і locale-aware API для контенту.

## Скриншоти

![Словник перекладів](../assets/images/slang-dictionary.png)

![Налаштування мов](../assets/images/slang-configuration.png)

![Редактор ресурсу з відкритою українською вкладкою](../assets/images/slang-resource-editor.png)

## Сторінки

- [Початок роботи](getting-started.md)
- [Гайд користувача](user-guide.md)
- [Вкладки керування](management-tabs.md)
- [Конфігурація](configuration.md)
- [Використання в Blade](use-in-blade.md)
- [Гайд розробника](developer-guide.md)
- [Довідник](reference.md)
- [Frontend-гайд](frontend-guide.md)
- [Міст ресурсу](resource-bridge.md)
- [Діагностика](troubleshooting.md)
- [Release checklist](release-checklist.md)

## Можливості

- Автоматичний переклад фраз через Google або кастомний провайдер.
- Автоматичний пошук перекладів у шаблонах.
- Мультимовні вкладки в ресурсах.
- Необмежена кількість мов перекладу.
- Підтримка мультимовного SEO.

## Locale-aware модель контенту

- `Seiger\sLang\Models\sLangContent` за замовчуванням враховує поточну мову через `evo()->getLocale()`.
- Явний вибір мови доступний через scope `lang()`.
- TV-параметри можна додавати через `withTVs()`.
- `langAndTvs()` працює в наявному коді. Для нових запитів використовуйте `lang()` і `withTVs()`.
