<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetsTable extends Migration
{
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('serial_number')->nullable();
            $table->string('name')->nullable();
            $table->longText('notes')->nullable();
            $table->string('type')->nullable();
            $table->date('date_achat')->nullable();
            $table->date('date_mise_en_service')->nullable();
            $table->string('modele')->nullable();
            $table->string('assigned_to')->nullable();
            $table->string('qr_code')->nullable(); // QR code column
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('assets');
    }
}
 