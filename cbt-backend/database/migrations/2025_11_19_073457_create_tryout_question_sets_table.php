<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tryout_question_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tryout_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->integer('order_number');
            $table->decimal('score_per_question', 5, 2)->default(4.00);
            $table->timestamps();

            $table->unique(['tryout_id', 'question_id']);
            $table->index('tryout_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tryout_question_sets');
    }
};