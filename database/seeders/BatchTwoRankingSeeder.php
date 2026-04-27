<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BatchTwoRankingSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        
        // Ensure some students exist if not already there
        $studentNames = [
            'Rahmat Gunawan', 'Siti Nur Azizah', 'Budi Santoso', 'Eka Dewi Sari', 
            'Roni Hermawan', 'Dwi Cahya Putri', 'Agus Prayogo', 'Lestari Wahyuni',
            'Fajar Ramadhan', 'Indah Permata', 'Bambang Kusuma', 'Dewi Lestari',
            'Andi Wijaya', 'Siska Amelia', 'Hendra Saputra', 'Maya Kartika',
            'Rizky Pratama', 'Yulia Fitri', 'Taufik Hidayat', 'Anita Sari'
        ];

        $rekomendasiOptions = ['Pemrograman', 'Digital Marketing', 'Administrasi'];

        foreach ($studentNames as $index => $name) {
            // Find or create student
            $student = DB::table('students')->where('full_name', $name)->first();
            if (!$student) {
                $studentId = DB::table('students')->insertGetId([
                    'class_id' => ($index % 3) + 1,
                    'full_name' => $name,
                    'student_number' => str_pad($index + 10, 3, '0', STR_PAD_LEFT),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                $studentId = $student->id;
            }

            // Generate random scores
            $web = rand(10, 95);
            $marketing = rand(10, 95);
            $admin = rand(10, 95);
            
            // Determine recommendation based on highest score
            $scores = [
                'Pemrograman' => $web,
                'Digital Marketing' => $marketing,
                'Administrasi' => $admin
            ];
            arsort($scores);
            $rekomendasi = key($scores);

            // Clean existing results for this student to avoid unique constraint issues
            DB::table('batch_two_ct_student_results')->where('siswa_id', $studentId)->delete();

            // Insert result
            DB::table('batch_two_ct_student_results')->insert([
                'siswa_id' => $studentId,
                'attempt_no' => 1,
                'total_web' => $web,
                'total_marketing' => $marketing,
                'total_admin' => $admin,
                'persen_web' => ($web / 100) * 100,
                'persen_marketing' => ($marketing / 100) * 100,
                'persen_admin' => ($admin / 100) * 100,
                'rekomendasi' => $rekomendasi,
                'jawaban_json' => json_encode([]),
                'submitted_at' => $now->copy()->subMinutes(rand(1, 1000)),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
