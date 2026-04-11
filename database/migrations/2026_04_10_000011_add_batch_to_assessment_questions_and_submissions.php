<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('assessment_questions', 'batch_id')) {
            Schema::table('assessment_questions', function (Blueprint $table) {
                $table->unsignedBigInteger('batch_id')->nullable()->after('id');
            });
        }

        if (!$this->foreignKeyExists('assessment_questions', 'assessment_questions_batch_id_foreign')) {
            Schema::table('assessment_questions', function (Blueprint $table) {
                $table->foreign('batch_id')->references('id')->on('assessment_batches')->onDelete('cascade');
            });
        }

        if (!Schema::hasColumn('assessment_submissions', 'batch_id')) {
            Schema::table('assessment_submissions', function (Blueprint $table) {
                $table->unsignedBigInteger('batch_id')->nullable()->after('id');
            });
        }

        if (!$this->foreignKeyExists('assessment_submissions', 'assessment_submissions_batch_id_foreign')) {
            Schema::table('assessment_submissions', function (Blueprint $table) {
                $table->foreign('batch_id')->references('id')->on('assessment_batches')->onDelete('cascade');
            });
        }

        $batchId = DB::table('assessment_batches')->where('batch_name', 'Batch 1')->value('id');
        if (!$batchId) {
            $batchId = DB::table('assessment_batches')->insertGetId([
                'batch_name' => 'Batch 1',
                'description' => 'Batch awal asesmen.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('assessment_questions')->whereNull('batch_id')->update(['batch_id' => $batchId]);
        DB::table('assessment_submissions')->whereNull('batch_id')->update(['batch_id' => $batchId]);

        if (!$this->indexExists('assessment_submissions', 'assessment_submissions_student_id_batch_id_unique')) {
            Schema::table('assessment_submissions', function (Blueprint $table) {
                $table->unique(['student_id', 'batch_id']);
            });
        }

        if ($this->indexExists('assessment_submissions', 'assessment_submissions_student_id_unique')) {
            Schema::table('assessment_submissions', function (Blueprint $table) {
                $table->dropUnique('assessment_submissions_student_id_unique');
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('assessment_submissions', 'assessment_submissions_student_id_batch_id_unique')) {
            Schema::table('assessment_submissions', function (Blueprint $table) {
                $table->dropUnique('assessment_submissions_student_id_batch_id_unique');
            });
        }

        if (!$this->indexExists('assessment_submissions', 'assessment_submissions_student_id_unique')) {
            Schema::table('assessment_submissions', function (Blueprint $table) {
                $table->unique('student_id');
            });
        }

        if ($this->foreignKeyExists('assessment_submissions', 'assessment_submissions_batch_id_foreign')) {
            Schema::table('assessment_submissions', function (Blueprint $table) {
                $table->dropForeign(['batch_id']);
            });
        }

        if (Schema::hasColumn('assessment_submissions', 'batch_id')) {
            Schema::table('assessment_submissions', function (Blueprint $table) {
                $table->dropColumn('batch_id');
            });
        }

        if ($this->foreignKeyExists('assessment_questions', 'assessment_questions_batch_id_foreign')) {
            Schema::table('assessment_questions', function (Blueprint $table) {
                $table->dropForeign(['batch_id']);
            });
        }

        if (Schema::hasColumn('assessment_questions', 'batch_id')) {
            Schema::table('assessment_questions', function (Blueprint $table) {
                $table->dropColumn('batch_id');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$table}')");

            foreach ($indexes as $index) {
                $name = $index->name ?? null;
                if ($name === $indexName) {
                    return true;
                }
            }

            return false;
        }

        return count(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName])) > 0;
    }

    private function foreignKeyExists(string $table, string $foreignKeyName): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            $foreignKeys = DB::select("PRAGMA foreign_key_list('{$table}')");

            foreach ($foreignKeys as $foreignKey) {
                $column = strtolower((string) ($foreignKey->from ?? ''));
                $referencedTable = strtolower((string) ($foreignKey->table ?? ''));

                if ($column === 'batch_id' && $referencedTable === 'assessment_batches') {
                    return true;
                }
            }

            return false;
        }

        return count(DB::select(
            'SELECT 1 FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND table_name = ? AND constraint_name = ? AND constraint_type = ? LIMIT 1',
            [$table, $foreignKeyName, 'FOREIGN KEY']
        )) > 0;
    }
};
