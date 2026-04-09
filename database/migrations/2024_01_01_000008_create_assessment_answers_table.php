<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('assessment_submissions')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('assessment_questions')->onDelete('cascade');
            $table->string('answer', 10);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_answers');
    }
};
