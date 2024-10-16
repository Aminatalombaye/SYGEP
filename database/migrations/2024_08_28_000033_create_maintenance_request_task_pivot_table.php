<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaintenanceRequestTaskPivotTable extends Migration
{
    public function up()
    {
        Schema::create('maintenance_request_task', function (Blueprint $table) {
            $table->unsignedBigInteger('maintenance_request_id');
            $table->foreign('maintenance_request_id', 'maintenance_request_id_fk_9996164')->references('id')->on('maintenance_requests')->onDelete('cascade');
            $table->unsignedBigInteger('task_id');
            $table->foreign('task_id', 'task_id_fk_9996164')->references('id')->on('tasks')->onDelete('cascade');
        });
    }
}
