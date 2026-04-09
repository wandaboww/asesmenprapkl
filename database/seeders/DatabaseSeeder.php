<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Admin Default
        DB::table('admins')->insert([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'full_name' => 'Administrator',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Kategori Kompetensi
        $categories = [
            ['category_name' => 'Pemrograman Website', 'description' => 'Skill teknis seperti HTML, CSS, JavaScript, database', 'icon' => '💻'],
            ['category_name' => 'Administrasi Perkantoran', 'description' => 'Kemampuan perkantoran, manajemen data, surat menyurat', 'icon' => '📋'],
            ['category_name' => 'Digital Marketing', 'description' => 'Desain grafis, media sosial, iklan digital, live host', 'icon' => '📱'],
        ];

        foreach ($categories as $i => $cat) {
            DB::table('competency_categories')->insert(array_merge($cat, ['created_at' => $now, 'updated_at' => $now]));
        }

        // Kelas
        $classes = ['11 PPLG 1', '11 PPLG 2', '11 PPLG 3', 'XII RPL A', 'XII RPL B', 'XII BDP A', 'XII MM A'];
        foreach ($classes as $c) {
            DB::table('classes')->insert(['class_name' => $c, 'created_at' => $now, 'updated_at' => $now]);
        }

        // Industri
        $industries = [
            ['industry_name' => 'Software House & Digital Agency', 'description' => 'Perusahaan yang mengembangkan aplikasi/website sekaligus mengelola pemasaran digital', 'primary_competency' => 'programming', 'secondary_competency' => 'marketing'],
            ['industry_name' => 'Tech Startup / SaaS Company', 'description' => 'Startup teknologi yang butuh development dan administrasi sistem', 'primary_competency' => 'programming', 'secondary_competency' => 'administration'],
            ['industry_name' => 'Creative Digital Agency', 'description' => 'Agency kreatif yang fokus konten digital dengan dukungan web development', 'primary_competency' => 'marketing', 'secondary_competency' => 'programming'],
            ['industry_name' => 'Media & Broadcasting Company', 'description' => 'Perusahaan media yang mengelola konten kreatif dan administrasi konten', 'primary_competency' => 'marketing', 'secondary_competency' => 'administration'],
            ['industry_name' => 'Pemerintahan / E-Government', 'description' => 'Instansi pemerintah yang mengelola sistem digital dan administrasi', 'primary_competency' => 'administration', 'secondary_competency' => 'programming'],
            ['industry_name' => 'Corporate / BUMN', 'description' => 'Perusahaan besar dengan fokus administrasi korporat dan branding', 'primary_competency' => 'administration', 'secondary_competency' => 'marketing']
        ];
        foreach ($industries as $i) {
            DB::table('industries')->insert(array_merge($i, ['created_at' => $now, 'updated_at' => $now]));
        }

        // Sample Siswa (Mapping ke kelas berdasarkan data di array - asumsi id sama dengan array 0-index)
        $students = [
            ['class_id' => 1, 'full_name' => 'Rahmat Gunawan', 'student_number' => '001'],
            ['class_id' => 1, 'full_name' => 'Siti Nur Azizah', 'student_number' => '002'],
            ['class_id' => 1, 'full_name' => 'Budi Santoso', 'student_number' => '003'],
            ['class_id' => 2, 'full_name' => 'Eka Dewi Sari', 'student_number' => '004'],
            ['class_id' => 2, 'full_name' => 'Roni Hermawan', 'student_number' => '005'],
            ['class_id' => 3, 'full_name' => 'Dwi Cahya Putri', 'student_number' => '006'],
        ];
        foreach ($students as $s) {
            DB::table('students')->insert(array_merge($s, ['created_at' => $now, 'updated_at' => $now]));
        }

        // Pertanyaan
        $q_web = [
            'Apakah Anda tertarik dengan teknologi HTML, CSS, dan JavaScript?',
            'Apakah Anda menyukai mencocokkan logic dan problem solving dalam programming?',
            'Apakah Anda tertarik mendesain dan membuat website yang menarik?',
            'Apakah Anda bersedia mempelajari database dan backend programming?',
            'Apakah Anda tertarik mempelajari framework seperti Laravel, React, atau Vue.js?',
            'Apakah Anda suka mengoptimalkan performa website dan kecepatan loading?',
            'Apakah Anda mampu memahami dan menggunakan API dalam pembuatan website?',
            'Apakah Anda tertarik dengan responsive design dan mobile-first approach?',
            'Apakah Anda bersedia bekerja dengan version control seperti Git?',
            'Apakah Anda tertarik dengan debugging dan testing kode secara menyeluruh?'
        ];
        
        $q_admin = [
            'Apakah Anda terbiasa menangani pekerjaan administratif dan dokumentasi?',
            'Apakah Anda rapi dan teliti dalam pekerjaan?',
            'Apakah Anda menyukai bekerja dengan data dan laporan perusahaan?',
            'Apakah Anda mampu berkomunikasi dengan baik di depan pimpinan?',
            'Apakah Anda terbiasa menggunakan Microsoft Office (Word, Excel, PowerPoint)?',
            'Apakah Anda cepat dalam mengetik dan entry data?',
            'Apakah Anda suka mengorganisir file dan sistem dokumentasi?',
            'Apakah Anda mampu menangani telepon dan tamu dengan profesional?',
            'Apakah Anda terbiasa membuat surat menyurat bisnis yang formal?',
            'Apakah Anda mampu mengelola agenda dan jadwal meeting pimpinan?'
        ];
        
        $q_mkt = [
            'Apakah Anda tertarik dengan dunia digital marketing dan media sosial?',
            'Apakah Anda kreatif dalam membuat konten visual dan copywriting?',
            'Apakah Anda aktif di berbagai platform media sosial?',
            'Apakah Anda mampu berkomunikasi dengan audiens secara engaging?',
            'Apakah Anda tertarik dengan fotografi dan videografi untuk konten?',
            'Apakah Anda mampu menggunakan Photoshop atau Canva untuk desain?',
            'Apakah Anda mengerti tentang analytics dan metrics performa konten?',
            'Apakah Anda tertarik dengan SEO dan optimasi mesin pencari?',
            'Apakah Anda paham tentang advertising dan sponsored content?',
            'Apakah Anda suka brainstorming ide kampanye kreatif?'
        ];

        $order = 1;
        foreach ($q_web as $q) {
            DB::table('assessment_questions')->insert(['category_id' => 1, 'question_text' => $q, 'question_order' => $order++, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }
        $order = 1;
        foreach ($q_admin as $q) {
            DB::table('assessment_questions')->insert(['category_id' => 2, 'question_text' => $q, 'question_order' => $order++, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }
        $order = 1;
        foreach ($q_mkt as $q) {
            DB::table('assessment_questions')->insert(['category_id' => 3, 'question_text' => $q, 'question_order' => $order++, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }
    }
}
