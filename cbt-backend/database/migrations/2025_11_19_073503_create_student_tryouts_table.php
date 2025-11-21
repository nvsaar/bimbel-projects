<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_tryouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tryout_id')->constrained()->onDelete('cascade');
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->enum('status', ['not_started', 'in_progress', 'submitted', 'expired'])
                ->default('not_started');
            $table->decimal('total_score', 8, 2)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'tryout_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_tryouts');
    }
};