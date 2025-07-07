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
        Schema::create('leave_request_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('bg_color');
            $table->string('text_color');
            $table->boolean('is_productive')->default(0);
            $table->double('cost_multiply')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_request_types');
    }
};
