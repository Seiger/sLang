---
sidebar_position: 2
---

# Bien démarrer

## Prérequis

- Evolution CMS **^3.5**. La version minimale peut dépendre de la version d’EvoUI résolue par Composer.
- PHP **^8.4**
- Composer **2.2+**
- Une base de données parmi **MySQL 8.0+** / **MariaDB 10.5+** / **PostgreSQL 10+** / **SQLite 3.25+**

## Installation avec artisan package installer

Allez dans le dossier `core` de votre Evolution CMS:

```console
cd path/to/your/evolution/cms/core
```

Exécutez la commande artisan:

Une fois la version stable 2.x publiée sur Packagist, la commande suivante permet de l’installer.

```console
php artisan package:installrequire seiger/slang "^2.0"
```

Publiez les fichiers du package:

```console
php artisan vendor:publish --provider="Seiger\sLang\sLangServiceProvider"
```

Créez la structure de base de données:

```console
php artisan migrate
```

## Gestion

Après l'installation, le module peut être utilisé immédiatement. Chemin dans le panneau d'administration: **Admin Panel -> Modules -> Multilingual**.

La ressource contient des onglets séparés pour chaque langue.

![Éditeur de ressources avec l’onglet ukrainien sélectionné](../assets/images/slang-resource-editor.png)

[Onglets de gestion](management-tabs.md)

## Extra

Si votre propre code doit s'intégrer à sLang, vérifiez la présence du module avec une variable de configuration.

```php
if (evo()->getConfig('check_sLang', false)) {
    // Your code
}
```

Si le plugin est installé, `evo()->getConfig('check_sLang', false)` retourne toujours `true`. Sinon, il retourne `false`.

## Contenu localisé

Utilisez le modèle `sLangContent` pour charger des ressources traduites:

```php
use Seiger\sLang\Models\sLangContent;

$items = sLangContent::active()->get(); // locale resolved automatically
$itemsEn = sLangContent::lang('en')->withTVs(['preview'])->get();
```

> **Deprecated:** `langAndTvs()` reste disponible pour le code existant. Utilisez `lang()` et `withTVs()` pour les nouvelles requêtes.
