<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bons d'entrée et de sortie du stock : un bon regroupe plusieurs articles
 * et génère un mouvement de stock par ligne.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stock_vouchers')) {
            Schema::create('stock_vouchers', function (Blueprint $table) {
                $table->id();
                $table->string('reference')->unique();
                $table->string('type', 10);
                $table->date('moved_at');
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
                $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
                $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
                $table->string('document')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('stock_movements', 'voucher_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->foreignId('voucher_id')->nullable()->after('stock_item_id')->constrained('stock_vouchers')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('stock_movements', 'voucher_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropConstrainedForeignId('voucher_id');
            });
        }

        Schema::dropIfExists('stock_vouchers');
    }
};
