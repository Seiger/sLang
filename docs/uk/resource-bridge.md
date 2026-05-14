# Міст вкладок ресурсу

sLang має дві адмінські поверхні з різними контрактами.

Екрани модуля, Словник і Конфігурація, працюють як Livewire-екрани через evo-ui. Вони використовують evo-ui assets і маленький `assets/js/manager.js` synchronizer для title/icon; module-owned CSS там не підключається.

Вкладки редагування ресурсу досі вбудовуються у стандартну форму ресурсу Evolution. Цей міст має залишатися вузьким і явним:

- вкладки реєструються через `tpSettings.addTabPage`;
- значення полів синхронізуються через наявну форму ресурсу `form#mutate`;
- JavaScript-адаптер доступний як `window.sLangResourceTabs`;
- сумісність редактора зберігається для TinyMCE, CodeMirror і стандартного textarea.

Документовані bridge-файли: `views/tabs.blade.php`, `views/resourceGeneralTab.blade.php`, `views/resourceTemplateVariablesTab.blade.php`, `views/resourceSettingsTab.blade.php` і TabPane output у `src/Controllers/sLangController.php`.

Не монтуйте evo-ui module tables або dirty-state форми всередині вкладок ресурсу. Міст може залишати scoped inline CSS і JavaScript тільки там, де це потрібно для роботи з legacy-редактором ресурсів Evolution.
