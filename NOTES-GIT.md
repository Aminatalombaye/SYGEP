# Notes Git (pour apprendre)

## Récupérer ce qui est nouveau sur GitHub
Git ne contacte GitHub que si on le lui demande.
Une nouvelle branche ou de nouveaux commits n'apparaissent pas tout seuls en local.

- `git fetch origin` : télécharge les nouveautés (branches, commits) sans toucher à vos fichiers.
- `git pull origin <branche>` : `fetch` + fusion dans la branche courante. Ne met à jour que cette branche.

## Fusionner une branche dans master
```
git checkout master
git fetch origin
git merge origin/branch-2
git push origin master
```
- Sans `fetch`, le dossier ne connaît pas `branch-2`.
- Sans `push`, la fusion reste uniquement sur votre machine.

Vérifier ensuite : `git log --oneline -3` doit montrer le commit fusionné.

## Autre façon : pull request sur GitHub
Pull requests > New pull request > base `master`, compare `branch-2` > Create > Merge pull request.
Puis en local : `git checkout master` et `git pull origin master`.

## Renommer une branche
```
git branch -m ancien-nom nouveau-nom
git push -u origin nouveau-nom
git push origin --delete ancien-nom
```
(la dernière ligne peut être faite sur GitHub : Branches > icône poubelle)

## Changer l'auteur du dernier commit
```
git commit --amend --reset-author --no-edit
git push --force-with-lease origin <branche>
```
`--force-with-lease` est nécessaire car l'historique a été réécrit.

## Laravel : si une modification de vue ne s'affiche pas
```
php artisan view:clear
```
puis Ctrl+F5 dans le navigateur.

## Commandes de vérification utiles
- `git status` : fichiers modifiés, branche courante
- `git log --oneline -5` : derniers commits
- `git branch -a` : toutes les branches (locales et distantes)
- `git ls-remote --heads origin` : branches réellement présentes sur GitHub
