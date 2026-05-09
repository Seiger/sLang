# sLang Dokumentation

**sLang** ist ein Modul zur Verwaltung mehrsprachiger Inhalte in Evolution CMS. Es unterstützt Übersetzungsphrasen, Frontend-Sprachen, mehrsprachige Ressource-Tabs, URL-Sprachsegmente, automatische Übersetzung und locale-aware Content-APIs.

## Seiten

- [Erste Schritte](getting-started.md)
- [Verwaltungs-Tabs](management-tabs.md)
- [Verwendung in Blade](use-in-blade.md)

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
- `langAndTvs()` bleibt aus Gründen der Abwärtskompatibilität erhalten, ist aber seit `1.0.8` veraltet und wird in `v1.2` entfernt.
