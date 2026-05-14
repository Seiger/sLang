# Documentation sLang

**sLang** est un module de gestion multilingue pour Evolution CMS. Il permet de gérer les phrases de traduction, les langues frontend, les onglets multilingues des ressources, les segments d'URL, la traduction automatique et les API de contenu sensibles à la locale.

## Pages

- [Bien démarrer](getting-started.md)
- [Guide utilisateur](user-guide.md)
- [Onglets de gestion](management-tabs.md)
- [Configuration](configuration.md)
- [Utilisation dans Blade](use-in-blade.md)
- [Guide développeur](developer-guide.md)
- [Référence](reference.md)
- [Guide frontend](frontend-guide.md)
- [Pont des ressources](resource-bridge.md)
- [Dépannage](troubleshooting.md)
- [Release checklist](release-checklist.md)

## Fonctionnalités

- Traduction automatique des phrases via Google ou un fournisseur personnalisé.
- Recherche automatique des traductions dans les templates.
- Onglets multilingues dans les ressources.
- Support d'un nombre illimité de langues de traduction.
- Support du SEO multilingue.

## Modèle de contenu sensible à la locale

- `Seiger\sLang\Models\sLangContent` respecte la locale courante par défaut via `evo()->getLocale()`.
- Le choix explicite de la langue est disponible avec le scope `lang()`.
- Les Template Variables peuvent être ajoutées avec `withTVs()`.
- `langAndTvs()` reste disponible pour compatibilité, mais il est déprécié depuis `1.0.8` et sera supprimé en `v1.2`.
