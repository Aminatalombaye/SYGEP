<?php

use App\Support\RoleProfiles;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rattachement des comptes à un service (périmètre local), nouveaux droits
 * (approbation du Directeur, clôture d'inventaire) et réinitialisation des
 * rôles selon les profils métier, dont le Super administrateur.
 */
return new class extends Migration
{
    private array $permissions = ['perimetre_service', 'maintenance_request_approve', 'inventaire_close'];

    public function up(): void
    {
        if (! Schema::hasColumn('users', 'service_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('service_id')->nullable()->after('email')->constrained('services')->nullOnDelete();
            });
        }

        Schema::table('maintenance_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('maintenance_requests', 'approved_by_id')) {
                $table->foreignId('approved_by_id')->nullable()->after('decision_notes')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('maintenance_requests', 'approved_at')) {
                $table->dateTime('approved_at')->nullable()->after('approved_by_id');
            }
            if (! Schema::hasColumn('maintenance_requests', 'approval_notes')) {
                $table->text('approval_notes')->nullable()->after('approved_at');
            }
        });

        foreach ($this->permissions as $title) {
            if (! DB::table('permissions')->where('title', $title)->exists()) {
                DB::table('permissions')->insert(['title' => $title, 'created_at' => now(), 'updated_at' => now()]);
            }
        }

        RoleProfiles::apply(reset: true);
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'service_id')) {
            Schema::table('users', fn (Blueprint $table) => $table->dropConstrainedForeignId('service_id'));
        }
        if (Schema::hasColumn('maintenance_requests', 'approved_by_id')) {
            Schema::table('maintenance_requests', function (Blueprint $table) {
                $table->dropConstrainedForeignId('approved_by_id');
                $table->dropColumn(['approved_at', 'approval_notes']);
            });
        }

        $ids = DB::table('permissions')->whereIn('title', $this->permissions)->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
