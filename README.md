# SYGEP

Système de Gestion du Patrimoine Matériel du Ministère de l'Emploi et de la
Formation Professionnelle et Technique (MEFPT).

## Fonctionnalités

- **Matières** : enregistrement, catégories, emplacements, statuts, étiquettes QR et scan depuis un téléphone
- **Affectations** : bons d'affectation imprimables, restitutions et transferts entre agents et services
- **Inventaires** : campagnes de contrôle par scan et procès-verbal
- **Stock des consommables** : articles, entrées, sorties, ajustements, seuils d'alerte et péremption
- **Infrastructures** : structures, bâtiments et blocs, état constaté, plan d'amortissement
- **Projets** : jalons, avancement, budget et intervenants
- **Maintenance** : demandes avec avis technique et approbation du Directeur, planification et maintenance préventive
- **Rapports périodiques** imprimables pour la direction
- **Tableaux de bord** par module et notifications internes
- **Utilisateurs et rôles** : profils métier et périmètre limité au service pour les comptes locaux

## Prérequis

- PHP 8.3 ou plus
- Composer 2
- MySQL / MariaDB
- Node.js 20 ou plus

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
# renseigner DB_* et CONTACT_* dans .env
php artisan migrate --seed
php artisan sygep:profils --reinitialiser
php artisan storage:link
npm install
```

## Configuration

Les coordonnées affichées sur la page Contact se règlent dans `.env` :

```
CONTACT_ADRESSE=
CONTACT_TELEPHONE=
CONTACT_EMAIL=
CONTACT_HORAIRES=
```

## Stack

Laravel 13, Blade, CoreUI 3, DataTables, Chart.js, html5-qrcode, Spatie Media Library.
