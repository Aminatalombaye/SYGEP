<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetAssignmentPivotTable extends Migration
{
    public function up()
    {
        Schema::create('asset_assignment', function (Blueprint $table) {
            $table->unsignedBigInteger('assignment_id');
            $table->foreign('assignment_id', 'assignment_id_fk_10016937')->references('id')->on('assignments')->onDelete('cascade');
            $table->unsignedBigInteger('asset_id');
            $table->foreign('asset_id', 'asset_id_fk_10016937')->references('id')->on('assets')->onDelete('cascade');
        });
    }
}
