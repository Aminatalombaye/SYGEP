<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Traduit les statuts de matière en français (et corrige « Assigner » en « Affecté »).
 * Les matières gardent leur statut : elles pointent vers l'identifiant, pas vers le nom.
 */
return new class extends Migration
{
    private array $names = [
        'Assigner'       => 'Affecté',
        'Assigned'       => 'Affecté',
        'Available'      => 'Disponible',
        'Broken'         => 'En panne',
        'Not Available'  => 'Pas disponible',
        'Out for Repair' => 'En réparation',
    ];

    public function up(): void
    {
        foreach ($this->names as $old => $new) {
            DB::table('asset_statuses')->where('name', $old)->update(['name' => $new, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        $back = ['Affecté' => 'Assigned', 'Disponible' => 'Available', 'En panne' => 'Broken', 'Pas disponible' => 'Not Available', 'En réparation' => 'Out for Repair'];

        foreach ($back as $new => $old) {
            DB::table('asset_statuses')->where('name', $new)->update(['name' => $old, 'updated_at' => now()]);
        }
    }
};
