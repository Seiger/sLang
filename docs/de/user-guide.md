# Benutzerhandbuch

Dieses Handbuch beschreibt die Arbeit mit sLang im Manager: Wörterbuch, Sprachkonfiguration und mehrsprachige Resource-Tabs.

## Modul Öffnen

Öffnen Sie **Module** und wählen Sie **Multilanguage**. **Dictionary** verwaltet Übersetzungsschlüssel. **Configuration** verwaltet Standardsprache, Website-Sprachen, Frontend-Sprachen, URL-Segmente und mehrsprachige TVs.

## Wörterbuch Pflegen

**Synchronize** scannt Templates und Blade-Dateien nach Übersetzungsschlüsseln und fügt fehlende Datensätze hinzu. Die Aktion verwendet den vorhandenen Backend-Parser und darf den Manager-iframe nicht neu laden.

Neue Schlüssel werden über den grünen Button angelegt. sLang öffnet ein Erstellen-Modal, in dem der Schlüssel und die Werte für die aktuell konfigurierten Website-Sprachen eingetragen werden. Die Schlüsselspalte bleibt schreibgeschützt, damit Template-Lookups stabil bleiben.

Übersetzungsbuttons in einer Zeile füllen einen Wert. Der Button im Spaltenkopf füllt alle leeren Werte dieser Sprache.

## Sprachen Konfigurieren

Wählen Sie zuerst die Standardsprache. **Use in URL** legt fest, ob die Standardsprache im URL-Segment erscheint.

Frontend-Sprachen sind von den Website-Sprachen abhängig. Eine Sprache kann erst im Frontend aktiviert werden, wenn sie in `s_lang_config` ausgewählt ist.

## Resource Bearbeiten

sLang fügt dem Evolution Resource Editor Sprach-Tabs für allgemeine Felder, Template Variables und Einstellungen hinzu. Gespeichert wird mit den normalen Evolution-Buttons.

## Empfohlene Reihenfolge

Für eine neue mehrsprachige Website zuerst die Sprachen konfigurieren:

1. Standardsprache wählen.
2. Alle Website-Sprachen auswählen.
3. Frontend-Sprachen aus dieser Auswahl aktivieren.
4. URL-Segmente prüfen.
5. Mehrsprachige Template Variables auswählen.
6. Configuration speichern.
7. Dictionary synchronisieren.
8. Fehlende Übersetzungen manuell oder per Bulk-Aktion füllen.

## Nach Änderungen Prüfen

Öffnen Sie danach eine Resource und prüfen Sie, ob allgemeine Felder, TVs und Einstellungen dieselben Sprachen zeigen. Prüfen Sie außerdem die öffentlichen URLs und den Sprachumschalter für jede Frontend-Sprache.
