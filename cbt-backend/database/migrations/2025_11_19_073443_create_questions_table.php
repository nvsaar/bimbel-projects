<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->text('question_text');
            $table->text('option_a');
            $table->text('option_b');
            $table->text('option_c');
            $table->text('option_d');
            $table->text('option_e')->nullable();
            $table->enum('correct_option', ['A', 'B', 'C', 'D', 'E']);
            $table->text('explanation')->nullable();
            $table->enum('difficulty_level', ['mudah', 'sedang', 'sulit'])->default('sedang');
            $table->timestamps();

            $table->index('subject_id');
            $table->index('difficulty_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};