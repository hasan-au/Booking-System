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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->time('work_start_time')->default('09:00:00')->nullable();
            $table->time('work_end_time')->default('17:00:00')->nullable();
            $table->string('photo')->nullable();
            $table->string('job_title')->nullable();
            $table->text('bio')->nullable();
            $table->double('rating')->default(4.5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
