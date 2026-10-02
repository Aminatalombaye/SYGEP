<?php

use App\Support\RoleProfiles;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Description des rôles : missions et périmètre, affichés dans la liste des rôles.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('roles', 'description')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->text('description')->nullable()->after('title');
            });
        }

        // Renseigne la description des rôles métier (sans toucher à leurs droits).
        RoleProfiles::apply();
    }

    public function down(): void
    {
        if (Schema::hasColumn('roles', 'description')) {
            Schema::table('roles', fn (Blueprint $table) => $table->dropColumn('description'));
        }
    }
};
