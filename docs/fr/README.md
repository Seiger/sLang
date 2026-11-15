---
slug: /
sidebar_position: 1
---

# Documentation sLang

[![Dernière version stable](https://img.shields.io/packagist/v/seiger/slang?label=version)](https://packagist.org/packages/seiger/slang)
[![Version requise d’Evolution CMS](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FSeiger%2FsLang%2F2.x%2Fcomposer.json&query=%24.require%5B%27evolution-cms%2Fevolution%27%5D&label=CMS&prefix=Evolution%20&color=brightgreen)](https://github.com/evolution-cms/evolution)
[![PHP 8.4, 8.5 et 8.6](https://img.shields.io/badge/PHP-8.4_%7C_8.5_%7C_8.6-777BB4?logo=php&logoColor=white)](getting-started.md)
[![Licence](https://img.shields.io/packagist/l/seiger/slang)](https://packagist.org/packages/seiger/slang)
[![Issues ouvertes](https://img.shields.io/github/issues/Seiger/slang)](https://github.com/Seiger/slang/issues)
[![Étoiles sur GitHub](https://img.shields.io/packagist/stars/Seiger/slang)](https://github.com/Seiger/sLang)
[![Total des téléchargements](https://img.shields.io/packagist/dt/seiger/slang)](https://packagist.org/packages/seiger/slang)
![Fabriqué en Ukraine](https://img.shields.io/badge/made_in-Ukraine-ffd700.svg?labelColor=0057b7)

[★ Ajouter une étoile sur GitHub](https://github.com/Seiger/sLang)

**sLang** est un module de gestion multilingue pour Evolution CMS. Il permet de gérer les phrases de traduction, les langues frontend, les onglets multilingues des ressources, les segments d'URL, la traduction automatique et les API de contenu sensibles à la locale.

## Captures d’écran

![Dictionnaire de traductions](../assets/images/slang-dictionary.png)

![Configuration des langues](../assets/images/slang-configuration.png)

![Éditeur de ressources avec l’onglet ukrainien sélectionné](../assets/images/slang-resource-editor.png)

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
- `langAndTvs()` reste disponible pour le code existant. Utilisez `lang()` et `withTVs()` pour les nouvelles requêtes.
