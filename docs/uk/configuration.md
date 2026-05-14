# Конфігурація

Ця сторінка описує основні налаштування sLang.

## Налаштування менеджера

| Ключ | Призначення |
| --- | --- |
| `s_lang_default` | Типова мова сайту. |
| `s_lang_default_show` | Показувати типову мову в URL. |
| `s_lang_config` | Мови сайту, які підтримує менеджер. |
| `s_lang_front` | Мови, доступні на фронтенді. |
| `s_lang_url_map` | Кастомні URL-сегменти мов. |
| `s_lang_tvs` | Multilingual Template Variables. |
| `check_sLang` | Compatibility check toggle. |

## Файли конфігурації

| Файл | Призначення |
| --- | --- |
| `config/lang-list.php` | Повний список мов для choices. |
| `config/coutry-lang.php` | Compatibility mapping мов і країн. |
| `config/sLangAlias.php` | Facade alias. |
| `config/sLangCheck.php` | Runtime checks. |
| `config/translates/table.php` | evo-ui table preset словника. |

## Правило локалі

Для української документації використовується `uk`, не `ua`.

## Правила Валідації

- `s_lang_default` має бути в `s_lang_config`.
- `s_lang_front` має бути підмножиною `s_lang_config`.
- URL-сегменти мають бути унікальними після обрізання слешів.
- Після додавання нової мови потрібно оновити таблицю словника, інакше inline save отримає SQL помилку.
- Після видалення мови сайту треба перевірити мови фронтенду і URL map.

## Maintenance Дії

Синхронізація словника додає ключі, знайдені в коді. Очищення obsolete keys видаляє записи, яких більше немає в коді і які виглядають як згенеровані або невикористані.
