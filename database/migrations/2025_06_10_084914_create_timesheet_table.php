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
        Schema::create('timesheet', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('start_date')->nullable();
            $table->integer('status')->nullable();
            $table->integer('approver_id')->nullable();
            $table->datetime('submitted_at')->nullable();
            $table->double('work_hours')->nullable();
            $table->longText('comments')->nullable();
            $table->longText('reject_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheet');
    }
};
