# SYGEP

Système de Gestion du Patrimoine Matériel du Ministère de l'Emploi et de la
Formation Professionnelle et Technique (MEFPT).

## Fonctionnalités

- Inventaire des matières, catégories, emplacements et statuts
- Affectations aux agents et aux services, historique des mouvements
- Infrastructures, projets, rapports et chefs de projet
- Tâches et demandes de maintenance
- Tableau de bord avec indicateurs et graphiques
- Gestion des utilisateurs, rôles et permissions
- Page de contact publique et consultation des messages dans l'espace admin

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

Laravel 13, Blade, CoreUI 3, DataTables, Chart.js, Spatie Media Library.
