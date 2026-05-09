# Bien démarrer

## Prérequis

- Evolution CMS **3.3+**
- PHP **8.3+**
- Composer **2.2+**
- Une base de données parmi **MySQL 8.0+** / **MariaDB 10.5+** / **PostgreSQL 10+** / **SQLite 3.25+**

## Installation avec artisan package installer

Allez dans le dossier `core` de votre Evolution CMS:

```console
cd path/to/your/evolution/cms/core
```

Exécutez la commande artisan:

```console
php artisan package:installrequire seiger/slang "*"
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

![Multilingual tabs](https://github.com/Seiger/slang/releases/download/v1.0.0/sLang.png)

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

> **Deprecated:** Le scope `langAndTvs()` est déprécié depuis `1.0.8` et sera supprimé en `v1.2`. Utilisez `lang()` et `withTVs()`.
