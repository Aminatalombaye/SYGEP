<?php

use App\Support\RoleProfiles;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Droits des nouveaux modules (intervenants, rapports périodiques) puis
 * application des profils métier : Directeur, comptables, administrateur des
 * matières, responsables maintenance et infrastructures.
 */
return new class extends Migration
{
    private array $permissions = [
        'intervenant_access', 'intervenant_create', 'intervenant_edit', 'intervenant_show', 'intervenant_delete',
        'periodic_report_access',
    ];

    public function up(): void
    {
        foreach ($this->permissions as $title) {
            if (! DB::table('permissions')->where('title', $title)->exists()) {
                DB::table('permissions')->insert(['title' => $title, 'created_at' => now(), 'updated_at' => now()]);
            }
        }

        RoleProfiles::apply();
    }

    public function down(): void
    {
        $ids = DB::table('permissions')->whereIn('title', $this->permissions)->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
