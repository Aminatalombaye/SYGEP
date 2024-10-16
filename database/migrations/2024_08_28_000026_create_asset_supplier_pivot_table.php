<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetSupplierPivotTable extends Migration
{
    public function up()
    {
        Schema::create('asset_supplier', function (Blueprint $table) {
            $table->unsignedBigInteger('asset_id');
            $table->foreign('asset_id', 'asset_id_fk_10016534')->references('id')->on('assets')->onDelete('cascade');
            $table->unsignedBigInteger('supplier_id');
            $table->foreign('supplier_id', 'supplier_id_fk_10016534')->references('id')->on('suppliers')->onDelete('cascade');
        });
    }
}
