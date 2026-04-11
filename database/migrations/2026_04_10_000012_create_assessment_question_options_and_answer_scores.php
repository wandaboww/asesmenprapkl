<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('assessment_questions')->onDelete('cascade');
            $table->string('option_text', 150);
            $table->decimal('option_score', 8, 2)->default(0);
            $table->integer('option_order')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['question_id', 'option_order']);
        });

        Schema::table('assessment_answers', function (Blueprint $table) {
            $table->unsignedBigInteger('question_option_id')->nullable()->after('question_id');
            $table->decimal('score_value', 8, 2)->default(0)->after('answer');
            $table->decimal('max_score_value', 8, 2)->default(1)->after('score_value');
            $table->foreign('question_option_id')->references('id')->on('assessment_question_options')->nullOnDelete();
        });

        $questionIds = DB::table('assessment_questions')->pluck('id');
        foreach ($questionIds as $questionId) {
            $exists = DB::table('assessment_question_options')->where('question_id', $questionId)->exists();
            if ($exists) {
                continue;
            }

            DB::table('assessment_question_options')->insert([
                [
                    'question_id' => $questionId,
                    'option_text' => 'Ya',
                    'option_score' => 1,
                    'option_order' => 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'question_id' => $questionId,
                    'option_text' => 'Tidak',
                    'option_score' => 0,
                    'option_order' => 2,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        DB::table('assessment_answers')->update(['score_value' => 0, 'max_score_value' => 1]);
        DB::table('assessment_answers')
            ->whereRaw("LOWER(answer) IN ('ya', 'yes')")
            ->update(['score_value' => 1]);
    }

    public function down(): void
    {
        Schema::table('assessment_answers', function (Blueprint $table) {
            $table->dropForeign(['question_option_id']);
            $table->dropColumn(['question_option_id', 'score_value', 'max_score_value']);
        });

        Schema::dropIfExists('assessment_question_options');
    }
};
