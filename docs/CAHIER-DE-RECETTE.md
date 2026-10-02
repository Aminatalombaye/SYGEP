# Cahier de recette SYGEP

Document de validation fonctionnelle de l'application. Chaque cas est exécutable à la main par un testeur ; la colonne *Pré-recette* indique le résultat obtenu lors de l'exécution automatique (navigateur + requêtes HTTP) sur la base de démonstration.

## Mode d'emploi

1. Installer l'application avec les données de démonstration (`php artisan migrate --seed` puis `php artisan db:seed --class=DemoDataSeeder`).
2. Pour chaque cas : se connecter avec le **compte** indiqué, vérifier les **préconditions**, suivre les **étapes**, comparer au **résultat attendu**.
3. Renseigner le résultat (OK / KO / Bloqué / N/A), la date, le testeur et les observations (dans le fichier Excel).
4. Priorités : **C** critique (bloquant pour la mise en production), **M** majeur, **m** mineur.
5. Verdict : recette acceptée si 100 % des cas critiques sont OK et aucune anomalie bloquante ouverte.

## Comptes de test (mot de passe de démonstration : `password`)

| Code | E-mail | Rôle |
|---|---|---|
| ADM | admin@admin.com | Super administrateur |
| DIR | directeur@sygep.test | Directeur |
| CPT | comptable@sygep.test | Comptable matière principal |
| SEC | secondaire@sygep.test | Comptable matière secondaire (CFP de Thiès) |
| MNT | maintenance@sygep.test | Responsable maintenance |
| INF | infrastructures@sygep.test | Responsable infrastructures |
| MAT | matieres@sygep.test | Administrateur des matières |
| AGT | agent@sygep.test | Agent / demandeur (CFP de Thiès) |

## Synthèse de la pré-recette automatique

- Cas au cahier : **293** ; vérifiés automatiquement : **264** (OK : 263, KO : 1) ; à faire à la main : **29** (caméra, HTTPS, cron, sauvegarde, impression…).

| Module | Cas | OK auto | KO auto | Manuel |
|---|---|---|---|---|
| AUT – Connexion, comptes et sécurité d'accès | 10 | 7 | 0 | 3 |
| USR – Utilisateurs, rôles et droits | 14 | 12 | 1 | 1 |
| REF – Référentiels (services, catégories, emplacements, fournisseurs, bons) | 14 | 13 | 0 | 1 |
| AGT – Agents | 6 | 5 | 0 | 1 |
| MAT – Matières, étiquettes QR et scanner | 30 | 25 | 0 | 5 |
| AFF – Affectations, restitutions et transferts | 25 | 25 | 0 | 0 |
| INV – Inventaires | 17 | 15 | 0 | 2 |
| STK – Stock des consommables : articles et mouvements | 23 | 22 | 0 | 1 |
| BST – Bons d'entrée et de sortie du stock | 19 | 19 | 0 | 0 |
| INF – Infrastructures | 14 | 13 | 0 | 1 |
| PRJ – Projets, jalons, chefs de projet, intervenants et rapports | 20 | 19 | 0 | 1 |
| MNT – Demandes de maintenance (circuit complet) | 23 | 23 | 0 | 0 |
| PRV – Maintenance préventive | 11 | 10 | 0 | 1 |
| TCH – Tâches, étiquettes et calendrier | 8 | 8 | 0 | 0 |
| RPT – Rapports périodiques | 7 | 7 | 0 | 0 |
| NTF – Notifications | 9 | 8 | 0 | 1 |
| DSH – Tableau de bord, listes et ergonomie | 13 | 13 | 0 | 0 |
| PUB – Site public et page Contact | 7 | 6 | 0 | 1 |
| SEC – Droits et isolation des données | 12 | 10 | 0 | 2 |
| INS – Installation et exploitation | 11 | 3 | 0 | 8 |

### Anomalies connues

- **USR-08** : statut 500 (500 attendu tant que PermissionCatalog.php n'est pas versionné)

### Défauts trouvés et corrigés pendant la pré-recette

- **Sécurité (IDOR)** : un utilisateur « local » pouvait ouvrir par URL directe une matière, une affectation ou un bon d'un autre service → désormais 404 (cas SEC-12).
- Un utilisateur local ne pouvait pas transférer une matière à un agent de son service → corrigé (AFF-24/25).
- Sortie de stock « par agent » : validation inatteignable → corrigée.
- Alerte de rupture non émise quand le stock tombe à zéro → corrigée (STK-19).
- Débordement horizontal sur mobile → corrigé.
- Installation neuve : seeders non idempotents, rôle d'inscription libre mal ciblé → corrigés.

## AUT – Connexion, comptes et sécurité d'accès

*Objectif : Vérifier qui peut entrer, avec quels droits, et que l'accès est correctement protégé.*

### AUT-01 · Connexion réussie

- **Compte** : ADM (admin@admin.com) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Compte actif et approuvé
- **Étapes** :
  1. Ouvrir /login
  2. Saisir admin@admin.com et le mot de passe
  3. Valider
- **Résultat attendu** : Le tableau de bord s'affiche. Le nom « Admin » apparaît en haut à droite.
- **Constat pré-recette** : [302,"http:\/\/localhost\/home",true]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AUT-02 · Mot de passe erroné

- **Compte** : ADM (admin@admin.com) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Ouvrir /login
  2. Saisir un bon e-mail et un mauvais mot de passe
  3. Valider
- **Résultat attendu** : Un message d'erreur s'affiche ; l'utilisateur reste sur la page de connexion.
- **Constat pré-recette** : [302,{"email":["Ces identifiants ne sont pas reconnus."]}]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AUT-03 · Compte non approuvé refusé

- **Compte** : ADM (admin@admin.com) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Un utilisateur créé SANS cocher « Approuvé »
- **Étapes** :
  1. Se connecter avec ce compte
- **Résultat attendu** : La connexion est refusée avec un message indiquant que le compte doit être approuvé par un administrateur.
- **Constat pré-recette** : [302,"http:\/\/localhost\/login"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AUT-04 · Inscription libre

- **Compte** : — · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Page /register accessible
- **Étapes** :
  1. Ouvrir /register
  2. Créer un compte avec un nouvel e-mail
  3. Tenter de se connecter avec
- **Résultat attendu** : Le compte est créé mais ne peut pas se connecter (non approuvé). Dans Utilisateurs, il porte le rôle « User ».
- **Constat pré-recette** : [302,["User"],0]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AUT-05 · Approuver un compte inscrit

- **Compte** : ADM (admin@admin.com) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Compte créé à l'étape AUT-04
- **Étapes** :
  1. Utilisateurs > modifier le compte
  2. Cocher « Approuvé » et enregistrer
  3. Se connecter avec ce compte
- **Résultat attendu** : La connexion réussit. Le menu ne montre presque aucun module (droits très limités du rôle « User »).
- **Constat pré-recette** : [200]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AUT-06 · Déconnexion

- **Compte** : ADM (admin@admin.com) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Connecté
- **Étapes** :
  1. Cliquer « Déconnexion » en bas du menu
  2. Confirmer « Se déconnecter »
- **Résultat attendu** : Retour à la page de connexion. Le bouton Précédent du navigateur ne rouvre pas l'espace de gestion.
- **Constat pré-recette** : [302,"http:\/\/localhost"] ; modal true, url http://127.0.0.1:8123/
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AUT-07 · Page protégée sans connexion

- **Compte** : — · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Déconnecté
- **Étapes** :
  1. Saisir directement l'adresse /admin/assets
- **Résultat attendu** : Redirection vers la page de connexion.
- **Constat pré-recette** : [302,"http:\/\/localhost\/login"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AUT-08 · Changer son mot de passe

- **Compte** : ADM (admin@admin.com) · **Priorité** : Majeur · **Pré-recette** : Manuel
- **Préconditions** : Droit « profil » présent
- **Étapes** :
  1. Menu du compte > Profil
  2. Saisir l'ancien puis le nouveau mot de passe
  3. Se déconnecter et se reconnecter avec le nouveau
- **Résultat attendu** : Le nouveau mot de passe est accepté ; l'ancien ne fonctionne plus.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AUT-09 · Mot de passe oublié

- **Compte** : — · **Priorité** : Mineur · **Pré-recette** : Manuel
- **Préconditions** : Courrier (MAIL_*) configuré
- **Étapes** :
  1. Sur /login, cliquer « mot de passe oublié »
  2. Saisir un e-mail existant
- **Résultat attendu** : Un message de confirmation s'affiche et un courriel de réinitialisation est envoyé.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AUT-10 · Session de longue durée

- **Compte** : ADM (admin@admin.com) · **Priorité** : Mineur · **Pré-recette** : Manuel
- **Préconditions** : Connecté
- **Étapes** :
  1. Laisser la page ouverte au-delà de la durée de session (120 min par défaut)
  2. Soumettre un formulaire
- **Résultat attendu** : Un message de page expirée (419) s'affiche proprement ; recharger la page permet de continuer.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

## USR – Utilisateurs, rôles et droits

*Objectif : Vérifier la gestion des comptes et le fait que chaque profil ne voit que ce qui le concerne.*

### USR-01 · Créer un utilisateur valide

- **Compte** : ADM (admin@admin.com) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Un service existe
- **Étapes** :
  1. Utilisateurs > Ajouter
  2. Nom, e-mail unique, mot de passe, cocher « Approuvé », service, rôle « Agent / demandeur »
  3. Enregistrer
- **Résultat attendu** : L'utilisateur apparaît dans la liste. Il peut se connecter.
- **Constat pré-recette** : [302,[]]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### USR-02 · E-mail déjà utilisé

- **Compte** : ADM (admin@admin.com) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Un utilisateur existe avec l'e-mail X
- **Étapes** :
  1. Créer un utilisateur avec le même e-mail X
- **Résultat attendu** : Refus avec un message sous le champ E-mail.
- **Constat pré-recette** : {"email":["e-mail a déjà été pris."],"service_id":["Le service de rattachement est obligatoire pour ce rôle."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### USR-03 · Rôle local sans service

- **Compte** : ADM (admin@admin.com) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Créer un utilisateur avec le rôle « Comptable matière secondaire »
  2. Laisser le service vide
  3. Enregistrer
- **Résultat attendu** : Refus : « Le service de rattachement est obligatoire pour ce rôle. »
- **Constat pré-recette** : {"service_id":["Le service de rattachement est obligatoire pour ce rôle."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### USR-04 · Rôle local sans service (modification)

- **Compte** : ADM (admin@admin.com) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Un utilisateur au rôle « Agent / demandeur » avec service
- **Étapes** :
  1. Modifier l'utilisateur
  2. Vider le service
  3. Enregistrer
- **Résultat attendu** : Refus avec le même message.
- **Constat pré-recette** : {"service_id":["Le service de rattachement est obligatoire pour ce rôle."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### USR-05 · Aucun rôle

- **Compte** : ADM (admin@admin.com) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Créer un utilisateur sans choisir de rôle
- **Résultat attendu** : Refus (au moins un rôle est obligatoire).
- **Constat pré-recette** : {"roles":["rôles champ est requis."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### USR-06 · Champs obligatoires signalés

- **Compte** : ADM (admin@admin.com) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Ouvrir Utilisateurs > Ajouter
- **Résultat attendu** : Nom, e-mail, mot de passe et rôles portent l'astérisque rouge.
- **Constat pré-recette** : Nom | E-mail | Mot de passe | Rôles
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### USR-07 · Modifier le rôle d'un utilisateur

- **Compte** : ADM (admin@admin.com) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Utilisateur agent
- **Étapes** :
  1. Changer son rôle en « Comptable matière secondaire »
  2. Se connecter avec ce compte
- **Résultat attendu** : Le menu et les droits correspondent au nouveau rôle.
- **Constat pré-recette** : []
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### USR-08 · La page Rôles s'affiche

- **Compte** : ADM (admin@admin.com) · **Priorité** : Critique · **Pré-recette** : KO
- **Préconditions** : —
- **Étapes** :
  1. Rôles et autorisations > Rôles
  2. Ouvrir un rôle (œil) puis sa modification
- **Résultat attendu** : La liste montre pour chaque rôle sa description et ses droits par domaine ; la fiche et le formulaire s'affichent sans erreur. (Échoue si le fichier PermissionCatalog.php est absent : voir Guide 10.1.)
- **Constat pré-recette** : statut 500 (500 attendu tant que PermissionCatalog.php n'est pas versionné)
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### USR-09 · Modifier les droits d'un rôle

- **Compte** : ADM (admin@admin.com) · **Priorité** : Majeur · **Pré-recette** : Manuel
- **Préconditions** : Un rôle de test avec le droit « supprimer les matières »
- **Étapes** :
  1. Retirer ce droit du rôle
  2. Se reconnecter avec un compte de ce rôle
  3. Ouvrir la liste des matières
- **Résultat attendu** : L'icône de suppression n'apparaît plus.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### USR-10 · Menu adapté au profil

- **Compte** : AGT (agent@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Compte agent
- **Étapes** :
  1. Se connecter
  2. Observer le menu
- **Résultat attendu** : Seuls les modules autorisés apparaissent (matières du service, demandes de maintenance…) ; pas de Rôles, Stock, Infrastructures, Projets.
- **Constat pré-recette** : [{"0":"\/admin\/assets","1":"\/admin\/scanner","2":"\/admin\/maintenance-requests","3":"\/admin\/user-alerts","4":"\/admin\/notifications","6":"\/admin\/maintenance-requests\/create"},[]]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### USR-11 · Accès direct interdit

- **Compte** : AGT (agent@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Compte agent
- **Étapes** :
  1. Saisir l'adresse /admin/users ou /admin/stock-items
- **Résultat attendu** : Erreur 403 (accès interdit).
- **Constat pré-recette** : 403/403
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### USR-12 · Directeur en lecture seule

- **Compte** : DIR (directeur@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Ouvrir Matières, Projets, Infrastructures
- **Résultat attendu** : Aucun bouton « Ajouter », « Modifier » ni « Supprimer » n'est proposé.
- **Constat pré-recette** : liste 200 create 403
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### USR-13 · Administrateur système hors données métier

- **Compte** : ADM (admin@admin.com) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Un compte avec le rôle « Administrateur système »
- **Étapes** :
  1. Se connecter avec ce compte
  2. Observer le menu
- **Résultat attendu** : Seuls Rôles et autorisations (et services en lecture) sont accessibles ; pas les matières ni le stock.
- **Constat pré-recette** : assets 403 users 200 stock 403
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### USR-14 · Suppression d'un utilisateur

- **Compte** : ADM (admin@admin.com) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : Un utilisateur de test
- **Étapes** :
  1. Supprimer l'utilisateur (icône corbeille)
  2. Confirmer
- **Résultat attendu** : Il disparaît de la liste. Ses actions passées restent dans l'historique.
- **Constat pré-recette** : [302]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

## REF – Référentiels (services, catégories, emplacements, fournisseurs, bons)

*Objectif : Vérifier les listes de choix et les bons d'acquisition.*

### REF-01 · Créer un service

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Seul le comptable principal (ou le super administrateur) crée des services
- **Étapes** :
  1. Gestion Matières > Services > Ajouter
  2. Nom : « Service test »
  3. Enregistrer
- **Résultat attendu** : Le service apparaît dans la liste.
- **Constat pré-recette** : [302,[]]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### REF-02 · Service sans nom

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Services > Ajouter
  2. Laisser le nom vide
  3. Enregistrer
- **Résultat attendu** : Refus avec message sous le champ Nom.
- **Constat pré-recette** : {"name":["nom doit être une chaine de caractères","nom champ est requis."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### REF-03 · Créer une catégorie

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Catégories > Ajouter
  2. Nom : « Catégorie test »
  3. Enregistrer
- **Résultat attendu** : La catégorie apparaît ; elle est proposée dans le formulaire des matières.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### REF-04 · Créer un emplacement

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Emplacements > Ajouter
  2. Nom : « Salle test »
  3. Enregistrer
- **Résultat attendu** : L'emplacement apparaît ; il est proposé dans le formulaire des matières.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### REF-05 · Statuts de matières en français

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Base installée avec les seeders
- **Étapes** :
  1. Statuts des matières
- **Résultat attendu** : Les cinq statuts existent : Disponible, Pas disponible, En panne, En réparation, Affecté.
- **Constat pré-recette** : ["Affecté","Disponible","En panne","En réparation","Pas disponible"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### REF-06 · Créer un fournisseur

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Fournisseurs > Ajouter
  2. Nom et contact
  3. Enregistrer
- **Résultat attendu** : Le fournisseur apparaît dans la liste.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### REF-07 · Fournisseur sans contact

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Fournisseurs > Ajouter
  2. Renseigner le nom seulement
- **Résultat attendu** : Refus : le contact est obligatoire.
- **Constat pré-recette** : {"contact":["contact doit être une chaine de caractères","contact champ est requis."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### REF-08 · Créer un bon valide

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Bons > Ajouter
  2. Date d'émission, organisation, référence de commande, destinataire, « Bon », date de livraison
  3. Enregistrer
- **Résultat attendu** : Le bon apparaît dans la liste avec le nombre de matières à 0.
- **Constat pré-recette** : [302,[]]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### REF-09 · Référence de commande en double

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Un bon avec la référence R1 existe
- **Étapes** :
  1. Créer un second bon avec la référence R1
- **Résultat attendu** : Refus : la référence est déjà utilisée.
- **Constat pré-recette** : {"reference_commande":["reference commande a déjà été pris."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### REF-10 · Même organisation, deux bons

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Un bon de l'organisation O existe
- **Étapes** :
  1. Créer un bon de la même organisation O avec une autre référence
- **Résultat attendu** : Accepté (une organisation peut avoir plusieurs bons).
- **Constat pré-recette** : []
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### REF-11 · Livraison avant émission

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Créer un bon avec une date de livraison antérieure à la date d'émission
- **Résultat attendu** : Refus avec message sous la date de livraison.
- **Constat pré-recette** : {"date_livraison":["date livraison doit être une date postérieure ou égale date emission."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### REF-12 · Bon sans libellé « Bon »

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Créer un bon en laissant le champ « Bon » vide
- **Résultat attendu** : Refus (champ obligatoire).
- **Constat pré-recette** : {"bon":["bon doit être une chaine de caractères","bon champ est requis."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### REF-13 · Fiche d'un bon : matières reçues

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : Manuel
- **Préconditions** : Une matière créée avec ce bon (voir MAT-01)
- **Étapes** :
  1. Bons > œil du bon concerné
- **Résultat attendu** : La fiche liste les matières reçues (code, nom, catégorie, état). La liste des bons indique le bon nombre.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### REF-14 · Modifier un bon

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : Un bon existe
- **Étapes** :
  1. Modifier la date de livraison
  2. Enregistrer
- **Résultat attendu** : La modification est enregistrée ; les mêmes règles s'appliquent.
- **Constat pré-recette** : []
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

## AGT – Agents

*Objectif : Vérifier l'enregistrement des bénéficiaires du matériel.*

### AGT-01 · Créer un agent valide

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Un service existe
- **Étapes** :
  1. Agents > Ajouter
  2. Nom, prénom, téléphone, service
  3. Enregistrer
- **Résultat attendu** : L'agent apparaît dans la liste avec son service.
- **Constat pré-recette** : []
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AGT-02 · Champs obligatoires

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Agents > Ajouter
  2. Laisser nom, prénom, téléphone et service vides
  3. Enregistrer
- **Résultat attendu** : Refus ; chaque champ obligatoire affiche son message. Les libellés portent l'astérisque.
- **Constat pré-recette** : ["nom","prenom","telephone","service_id"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AGT-03 · E-mail invalide

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Saisir « pas-un-mail » dans l'e-mail
- **Résultat attendu** : Refus avec message sous le champ.
- **Constat pré-recette** : {"email":["e-mail doit être une adresse e-mail valide"]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AGT-04 · E-mail facultatif et non unique

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : Un agent avec l'e-mail X existe
- **Étapes** :
  1. Créer un second agent avec le même e-mail X
  2. Créer un troisième sans e-mail
- **Résultat attendu** : Les deux sont acceptés.
- **Constat pré-recette** : [[],[],[]]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AGT-05 · Fiche d'un agent

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Un agent détient du matériel (ex. Moussa Ndiaye)
- **Étapes** :
  1. Agents > œil
- **Résultat attendu** : La fiche montre le matériel détenu et ses bons d'affectation en cours.
- **Constat pré-recette** : statut 200
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AGT-06 · Chiffres de la liste

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Mineur · **Pré-recette** : Manuel
- **Préconditions** : Jeu de démonstration
- **Étapes** :
  1. Agents
- **Résultat attendu** : Les cartes (agents, détenteurs de matériel, sans matériel, retours en retard) sont cohérentes avec la liste.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

## MAT – Matières, étiquettes QR et scanner

*Objectif : Vérifier l'enregistrement des biens durables, leur identification et leur historique.*

### MAT-01 · Créer une matière minimale

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Catégorie et emplacement existent
- **Étapes** :
  1. Matières > Ajouter
  2. Catégorie, nom, laisser l'état « Disponible », localisation, date d'achat (aujourd'hui ou avant)
  3. Enregistrer
- **Résultat attendu** : La matière apparaît dans la liste. Sa fiche montre un code SYGEP-MAT-xxxxxx et, dans l'historique, une ligne « création ».
- **Constat pré-recette** : [[],"SYGEP-MAT-000036",true]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-02 · État proposé à la création

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Matières > Ajouter
  2. Ouvrir la liste « État »
- **Résultat attendu** : « Disponible » est présélectionné ; « Affecté » et « En réparation » ne sont pas proposés.
- **Constat pré-recette** : name="status_id" id="status_id" required>
                                            Sélectionner SVP
                                            Disponible
                                         
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-03 · Date d'achat future

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Saisir une date d'achat de demain
  2. Enregistrer
- **Résultat attendu** : Refus avec message sous la date.
- **Constat pré-recette** : {"date_achat":["date achat doit être une date postérieure ou égale  à 2026-10-02."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-04 · Mise en service avant l'achat

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Date d'achat 15/06, mise en service 01/06 (même année)
  2. Enregistrer
- **Résultat attendu** : Refus avec message sous la date de mise en service.
- **Constat pré-recette** : {"date_mise_en_service":["date mise en service doit être une date postérieure ou égale date achat."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-05 · Numéro de série en double

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Une matière avec le numéro de série S1 existe
- **Étapes** :
  1. Créer une autre matière avec le numéro S1
- **Résultat attendu** : Refus (numéro déjà utilisé).
- **Constat pré-recette** : {"serial_number":["numéro de série a déjà été pris."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-06 · Numéro de série vide accepté

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Créer deux matières sans numéro de série
- **Résultat attendu** : Les deux sont acceptées.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-07 · Champs facultatifs

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Créer une matière sans type, sans modèle, sans fournisseur, sans bon
- **Résultat attendu** : Acceptée. Le tableau et la fiche n'affichent rien (ou un tiret) pour ces champs.
- **Constat pré-recette** : champs facultatifs vides
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-08 · Fournisseur et bon à choix unique

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Matières > Ajouter
  2. Observer les listes Fournisseur et Bon
- **Résultat attendu** : Chaque liste ne permet de choisir qu'un seul élément ; pas de boutons « tout sélectionner ».
- **Constat pré-recette** : listes à choix unique
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-09 · Photo de la matière

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : Manuel
- **Préconditions** : Une image de moins de 2 Mo
- **Étapes** :
  1. Glisser une image dans la zone Photos
  2. Enregistrer
  3. Ouvrir la fiche
- **Résultat attendu** : La photo est enregistrée et visible (lien « voir » dans la fiche).
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-10 · Photo trop lourde

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Mineur · **Pré-recette** : Manuel
- **Préconditions** : Une image de plus de 2 Mo
- **Étapes** :
  1. Glisser l'image dans la zone Photos
- **Résultat attendu** : L'image est refusée avec un message de taille maximale.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-11 · Modifier une matière

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Une matière existe
- **Étapes** :
  1. Modifier : changer la localisation
  2. Enregistrer
  3. Ouvrir la fiche > Historique
- **Résultat attendu** : Une ligne « modification » apparaît avec la nouvelle localisation et l'auteur.
- **Constat pré-recette** : [[]]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-12 · Fiche d'une matière

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Matières > œil
- **Résultat attendu** : Trois blocs : Situation actuelle, QR code, Historique. Le bouton Transférer n'est proposé que si la matière est détenue (affectée).
- **Constat pré-recette** : statut 200 ; Transférer proposé seulement pour une matière détenue
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-13 · Étiquette imprimable

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Fiche > Étiquette
- **Résultat attendu** : Une page imprimable affiche le QR code, le code et le nom de la matière.
- **Constat pré-recette** : statut 200
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-14 · Télécharger le QR en SVG

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Fiche > bouton SVG
- **Résultat attendu** : Un fichier SVG du QR code est téléchargé.
- **Constat pré-recette** : statut 200
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-15 · QR code : adresse correcte

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : APP_URL réglé
- **Étapes** :
  1. Scanner le QR code d'une étiquette avec un téléphone (ou lire l'adresse encodée)
- **Résultat attendu** : L'adresse commence par APP_URL puis /q/SYGEP-MAT-xxxxxx.
- **Constat pré-recette** : http://localhost/q/SYGEP-MAT-000036
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-16 · Liste : colonnes essentielles

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Matières
- **Résultat attendu** : Le tableau affiche : ID, catégorie, n° de série, nom, état, localisation, date d'achat, détenteur, actions.
- **Constat pré-recette** : colonnes (avec case et actions) 10
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-17 · Liste : recherche, tri, pagination

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Jeu de démonstration
- **Étapes** :
  1. Taper « HP » dans Rechercher
  2. Cliquer sur l'en-tête « Nom »
  3. Changer « Afficher » à 10
- **Résultat attendu** : La liste se filtre, se trie et se pagine sans recharger la page.
- **Constat pré-recette** : total 38, filtré 8, par 10: 10
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-18 · Liste : export

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Exporter > Excel
  2. Exporter > PDF
- **Résultat attendu** : Les fichiers contiennent les colonnes visibles et les lignes affichées.
- **Constat pré-recette** : Excel:download PDF:download
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-19 · Liste : masquer une colonne

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Colonnes > décocher « Localisation »
- **Résultat attendu** : La colonne disparaît et n'est plus exportée.
- **Constat pré-recette** : colonne masquée (en-têtes visibles 20 -> 18, doublons du défilement compris)
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-20 · Supprimer une matière

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Une matière de test sans affectation
- **Étapes** :
  1. Cliquer la corbeille
  2. Confirmer
- **Résultat attendu** : Elle disparaît de la liste (suppression logique).
- **Constat pré-recette** : [302]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-21 · Scanner : saisie du code

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Code d'une matière connue (ex. SYGEP-MAT-000001)
- **Étapes** :
  1. Scanner un QR code > Saisie manuelle
  2. Saisir le code
  3. Valider
- **Résultat attendu** : La fiche de la matière s'ouvre.
- **Constat pré-recette** : [302,"http:\/\/localhost\/admin\/assets\/36"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-22 · Scanner : numéro de série ou lien

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Saisir un numéro de série existant
  2. Puis le lien complet …/q/SYGEP-MAT-000001
- **Résultat attendu** : Dans les deux cas la fiche correspondante s'ouvre.
- **Constat pré-recette** : ["http:\/\/localhost\/admin\/assets\/37","http:\/\/localhost\/admin\/assets\/37"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-23 · Scanner : code inconnu

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Saisir un code inexistant
- **Résultat attendu** : Message « Aucune matière ne correspond à … » ; on reste sur le scanner.
- **Constat pré-recette** : [302,"Aucune matière ne correspond à « INCONNU-123 »."]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-24 · Scanner : caméra en direct

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : Manuel
- **Préconditions** : Téléphone, site en HTTPS, étiquette imprimée
- **Étapes** :
  1. Appuyer sur Démarrer, autoriser la caméra
  2. Viser l'étiquette
- **Résultat attendu** : Le téléphone vibre, « Code lu » s'affiche et la fiche s'ouvre.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-25 · Scanner : photo d'un QR

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Mineur · **Pré-recette** : Manuel
- **Préconditions** : Une image d'un QR code
- **Étapes** :
  1. Cliquer « Prendre une photo »
  2. Choisir l'image
- **Résultat attendu** : Le code est lu et la fiche s'ouvre ; une image sans QR affiche un message d'échec.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-26 · Page publique d'un QR

- **Compte** : — · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Déconnecté ; code d'une matière connue
- **Étapes** :
  1. Ouvrir /q/SYGEP-MAT-000001
- **Résultat attendu** : Une page publique « Propriété du MEFPT » affiche seulement le nom, la catégorie et le code ; aucune information interne (détenteur, état…).
- **Constat pré-recette** : statut 200
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-27 · Page publique : code inconnu

- **Compte** : — · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : Déconnecté
- **Étapes** :
  1. Ouvrir /q/CODE-INEXISTANT
- **Résultat attendu** : Une page « code inconnu » (erreur 404) s'affiche.
- **Constat pré-recette** : statut 404
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-28 · QR scanné par un compte connecté

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Connecté, droit de voir les matières
- **Étapes** :
  1. Ouvrir /q/SYGEP-MAT-000001
- **Résultat attendu** : Redirection vers la fiche de gestion de la matière.
- **Constat pré-recette** : [302,"http:\/\/localhost\/admin\/assets\/36"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-29 · Historique des matières

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Quelques opérations réalisées
- **Étapes** :
  1. Historique matières
- **Résultat attendu** : Chaque création, affectation, restitution, transfert ou changement d'état apparaît avec date, matière, détenteur, état, emplacement et auteur. Aucun bouton de modification.
- **Constat pré-recette** : statut 200
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MAT-30 · Photos et notes dans la fiche

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Mineur · **Pré-recette** : Manuel
- **Préconditions** : Matière avec photo et notes
- **Étapes** :
  1. Ouvrir la fiche
- **Résultat attendu** : Les photos, notes, modèle, fournisseur et bon sont affichés.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

## AFF – Affectations, restitutions et transferts

*Objectif : Vérifier la remise de matériel, le bon imprimé, les retours et les statuts qui en découlent.*

### AFF-01 · Dotation à un agent

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Une matière Disponible ; un agent
- **Étapes** :
  1. Affectations > Nouvelle affectation
  2. Cocher la matière
  3. Choisir l'agent ; type « Dotation » ; date du jour
  4. Enregistrer
- **Résultat attendu** : Un bon AFF-AAAA-nnnn est créé. La matière passe à « Affecté » et l'agent apparaît comme détenteur. L'historique contient une ligne « affectation ».
- **Constat pré-recette** : [[],"AFF-2026-0009"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AFF-02 · Plusieurs matières, un seul bon

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Trois matières Disponibles
- **Étapes** :
  1. Cocher les trois matières
  2. Choisir un agent
  3. Enregistrer
- **Résultat attendu** : Un seul bon liste les trois matières ; la tuile « Matières affectées » augmente de 3.
- **Constat pré-recette** : []
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AFF-03 · Affectation à un service

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Une matière Disponible
- **Étapes** :
  1. Choisir « Aucun agent » et un service
  2. Enregistrer
- **Résultat attendu** : Accepté : le bénéficiaire est le service.
- **Constat pré-recette** : []
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AFF-04 · Aucun bénéficiaire

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Cocher une matière
  2. Ne choisir ni agent ni service
  3. Enregistrer
- **Résultat attendu** : Refus : « Choisissez un agent ou un service bénéficiaire. »
- **Constat pré-recette** : {"agent_id":["Choisissez un agent ou un service bénéficiaire."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AFF-05 · Service repris de l'agent

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Choisir un agent sans choisir de service
- **Résultat attendu** : Le champ Service indique « Service de l'agent » (rempli automatiquement) ; le bon porte le service de l'agent.
- **Constat pré-recette** : [1,1]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AFF-06 · Seules les matières disponibles sont proposées

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Des matières affectées et en panne existent
- **Étapes** :
  1. Ouvrir Nouvelle affectation
- **Résultat attendu** : Les matières « Affecté » et « En panne » n'apparaissent pas. S'il n'y en a aucune de disponible, un message l'indique.
- **Constat pré-recette** : matières disponibles seulement
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AFF-07 · Aucune matière cochée

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Choisir un agent mais ne cocher aucune matière
  2. Enregistrer
- **Résultat attendu** : Refus : « Sélectionnez au moins une matière. »
- **Constat pré-recette** : {"assets":["Sélectionnez au moins une matière."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AFF-08 · Temporaire sans date de retour

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Type « Mise à disposition temporaire »
  2. Laisser « Retour prévu » vide
  3. Enregistrer
- **Résultat attendu** : Refus : date de retour prévue obligatoire pour ce type.
- **Constat pré-recette** : {"expected_return_at":["Indiquez la date de retour prévue pour ce type d'affectation."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AFF-09 · Dotation sans date de retour

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Type « Dotation »
  2. Laisser « Retour prévu » vide
- **Résultat attendu** : Accepté.
- **Constat pré-recette** : []
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AFF-10 · Retour avant l'affectation

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Date d'affectation 20/10, retour prévu 10/10
  2. Enregistrer
- **Résultat attendu** : Refus : la date de retour doit suivre la date d'affectation.
- **Constat pré-recette** : {"expected_return_at":["La date de retour prévue doit suivre la date d'affectation."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AFF-11 · Bon imprimé

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Une affectation existe
- **Étapes** :
  1. Fiche de l'affectation > Imprimer le bon
- **Résultat attendu** : Le bon affiche : en-tête République, numéro et date, bénéficiaire, conditions, tableau des matières (code, désignation, catégorie, série, modèle), texte d'engagement, signatures « Remis par » / « Reçu par ».
- **Constat pré-recette** : statut 200
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AFF-12 · Restitution partielle

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Affectation de 3 matières
- **Étapes** :
  1. Fiche > Restituer
  2. Cocher une seule matière
  3. État « Bon état », date du jour
  4. Enregistrer
- **Résultat attendu** : La matière redevient Disponible, sans détenteur. L'affectation passe à « Partielle ».
- **Constat pré-recette** : [[],"partiel"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AFF-13 · Restitution complète

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Affectation partielle (AFF-12)
- **Étapes** :
  1. Restituer les matières restantes
- **Résultat attendu** : L'affectation passe à « Restituée ». Toutes les matières sont Disponibles.
- **Constat pré-recette** : [[],"restitue"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AFF-14 · Restitution d'une matière endommagée

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Une affectation ouverte
- **Étapes** :
  1. Restituer avec l'état « Endommagé »
- **Résultat attendu** : La matière passe à « En panne », pas à « Disponible ».
- **Constat pré-recette** : []
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AFF-15 · Restitution sans matière cochée

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Restituer sans cocher de matière
- **Résultat attendu** : Refus : « Cochez au moins une matière à restituer. »
- **Constat pré-recette** : {"assets":["Cochez au moins une matière à restituer."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AFF-16 · Restitution à date future

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Saisir une date de retour de demain
- **Résultat attendu** : Refus : « La date de retour ne peut pas être dans le futur. »
- **Constat pré-recette** : {"returned_at":["La date de retour ne peut pas être dans le futur."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AFF-17 · Retard de restitution

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Affectation temporaire avec retour prévu dépassé (jeu de démonstration)
- **Étapes** :
  1. Affectations > onglet « En retard »
  2. Tableau de bord
- **Résultat attendu** : L'affectation figure dans « En retard » ; la tuile « Retours en retard » la compte.
- **Constat pré-recette** : en retard: 1
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AFF-18 · Transférer une matière

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Une matière détenue par l'agent A
- **Étapes** :
  1. Fiche de la matière > Transférer
  2. Choisir l'agent B
  3. Enregistrer
- **Résultat attendu** : L'ancienne affectation est clôturée pour cette matière (condition « Transféré »). Un nouveau bon est créé au nom de B. L'historique contient « transfert ».
- **Constat pré-recette** : [[],"transfert"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AFF-19 · Transfert sans bénéficiaire

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Transférer sans choisir ni agent ni service
- **Résultat attendu** : Refus : « Choisissez le nouvel agent ou service bénéficiaire. »
- **Constat pré-recette** : {"agent_id":["Choisissez le nouvel agent ou service bénéficiaire."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AFF-20 · Supprimer un bon

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Une affectation ouverte (de test)
- **Étapes** :
  1. Fiche > Supprimer ce bon
  2. Confirmer
- **Résultat attendu** : Les matières encore détenues redeviennent Disponibles.
- **Constat pré-recette** : [302]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AFF-21 · Modifier une affectation

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : Une affectation existe
- **Étapes** :
  1. Modifier : changer le motif et la date de retour prévue
  2. Enregistrer
- **Résultat attendu** : Les modifications sont enregistrées, avec les mêmes règles de dates.
- **Constat pré-recette** : []
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AFF-22 · Filtres de la liste

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : Jeu de démonstration
- **Étapes** :
  1. Cliquer les onglets En cours / En retard / Restituées / Toutes
- **Résultat attendu** : Les compteurs des onglets correspondent au contenu de la liste.
- **Constat pré-recette** : statut 200
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AFF-23 · Périmètre du comptable secondaire

- **Compte** : SEC (secondaire@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Affectations de plusieurs services
- **Étapes** :
  1. Se connecter en SEC
  2. Ouvrir Affectations
- **Résultat attendu** : Seules les affectations du service « CFP de Thiès » sont visibles.
- **Constat pré-recette** : vues par SEC: 3 / total 17
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AFF-24 · Redistribuer au sein de son service (transfert)

- **Compte** : SEC (secondaire@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Une matière détenue par le service « CFP de Thiès » (sans agent précis)
- **Étapes** :
  1. Fiche de la matière > Transférer
  2. Choisir un agent du CFP de Thiès
  3. Enregistrer
- **Résultat attendu** : Accepté : un bon est créé au nom de l'agent, la matière reste « Affecté » et rattachée au service. (Le comptable secondaire redistribue ainsi le matériel que le magasin central a remis à son service.)
- **Constat pré-recette** : [302,[],"matières proposables à la création: 0"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### AFF-25 · Impossible de transférer hors de son service

- **Compte** : SEC (secondaire@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Une matière du CFP de Thiès ; un agent d'un AUTRE service
- **Étapes** :
  1. Tenter le transfert vers cet agent (ou saisir son identifiant)
- **Résultat attendu** : Impossible : l'agent d'un autre service n'est pas accessible (erreur 404) ; aucun bon n'est créé et la matière ne change pas de service.
- **Constat pré-recette** : transfert vers un agent d'un autre service refusé (404), aucun bon créé
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

## INV – Inventaires

*Objectif : Vérifier les campagnes de contrôle physique, du démarrage au procès-verbal.*

### INV-01 · Créer une campagne sur tout le parc

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Inventaires > Ajouter
  2. Nom seulement ; laisser emplacement, service et catégorie sur « Tous »
  3. Enregistrer
- **Résultat attendu** : La campagne INV-AAAA-nn est créée « En préparation ».
- **Constat pré-recette** : [[],"INV-2026-03"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INV-02 · Campagne sans nom

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Ajouter une campagne sans nom
- **Résultat attendu** : Refus : « Donnez un nom à la campagne. »
- **Constat pré-recette** : {"nom":["Donnez un nom à la campagne."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INV-03 · Fin avant le début

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Début prévu 20/10, fin prévue 10/10
- **Résultat attendu** : Refus : « La date de fin doit suivre la date de début. »
- **Constat pré-recette** : {"ends_at":["La date de fin doit suivre la date de début."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INV-04 · Démarrer avec un périmètre

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Campagne limitée au service « CFP de Thiès »
- **Étapes** :
  1. Cliquer « Démarrer la campagne »
- **Résultat attendu** : La campagne passe « En cours ». Le tableau liste exactement les matières du service, toutes « À contrôler ».
- **Constat pré-recette** : ["en_cours",7,7]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INV-05 · Pointer à la main

- **Compte** : SEC (secondaire@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Campagne en cours
- **Étapes** :
  1. Cliquer « Pointer » sur une matière
- **Résultat attendu** : La matière passe « Contrôlée » ; l'avancement augmente.
- **Constat pré-recette** : ["Contrôlée : Ordinateur portable Dell Latitude"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INV-06 · Scanner une matière attendue

- **Compte** : SEC (secondaire@sygep.test) · **Priorité** : Critique · **Pré-recette** : Manuel
- **Préconditions** : Campagne en cours
- **Étapes** :
  1. Scanner > saisir le code d'une matière attendue
  2. État « Bon état »
- **Résultat attendu** : Message « Contrôlée : … » ; la matière est contrôlée.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INV-07 · Lire deux fois la même matière

- **Compte** : SEC (secondaire@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Matière déjà contrôlée
- **Étapes** :
  1. Saisir de nouveau son code
- **Résultat attendu** : Message « Déjà contrôlée » ; rien n'est doublé.
- **Constat pré-recette** : ["Déjà contrôlée : Ordinateur portable Dell Latitude"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INV-08 · Matière hors périmètre

- **Compte** : SEC (secondaire@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Une matière d'un autre emplacement ou service
- **Étapes** :
  1. Saisir son code dans le scanner
- **Résultat attendu** : Message « Hors périmètre, ajoutée à la campagne » ; elle apparaît dans l'onglet Écarts.
- **Constat pré-recette** : ["Hors périmètre, ajoutée à la campagne : Ordinateur portable HP ProBook"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INV-09 · Code inconnu

- **Compte** : SEC (secondaire@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Saisir un code inexistant
- **Résultat attendu** : Message « Code inconnu » ; aucune ligne créée.
- **Constat pré-recette** : ["Code inconnu : ZZZ-INCONNU"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INV-10 · Clôture réservée

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Campagne en cours
- **Étapes** :
  1. Se connecter en MAT (administrateur des matières)
  2. Ouvrir la campagne
- **Résultat attendu** : Le bouton « Clôturer la campagne » n'est pas proposé (droit réservé au comptable principal).
- **Constat pré-recette** : statut 403
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INV-11 · Clôturer : non vues = manquantes

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Campagne en cours avec des matières non contrôlées
- **Étapes** :
  1. Clôturer la campagne
- **Résultat attendu** : Les matières non contrôlées passent « Manquante ». La campagne est « Clôturée ».
- **Constat pré-recette** : ["cloture",6,6]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INV-12 · Clôture avec mise à jour des fiches

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Une matière contrôlée à un autre lieu que prévu ; une matière contrôlée « Hors service »
- **Étapes** :
  1. Clôturer avec « Mettre à jour les fiches » coché
  2. Ouvrir les deux matières
- **Résultat attendu** : L'emplacement de la première est corrigé ; la seconde passe « En panne ». Chaque correction figure dans l'historique (« Mise à jour suite à l'inventaire … »).
- **Constat pré-recette** : {"lieu A":[5,"->",1],"état B":4}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INV-13 · Clôture sans mise à jour

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Mêmes conditions
- **Étapes** :
  1. Clôturer en décochant « Mettre à jour les fiches »
- **Résultat attendu** : Les fiches des matières ne changent pas.
- **Constat pré-recette** : {"lieu avant":1,"après":1}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INV-14 · Procès-verbal

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Campagne clôturée
- **Étapes** :
  1. Procès-verbal
- **Résultat attendu** : Le document affiche la campagne, les responsables (signatures), les matières manquantes, abîmées ou hors service, déplacées ou hors périmètre.
- **Constat pré-recette** : statut 200
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INV-15 · Campagne clôturée non modifiable

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Campagne clôturée
- **Étapes** :
  1. Tenter de scanner une matière sur cette campagne
- **Résultat attendu** : Message « La campagne n'est pas en cours ».
- **Constat pré-recette** : ["La campagne n'est pas en cours."]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INV-16 · Supprimer une campagne

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : Une campagne de test
- **Étapes** :
  1. Supprimer la campagne
  2. Confirmer
- **Résultat attendu** : Elle disparaît de la liste.
- **Constat pré-recette** : [302]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INV-17 · Scanner d'inventaire : lieu et état

- **Compte** : SEC (secondaire@sygep.test) · **Priorité** : Majeur · **Pré-recette** : Manuel
- **Préconditions** : Campagne en cours
- **Étapes** :
  1. Ouvrir Scanner
  2. Choisir « Hors service » et un lieu
  3. Saisir un code
- **Résultat attendu** : La matière est contrôlée avec l'état « Hors service » et le lieu choisi.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

## STK – Stock des consommables : articles et mouvements

*Objectif : Vérifier que le solde est toujours juste, justifié et protégé.*

### STK-01 · Créer un article avec stock initial

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Stock consommables > Articles en stock > Ajouter
  2. Désignation « Article test », unité, seuil 5, quantité en stock aujourd'hui 20
  3. Enregistrer
- **Résultat attendu** : L'article reçoit une référence ART-nnnn. Son solde est 20. Son historique contient un mouvement d'entrée « Stock initial ».
- **Constat pré-recette** : [[],"20.00"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### STK-02 · Seuil d'alerte obligatoire

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Créer un article sans seuil d'alerte
- **Résultat attendu** : Refus (champ obligatoire, astérisque rouge).
- **Constat pré-recette** : {"min_quantity":["seuil d'alerte champ est requis."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### STK-03 · Champs obligatoires de l'article

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Créer un article sans désignation ni unité
- **Résultat attendu** : Refus avec message sous chaque champ.
- **Constat pré-recette** : ["name","unit"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### STK-04 · Fiche d'un article

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Article avec mouvements
- **Étapes** :
  1. Ouvrir la fiche de « Ramette papier A4 80 g »
- **Résultat attendu** : La fiche montre le solde, les entrées et sorties du mois, la consommation moyenne mensuelle, la couverture en jours et la liste des mouvements.
- **Constat pré-recette** : statut 200
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### STK-05 · Entrée valide

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Article existant (solde S)
- **Étapes** :
  1. Mouvements de stock > Nouveau mouvement > Entrée
  2. Article, quantité 10, date du jour
  3. Enregistrer
- **Résultat attendu** : Message « … nouveau solde S+10 ». Le solde de l'article est S+10. Le mouvement est numéroté MVT-AAAA-nnnnn.
- **Constat pré-recette** : [[],"Entrée MVT-2026-00075 enregistrée : Article test — nouveau solde 30 unité."]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### STK-06 · Sortie valide

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Article avec solde ≥ 5 ; un service
- **Étapes** :
  1. Nouveau mouvement > Sortie
  2. Article, quantité 5, service bénéficiaire
  3. Enregistrer
- **Résultat attendu** : Le solde diminue de 5. Le mouvement indique le service bénéficiaire.
- **Constat pré-recette** : []
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### STK-07 · Sortie sans bénéficiaire

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Sortie sans service ni agent
- **Résultat attendu** : Refus : « Indiquez le service bénéficiaire de la sortie (ou l'agent demandeur). »
- **Constat pré-recette** : {"service_id":["Indiquez le service bénéficiaire de la sortie (ou l'agent demandeur)."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### STK-08 · Sortie par un agent

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Un agent d'un service
- **Étapes** :
  1. Sortie en choisissant l'agent demandeur et en laissant le service vide
- **Résultat attendu** : Acceptée : le service de l'agent est repris comme bénéficiaire.
- **Constat pré-recette** : [[],3,3]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### STK-09 · Sortie supérieure au stock

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Article avec solde 11
- **Étapes** :
  1. Sortie de 50
- **Résultat attendu** : Refus : « Stock insuffisant : il reste 11 … ». Le solde ne change pas.
- **Constat pré-recette** : {"quantity":["Stock insuffisant : il reste 24 unité de « Article test »."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### STK-10 · Quantité nulle

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Entrée ou sortie avec quantité 0
- **Résultat attendu** : Refus : la quantité doit être supérieure à zéro.
- **Constat pré-recette** : {"quantity":["La quantité doit être supérieure à zéro."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### STK-11 · Date future

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Mouvement daté de demain
- **Résultat attendu** : Refus : la date ne peut pas être dans le futur.
- **Constat pré-recette** : {"moved_at":["La date du mouvement ne peut pas être dans le futur."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### STK-12 · Ajustement après comptage

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Article avec solde 20
- **Étapes** :
  1. Nouveau mouvement > Ajustement
  2. Quantité comptée 18, observations « casse »
  3. Enregistrer
- **Résultat attendu** : Le solde devient 18. Le mouvement affiche un écart de −2.
- **Constat pré-recette** : [[],"18.00"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### STK-13 · Ajustement sans observations

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Ajustement sans observations
- **Résultat attendu** : Refus : « Expliquez l'écart constaté (comptage, casse, péremption…). »
- **Constat pré-recette** : {"notes":["Expliquez l'écart constaté (comptage, casse, péremption…)."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### STK-14 · Ajustement sans écart

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Article avec solde 18
- **Étapes** :
  1. Ajustement avec quantité comptée 18
- **Résultat attendu** : Refus : « La quantité comptée est identique au stock théorique : aucun ajustement nécessaire. »
- **Constat pré-recette** : {"quantity":["La quantité comptée est identique au stock théorique : aucun ajustement nécessaire."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### STK-15 · Ajustement réservé

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : Manuel
- **Préconditions** : Compte administrateur des matières
- **Étapes** :
  1. Nouveau mouvement
- **Résultat attendu** : Le choix « Ajustement » n'est pas proposé.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### STK-16 · Article périssable : péremption obligatoire

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Article coché « périssable » (ex. Détergent)
- **Étapes** :
  1. Entrée sans date de péremption
- **Résultat attendu** : Refus : « Cet article est périssable : indiquez la date de péremption du lot reçu. »
- **Constat pré-recette** : {"expires_at":["Cet article est périssable : indiquez la date de péremption du lot reçu."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### STK-17 · Péremption antérieure

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : Article périssable
- **Étapes** :
  1. Entrée avec une péremption antérieure à la date du mouvement
- **Résultat attendu** : Refus avec message sous la date de péremption.
- **Constat pré-recette** : {"expires_at":["date de péremption doit être une date ultérieure à date."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### STK-18 · Alerte de stock bas

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Article avec solde 12 et seuil 10 ; un compte avec droit de modifier les articles (ex. ADM)
- **Étapes** :
  1. Sortie de 3 (le solde passe à 9)
  2. Ouvrir la cloche de ADM
  3. Tableau de bord
- **Résultat attendu** : Une notification « Stock bas : … » est reçue. La tuile « Stock sous le seuil » compte l'article.
- **Constat pré-recette** : ["Stock bas : Alerte A (9 unité restant(s), seuil 10)"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### STK-19 · Alerte de rupture

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Article déjà sous son seuil (solde 7, seuil 10)
- **Étapes** :
  1. Sortie de 7 (le solde passe à 0)
- **Résultat attendu** : Notification « Rupture de stock : … », même si l'article était déjà sous le seuil.
- **Constat pré-recette** : ["Rupture de stock : Alerte A (0 unité restant(s), seuil 10)"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### STK-20 · Pas de notification répétée

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : Article déjà sous le seuil
- **Étapes** :
  1. Faire une nouvelle sortie (reste sous le seuil)
- **Résultat attendu** : Aucune nouvelle notification n'est envoyée (l'alerte ne se déclenche qu'au franchissement).
- **Constat pré-recette** : aucune nouvelle notification
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### STK-21 · Compte local : sorties seulement

- **Compte** : SEC (secondaire@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Compte comptable secondaire
- **Étapes** :
  1. Nouveau mouvement
- **Résultat attendu** : Seul « Sortie » est proposé ; une sortie pour son service est acceptée.
- **Constat pré-recette** : entrée refusée: 403
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### STK-22 · Journal : filtres

- **Compte** : CPT (comptable@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : Jeu de démonstration
- **Étapes** :
  1. Mouvements de stock
  2. Choisir l'onglet Sortie, changer les dates, filtrer par article
- **Résultat attendu** : Le journal ne montre que les mouvements correspondants.
- **Constat pré-recette** : statut 200
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### STK-23 · Modification d'un article

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Un article existe
- **Étapes** :
  1. Modifier l'article
  2. Constater que « Quantité en stock aujourd'hui » n'est pas proposée
- **Résultat attendu** : La quantité n'est pas modifiable à la main (elle ne change que par des mouvements).
- **Constat pré-recette** : pas de champ de quantité en modification
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

## BST – Bons d'entrée et de sortie du stock

*Objectif : Vérifier les bons multi-articles : exactitude, « tout ou rien », impression.*

### BST-01 · Bon d'entrée à deux lignes

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Deux articles existants ; un fournisseur
- **Étapes** :
  1. Bons de stock > Bon d'entrée
  2. Date du jour, fournisseur, N° de pièce
  3. Ajouter deux lignes : article A qté 20, article B qté 10
  4. Enregistrer
- **Résultat attendu** : Un bon BE-AAAA-nnnn est créé avec 2 articles. Les soldes de A et B augmentent de 20 et 10. Deux mouvements sont rattachés au bon.
- **Constat pré-recette** : [[],"BE-2026-0002"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### BST-02 · Bon de sortie à deux lignes

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Mêmes articles avec stock suffisant
- **Étapes** :
  1. Bon de sortie
  2. Service bénéficiaire, agent demandeur
  3. Deux lignes : A qté 5, B qté 2
  4. Enregistrer
- **Résultat attendu** : Un bon BS-AAAA-nnnn est créé. Les soldes de A et B diminuent de 5 et 2.
- **Constat pré-recette** : [[],"BS-2026-0003"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### BST-03 · Même article deux fois

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Ajouter deux lignes avec le même article
- **Résultat attendu** : Refus : « Cet article figure déjà sur le bon : regroupez les quantités sur une seule ligne. »
- **Constat pré-recette** : {"lines.0.stock_item_id":["Cet article figure déjà sur le bon : regroupez les quantités sur une seule ligne."],"lines.1.stock_item_id":["Cet article figure déjà sur le bon : regroupez les quantités sur une seule ligne."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### BST-04 · Tout ou rien

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Article A avec stock suffisant, article B avec stock faible
- **Étapes** :
  1. Bon de sortie avec A qté 1 et B qté supérieure à son stock
  2. Enregistrer
  3. Vérifier les soldes de A et B
- **Résultat attendu** : Refus avec le message « Stock insuffisant » affiché sur la ligne de B. AUCUN bon n'est créé et le solde de A n'a pas changé.
- **Constat pré-recette** : {"lines.1.quantity":["Stock insuffisant : il reste 27 boîte de « Stylos à bille (boîte de 50) »."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### BST-05 · Quantité nulle

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Ligne avec quantité 0
- **Résultat attendu** : Refus : la quantité doit être supérieure à zéro.
- **Constat pré-recette** : {"lines.0.quantity":["La quantité doit être supérieure à zéro."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### BST-06 · Bon sans article

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Laisser la ligne sans article
  2. Enregistrer
- **Résultat attendu** : Refus (l'article est obligatoire).
- **Constat pré-recette** : {"lines.0.stock_item_id":["Choisissez l'article."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### BST-07 · Sortie sans service

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Bon de sortie sans service bénéficiaire
- **Résultat attendu** : Refus : « Indiquez le service bénéficiaire du bon de sortie (ou l'agent demandeur). »
- **Constat pré-recette** : {"service_id":["Indiquez le service bénéficiaire du bon de sortie (ou l'agent demandeur)."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### BST-08 · Entrée périssable sans péremption

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Article périssable
- **Étapes** :
  1. Bon d'entrée avec cet article, sans date de péremption
- **Résultat attendu** : Refus : « Article périssable : indiquez la date de péremption du lot. » Rien n'est créé.
- **Constat pré-recette** : {"lines.0.expires_at":["Article périssable : indiquez la date de péremption du lot."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### BST-09 · Date future

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Bon daté de demain
- **Résultat attendu** : Refus : la date du bon ne peut pas être dans le futur.
- **Constat pré-recette** : {"moved_at":["La date du bon ne peut pas être dans le futur."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### BST-10 · Champs selon le type

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Alterner Bon d'entrée / Bon de sortie dans le formulaire
- **Résultat attendu** : En entrée : fournisseur, prix unitaire, péremption. En sortie : service et agent. Les autres champs sont masqués.
- **Constat pré-recette** : entrée masquée en sortie true; visible en entrée true; sortie masquée true
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### BST-11 · Choix d'un article

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Choisir un article dans une ligne
- **Résultat attendu** : L'unité et le stock actuel s'affichent sur la ligne.
- **Constat pré-recette** : unité lot, stock 4
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### BST-12 · Ajouter et retirer des lignes

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Cliquer « Ajouter un article » trois fois
  2. Retirer une ligne avec la croix
- **Résultat attendu** : Les lignes s'ajoutent et se retirent ; on ne peut pas retirer la dernière.
- **Constat pré-recette** : 3 -> 2 -> 1
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### BST-13 · Bon d'entrée imprimé

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Un bon d'entrée existe
- **Étapes** :
  1. Bons de stock > icône imprimante du bon d'entrée
- **Résultat attendu** : Le document est titré « Bon d'entrée en magasin », avec fournisseur, tableau (code, désignation, quantité, unité, prix) et signatures « Livré par » / « Reçu par (magasinier) ».
- **Constat pré-recette** : statut 200
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### BST-14 · Bon de sortie imprimé

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Un bon de sortie existe
- **Étapes** :
  1. Imprimer le bon de sortie
- **Résultat attendu** : Le document est titré « Bon de sortie de magasin », avec bénéficiaire, tableau et signatures « Remis par (magasinier) » / « Reçu par ».
- **Constat pré-recette** : statut 200
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### BST-15 · Liste et filtres

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Plusieurs bons
- **Étapes** :
  1. Bons de stock
  2. Cliquer les onglets Bon d'entrée / Bon de sortie
- **Résultat attendu** : La liste est filtrée ; chaque ligne montre date, numéro, type, bénéficiaire, nombre d'articles, pièce, auteur.
- **Constat pré-recette** : statut 200
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### BST-16 · Fiche d'un bon

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Ouvrir la fiche (œil)
- **Résultat attendu** : La fiche liste les articles, quantités, soldes après et références de mouvements.
- **Constat pré-recette** : statut 200
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### BST-17 · Lien depuis le journal des mouvements

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : Un bon existe
- **Étapes** :
  1. Mouvements de stock
  2. Repérer la colonne « Bon »
- **Résultat attendu** : Les mouvements créés par un bon affichent son numéro, cliquable.
- **Constat pré-recette** : référence du bon dans le journal
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### BST-18 · Compte local

- **Compte** : SEC (secondaire@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Compte comptable secondaire
- **Étapes** :
  1. Bons de stock
  2. Créer un bon
  3. Consulter la liste
- **Résultat attendu** : Seul le bon de sortie est proposé. La liste ne contient que les bons de son service.
- **Constat pré-recette** : entrée refusée 403; bons visibles 1; fuites 0
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### BST-19 · Numérotation

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : Deux bons créés dans la même année
- **Étapes** :
  1. Créer deux bons de sortie
- **Résultat attendu** : Les numéros se suivent (BS-AAAA-0001, 0002…).
- **Constat pré-recette** : BS-2026-0004 -> BS-2026-0005
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

## INF – Infrastructures

*Objectif : Vérifier la hiérarchie, l'état et l'amortissement.*

### INF-01 · Créer une structure

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Infrastructures > Ajouter
  2. Nom, nature « Structure », situation « En service »
  3. Enregistrer
- **Résultat attendu** : La structure apparaît dans la liste.
- **Constat pré-recette** : []
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INF-02 · Bâtiment rattaché à une structure

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Une structure existe
- **Étapes** :
  1. Nature « Bâtiment », rattaché à la structure
- **Résultat attendu** : Accepté.
- **Constat pré-recette** : []
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INF-03 · Bloc rattaché à un bâtiment

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Un bâtiment existe
- **Étapes** :
  1. Nature « Bloc », rattaché au bâtiment
- **Résultat attendu** : Accepté.
- **Constat pré-recette** : []
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INF-04 · Structure rattachée à autre chose

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Nature « Structure », rattachée à un bâtiment
- **Résultat attendu** : Refus : « Une structure est un site principal : elle ne se rattache à rien. »
- **Constat pré-recette** : {"parent_id":["Une structure est un site principal : elle ne se rattache à rien."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INF-05 · Bâtiment rattaché à un bloc

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Un bloc existe
- **Étapes** :
  1. Nature « Bâtiment », rattaché à un bloc
- **Résultat attendu** : Refus : « Un bâtiment se rattache à une structure. »
- **Constat pré-recette** : {"parent_id":["Un bâtiment se rattache à une structure."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INF-06 · Amortissement incomplet

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Renseigner seulement la valeur d'origine
  2. Enregistrer
- **Résultat attendu** : Refus : demande de la date de mise en service et de la durée, avec un message sous chaque champ manquant.
- **Constat pré-recette** : ["construction_date","depreciation_years"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INF-07 · Amortissement complet

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Valeur 400 000 000, mise en service il y a 10 ans, durée 40 ans
  2. Enregistrer
  3. Ouvrir la fiche
- **Résultat attendu** : La fiche affiche l'annuité (10 000 000), la part amortie (≈ 25 %), la valeur nette (≈ 300 000 000) et le plan année par année.
- **Constat pré-recette** : [[],10000000,300013689.25]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INF-08 · Sans valorisation

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Laisser les trois champs d'amortissement vides
- **Résultat attendu** : Accepté ; aucun plan d'amortissement n'est affiché.
- **Constat pré-recette** : []
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INF-09 · Visite future

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Date de dernière visite de demain
- **Résultat attendu** : Refus.
- **Constat pré-recette** : {"last_inspection_at":["date de la dernière visite doit être une date postérieure ou égale  à today."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INF-10 · Valeurs négatives

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Surface −5
- **Résultat attendu** : Refus.
- **Constat pré-recette** : {"surface":["surface doit être au minimum 0."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INF-11 · Durée hors limites

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Durée 0, puis 150
- **Résultat attendu** : Refus (la durée va de 1 à 100 ans).
- **Constat pré-recette** : [{"depreciation_years":["durée d'amortissement doit être au minimum 1."]},{"depreciation_years":["durée d'amortissement ne peut pas être plus grand que 100."]}]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INF-12 · Fiche d'une infrastructure

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Infrastructure avec projet et demande
- **Étapes** :
  1. Ouvrir la fiche
- **Résultat attendu** : La fiche montre les bâtiments/blocs rattachés, les projets liés, la maintenance, et le bouton « Signaler un problème ».
- **Constat pré-recette** : statut 200
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INF-13 · Signaler un problème

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Fiche > Signaler un problème
- **Résultat attendu** : Le formulaire de demande de maintenance s'ouvre avec l'infrastructure déjà choisie.
- **Constat pré-recette** : statut 200
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INF-14 · Chiffres clés et état

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Mineur · **Pré-recette** : Manuel
- **Préconditions** : Jeu de démonstration
- **Étapes** :
  1. Infrastructures
- **Résultat attendu** : Les chiffres clés (nombre, situation, état) sont cohérents avec la liste.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

## PRJ – Projets, jalons, chefs de projet, intervenants et rapports

*Objectif : Vérifier le suivi des chantiers et le calcul automatique de l'avancement.*

### PRJ-01 · Créer un projet planifié

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Projets > Ajouter
  2. Intitulé, nature, statut « Planifié »
  3. Enregistrer
- **Résultat attendu** : Le projet reçoit une référence PRJ-AAAA-nn. Message d'invitation à ajouter des jalons.
- **Constat pré-recette** : []
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRJ-02 · Projet lancé sans date de démarrage

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Statut « En cours », date de démarrage vide
  2. Enregistrer
- **Résultat attendu** : Refus : « Indiquez la date de démarrage : le projet est déjà lancé. »
- **Constat pré-recette** : {"start_date":["Indiquez la date de démarrage : le projet est déjà lancé."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRJ-03 · Fin avant démarrage

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Démarrage 20/10, fin prévue 10/10
- **Résultat attendu** : Refus : « La date de fin doit suivre la date de début. »
- **Constat pré-recette** : {"end_date":["La date de fin doit suivre la date de début."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRJ-04 · Engagé supérieur au budget

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Budget 1 000 000, montant engagé 1 500 000
- **Résultat attendu** : Une alerte rouge s'affiche en direct sous le champ ; à l'enregistrement : « Le montant engagé dépasse le budget prévu… ».
- **Constat pré-recette** : {"spent":["Le montant engagé dépasse le budget prévu : relevez le budget ou corrigez le montant."]} ; alerte visible true puis masquée true
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRJ-05 · Ajouter des jalons

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Un projet existe
- **Étapes** :
  1. Fiche du projet > Jalons : ajouter A (poids 1), B (poids 3)
- **Résultat attendu** : Les jalons apparaissent dans l'ordre. L'avancement reste 0 %.
- **Constat pré-recette** : 2 jalons
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRJ-06 · Avancement pondéré

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Jalons A (1) et B (3)
- **Étapes** :
  1. Cocher le jalon B
- **Résultat attendu** : L'avancement passe à 75 % (3/4). Le projet « Planifié » devient « En cours ».
- **Constat pré-recette** : {"avancement":75,"statut":"en_cours"} ; statut en_cours
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRJ-07 · Projet terminé automatiquement

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Cocher aussi le jalon A
- **Résultat attendu** : L'avancement est 100 % ; le projet passe « Terminé ».
- **Constat pré-recette** : [100,"termine"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRJ-08 · Décocher un jalon

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Projet terminé
- **Étapes** :
  1. Décocher le jalon A
- **Résultat attendu** : L'avancement redescend (75 %) et le projet repasse « En cours ».
- **Constat pré-recette** : [75,"en_cours"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRJ-09 · Projet terminé sans jalon

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Créer un projet « Terminé » avec date de démarrage, sans jalon, avancement laissé à 0
- **Résultat attendu** : L'avancement enregistré est 100 %.
- **Constat pré-recette** : [[],100]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRJ-10 · Avancement verrouillé

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : Projet avec jalons
- **Étapes** :
  1. Modifier le projet
- **Résultat attendu** : Le champ Avancement est désactivé avec la mention « calculé à partir des jalons ».
- **Constat pré-recette** : avancement verrouillé
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRJ-11 · Projet en retard

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : Projet « En cours » dont la fin prévue est dépassée
- **Étapes** :
  1. Projets
  2. Chiffres clés
- **Résultat attendu** : Il est compté en retard.
- **Constat pré-recette** : projets en retard: 0
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRJ-12 · Ajouter un intervenant au projet

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Un intervenant existe
- **Étapes** :
  1. Fiche du projet > Intervenants > Ajouter au projet
- **Résultat attendu** : L'intervenant apparaît dans la liste du projet.
- **Constat pré-recette** : [302,[]]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRJ-13 · Créer un chef de projet

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Chefs de projet > Ajouter
  2. Nom, prénom, téléphone
  3. Enregistrer
- **Résultat attendu** : Accepté ; il est proposé dans le formulaire des projets.
- **Constat pré-recette** : []
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRJ-14 · Chef de projet sans contact

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Nom et prénom seulement
- **Résultat attendu** : Refus : « Indiquez au moins un téléphone ou un e-mail… »
- **Constat pré-recette** : ["e_mail","telephone"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRJ-15 · Chef de projet sans nom

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Laisser nom et prénom vides
- **Résultat attendu** : Refus.
- **Constat pré-recette** : ["nom","prenom"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRJ-16 · Entreprise sans organisme

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Intervenants > Ajouter
  2. Type « Entreprise de travaux », organisme vide, un téléphone
- **Résultat attendu** : Refus : « Indiquez l'entreprise ou l'organisme de cet intervenant. »
- **Constat pré-recette** : {"organisation":["Indiquez l'entreprise ou l'organisme de cet intervenant."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRJ-17 · Technicien indépendant

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Type « Technicien / artisan », nom, téléphone, organisme vide
- **Résultat attendu** : Accepté.
- **Constat pré-recette** : []
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRJ-18 · Intervenant sans moyen de contact

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Ni téléphone ni e-mail
- **Résultat attendu** : Refus : au moins un des deux.
- **Constat pré-recette** : ["telephone","email"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRJ-19 · Rapport de projet

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Majeur · **Pré-recette** : Manuel
- **Préconditions** : Un projet et un fichier de moins de 25 Mo
- **Étapes** :
  1. Rapports > Ajouter
  2. Titre, fichier, date du jour, projet
  3. Enregistrer
- **Résultat attendu** : Le rapport apparaît dans la liste et dans la fiche du projet.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRJ-20 · Rapport : date future ou sans projet

- **Compte** : INF (infrastructures@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Date de demain, puis aucun projet
- **Résultat attendu** : Refus dans les deux cas.
- **Constat pré-recette** : [["report_date"],["projects"]]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

## MNT – Demandes de maintenance (circuit complet)

*Objectif : Vérifier chaque étape du circuit et ses effets sur les matières et infrastructures.*

### MNT-01 · Déposer une demande valide

- **Compte** : AGT (agent@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Une matière du service existe
- **Étapes** :
  1. Demandes de maintenance > Ajouter
  2. Objet, type Corrective, priorité Normale, Concerne « Matière », choisir la matière
  3. Décrire le problème
  4. Enregistrer
- **Résultat attendu** : La demande reçoit une référence DMT-AAAA-nnn au statut « Soumise ». Message « transmise pour validation ».
- **Constat pré-recette** : [[],"DMT-2026-009"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MNT-02 · Description obligatoire

- **Compte** : AGT (agent@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Déposer une demande sans description
- **Résultat attendu** : Refus : « Décrivez le problème (constat, localisation, depuis quand)… »
- **Constat pré-recette** : {"description":["Décrivez le problème (constat, localisation, depuis quand) pour que le responsable puisse l'évaluer."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MNT-03 · Cible infrastructure sans infrastructure

- **Compte** : AGT (agent@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Concerne « Bâtiment / infrastructure », aucune infrastructure choisie
- **Résultat attendu** : Refus : « Choisissez l'infrastructure concernée. »
- **Constat pré-recette** : {"infrastructure_id":["Choisissez l'infrastructure concernée."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MNT-04 · Cible matière sans matière

- **Compte** : AGT (agent@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Concerne « Matière / équipement », aucune matière choisie
- **Résultat attendu** : Refus : « Choisissez la matière concernée. »
- **Constat pré-recette** : {"asset_id":["Choisissez la matière concernée."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MNT-05 · Notification au responsable

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Demande MNT-01 déposée
- **Étapes** :
  1. Se connecter en MNT
  2. Ouvrir la cloche
- **Résultat attendu** : Une notification « Nouvelle demande de maintenance … à valider » est présente, avec un lien vers la demande.
- **Constat pré-recette** : notification reçue par le responsable maintenance
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MNT-06 · Avis technique favorable

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Demande « Soumise »
- **Étapes** :
  1. Ouvrir la demande
  2. Bloc « Avis technique » > Valider
- **Résultat attendu** : Statut « Attente du Directeur ». Le Directeur reçoit une notification.
- **Constat pré-recette** : statut en_attente_direction
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MNT-07 · Rejet sans motif

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Demande « Soumise »
- **Étapes** :
  1. Rejeter sans saisir de motif
- **Résultat attendu** : Refus : « Indiquez le motif du rejet. »
- **Constat pré-recette** : {"decision_notes":["Indiquez le motif du rejet."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MNT-08 · Rejet avec motif

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Demande « Soumise »
- **Étapes** :
  1. Rejeter avec un motif
- **Résultat attendu** : Statut « Rejetée ». Le demandeur est notifié ; le motif est visible dans le suivi.
- **Constat pré-recette** : statut rejetee
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MNT-09 · Approbation du Directeur

- **Compte** : DIR (directeur@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Demande « Attente du Directeur »
- **Étapes** :
  1. Ouvrir la demande
  2. Bloc « Approbation du Directeur » > Valider
- **Résultat attendu** : Statut « Approuvée ». Le responsable maintenance est notifié.
- **Constat pré-recette** : statut validee
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MNT-10 · Le Directeur ne donne pas l'avis technique

- **Compte** : DIR (directeur@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Demande « Soumise »
- **Étapes** :
  1. Ouvrir la demande
- **Résultat attendu** : Aucun bouton de décision n'est proposé (ou erreur 403 si l'action est forcée).
- **Constat pré-recette** : statut 403
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MNT-11 · Le responsable maintenance n'approuve pas

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Demande « Attente du Directeur »
- **Étapes** :
  1. Ouvrir la demande
- **Résultat attendu** : Aucun bouton d'approbation n'est proposé.
- **Constat pré-recette** : statut 403
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MNT-12 · Planifier

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Demande « Approuvée »
- **Étapes** :
  1. Bloc « Planifier » : date prévue, échéance, technicien, instructions
  2. Enregistrer
- **Résultat attendu** : Statut « Planifiée ». Une tâche « [DMT-…] Objet » apparaît dans Tâches et dans le calendrier. Le technicien est notifié.
- **Constat pré-recette** : [[],"planifiee","[DMT-2026-009] Panne recette"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MNT-13 · Planification : date prévue obligatoire

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Planifier sans date prévue
- **Résultat attendu** : Refus.
- **Constat pré-recette** : {"planned_for":["date d'intervention champ est requis."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MNT-14 · Planification : échéance avant la date prévue

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Échéance antérieure à la date prévue
- **Résultat attendu** : Refus (l'échéance doit être égale ou postérieure).
- **Constat pré-recette** : {"due_date":["échéance doit être une date postérieure ou égale date d'intervention."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MNT-15 · Démarrer l'intervention sur une matière

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Demande planifiée portant sur une matière
- **Étapes** :
  1. Démarrer l'intervention
- **Résultat attendu** : Statut « En cours ». La matière passe « En réparation ». La tâche passe « En cours ».
- **Constat pré-recette** : ["en_cours",5,"En cours"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MNT-16 · Démarrer sur une infrastructure urgente

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Demande urgente sur une infrastructure « En service »
- **Étapes** :
  1. Démarrer l'intervention
- **Résultat attendu** : L'infrastructure passe « En maintenance ».
- **Constat pré-recette** : statut infrastructure en_maintenance
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MNT-17 · Clôture sans compte rendu

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Demande « En cours »
- **Étapes** :
  1. Clôturer sans saisir l'intervention réalisée
- **Résultat attendu** : Refus : « Décrivez l'intervention réalisée. »
- **Constat pré-recette** : {"resolution":["Décrivez l'intervention réalisée."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MNT-18 · Clôture avec remise en service

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Demande en cours sur une matière
- **Étapes** :
  1. Clôturer : compte rendu, coût, cocher « Remise en service »
- **Résultat attendu** : Statut « Terminée ». La matière redevient « Disponible » (ou « Affecté » si elle était affectée). Le demandeur est notifié. La tâche passe « Terminée ».
- **Constat pré-recette** : ["terminee",1,"attendu Affecté 1"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MNT-19 · Clôture sans remise en service

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Demande en cours sur une matière
- **Étapes** :
  1. Clôturer sans cocher « Remise en service »
- **Résultat attendu** : La matière passe « En panne ».
- **Constat pré-recette** : matière En panne
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MNT-20 · Clôture sur une infrastructure

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Infrastructure « En maintenance »
- **Étapes** :
  1. Clôturer en indiquant l'état constaté « Bon »
- **Résultat attendu** : L'infrastructure repasse « En service », son état devient « Bon » et sa date de dernière visite est celle du jour.
- **Constat pré-recette** : ["en_service","bon","2026-10-02 00:00:00"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MNT-21 · Visibilité pour un agent

- **Compte** : AGT (agent@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Demandes de plusieurs services
- **Étapes** :
  1. Se connecter en AGT
  2. Ouvrir Demandes de maintenance
- **Résultat attendu** : Il ne voit que ses demandes et celles du matériel de son service.
- **Constat pré-recette** : visibles 7 / total 13; hors périmètre 0
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MNT-22 · Liste et chiffres clés

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : Jeu de démonstration
- **Étapes** :
  1. Demandes de maintenance
- **Résultat attendu** : Les chiffres (en attente, en cours, terminées…) sont cohérents avec la liste ; les statuts ont des pastilles de couleur.
- **Constat pré-recette** : statut 200
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### MNT-23 · Supprimer une demande

- **Compte** : ADM (admin@admin.com) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : Une demande de test
- **Étapes** :
  1. Supprimer la demande
  2. Confirmer
- **Résultat attendu** : Elle disparaît de la liste.
- **Constat pré-recette** : suppression logique
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

## PRV – Maintenance préventive

*Objectif : Vérifier la génération automatique des demandes d'entretien.*

### PRV-01 · Créer un plan valide

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Une matière ou infrastructure
- **Étapes** :
  1. Maintenance préventive > Ajouter
  2. Opération, cible, périodicité Semestrielle, échéance dans 20 jours, délai 7 jours, points de contrôle
  3. Enregistrer
- **Résultat attendu** : Le plan apparaît, actif, avec sa prochaine échéance.
- **Constat pré-recette** : []
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRV-02 · Points de contrôle obligatoires

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Créer un plan sans points de contrôle
- **Résultat attendu** : Refus : « Listez les points de contrôle à vérifier à chaque opération. »
- **Constat pré-recette** : {"description":["Listez les points de contrôle à vérifier à chaque opération."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRV-03 · Échéance dans le passé

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Prochaine échéance = hier (à la création)
- **Résultat attendu** : Refus : « La prochaine échéance ne peut pas être dans le passé. »
- **Constat pré-recette** : {"next_due_at":["La prochaine échéance ne peut pas être dans le passé."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRV-04 · Délai supérieur à la périodicité

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Périodicité Mensuelle, délai 45 jours
- **Résultat attendu** : Refus : « Le délai de création doit rester inférieur à la périodicité du plan. »
- **Constat pré-recette** : {"lead_days":["Le délai de création doit rester inférieur à la périodicité du plan."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRV-05 · Lancer un plan

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Un plan actif
- **Étapes** :
  1. Cliquer ⚡ « Lancer » sur le plan
- **Résultat attendu** : Une demande préventive est créée au statut « Attente du Directeur » (avis technique déjà inscrit). Le Directeur et le responsable du plan sont notifiés.
- **Constat pré-recette** : [302,"en_attente_direction"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRV-06 · Pas de doublon

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Demande préventive déjà générée et encore ouverte
- **Étapes** :
  1. Cliquer de nouveau « Lancer »
- **Résultat attendu** : Aucune seconde demande n'est créée.
- **Constat pré-recette** : demandes: 1
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRV-07 · L'échéance avance

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Plan lancé (PRV-05)
- **Étapes** :
  1. Consulter la prochaine échéance du plan
- **Résultat attendu** : Elle a avancé d'une période (+ 6 mois pour un plan semestriel).
- **Constat pré-recette** : ["2026-10-22","2027-04-22"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRV-08 · Commande automatique

- **Compte** : — · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Accès au serveur ; un plan dont la date « échéance − délai » est atteinte
- **Étapes** :
  1. Exécuter php artisan sygep:maintenance-preventive
- **Résultat attendu** : « n demande(s) préventive(s) créée(s) » ; sinon « Aucun entretien à programmer ».
- **Constat pré-recette** : 1 demande(s) préventive(s) créée(s).
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRV-09 · Plan inactif

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Un plan dont l'échéance est atteinte
- **Étapes** :
  1. Décocher « Plan actif »
  2. Exécuter la commande ou cliquer Lancer
- **Résultat attendu** : Aucune demande n'est générée.
- **Constat pré-recette** : aucune demande
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRV-10 · Planificateur installé

- **Compte** : — · **Priorité** : Critique · **Pré-recette** : Manuel
- **Préconditions** : Accès au serveur
- **Étapes** :
  1. Vérifier la présence de la ligne cron « schedule:run » ; observer le lendemain 6 h 30
- **Résultat attendu** : Les plans échus génèrent leur demande sans intervention.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PRV-11 · Clôture d'une demande préventive

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : Demande préventive approuvée puis exécutée
- **Étapes** :
  1. Clôturer l'intervention
- **Résultat attendu** : La « dernière réalisation » du plan est mise à la date du jour.
- **Constat pré-recette** : dernière réalisation = aujourd'hui
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

## TCH – Tâches, étiquettes et calendrier

*Objectif : Vérifier l'organisation du travail des équipes.*

### TCH-01 · Créer une tâche valide

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Tâches > Ajouter
  2. Nom, état « Ouverte », date prévue
  3. Enregistrer
- **Résultat attendu** : La tâche apparaît dans la liste et dans le calendrier.
- **Constat pré-recette** : []
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### TCH-02 · Date limite avant la date prévue

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Date prévue 20/10, date limite 10/10
- **Résultat attendu** : Refus avec message sous la date limite.
- **Constat pré-recette** : {"due_date":["échéance doit être une date postérieure ou égale scheduled date."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### TCH-03 · Statuts en français

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Statuts des tâches
- **Résultat attendu** : Ouverte, En cours, Terminée sont présents.
- **Constat pré-recette** : ["En cours","Ouverte","Terminée"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### TCH-04 · Équipement affiché par nom et code

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Tâches > Ajouter
  2. Ouvrir la liste « Équipement »
- **Résultat attendu** : Chaque ligne affiche le nom suivi du code (et non un numéro de série éventuellement vide).
- **Constat pré-recette** : nom — code
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### TCH-05 · Étiquettes

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Étiquettes > Ajouter « Urgent »
  2. L'associer à une tâche
- **Résultat attendu** : L'étiquette apparaît sur la tâche.
- **Constat pré-recette** : étiquette liée
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### TCH-06 · Calendrier

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Une tâche avec date limite et une sans date limite
- **Étapes** :
  1. Calendrier
- **Résultat attendu** : Les tâches qui ont une date limite apparaissent à cette date ; celle qui n'en a pas n'apparaît pas.
- **Constat pré-recette** : seules les tâches avec date limite apparaissent
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### TCH-07 · Tâche issue d'une maintenance

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Demande planifiée (MNT-12)
- **Étapes** :
  1. Tâches
- **Résultat attendu** : La tâche créée automatiquement existe, avec l'état qui suit l'intervention.
- **Constat pré-recette** : tâche issue de la planification
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### TCH-08 · Assigner une tâche

- **Compte** : MNT (maintenance@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Choisir un utilisateur dans « Affecté à »
- **Résultat attendu** : La tâche affiche le responsable.
- **Constat pré-recette** : affectée
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

## RPT – Rapports périodiques

*Objectif : Vérifier les bilans destinés à la direction.*

### RPT-01 · Rapport mensuel

- **Compte** : DIR (directeur@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Nous sommes le 15 octobre (exemple)
- **Étapes** :
  1. Rapports périodiques > carte « Rapport mensuel »
- **Résultat attendu** : Un nouvel onglet s'ouvre sur « Septembre 2026 » (le mois précédent), toutes rubriques.
- **Constat pré-recette** : ["01\/09\/2026","30\/09\/2026","Septembre 2026"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### RPT-02 · Rapport trimestriel

- **Compte** : DIR (directeur@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Même date
- **Étapes** :
  1. Carte « Rapport trimestriel »
- **Résultat attendu** : Période : du 01/07 au 30/09 (le trimestre précédent).
- **Constat pré-recette** : ["01\/07\/2026","30\/09\/2026","Du 01\/07\/2026 au 30\/09\/2026"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### RPT-03 · Bilan semestriel

- **Compte** : DIR (directeur@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Même date
- **Étapes** :
  1. Carte « Bilan semestriel »
- **Résultat attendu** : Période : du 01/01 au 30/06 (le semestre précédent, terminé).
- **Constat pré-recette** : [["01\/01\/2026","30\/06\/2026","Du 01\/01\/2026 au 30\/06\/2026"],["01\/07\/2025","31\/12\/2025","Du 01\/07\/2025 au 31\/12\/2025"],["01\/01\/2026","30\/06\/2026","Du 01\/01\/2026 au 30\/06\/2026"],["01\/01\/2026","30\/06\/2026","Du 01\/01\/2026 au 30\/06\/2026"]]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### RPT-04 · Bilan annuel

- **Compte** : DIR (directeur@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Même date
- **Étapes** :
  1. Carte « Bilan annuel »
- **Résultat attendu** : Période : du 01/01 au 31/12 de l'année précédente.
- **Constat pré-recette** : ["01\/01\/2025","31\/12\/2025","Du 01\/01\/2025 au 31\/12\/2025"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### RPT-05 · Composer un rapport

- **Compte** : DIR (directeur@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Choisir « Période personnalisée », renseigner du…au…
  2. Décocher une rubrique
  3. Générer
- **Résultat attendu** : Le rapport couvre la période choisie et n'inclut que les rubriques cochées.
- **Constat pré-recette** : statut 200
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### RPT-06 · Impression / PDF

- **Compte** : DIR (directeur@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Un rapport ouvert
- **Étapes** :
  1. Utiliser « Imprimer » puis « Enregistrer au format PDF »
- **Résultat attendu** : La mise en page est correcte (A4), sans la barre d'outils.
- **Constat pré-recette** : statut 200
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### RPT-07 · Droit d'accès

- **Compte** : AGT (agent@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Compte agent
- **Étapes** :
  1. Saisir l'adresse /admin/rapports-periodiques
- **Résultat attendu** : Erreur 403.
- **Constat pré-recette** : statut 403
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

## NTF – Notifications

*Objectif : Vérifier l'envoi et la lecture des notifications internes.*

### NTF-01 · Envoyer à tous

- **Compte** : ADM (admin@admin.com) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Envoi de notifications > Ajouter
  2. Message, destinataires « Tous les utilisateurs »
  3. Enregistrer
- **Résultat attendu** : Message « Notification envoyée à N utilisateur(s) ». Chaque compte voit la notification dans sa cloche.
- **Constat pré-recette** : [[],12,12,"Notification envoyée à 12 utilisateur(s)."]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### NTF-02 · Envoyer à un rôle

- **Compte** : ADM (admin@admin.com) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Destinataires « Par rôle », choisir « Agent / demandeur »
- **Résultat attendu** : Seuls les comptes de ce rôle reçoivent la notification.
- **Constat pré-recette** : [2,2]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### NTF-03 · Envoyer à des personnes

- **Compte** : ADM (admin@admin.com) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Destinataires « Personnes choisies », en cocher deux
- **Résultat attendu** : Seules ces deux personnes la reçoivent.
- **Constat pré-recette** : []
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### NTF-04 · Rôle sans sélection

- **Compte** : ADM (admin@admin.com) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. « Par rôle » sans choisir de rôle
- **Résultat attendu** : Refus : « Choisissez au moins un rôle. »
- **Constat pré-recette** : {"roles":["Choisissez au moins un rôle."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### NTF-05 · Message trop long

- **Compte** : ADM (admin@admin.com) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Message de plus de 255 caractères
- **Résultat attendu** : Refus.
- **Constat pré-recette** : {"alert_text":["alert text ne peut pas être plus grand que 255 caractères."]}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### NTF-06 · Adresse personnalisée

- **Compte** : ADM (admin@admin.com) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Page « personnalisée » sans adresse, puis avec « abc »
- **Résultat attendu** : Refus dans les deux cas ; une adresse https://… complète est acceptée.
- **Constat pré-recette** : [{"alert_link":["Saisissez l'adresse personnalisée."]},{"alert_link":["Le lien doit être une adresse complète (https:\/\/…)."]},[]]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### NTF-07 · Ouvrir une notification

- **Compte** : AGT (agent@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Notification avec lien
- **Étapes** :
  1. Cliquer la cloche puis la notification
- **Résultat attendu** : La page cible s'ouvre ; la notification est marquée lue ; le compteur diminue.
- **Constat pré-recette** : {"avant":0,"apr\u00e8s":1,"statut":302}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### NTF-08 · Tout marquer comme lu

- **Compte** : AGT (agent@sygep.test) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Plusieurs notifications non lues
- **Étapes** :
  1. Cliquer « Tout marquer comme lu »
- **Résultat attendu** : Le compteur de la cloche tombe à zéro.
- **Constat pré-recette** : non lues: 0 -> 0
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### NTF-09 · Notifications automatiques

- **Compte** : ADM (admin@admin.com) · **Priorité** : Majeur · **Pré-recette** : Manuel
- **Préconditions** : Événements réalisés (stock bas, demande déposée…)
- **Étapes** :
  1. Ouvrir la cloche des comptes concernés
- **Résultat attendu** : Les notifications automatiques décrites au guide (5.7) sont présentes.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

## DSH – Tableau de bord, listes et ergonomie

*Objectif : Vérifier l'affichage général, la cohérence des chiffres et l'utilisabilité.*

### DSH-01 · Tuiles de chiffres clés

- **Compte** : ADM (admin@admin.com) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Jeu de démonstration
- **Étapes** :
  1. Ouvrir le tableau de bord
- **Résultat attendu** : Huit tuiles s'alignent en deux rangées de quatre, de même hauteur.
- **Constat pré-recette** : grille 4 colonnes, 8 tuiles ; tuiles 8, rangées 2, hauteurs distinctes 1
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### DSH-02 · Cohérence des chiffres

- **Compte** : ADM (admin@admin.com) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Comparer chaque tuile au module correspondant (ex. Disponibles ↔ liste filtrée)
- **Résultat attendu** : Les chiffres du tableau de bord correspondent aux listes.
- **Constat pré-recette** : [[38,13,22,5,1,7,4,5],38,13]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### DSH-03 · Graphiques en bas du tableau de bord

- **Compte** : ADM (admin@admin.com) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Faire défiler le tableau de bord
- **Résultat attendu** : Les listes (mouvements, projets, maintenance) précèdent les graphiques placés en bas de page.
- **Constat pré-recette** : listes avant graphiques
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### DSH-04 · Bloc Graphiques repliable

- **Compte** : ADM (admin@admin.com) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Ouvrir Matières
  2. Cliquer « Graphiques (n) »
  3. Aller sur un autre module
- **Résultat attendu** : Fermé par défaut ; une fois ouvert, il reste ouvert sur les autres modules (choix mémorisé).
- **Constat pré-recette** : fermé au départ true, canvas 4, ouvert sur un autre module true
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### DSH-05 · Actions rapides

- **Compte** : ADM (admin@admin.com) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Cliquer chaque bouton d'action rapide du tableau de bord
- **Résultat attendu** : Chaque bouton ouvre le bon formulaire. Un bouton n'apparaît que si le droit existe.
- **Constat pré-recette** : boutons selon droits
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### DSH-06 · Recherche globale

- **Compte** : ADM (admin@admin.com) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Taper « HP » dans la barre du haut
- **Résultat attendu** : Des résultats cliquables (matières, fournisseurs, bons…) apparaissent.
- **Constat pré-recette** : statut 200
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### DSH-07 · Actions alignées dans les tableaux

- **Compte** : ADM (admin@admin.com) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Ouvrir Utilisateurs, Matières, Affectations, Plans de maintenance
- **Résultat attendu** : Les icônes d'action sont alignées sur une seule ligne dans tous les tableaux.
- **Constat pré-recette** : /admin/users:0 /admin/assets:0 /admin/assignments:0 /admin/maintenance-plans:0 /admin/stock-vouchers:0
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### DSH-08 · Menu : déconnexion en bas

- **Compte** : ADM (admin@admin.com) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : Plusieurs groupes de menu ouverts, fenêtre basse
- **Étapes** :
  1. Ouvrir tous les groupes du menu
- **Résultat attendu** : Le bouton Déconnexion reste visible en bas, sans défiler.
- **Constat pré-recette** : {"b":465,"vh":520}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### DSH-09 · Sous-menu actif lisible

- **Compte** : ADM (admin@admin.com) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Ouvrir « Bons de stock »
- **Résultat attendu** : L'entrée du menu est surlignée en bleu avec un texte blanc lisible.
- **Constat pré-recette** : couleur rgb(255, 255, 255)
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### DSH-10 · Menus déroulants

- **Compte** : ADM (admin@admin.com) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Cliquer chaque groupe du menu
  2. Cliquer le menu du compte et la cloche
- **Résultat attendu** : Tout s'ouvre et se ferme ; les liens fonctionnent.
- **Constat pré-recette** : groupes ouverts 1, navigation true, menu compte true, erreurs JS 0
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### DSH-11 · Affichage sur téléphone

- **Compte** : ADM (admin@admin.com) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Téléphone ou fenêtre étroite (≈ 390 px)
- **Étapes** :
  1. Ouvrir le tableau de bord, une liste, un formulaire
- **Résultat attendu** : Pas de défilement horizontal de la page ; les tuiles passent sur deux colonnes ; le menu s'ouvre par le bouton en haut.
- **Constat pré-recette** : largeur: /admin:390/390 /admin/assets:390/390 /admin/assets/create:390/390 /admin/assignments/create:390/390 ; colonnes de tuiles 2
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### DSH-12 · Libellés français des tableaux

- **Compte** : ADM (admin@admin.com) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : Accès Internet
- **Étapes** :
  1. Ouvrir une liste
- **Résultat attendu** : « Afficher … entrées », « Rechercher », pagination en français.
- **Constat pré-recette** : libellés DataTables
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### DSH-13 · Message de confirmation

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Enregistrer un bon de stock (ou une affectation, un mouvement de stock, une clôture d'inventaire)
- **Résultat attendu** : Un bandeau vert confirme l'opération (ex. « Bon de sortie BS-… enregistré »). Les simples créations de référentiels (catégorie, service…) reviennent à la liste sans bandeau.
- **Constat pré-recette** : Bon de sortie BS-2026-0007 enregistré : 1 article(s) mis à jour.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

## PUB – Site public et page Contact

*Objectif : Vérifier ce que voient les visiteurs non connectés.*

### PUB-01 · Page d'accueil

- **Compte** : — · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Déconnecté
- **Étapes** :
  1. Ouvrir /
- **Résultat attendu** : La page de présentation de SYGEP s'affiche, avec un accès à la connexion.
- **Constat pré-recette** : statut 200
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PUB-02 · Accueil pour un connecté

- **Compte** : ADM (admin@admin.com) · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : Connecté
- **Étapes** :
  1. Ouvrir /
- **Résultat attendu** : Redirection vers le tableau de bord.
- **Constat pré-recette** : [302,"http:\/\/localhost\/admin"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PUB-03 · Envoyer un message

- **Compte** : — · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Déconnecté
- **Étapes** :
  1. Ouvrir /contact
  2. Remplir nom, e-mail, objet, message
  3. Envoyer
- **Résultat attendu** : Message de remerciement. Les comptes ayant le droit de lire les messages reçoivent une notification et voient le bandeau « nouveau message de contact à lire ».
- **Constat pré-recette** : [302,[],"Nouveau message de Visiteur : Demande d'information"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PUB-04 · Champs obligatoires

- **Compte** : — · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Envoyer le formulaire vide
- **Résultat attendu** : Refus avec messages.
- **Constat pré-recette** : ["name","email","subject","message"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PUB-05 · Limitation d'envoi

- **Compte** : — · **Priorité** : Mineur · **Pré-recette** : Manuel
- **Préconditions** : —
- **Étapes** :
  1. Envoyer six messages en une minute
- **Résultat attendu** : Le sixième est refusé (limite de 5 par minute).
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PUB-06 · Coordonnées

- **Compte** : — · **Priorité** : Mineur · **Pré-recette** : OK
- **Préconditions** : .env renseigné
- **Étapes** :
  1. Ouvrir /contact
- **Résultat attendu** : Adresse, téléphone, e-mail et horaires correspondent aux valeurs de CONTACT_* dans .env.
- **Constat pré-recette** : coordonnées de config/panel.php
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### PUB-07 · Lire un message reçu

- **Compte** : ADM (admin@admin.com) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Un message reçu
- **Étapes** :
  1. Ouvrir la notification ou la liste des messages de contact
- **Résultat attendu** : Le message est lisible ; il est marqué lu.
- **Constat pré-recette** : statut 200
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

## SEC – Droits et isolation des données

*Objectif : Vérifier que personne ne voit ni ne fait plus que ce qui lui est permis.*

### SEC-01 · Isolation du comptable secondaire

- **Compte** : SEC (secondaire@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Données de plusieurs services (jeu de démonstration)
- **Étapes** :
  1. Se connecter en SEC
  2. Ouvrir Matières, Agents, Affectations, Mouvements de stock, Inventaires
- **Résultat attendu** : Il ne voit que les données du service « CFP de Thiès ».
- **Constat pré-recette** : {"Asset":"7\/39","Agent":"2\/16","Assignment":"4\/18","StockMovement":"12\/73","Inventaire":"1\/2","StockVoucher":"1\/3"}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### SEC-02 · Création hors de son service

- **Compte** : SEC (secondaire@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Tenter de créer une affectation ou une sortie de stock pour un autre service
- **Résultat attendu** : Impossible : choix absent, refusé, ou erreur « Cette opération concerne un autre service que le vôtre ».
- **Constat pré-recette** : {"statut":404,"bons":[18,18],"service matière":5} ; [403,[]]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### SEC-03 · Agent : matériel de son service

- **Compte** : AGT (agent@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Ouvrir Matières
- **Résultat attendu** : Seules les matières de son service sont visibles, en lecture seule.
- **Constat pré-recette** : lignes 7, fuites 0, create 403
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### SEC-04 · Droits non cumulés par erreur

- **Compte** : AGT (agent@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Chercher dans le menu Stock, Infrastructures, Rapports, Rôles
- **Résultat attendu** : Aucun de ces modules n'est visible.
- **Constat pré-recette** : []
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### SEC-05 · 403 sur adresse directe

- **Compte** : AGT (agent@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Saisir /admin/stock-items, /admin/projects, /admin/roles
- **Résultat attendu** : Erreur 403 à chaque fois.
- **Constat pré-recette** : {"\/admin\/stock-items":403,"\/admin\/projects":403,"\/admin\/roles":403}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### SEC-06 · Directeur : aucune modification

- **Compte** : DIR (directeur@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Tenter d'ouvrir /admin/assets/create
- **Résultat attendu** : Erreur 403.
- **Constat pré-recette** : create 403 / post 403
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### SEC-07 · Ajustement de stock réservé

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Tenter l'ajustement en forçant le type dans l'adresse (…/stock-movements/create?type=ajustement)
- **Résultat attendu** : Le formulaire ne propose pas l'ajustement ; l'envoi est refusé.
- **Constat pré-recette** : post 403
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### SEC-08 · Clôture d'inventaire réservée

- **Compte** : MAT (matieres@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : —
- **Étapes** :
  1. Tenter de clôturer une campagne
- **Résultat attendu** : Bouton absent ; action interdite (403).
- **Constat pré-recette** : statut 403
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### SEC-09 · Mot de passe non lisible

- **Compte** : — · **Priorité** : Critique · **Pré-recette** : Manuel
- **Préconditions** : Accès à la base
- **Étapes** :
  1. Consulter la colonne password de la table users
- **Résultat attendu** : Les mots de passe sont chiffrés (empreinte commençant par $2y$), jamais en clair.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### SEC-10 · Compte supprimé ou désapprouvé

- **Compte** : ADM (admin@admin.com) · **Priorité** : Majeur · **Pré-recette** : OK
- **Préconditions** : Un utilisateur connecté dans un autre navigateur
- **Étapes** :
  1. Décocher « Approuvé » sur ce compte
  2. Actualiser une page dans l'autre navigateur
- **Résultat attendu** : L'utilisateur est déconnecté et ne peut plus se connecter.
- **Constat pré-recette** : [302,"http:\/\/localhost\/login"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### SEC-12 · Adresse directe d'une matière d'un autre service

- **Compte** : SEC (secondaire@sygep.test) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Connaître l'identifiant d'une matière d'un autre service (ex. /admin/assets/1)
- **Étapes** :
  1. Saisir directement l'adresse /admin/assets/1 (matière d'un autre service)
- **Résultat attendu** : Erreur 404 : la matière n'est pas accessible. Même contrôle pour les affectations, mouvements et bons de stock d'un autre service.
- **Constat pré-recette** : matière 404, affectation 404, bon de stock 404
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### SEC-11 · Mode débogage éteint

- **Compte** : — · **Priorité** : Critique · **Pré-recette** : Manuel
- **Préconditions** : Serveur de production
- **Étapes** :
  1. Provoquer une erreur (adresse inexistante, puis erreur serveur simulée)
- **Résultat attendu** : Aucune trace technique (chemins, requêtes SQL) n'est affichée à l'utilisateur.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

## INS – Installation et exploitation

*Objectif : Vérifier le déploiement, la sauvegarde et la reprise.*

### INS-01 · Installation sur base vide

- **Compte** : — · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Base vide, .env configuré
- **Étapes** :
  1. php artisan migrate --seed
- **Résultat attendu** : Aucune erreur. La base contient 149 droits, 9 rôles métier + le rôle User, les statuts de matières et de tâches, et le compte admin.
- **Constat pré-recette** : {"droits":149,"rôles":10,"statuts matières":5,"statuts tâches":3}
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INS-02 · Seeders relançables

- **Compte** : — · **Priorité** : Majeur · **Pré-recette** : Manuel
- **Préconditions** : Installation faite
- **Étapes** :
  1. php artisan db:seed
- **Résultat attendu** : Aucune erreur ni doublon.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INS-03 · Rôles du compte admin

- **Compte** : ADM (admin@admin.com) · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Installation neuve
- **Étapes** :
  1. Utilisateurs > admin@admin.com
- **Résultat attendu** : Le compte n'a que le rôle « Super administrateur ».
- **Constat pré-recette** : ["Super administrateur"]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INS-04 · Rôle de l'inscription libre

- **Compte** : — · **Priorité** : Critique · **Pré-recette** : OK
- **Préconditions** : Installation neuve
- **Étapes** :
  1. Créer un compte via /register
  2. Observer ses rôles
- **Résultat attendu** : Rôle « User » uniquement, droits très limités, compte non approuvé.
- **Constat pré-recette** : [["User"],11]
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INS-05 · Jeu de démonstration

- **Compte** : — · **Priorité** : Majeur · **Pré-recette** : Manuel
- **Préconditions** : Base de TEST
- **Étapes** :
  1. php artisan db:seed --class=DemoDataSeeder
  2. Le relancer
- **Résultat attendu** : Les données sont créées ; la seconde exécution indique que les données existent déjà et ne fait rien.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INS-06 · Fichiers déposés

- **Compte** : ADM (admin@admin.com) · **Priorité** : Majeur · **Pré-recette** : Manuel
- **Préconditions** : storage:link exécuté
- **Étapes** :
  1. Ajouter une photo à une matière
  2. Ouvrir la fiche
- **Résultat attendu** : La photo s'affiche (le lien public vers storage fonctionne).
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INS-07 · Sauvegarde et restauration

- **Compte** : — · **Priorité** : Critique · **Pré-recette** : Manuel
- **Préconditions** : Accès au serveur
- **Étapes** :
  1. Faire un mysqldump
  2. Restaurer dans une base vide
  3. Pointer un .env de test dessus
  4. Se connecter
- **Résultat attendu** : L'application fonctionne avec les données restaurées.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INS-08 · Mise à jour

- **Compte** : — · **Priorité** : Critique · **Pré-recette** : Manuel
- **Préconditions** : Sauvegarde faite
- **Étapes** :
  1. git pull
  2. composer install --no-dev
  3. php artisan migrate --force
  4. php artisan view:clear
- **Résultat attendu** : Aucune erreur ; l'application fonctionne ; les données sont intactes.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INS-09 · HTTPS

- **Compte** : — · **Priorité** : Critique · **Pré-recette** : Manuel
- **Préconditions** : Serveur de production
- **Étapes** :
  1. Ouvrir le site en https:// et tenter d'ouvrir http://
- **Résultat attendu** : Le site est servi en HTTPS ; la caméra du scanner fonctionne sur téléphone.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INS-10 · Permissions des dossiers

- **Compte** : — · **Priorité** : Majeur · **Pré-recette** : Manuel
- **Préconditions** : Serveur
- **Étapes** :
  1. Vérifier que storage/ et bootstrap/cache/ sont modifiables par le serveur web
- **Résultat attendu** : Les journaux s'écrivent ; aucune erreur de permission.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

### INS-11 · Configuration de production

- **Compte** : — · **Priorité** : Critique · **Pré-recette** : Manuel
- **Préconditions** : Serveur
- **Étapes** :
  1. Lire le fichier .env
- **Résultat attendu** : APP_ENV=production, APP_DEBUG=false, APP_URL définitif, accès base corrects, coordonnées de contact renseignées.
- **Résultat** : ☐ OK ☐ KO ☐ Bloqué ☐ N/A — Date : ____ Testeur : ________

## Procès-verbal de recette

| | |
|---|---|
| Application | SYGEP |
| Version / commit | |
| Date de recette | |
| Cas exécutés / OK / KO | |
| Anomalies bloquantes ouvertes | |
| Décision | ☐ Acceptée ☐ Acceptée avec réserves ☐ Refusée |
| Réserves | |

Signatures : Maîtrise d'ouvrage ________  Maîtrise d'œuvre ________
