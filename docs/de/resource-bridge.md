# Resource Bridge

sLang hat zwei Admin-Oberflächen mit unterschiedlichen Verträgen.

Die Modulansichten Dictionary und Configuration laufen als Livewire-Ansichten über evo-ui. Modul-eigene Styles und Skripte werden aus `assets/css/manager.css` und `assets/js/manager.js` geladen.

Die Resource-Tabs werden weiterhin in die native Evolution-Resource-Form eingebettet. Diese Bridge muss eng und ausdrücklich bleiben:

- Tabs werden über `tpSettings.addTabPage` registriert;
- Feldwerte werden über die bestehende Resource-Form `form#mutate` synchronisiert;
- der JavaScript-Adapter ist als `window.sLangResourceTabs` verfügbar;
- Editor-Kompatibilität bleibt für TinyMCE, CodeMirror und den nativen textarea-Fallback erhalten.

Keine evo-ui module tables oder dirty-state forms in Resource-Tabs mounten. Die Bridge darf scoped inline CSS und JavaScript nur behalten, wenn es für die Zusammenarbeit mit dem Legacy-Resource-Editor von Evolution nötig ist.
