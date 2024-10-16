<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectReportPivotTable extends Migration
{
    public function up()
    {
        Schema::create('project_report', function (Blueprint $table) {
            $table->unsignedBigInteger('report_id');
            $table->foreign('report_id', 'report_id_fk_9996239')->references('id')->on('reports')->onDelete('cascade');
            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id', 'project_id_fk_9996239')->references('id')->on('projects')->onDelete('cascade');
        });
    }
}
