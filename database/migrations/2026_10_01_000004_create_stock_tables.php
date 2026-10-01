<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Matières consommables (premier groupe) : articles en stock et mouvements
 * d'entrée, de sortie et d'ajustement après comptage.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stock_items')) {
            Schema::create('stock_items', function (Blueprint $table) {
                $table->id();
                $table->string('reference', 30)->unique();
                $table->string('name');
                $table->string('category', 40)->nullable()->index();
                $table->string('unit', 20)->default('unité');
                $table->decimal('quantity', 12, 2)->default(0);
                $table->decimal('min_quantity', 12, 2)->default(0);
                $table->decimal('unit_price', 15, 2)->nullable();
                $table->boolean('perishable')->default(false);
                $table->foreignId('location_id')->nullable()->constrained('asset_locations')->nullOnDelete();
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table) {
                $table->id();
                $table->string('reference', 30)->unique();
                $table->foreignId('stock_item_id')->constrained('stock_items')->cascadeOnDelete();
                $table->string('type', 12)->index();
                $table->decimal('quantity', 12, 2);
                $table->decimal('balance_after', 12, 2);
                $table->date('moved_at')->index();
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
                $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
                $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
                $table->string('document')->nullable();
                $table->decimal('unit_price', 15, 2)->nullable();
                $table->date('expires_at')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        $titles = [
            'stock_management_access',
            'stock_item_access', 'stock_item_create', 'stock_item_edit', 'stock_item_show', 'stock_item_delete',
            'stock_movement_access', 'stock_movement_create', 'stock_movement_adjust',
        ];

        $admin = DB::table('roles')->whereRaw('LOWER(title) = ?', ['admin'])->value('id') ?? 1;

        foreach ($titles as $title) {
            $id = DB::table('permissions')->where('title', $title)->value('id')
                ?? DB::table('permissions')->insertGetId(['title' => $title, 'created_at' => now(), 'updated_at' => now()]);

            if (DB::table('roles')->where('id', $admin)->exists()
                && ! DB::table('permission_role')->where(['role_id' => $admin, 'permission_id' => $id])->exists()) {
                DB::table('permission_role')->insert(['role_id' => $admin, 'permission_id' => $id]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_items');

        $ids = DB::table('permissions')->where('title', 'like', 'stock\_%')->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
