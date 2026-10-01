<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Une organisation peut recevoir plusieurs bons : l'unicité porte sur la
 * référence de commande (contrôlée à la saisie), plus sur l'organisation.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bons', function (Blueprint $table) {
            $table->dropUnique('bons_organisation_unique');
        });
    }

    public function down(): void
    {
        Schema::table('bons', function (Blueprint $table) {
            $table->unique('organisation');
        });
    }
};
