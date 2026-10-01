<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('agents')) {
            Schema::create('agents', function (Blueprint $table) {
                $table->id();
                $table->string('nom')->nullable();
                $table->string('prenom')->nullable();
                $table->string('adresse')->nullable();
                $table->string('email')->nullable();
                $table->string('telephone')->nullable();
                $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });

            return;
        }

        Schema::table('agents', function (Blueprint $table) {
            if (Schema::hasColumn('agents', 'e_mail') && ! Schema::hasColumn('agents', 'email')) {
                $table->renameColumn('e_mail', 'email');
            }

            foreach (['nom', 'prenom', 'adresse', 'telephone'] as $column) {
                if (! Schema::hasColumn('agents', $column)) {
                    $table->string($column)->nullable();
                }
            }

            if (! Schema::hasColumn('agents', 'email') && ! Schema::hasColumn('agents', 'e_mail')) {
                $table->string('email')->nullable();
            }

            if (! Schema::hasColumn('agents', 'service_id')) {
                $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('agents', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
