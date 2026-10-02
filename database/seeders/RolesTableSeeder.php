<?php

namespace Database\Seeders;

use App\Support\RoleProfiles;
use Illuminate\Database\Seeder;

/**
 * Crée les rôles métier (Super administrateur, Directeur, comptables, etc.) avec leurs droits.
 */
class RolesTableSeeder extends Seeder
{
    public function run()
    {
        RoleProfiles::apply();
    }
}
