---
slug: /
sidebar_position: 1
---

# Dokumentacja sLang

[![Najnowsza stabilna wersja](https://img.shields.io/packagist/v/seiger/slang?label=version)](https://packagist.org/packages/seiger/slang)
[![Wymagana wersja Evolution CMS](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FSeiger%2FsLang%2F2.x%2Fcomposer.json&query=%24.require%5B%27evolution-cms%2Fevolution%27%5D&label=CMS&prefix=Evolution%20&color=brightgreen)](https://github.com/evolution-cms/evolution)
[![PHP 8.4, 8.5 i 8.6](https://img.shields.io/badge/PHP-8.4_%7C_8.5_%7C_8.6-777BB4?logo=php&logoColor=white)](getting-started.md)
[![Licencja](https://img.shields.io/packagist/l/seiger/slang)](https://packagist.org/packages/seiger/slang)
[![Otwarte zgłoszenia](https://img.shields.io/github/issues/Seiger/slang)](https://github.com/Seiger/slang/issues)
[![Gwiazdki na GitHubie](https://img.shields.io/packagist/stars/Seiger/slang)](https://github.com/Seiger/sLang)
[![Łączna liczba pobrań](https://img.shields.io/packagist/dt/seiger/slang)](https://packagist.org/packages/seiger/slang)
![Wyprodukowano w Ukrainie](https://img.shields.io/badge/made_in-Ukraine-ffd700.svg?labelColor=0057b7)

**sLang** to moduł zarządzania wielojęzycznością dla Evolution CMS. Pomaga obsługiwać słownik tłumaczeń, języki frontendu, wielojęzyczne zakładki zasobów, segmenty językowe URL, automatyczne tłumaczenia i API treści zależne od locale.

## Zrzuty ekranu

![Słownik tłumaczeń](../assets/images/slang-dictionary.png)

![Konfiguracja języków](../assets/images/slang-configuration.png)

![Edytor zasobu z otwartą zakładką ukraińską](../assets/images/slang-resource-editor.png)

## Strony

- [Pierwsze kroki](getting-started.md)
- [Przewodnik użytkownika](user-guide.md)
- [Zakładki zarządzania](management-tabs.md)
- [Konfiguracja](configuration.md)
- [Użycie w Blade](use-in-blade.md)
- [Przewodnik dewelopera](developer-guide.md)
- [Referencja](reference.md)
- [Przewodnik frontend](frontend-guide.md)
- [Most zasobu](resource-bridge.md)
- [Rozwiązywanie problemów](troubleshooting.md)
- [Release checklist](release-checklist.md)

## Funkcje

- Automatyczne tłumaczenie fraz przez Google albo własnego providera.
- Automatyczne wyszukiwanie tłumaczeń w szablonach.
- Wielojęzyczne zakładki w zasobach.
- Obsługa nieograniczonej liczby języków.
- Obsługa wielojęzycznego SEO.

## Model treści zależny od locale

- `Seiger\sLang\Models\sLangContent` domyślnie używa aktualnej locale przez `evo()->getLocale()`.
- Jawny wybór języka jest dostępny przez scope `lang()`.
- Template Variables można dołączyć przez `withTVs()`.
- `langAndTvs()` działa w istniejącym kodzie. W nowych zapytaniach używaj `lang()` i `withTVs()`.
