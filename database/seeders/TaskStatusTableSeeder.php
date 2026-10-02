<?php

namespace Database\Seeders;

use App\Models\TaskStatus;
use Illuminate\Database\Seeder;

class TaskStatusTableSeeder extends Seeder
{
    /** Nom français => nom équivalent d'une ancienne installation. */
    private const STATUSES = [
        'Ouverte'  => 'Open',
        'En cours' => 'In progress',
        'Terminée' => 'Closed',
    ];

    public function run()
    {
        foreach (self::STATUSES as $name => $legacy) {
            if (! TaskStatus::whereIn('name', [$name, $legacy])->exists()) {
                TaskStatus::create(['name' => $name]);
            }
        }
    }
}
