<?php

namespace Database\Seeders;

use App\Models\AssetStatus;
use Illuminate\Database\Seeder;

class AssetStatusTableSeeder extends Seeder
{
    /** Nom français => noms équivalents d'une ancienne installation. */
    private const STATUSES = [
        'Disponible'     => ['Available'],
        'Pas disponible' => ['Not Available'],
        'En panne'       => ['Broken'],
        'En réparation'  => ['Out for Repair'],
        'Affecté'        => ['Assigned', 'Assigner'],
    ];

    public function run()
    {
        foreach (self::STATUSES as $name => $alternatives) {
            if (! AssetStatus::whereIn('name', array_merge([$name], $alternatives))->exists()) {
                AssetStatus::create(['name' => $name]);
            }
        }
    }
}
