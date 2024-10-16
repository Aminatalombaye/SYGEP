<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetTaskPivotTable extends Migration
{
    public function up()
    {
        Schema::create('asset_task', function (Blueprint $table) {
            $table->unsignedBigInteger('task_id');
            $table->foreign('task_id', 'task_id_fk_9996163')->references('id')->on('tasks')->onDelete('cascade');
            $table->unsignedBigInteger('asset_id');
            $table->foreign('asset_id', 'asset_id_fk_9996163')->references('id')->on('assets')->onDelete('cascade');
        });
    }
}
