# Pont des onglets de ressource

sLang expose deux surfaces d'administration avec des contrats differents.

Les ecrans du module, Dictionary et Configuration, sont des ecrans Livewire rendus avec evo-ui. Les styles et scripts propres au module sont charges depuis `assets/css/manager.css` et `assets/js/manager.js`.

Les onglets d'edition de ressource restent integres dans le formulaire natif d'Evolution. Ce pont doit rester limite et explicite:

- les onglets sont enregistres avec `tpSettings.addTabPage`;
- les valeurs des champs sont synchronisees avec le formulaire existant `form#mutate`;
- l'adaptateur JavaScript est expose comme `window.sLangResourceTabs`;
- la compatibilite editeur reste disponible pour TinyMCE, CodeMirror et le textarea natif.

Ne montez pas de evo-ui module tables ni de dirty-state forms dans les onglets de ressource. Le pont peut conserver du CSS et du JavaScript inline scopes uniquement quand c'est necessaire pour cooperer avec l'editeur legacy de ressources Evolution.
