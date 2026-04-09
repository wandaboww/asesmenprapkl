<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->timestamp('submitted_at')->useCurrent();
            $table->unique('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_submissions');
    }
};
