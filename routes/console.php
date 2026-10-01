<?php

use App\Services\MaintenanceWorkflow;
use App\Support\RoleProfiles;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sygep:maintenance-preventive', function (MaintenanceWorkflow $workflow) {
    $created = $workflow->generatePreventive();
    $this->info($created ? "$created demande(s) préventive(s) créée(s)." : 'Aucun entretien à programmer.');
})->purpose('Crée les demandes de maintenance préventive arrivées à échéance');

Artisan::command('sygep:profils {--reinitialiser : Remplace les droits de chaque rôle par ceux de son profil}', function () {
    $reset = (bool) $this->option('reinitialiser');

    if ($reset && ! $this->confirm('Les droits ajustés à la main seront remplacés. Continuer ?', true)) {
        return;
    }

    foreach (RoleProfiles::apply($reset) as $role => $result) {
        $this->line(str_pad($role, 34).$result);
    }
})->purpose('Applique les profils métier (droits par acteur) aux rôles');

Schedule::command('sygep:maintenance-preventive')->dailyAt('06:30');
