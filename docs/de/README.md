---
slug: /
sidebar_position: 1
---

# sLang Dokumentation

[![Neueste stabile Version](https://img.shields.io/packagist/v/seiger/slang?label=version)](https://packagist.org/packages/seiger/slang)
[![Versionsanforderung für Evolution CMS](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FSeiger%2FsLang%2F2.x%2Fcomposer.json&query=%24.require%5B%27evolution-cms%2Fevolution%27%5D&label=CMS&prefix=Evolution%20&color=brightgreen)](https://github.com/evolution-cms/evolution)
[![PHP 8.4, 8.5 und 8.6](https://img.shields.io/badge/PHP-8.4_%7C_8.5_%7C_8.6-777BB4?logo=php&logoColor=white)](getting-started.md)
[![Lizenz](https://img.shields.io/packagist/l/seiger/slang)](https://packagist.org/packages/seiger/slang)
[![Offene Issues](https://img.shields.io/github/issues/Seiger/slang)](https://github.com/Seiger/slang/issues)
[![Sterne auf GitHub](https://img.shields.io/packagist/stars/Seiger/slang)](https://github.com/Seiger/sLang)
[![Downloads insgesamt](https://img.shields.io/packagist/dt/seiger/slang)](https://packagist.org/packages/seiger/slang)
![Hergestellt in der Ukraine](https://img.shields.io/badge/made_in-Ukraine-ffd700.svg?labelColor=0057b7)

**sLang** ist ein Modul zur Verwaltung mehrsprachiger Inhalte in Evolution CMS. Es unterstützt Übersetzungsphrasen, Frontend-Sprachen, mehrsprachige Ressource-Tabs, URL-Sprachsegmente, automatische Übersetzung und locale-aware Content-APIs.

## Screenshots

![Übersetzungswörterbuch](../assets/images/slang-dictionary.png)

![Spracheinstellungen](../assets/images/slang-configuration.png)

![Ressourcen-Editor mit ausgewähltem ukrainischen Tab](../assets/images/slang-resource-editor.png)

## Seiten

- [Erste Schritte](getting-started.md)
- [Benutzerhandbuch](user-guide.md)
- [Verwaltungs-Tabs](management-tabs.md)
- [Konfiguration](configuration.md)
- [Verwendung in Blade](use-in-blade.md)
- [Entwicklerhandbuch](developer-guide.md)
- [Referenz](reference.md)
- [Frontend-Leitfaden](frontend-guide.md)
- [Resource Bridge](resource-bridge.md)
- [Fehlerbehebung](troubleshooting.md)
- [Release Checklist](release-checklist.md)

## Funktionen

- Automatische Übersetzung von Phrasen über Google oder eigene Provider.
- Automatische Suche nach Übersetzungen in Templates.
- Mehrsprachige Tabs in Ressourcen.
- Unterstützung für unbegrenzt viele Übersetzungssprachen.
- Unterstützung für mehrsprachiges SEO.

## Locale-aware Content-Modell

- `Seiger\sLang\Models\sLangContent` verwendet standardmäßig die aktuelle Sprache über `evo()->getLocale()`.
- Eine Sprache kann explizit über den Scope `lang()` gewählt werden.
- Template Variables können mit `withTVs()` geladen werden.
- `langAndTvs()` bleibt für bestehenden Code verfügbar. Verwenden Sie für neue Abfragen `lang()` und `withTVs()`.
