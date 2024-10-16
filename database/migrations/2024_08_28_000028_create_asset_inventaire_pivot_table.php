<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetInventairePivotTable extends Migration
{
    public function up()
    {
        Schema::create('asset_inventaire', function (Blueprint $table) {
            $table->unsignedBigInteger('asset_id');
            $table->foreign('asset_id', 'asset_id_fk_10081118')->references('id')->on('assets')->onDelete('cascade');
            $table->unsignedBigInteger('inventaire_id');
            $table->foreign('inventaire_id', 'inventaire_id_fk_10081118')->references('id')->on('inventaires')->onDelete('cascade');
        });
    }
}
