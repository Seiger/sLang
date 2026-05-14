# Guide utilisateur

Ce guide décrit les tâches courantes dans sLang: dictionnaire, configuration des langues et onglets multilingues des ressources.

## Ouvrir Le Module

Dans **Modules**, ouvrez **Multilanguage**. **Dictionary** gère les clés de traduction. **Configuration** gère la langue par défaut, les langues du site, les langues frontend, les segments URL et les TVs multilingues.

## Gérer Le Dictionnaire

**Synchronize** analyse les templates et fichiers Blade, puis ajoute les clés manquantes dans la base. L'action utilise le parser backend existant et ne doit pas recharger l'iframe du manager.

Les nouvelles clés sont ajoutées avec le bouton vert. sLang ouvre une modale de création où la clé et les valeurs des langues du site configurées sont renseignées. La colonne de clé reste en lecture seule pour garder les templates stables.

Les boutons de traduction de ligne remplissent une valeur. Le bouton de l'en-tête de colonne remplit toutes les valeurs vides de cette langue.

## Configurer Les Langues

Choisissez d'abord la langue par défaut. **Use in URL** contrôle si cette langue apparaît dans l'URL.

Les langues frontend dépendent des langues du site. `s_lang_front` doit rester un sous-ensemble de `s_lang_config`.

## Modifier Les Ressources

sLang ajoute des onglets de langue dans l'éditeur de ressource Evolution. Les sauvegardes utilisent les boutons natifs d'Evolution.

## Ordre Recommandé

Pour un nouveau site multilingue, configurez les langues avant de remplir le dictionnaire:

1. Choisir la langue par défaut.
2. Ajouter les langues du site.
3. Activer les langues frontend depuis cette liste.
4. Vérifier les segments URL.
5. Choisir les Template Variables multilingues.
6. Sauvegarder Configuration.
7. Synchroniser Dictionary.
8. Remplir les traductions manquantes manuellement ou avec l'action bulk de colonne.

## Vérifier Après Modification

Ouvrez une ressource et vérifiez que les champs généraux, TVs et paramètres affichent le même ensemble de langues. Vérifiez ensuite les URLs publiques et le sélecteur de langue pour chaque langue frontend.
