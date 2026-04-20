<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_two_ct_questions', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis_ct', ['Decomposition', 'Pattern Recognition', 'Abstraction', 'Algorithmic Thinking']);
            $table->text('narasi_soal');
            $table->enum('level_kesulitan', ['easy', 'medium', 'hard'])->default('medium');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['jenis_ct', 'level_kesulitan']);
        });

        Schema::create('batch_two_ct_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('soal_id')->constrained('batch_two_ct_questions')->onDelete('cascade');
            $table->string('label_opsi', 5);
            $table->text('teks_opsi');
            $table->unsignedTinyInteger('bobot_web')->default(0);
            $table->unsignedTinyInteger('bobot_marketing')->default(0);
            $table->unsignedTinyInteger('bobot_admin')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['soal_id', 'label_opsi']);
            $table->index(['soal_id', 'is_active']);
        });

        Schema::create('batch_two_ct_student_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('students')->onDelete('cascade');
            $table->unsignedInteger('attempt_no')->default(1);
            $table->unsignedInteger('total_web')->default(0);
            $table->unsignedInteger('total_marketing')->default(0);
            $table->unsignedInteger('total_admin')->default(0);
            $table->decimal('persen_web', 5, 2)->default(0);
            $table->decimal('persen_marketing', 5, 2)->default(0);
            $table->decimal('persen_admin', 5, 2)->default(0);
            $table->string('rekomendasi', 120);
            $table->json('jawaban_json')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['siswa_id', 'attempt_no']);
            $table->index(['siswa_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_two_ct_student_results');
        Schema::dropIfExists('batch_two_ct_question_options');
        Schema::dropIfExists('batch_two_ct_questions');
    }
};
