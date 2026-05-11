# Resource Bridge

sLang has two admin surfaces with different contracts.

The module screens, Dictionary and Configuration, are Livewire screens rendered through evo-ui. They load module-owned styles and scripts from `assets/css/manager.css` and `assets/js/manager.js`.

Resource editing tabs are still embedded into the native Evolution resource form. That bridge must stay narrow and explicit:

- tabs are registered through `tpSettings.addTabPage`;
- field values are synchronized through the existing `form#mutate` resource form;
- the JavaScript adapter is exposed as `window.sLangResourceTabs`;
- editor compatibility is kept for TinyMCE, CodeMirror, and the native textarea fallback.

Do not mount evo-ui module tables or dirty-state forms inside resource tabs. The bridge may keep scoped inline CSS and JavaScript only when it is needed to cooperate with Evolution's legacy resource editor.
