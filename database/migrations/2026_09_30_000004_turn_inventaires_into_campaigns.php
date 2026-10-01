<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventaires', function (Blueprint $table) {
            if (! Schema::hasColumn('inventaires', 'status')) {
                $table->string('status', 20)->default('brouillon')->index();
            }
            if (! Schema::hasColumn('inventaires', 'starts_at')) {
                $table->date('starts_at')->nullable();
            }
            if (! Schema::hasColumn('inventaires', 'ends_at')) {
                $table->date('ends_at')->nullable();
            }
            if (! Schema::hasColumn('inventaires', 'started_at')) {
                $table->dateTime('started_at')->nullable();
            }
            if (! Schema::hasColumn('inventaires', 'closed_at')) {
                $table->dateTime('closed_at')->nullable();
            }
            if (! Schema::hasColumn('inventaires', 'location_id')) {
                $table->foreignId('location_id')->nullable()->constrained('asset_locations')->nullOnDelete();
            }
            if (! Schema::hasColumn('inventaires', 'service_id')) {
                $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            }
            if (! Schema::hasColumn('inventaires', 'category_id')) {
                $table->foreignId('category_id')->nullable()->constrained('asset_categories')->nullOnDelete();
            }
            if (! Schema::hasColumn('inventaires', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (! Schema::hasColumn('inventaires', 'created_by_id')) {
                $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('inventaires', 'closed_by_id')) {
                $table->foreignId('closed_by_id')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('asset_inventaire', function (Blueprint $table) {
            if (! Schema::hasColumn('asset_inventaire', 'status')) {
                $table->string('status', 20)->default('attendu')->index();
            }
            if (! Schema::hasColumn('asset_inventaire', 'condition')) {
                $table->string('condition', 20)->nullable();
            }
            if (! Schema::hasColumn('asset_inventaire', 'expected_location_id')) {
                $table->unsignedBigInteger('expected_location_id')->nullable();
            }
            if (! Schema::hasColumn('asset_inventaire', 'found_location_id')) {
                $table->foreignId('found_location_id')->nullable()->constrained('asset_locations')->nullOnDelete();
            }
            if (! Schema::hasColumn('asset_inventaire', 'checked_at')) {
                $table->dateTime('checked_at')->nullable();
            }
            if (! Schema::hasColumn('asset_inventaire', 'checked_by_id')) {
                $table->foreignId('checked_by_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('asset_inventaire', 'notes')) {
                $table->string('notes')->nullable();
            }
        });

        // Les anciens rattachements sont considérés comme des contrôles effectués
        DB::table('asset_inventaire')->where('status', 'attendu')->whereNull('checked_at')->update(['status' => 'vu']);

        DB::table('inventaires')->where('status', 'brouillon')
            ->whereExists(fn ($q) => $q->from('asset_inventaire')->whereColumn('asset_inventaire.inventaire_id', 'inventaires.id'))
            ->update(['status' => 'cloture', 'closed_at' => DB::raw('updated_at')]);

        DB::table('inventaires')->whereNull('reference')->orWhere('reference', '')->update([
            'reference' => DB::raw("CONCAT('INV-', LPAD(id, 4, '0'))"),
        ]);
    }

    public function down(): void
    {
        $drop = function (string $tableName, array $foreign, array $plain) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $foreign, $plain) {
                foreach ($foreign as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropConstrainedForeignId($column);
                    }
                }
                $existing = array_values(array_filter($plain, fn ($c) => Schema::hasColumn($tableName, $c)));
                if ($existing) {
                    $table->dropColumn($existing);
                }
            });
        };

        $drop('asset_inventaire', ['found_location_id', 'checked_by_id'], ['status', 'condition', 'expected_location_id', 'checked_at', 'notes']);
        $drop('inventaires', ['location_id', 'service_id', 'category_id', 'created_by_id', 'closed_by_id'], ['status', 'starts_at', 'ends_at', 'started_at', 'closed_at', 'notes']);
    }
};
