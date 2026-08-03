# Most zakladek zasobu

sLang ma dwie powierzchnie administracyjne z roznymi kontraktami.

Ekrany modulu, Dictionary i Configuration, dzialaja jako ekrany Livewire renderowane przez evo-ui. Uzywaja assetow evo-ui oraz malego synchronizera `assets/js/manager.js` dla tytulu/ikony; CSS nalezacy do modulu nie jest tam ladowany.

Zakladki edycji zasobu nadal sa osadzone w natywnym formularzu zasobu Evolution. Ten most musi pozostac waski i jawny:

- zakladki sa rejestrowane przez `tpSettings.addTabPage`;
- wartosci pol sa synchronizowane przez istniejacy formularz zasobu `form#mutate`;
- adapter JavaScript jest dostepny jako `window.sLangResourceTabs`;
- zgodnosc edytora pozostaje dla TinyMCE, CodeMirror i natywnego textarea.

Udokumentowane pliki bridge to `views/tabs.blade.php`, `views/resourceGeneralTab.blade.php`, `views/resourceTemplateVariablesTab.blade.php`, `views/resourceSettingsTab.blade.php` oraz wyjscie TabPane w `src/Controllers/sLangController.php`.

Nie montuj evo-ui module tables ani dirty-state forms wewnatrz zakladek zasobu. Most moze zachowac scoped inline CSS i JavaScript tylko wtedy, gdy jest to potrzebne do wspolpracy z legacy edytorem zasobow Evolution.
