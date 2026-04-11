<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_name', 100)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        DB::table('assessment_batches')->insert([
            'batch_name' => 'Batch 1',
            'description' => 'Batch awal asesmen.',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_batches');
    }
};
