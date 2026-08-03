# Resource Bridge

sLang hat zwei Admin-Oberflächen mit unterschiedlichen Verträgen.

Die Modulansichten Dictionary und Configuration laufen als Livewire-Ansichten über evo-ui. Sie nutzen evo-ui Assets und den kleinen `assets/js/manager.js` Synchronizer für Titel/Icon; modul-eigenes CSS wird dort nicht geladen.

Die Resource-Tabs werden weiterhin in die native Evolution-Resource-Form eingebettet. Diese Bridge muss eng und ausdrücklich bleiben:

- Tabs werden über `tpSettings.addTabPage` registriert;
- Feldwerte werden über die bestehende Resource-Form `form#mutate` synchronisiert;
- der JavaScript-Adapter ist als `window.sLangResourceTabs` verfügbar;
- Editor-Kompatibilität bleibt für TinyMCE, CodeMirror und den nativen textarea-Fallback erhalten.

Die dokumentierten Bridge-Dateien sind `views/tabs.blade.php`, `views/resourceGeneralTab.blade.php`, `views/resourceTemplateVariablesTab.blade.php`, `views/resourceSettingsTab.blade.php` und die TabPane-Ausgabe in `src/Controllers/sLangController.php`.

Keine evo-ui module tables oder dirty-state forms in Resource-Tabs mounten. Die Bridge darf scoped inline CSS und JavaScript nur behalten, wenn es für die Zusammenarbeit mit dem Legacy-Resource-Editor von Evolution nötig ist.
