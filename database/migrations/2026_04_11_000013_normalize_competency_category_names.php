<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('competency_categories')
            ->whereRaw("LOWER(category_name) LIKE 'administrasi%'")
            ->update([
                'category_name' => 'Administrasi',
                'updated_at' => now(),
            ]);

        DB::table('competency_categories')
            ->whereRaw("LOWER(category_name) LIKE 'digital marketing%' OR LOWER(category_name) = 'marketing'")
            ->update([
                'category_name' => 'Digital Marketing',
                'updated_at' => now(),
            ]);

        DB::table('competency_categories')
            ->whereRaw("LOWER(category_name) LIKE 'pemrograman%' OR LOWER(category_name) = 'programming'")
            ->update([
                'category_name' => 'Pemrograman',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('competency_categories')
            ->where('category_name', 'Administrasi')
            ->update([
                'category_name' => 'Administrasi Perkantoran',
                'updated_at' => now(),
            ]);

        DB::table('competency_categories')
            ->where('category_name', 'Pemrograman')
            ->update([
                'category_name' => 'Pemrograman Website',
                'updated_at' => now(),
            ]);
    }
};
