# 🎓 Aplikasi Asesmen Pra-PKL (Pemetaan Siswa)

Aplikasi berbasis **Laravel 11** ini dirancang secara khusus untuk memfasilitasi proses penyelarasan minat, bakat, dan kompetensi siswa SMK (difokuskan untuk jurusan PPLG) sebelum mereka diberangkatkan untuk Praktik Kerja Lapangan (PKL) ke Industri.

Dengan algoritma cerdas, aplikasi ini mengukur kekuatan kompetensi dominan siswa (Skor _Pemrograman Web_, _Administrasi Perkantoran_, dan _Digital Marketing_) dari 60 daftar pertanyaan Asesmen lalu memetakan siswa tersebut kepada **Rekomendasi Industri PKL** yang paling logis dan potensial.

Aplikasi telah mengadopsi standar **Ultra Mobile Responsive** lengkap dengan desain _Card_ berdimensi premium dan _Navbar Hamburger Dropdown_ agar lancar saat diakses melalui _smartphone_ siswa maupun Admin.

---

## 🚀 Fitur Utama & Fungsionalitas

### 🛡️ Portal Admin

- **Dashboard Analitik:** Menyajikan jumlah agregat total siswa terdaftar, grafik siswa yang sudah dan yang belum mengerjakan, lengkap dengan persentase _Doughnut Chart_ Ringkasan Rekomendasi Industri (menggunakan pustaka _Chart.js_).
- **Pengaturan & Kelola Siswa Terskala (_Excel Integrations_):** Memudahkan penginputan data dengan fitur unggah (_import_) dan format (_template_) berbasis `.xlsx`. Sistem ditanam dengan keamanan validasi di mana hanya kelas yang berwenang (11 PPLG 1, 11 PPLG 2, 11 PPLG 3) yang akan dimuat ke _database_.
- **Laporan & Monitor Hasil Akurat:** Admin dapat meninjau capaian presisi persentase penguasaan kompetensi dari seluruh siswa di laman "Hasil Asesmen". Fitur ini dibalut dengan pencarian (_filtering_) spesifik.
- **Fitur Ekspor Excel Laporan Otomatis:** Memudahkan rekapitulasi massal seluruh hasil siswa ke mesin _spreadsheet_.
- **Fitur Reset (Keringanan Retake):** Dengan kemampuan menghapus rekam jejak penilaian siswa sebelumnya yang gagal / keliru, siswa terkait dapat diberikan akses untuk mengerjakan ulang.

### 👨‍🎓 Portal Siswa (Peserta PKL)

- **Login Fleksibel tanpa Password:** Melalui proteksi _combo bind_ ganda, Siswa hanya diwajibkan untuk memilih Kelas yang relevan dan Memilih nama mereka sendiri melalui antarmuka _dropdown ajax_ interaktif.
- **Kuesioner Interaktif (Validasi UX):** Pengisian 60 tipe soal minat/pertanyaan kemampuan dengan mekanisme tombol ("✅ Ya" dan "❌ Tidak"). Desain kuesionernya menjamin siswa untuk menyelesaikan semua pertanyaan (tidak ada _skip_) sebelum di-_submit_.
- **Cetak Penilaian Seketika (Real-time Feedback):** Setelah dikirim, aplikasi mengalihkan layar menuju laporan skor individu. Hasil kalkulasi _back-end_ memecah progres bar menjadi skala (0-100%) dan memberikan detail Rekomendasi Industri Spesifik yang cocok dengan minat sang murid.

---

## 🛠️ Stack Teknologi

- **Backend:** Laravel 11 (PHP 8.2+) dengan arsitektur MVC murni.
- **Database:** Struktur RDBMS Relasional ber-tumpuk riwayat lengkap menggunakan _cascade delete constraints_.
- **Styling:** Vanilla CSS + Bootstrap 5.3 (Mobile-First, Glassmorphism gradients & Premium Box-Shadows).
- **Ekstensi:** `phpoffice/phpspreadsheet` (Pengolahan _Excel_ tingkat Enterprise).

---

## 🧠 Model Soal & Skoring (Ringkasan Penting)

Model soal pada sistem ini adalah **Single-Choice Weighted Question** (pilih 1 opsi per soal, tiap opsi punya bobot skor).

- **Tipe Jawaban:** Satu soal hanya bisa memilih **satu** jawaban (radio).
- **Struktur Opsi:** Tiap soal memiliki minimal 2 opsi, dan dapat ditambah fleksibel dari panel Admin.
- **Bobot Nilai:** Tiap opsi menyimpan nilai numerik (`option_score`), urutan (`option_order`), dan status aktif/nonaktif.
- **Default Template:** Seed bawaan menggunakan pola biner **Ya (1)** dan **Tidak (0)**, tetapi sistem mendukung banyak opsi dan skor desimal.

### Cara Hitung Nilai

- Nilai jawaban siswa diambil dari skor opsi yang dipilih.
- Nilai maksimum per soal diambil dari **opsi skor tertinggi** pada soal tersebut.
- Skor kompetensi dihitung per kategori dengan rumus:
    `total skor diperoleh / total skor maksimum kategori * 100%`
- Sistem mengurutkan kompetensi dari skor tertinggi untuk menentukan rekomendasi industri utama.

### Catatan Batch

- **Batch 1:** Siswa mengerjakan semua kategori kompetensi.
- **Batch 2:** Soal difokuskan ke kategori hasil rekomendasi utama dari Batch 1.

### Modul Baru: Batch 2 CT (Weighted Scoring W-M-A)

Sistem kini menyediakan modul khusus **Batch 2 berbasis Computational Thinking** untuk memetakan kecenderungan siswa ke:

1. **Web Programming (W)**
2. **Digital Marketing (M)**
3. **Administratif (A)**

#### Struktur Data Batch 2 CT

- **Soal CT:** `batch_two_ct_questions`
    - `id`
    - `jenis_ct` (`Decomposition`, `Pattern Recognition`, `Abstraction`, `Algorithmic Thinking`)
    - `narasi_soal`
    - `level_kesulitan` (`easy`, `medium`, `hard`)
    - `is_active`
- **Opsi Jawaban CT:** `batch_two_ct_question_options`
    - `id`
    - `soal_id`
    - `label_opsi` (A/B/C/dst)
    - `teks_opsi`
    - `bobot_web` (0-4)
    - `bobot_marketing` (0-4)
    - `bobot_admin` (0-4)
- **Hasil Siswa CT:** `batch_two_ct_student_results`
    - `id`
    - `siswa_id`
    - `attempt_no` (multi attempt)
    - `total_web`, `total_marketing`, `total_admin`
    - `persen_web`, `persen_marketing`, `persen_admin`
    - `rekomendasi`

#### Logika Scoring Batch 2 CT

- Saat siswa memilih opsi, sistem menambahkan bobot ke masing-masing kategori:
    - `total_web = Σ bobot_web`
    - `total_marketing = Σ bobot_marketing`
    - `total_admin = Σ bobot_admin`

#### Logika Penentuan Rekomendasi

- Web tertinggi -> **Web Programming**
- Marketing tertinggi -> **Digital Marketing**
- Admin tertinggi -> **Administratif**
- Seri kombinasi:
    - Web + Marketing -> **Startup / Produk Digital**
    - Web + Admin -> **Backend / System Support**
    - Marketing + Admin -> **Admin Marketplace / Operasional Digital**

#### Dashboard Batch 2 CT

- Grafik rata-rata skor W-M-A.
- Persentase kecenderungan siswa.
- Distribusi rekomendasi PKL.
- Ranking siswa per kategori (Web/Marketing/Admin).

#### Fitur Tambahan Batch 2 CT

- **Bank soal CT** dengan filter jenis CT dan level kesulitan.
- **Import / Export soal** via **JSON** dan **Excel**.
- **Randomisasi urutan soal** saat asesmen.
- **Multi attempt** untuk simulasi ulang siswa.

#### Akses Modul Batch 2 CT

- Admin: `/admin/batch2-ct`
- Siswa Assessment: `/batch2-ct/assessment`
- Siswa Hasil: `/batch2-ct/result`

---

## 📖 Cara Pemasangan / Penggunaan Sistem (_Guide_)

### Tahap 1: Inisialisasi Sistem

1. Siapkan Lingkungan anda (XAMPP / Laragon).
2. Tautkan berkas `.env` anda ke basis data lokal yang bersih.
3. Jalankan _command_ migrasi sistem secara bersamaan dengan memuat sampel bibit (_seeders_) bawaan. Command:
    ```bash
    php artisan migrate:fresh --seed
    ```
    **Catatan:** `DatabaseSeeder` secara otomatis akan membangkitkan dan mendistribusikan semua referensi Industri, 60 Kumpulan Pertanyaan, 3 Kelas Dasar PPLG, serta akun otoritas admin.

### Tahap 2: Operasi Admin (Menyiapkan Siswa)

1. Akses aplikasi kemudian masuk ke ranah `/admin`.
2. Gunakan hak istimewa _default_ berikut untuk log-in.
    - **Username:** `admin`
    - **Password:** `password`
3. Pergi ke tab navigasi **Kelola Siswa** untuk mengumpulkan data murid. Gunakan tombol **Download Template** Excel.
4. Isi File Template dengan nama-nama murid, asalkan dengan Kelas yang sah (`11 PPLG 1`, `11 PPLG 2`, `11 PPLG 3`), simpan kembali (cth. `Siswa_Rombel.xlsx`).
5. Impor fail Excel tersebut pada menu Impor Data lalu klik "Unggah".

### Tahap 3: Ujian / Operasional Siswa

1. Berikan alamat URL pendaratan utama (`/`) kepada para Siswa.
2. Siswa hanya perlu menjatuhkan urutan pada _Dropdown_ Pertama ("Filter Kelas"), maka pilihan nama di _Dropdown_ Kedua ("Nama Siswa") akan muncul sesuai temannya satu-kelas menggunakan teknologi Ajax yang dipanggil di latar belakang aplikasi.
3. Tekan _Mulai Assessment Sekarang_ dan isi hingga tuntas per subkompetensi kehlian.
4. Tekan centang untuk menyetujui, dan klik **Submit Assessment**.

### Tahap 4: Monitor & Rekap Admin

1. Melalui halaman utama `/admin`, Admin dapat melihat pergerakan frekuensi _Doughnut Chart_ yang berubah seiring masuknya hasil anak-anak diiringi akumulasi "Sudah Mengerjakan" pada kartu Statistik.
2. Lakukan _Filter_ per kelas dari menu **Hasil Asesmen**.
3. Di akhir periode pengerjaan, tekan tomol hijau **Export Excel** di kanan atas untuk menyerap rekapitulasi data raihan nilai mentah utuh menjadi laporan file Excel di komputer administrator!
