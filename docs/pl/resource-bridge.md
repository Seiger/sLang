# Most zakladek zasobu

sLang ma dwie powierzchnie administracyjne z roznymi kontraktami.

Ekrany modulu, Dictionary i Configuration, dzialaja jako ekrany Livewire renderowane przez evo-ui. Style i skrypty nalezace do modulu sa ladowane z `assets/css/manager.css` oraz `assets/js/manager.js`.

Zakladki edycji zasobu nadal sa osadzone w natywnym formularzu zasobu Evolution. Ten most musi pozostac waski i jawny:

- zakladki sa rejestrowane przez `tpSettings.addTabPage`;
- wartosci pol sa synchronizowane przez istniejacy formularz zasobu `form#mutate`;
- adapter JavaScript jest dostepny jako `window.sLangResourceTabs`;
- zgodnosc edytora pozostaje dla TinyMCE, CodeMirror i natywnego textarea.

Nie montuj evo-ui module tables ani dirty-state forms wewnatrz zakladek zasobu. Most moze zachowac scoped inline CSS i JavaScript tylko wtedy, gdy jest to potrzebne do wspolpracy z legacy edytorem zasobow Evolution.
