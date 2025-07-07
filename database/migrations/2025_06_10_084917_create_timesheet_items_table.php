<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('timesheet_items', function (Blueprint $table) {
            $table->id();
            $table->integer('report_id')->nullable();
            $table->integer('activity_type')->nullable();
            $table->string('project_code')->nullable();            
            $table->string('comment')->nullable();
            $table->string('day_1')->nullable();
            $table->double('hour_1')->nullable();
            $table->string('day_2')->nullable();
            $table->double('hour_2')->nullable();
            $table->string('day_3')->nullable();
            $table->double('hour_3')->nullable();
            $table->string('day_4')->nullable();
            $table->double('hour_4')->nullable();
            $table->string('day_5')->nullable();
            $table->double('hour_5')->nullable();
            $table->string('day_6')->nullable();
            $table->double('hour_6')->nullable();
            $table->string('day_7')->nullable();
            $table->double('hour_7')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheet_items');
    }
};
