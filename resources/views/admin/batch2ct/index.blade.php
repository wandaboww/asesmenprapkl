@extends('layouts.admin')

@section('page_title', 'Kelola Soal Batch 2 CT')

@section('styles')
<style>
    .card-stat {
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        position: relative;
        background: #fff;
    }

    .card-stat:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }

    .card-stat .card-body {
        position: relative;
        z-index: 2;
        padding: 30px;
    }

    .card-stat .icon-box {
        width: 60px;
        height: 60px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }

    .card-stat h6 {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-bottom: 8px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .card-stat h3 {
        font-size: 2.2rem;
        font-weight: 800;
        margin-bottom: 0;
        color: var(--text-main);
    }

    .chart-panel {
        border-radius: 24px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        background: #fff;
        padding: 30px;
        height: 100%;
        box-shadow: 0 4px 25px rgba(0,0,0,0.03);
    }

    .chart-canvas-wrap {
        position: relative;
        height: 300px;
        width: 100%;
    }

    .table-card {
        border-radius: 24px;
        border: none;
        box-shadow: 0 4px 25px rgba(0,0,0,0.03);
        background: #fff;
        overflow: hidden;
    }

    .option-chip {
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 12px 18px;
        margin-bottom: 10px;
        background: #f8fafc;
        font-size: 0.85rem;
        transition: all 0.2s;
    }

    .option-chip:hover {
        border-color: var(--primary-color);
        background: #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .legend-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 6px;
    }

    .chart-empty-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        width: 100%;
        z-index: 10;
        pointer-events: none;
    }

    .chart-empty-overlay i {
        font-size: 2.5rem;
        color: #cbd5e1;
        margin-bottom: 10px;
        display: block;
    }

    .chart-empty-overlay span {
        font-size: 0.85rem;
        color: #94a3b8;
        font-weight: 600;
    }


    .badge-soft {
        display: inline-flex;
        align-items: center;
        border-radius: 10px;
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .bg-indigo-soft { background-color: #eef2ff; }
    .bg-success-soft { background-color: #f0fdf4; }
    .bg-danger-soft { background-color: #fef2f2; }

    .rank-badge {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 800;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .rank-1 { background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%); color: #fff; }
    .rank-2 { background: linear-gradient(135deg, #94a3b8 0%, #475569 100%); color: #fff; }
    .rank-3 { background: linear-gradient(135deg, #d97706 0%, #92400e 100%); color: #fff; }


    .instruction-card {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: #ffffff;
        border-radius: 24px;
        padding: 35px;
        border: none;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(79, 70, 229, 0.2);
    }

    .instruction-card .guide-title {
        color: #ffffff;
        font-weight: 800;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .instruction-card .guide-text {
        color: rgba(255, 255, 255, 0.95);
        font-size: 1rem;
        line-height: 1.6;
    }

    .instruction-card ul {
        list-style: none;
        padding-left: 0;
    }

    .instruction-card ul li {
        position: relative;
        padding-left: 28px;
        margin-bottom: 12px;
        color: #ffffff;
        font-weight: 500;
    }

    .instruction-card ul li::before {
        content: '\f058';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        left: 0;
        top: 2px;
        color: #10b981;
        background: white;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: 1.1rem;
    }
    
    .btn-white-glass {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        transition: all 0.3s;
    }

    .btn-white-glass:hover {
        background: white;
        color: #4f46e5;
        transform: translateY(-2px);
    }


    .metric-table th {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        background: #f8fafc;
        padding: 12px 15px !important;
    }

    .metric-table td {
        padding: 12px 15px !important;
        vertical-align: middle;
    }

    .score-input {
        border-radius: 10px;
        text-align: center;
        font-weight: 700;
        border: 1px solid #e2e8f0;
    }

    .form-section-title {
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .table-scroll-container {
        max-height: 800px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: var(--primary-color) #f1f5f9;
    }

    .table-scroll-container::-webkit-scrollbar {
        width: 6px;
    }

    .table-scroll-container::-webkit-scrollbar-track {
        background: #f1f5f9;
    }

    .table-scroll-container::-webkit-scrollbar-thumb {
        background-color: var(--primary-color);
        border-radius: 20px;
    }

    .table-scroll-container thead {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #fff;
    }


</style>
@endsection

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="instruction-card">
        <div class="row align-items-center">
            <div class="col-md-9">
                <h4 class="guide-title mb-3"><i class="fas fa-info-circle me-2"></i> Panduan Pengelolaan Soal Batch 2 (CT)</h4>
                <p class="guide-text mb-4">Soal Batch 2 menggunakan metode <strong>Weighted Scoring</strong> untuk memetakan minat siswa ke 3 bidang utama: <strong>Web Programming (W)</strong>, <strong>Digital Marketing (M)</strong>, dan <strong>Administratif (A)</strong>.</p>
                <ul class="mb-0">
                    <li>Setiap opsi jawaban wajib memiliki bobot skor (0-4) pada satu atau lebih bidang.</li>
                    <li>Siswa akan direkomendasikan ke bidang dengan akumulasi skor tertinggi di akhir tes.</li>
                    <li>Gunakan level kesulitan untuk menyeimbangkan variasi soal Computational Thinking.</li>
                </ul>
            </div>
            <div class="col-md-3 text-md-end mt-4 mt-md-0">
                <button class="btn btn-white-glass rounded-pill px-4 py-2 fw-bold" data-bs-toggle="collapse" data-bs-target="#ctGuideDetails">
                    <i class="fas fa-chevron-down me-2"></i> Detail Import
                </button>
            </div>
        </div>
        <div class="collapse mt-4" id="ctGuideDetails">
            <div class="p-4 bg-white bg-opacity-10 rounded-4 border border-white border-opacity-20">
                <p class="small mb-2 fw-bold text-white"><i class="fas fa-file-excel me-2"></i> Tips Import Data:</p>
                <p class="small mb-0 text-white opacity-90">Pastikan file Excel mengikuti format yang disediakan. Bobot skor harus berupa angka bulat antara 0 sampai 4. Anda dapat mengunduh contoh format melalui tombol Export di bawah untuk dijadikan referensi.</p>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stat">
                <div class="card-body">
                    <div class="icon-box" style="background: #eef2ff; color: #4f46e5;">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <h6>Total Soal CT</h6>
                    <h3>{{ $dashboardSummary['total_questions'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stat">
                <div class="card-body">
                    <div class="icon-box" style="background: #ecfdf5; color: #10b981;">
                        <i class="fas fa-check"></i>
                    </div>
                    <h6>Soal Aktif</h6>
                    <h3>{{ $dashboardSummary['active_questions'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stat">
                <div class="card-body">
                    <div class="icon-box" style="background: #f0f9ff; color: #0ea5e9;">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h6>Siswa Tervalidasi</h6>
                    <h3>{{ $dashboardSummary['evaluated_students'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stat">
                <div class="card-body">
                    <div class="icon-box" style="background: #fff7ed; color: #f59e0b;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h6>Avg. Skor Total</h6>
                    <div class="d-flex gap-2 mt-2">
                        <span class="badge bg-primary px-2">W {{ number_format($dashboardSummary['avg_web'], 1) }}</span>
                        <span class="badge bg-success px-2">M {{ number_format($dashboardSummary['avg_marketing'], 1) }}</span>
                        <span class="badge bg-danger px-2">A {{ number_format($dashboardSummary['avg_admin'], 1) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="chart-panel">
                <div class="form-section-title">
                    <i class="fas fa-chart-bar"></i>
                    Komparasi Rata-rata Skor Per Bidang
                </div>
                <div class="chart-canvas-wrap">
                    @if($dashboardSummary['avg_web'] == 0 && $dashboardSummary['avg_marketing'] == 0 && $dashboardSummary['avg_admin'] == 0)
                        <div class="chart-empty-overlay">
                            <i class="fas fa-chart-bar"></i>
                            <span>Belum Ada Data Skor</span>
                        </div>
                    @endif
                    <canvas id="avgScoreChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="chart-panel">
                <div class="form-section-title">
                    <i class="fas fa-chart-pie"></i>
                    Distribusi Rekomendasi Industri
                </div>
                <div class="chart-canvas-wrap">
                    @if($recommendationCounts->isEmpty())
                        <div class="chart-empty-overlay">
                            <i class="fas fa-pie-chart"></i>
                            <span>Belum Ada Rekomendasi</span>
                        </div>
                    @endif
                    <canvas id="recommendationChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <!-- Ranking Web -->
        <div class="col-lg-4">
            <div class="table-card h-100">
                <div class="p-3 border-bottom d-flex align-items-center gap-2">
                    <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-code"></i>
                    </div>
                    <h6 class="mb-0 fw-800 text-dark">Top Web Programming</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="bg-light bg-opacity-50">
                                <tr>
                                    <th class="ps-3 py-2 border-0" style="width: 50px;">#</th>
                                    <th class="py-2 border-0">Siswa</th>
                                    <th class="pe-3 py-2 border-0 text-end">Skor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rankingWeb as $idx => $item)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="rank-badge {{ $idx < 3 ? 'rank-'.($idx+1) : '' }}">
                                                {{ $idx + 1 }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="ranking-avatar">
                                                    {{ substr(optional($item->student)->full_name ?? 'S', 0, 1) }}
                                                </div>
                                                <span class="text-dark fw-600 text-truncate" style="max-width: 120px;">
                                                    {{ optional($item->student)->full_name ?? '-' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="pe-3 text-end">
                                            <span class="badge bg-indigo-soft text-primary fw-bold">{{ $item->total_web }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-4 text-muted small">Belum ada data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ranking Marketing -->
        <div class="col-lg-4">
            <div class="table-card h-100">
                <div class="p-3 border-bottom d-flex align-items-center gap-2">
                    <div class="p-2 rounded-3 bg-success bg-opacity-10 text-success">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <h6 class="mb-0 fw-800 text-dark">Top Digital Marketing</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="bg-light bg-opacity-50">
                                <tr>
                                    <th class="ps-3 py-2 border-0" style="width: 50px;">#</th>
                                    <th class="py-2 border-0">Siswa</th>
                                    <th class="pe-3 py-2 border-0 text-end">Skor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rankingMarketing as $idx => $item)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="rank-badge {{ $idx < 3 ? 'rank-'.($idx+1) : '' }}">
                                                {{ $idx + 1 }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="ranking-avatar">
                                                    {{ substr(optional($item->student)->full_name ?? 'S', 0, 1) }}
                                                </div>
                                                <span class="text-dark fw-600 text-truncate" style="max-width: 120px;">
                                                    {{ optional($item->student)->full_name ?? '-' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="pe-3 text-end">
                                            <span class="badge bg-success-soft text-success fw-bold">{{ $item->total_marketing }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-4 text-muted small">Belum ada data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ranking Admin -->
        <div class="col-lg-4">
            <div class="table-card h-100">
                <div class="p-3 border-bottom d-flex align-items-center gap-2">
                    <div class="p-2 rounded-3 bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <h6 class="mb-0 fw-800 text-dark">Top Administratif</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="bg-light bg-opacity-50">
                                <tr>
                                    <th class="ps-3 py-2 border-0" style="width: 50px;">#</th>
                                    <th class="py-2 border-0">Siswa</th>
                                    <th class="pe-3 py-2 border-0 text-end">Skor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rankingAdmin as $idx => $item)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="rank-badge {{ $idx < 3 ? 'rank-'.($idx+1) : '' }}">
                                                {{ $idx + 1 }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="ranking-avatar">
                                                    {{ substr(optional($item->student)->full_name ?? 'S', 0, 1) }}
                                                </div>
                                                <span class="text-dark fw-600 text-truncate" style="max-width: 120px;">
                                                    {{ optional($item->student)->full_name ?? '-' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="pe-3 text-end">
                                            <span class="badge bg-danger-soft text-danger fw-bold">{{ $item->total_admin }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-4 text-muted small">Belum ada data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="table-card p-4">
                <div class="form-section-title">
                    <i class="fas fa-file-import text-primary"></i>
                    Import Data Soal
                </div>
                <p class="text-muted small mb-4">Gunakan form di bawah untuk menambahkan soal secara massal melalui file Excel.</p>
                


                <form method="POST" action="{{ route('admin.batch2ct.import.excel') }}" enctype="multipart/form-data">
                    @csrf
                    <label class="form-label text-muted small fw-bold">IMPORT EXCEL (.XLSX)</label>
                    <div class="input-group">
                        <input type="file" name="excel_file" class="form-control bg-light" accept=".xlsx" required>
                        <button class="btn btn-success" type="submit">Import</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="table-card p-4 h-100 d-flex flex-column">
                <div class="form-section-title">
                    <i class="fas fa-file-export text-warning"></i>
                    Export Data Soal
                </div>
                <p class="text-muted small mb-4">Gunakan fitur export untuk backup atau mengunduh template format soal.</p>
                <div class="d-grid gap-3 mt-auto">

                    <a href="{{ route('admin.batch2ct.export.excel') }}" class="btn btn-outline-success border-2 fw-bold">
                        <i class="fas fa-file-excel me-2"></i> Download Excel
                    </a>
                </div>
                <div class="alert alert-light mt-4 mb-0 py-2 border">
                    <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Format export: satu baris per opsi jawaban.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="table-card p-4 mb-5">
        <div class="form-section-title">
            <i class="fas fa-plus-circle text-primary"></i>
            Tambah Soal Batch 2 CT
        </div>
        <form method="POST" action="{{ route('admin.batch2ct.questions.store') }}" id="ctQuestionForm">
            @csrf
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">JENIS COMPUTATIONAL THINKING</label>
                    <select class="form-select bg-light" name="jenis_ct" required>
                        <option value="">-- Pilih Jenis CT --</option>
                        @foreach($ctTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">LEVEL KESULITAN</label>
                    <select class="form-select bg-light" name="level_kesulitan" required>
                        @foreach($difficultyLevels as $level)
                            <option value="{{ $level }}">{{ strtoupper($level) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input type="checkbox" class="form-check-input" name="is_active" id="ctQuestionActive" value="1" checked>
                        <label class="form-check-label fw-bold text-dark" for="ctQuestionActive">Aktifkan Soal</label>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold small text-muted">NARASI SOAL</label>
                <textarea class="form-control bg-light" name="narasi_soal" rows="4" required placeholder="Tuliskan narasi soal dengan konteks Computational Thinking..."></textarea>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <label class="form-label fw-bold small text-muted mb-0">OPSI JAWABAN & BOBOT SKOR (W-M-A)</label>
                <button type="button" class="btn btn-sm btn-outline-primary fw-bold" id="addOptionBtn">
                    <i class="fas fa-plus me-1"></i> Tambah Opsi
                </button>
            </div>

            <div class="table-responsive mb-4 rounded-3 border">
                <table class="table metric-table mb-0" id="optionTable">
                    <thead>
                        <tr>
                            <th style="width: 100px;">Label</th>
                            <th>Teks Opsi</th>
                            <th class="text-center" style="width: 110px;">Bobot W</th>
                            <th class="text-center" style="width: 110px;">Bobot M</th>
                            <th class="text-center" style="width: 110px;">Bobot A</th>
                            <th class="text-center" style="width: 80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="alert alert-info border-0 bg-light py-2 px-3 mb-4">
                <small><i class="fas fa-info-circle me-1"></i> Setiap opsi wajib memiliki minimal satu bobot dominan (nilai > 0).</small>
            </div>

            <button type="submit" class="btn btn-premium w-100 py-3 shadow">
                <i class="fas fa-save me-2"></i> Simpan Soal Batch 2 CT
            </button>
        </form>
    </div>

    <div class="table-card">
        <div class="card-header bg-white border-bottom py-4 px-4">
            <h5 class="mb-1 fw-bold text-dark"><i class="fas fa-database me-2 text-primary"></i> Bank Soal Batch 2 CT</h5>
            <p class="text-muted small mb-0">Koleksi soal Computational Thinking yang terdaftar di sistem.</p>
        </div>
        <div class="p-4 border-bottom bg-light bg-opacity-50">
            <form method="GET" action="{{ route('admin.batch2ct.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">FILTER JENIS CT</label>
                    <select class="form-select bg-white" name="jenis_ct">
                        <option value="">-- Semua Jenis CT --</option>
                        @foreach($ctTypes as $type)
                            <option value="{{ $type }}" {{ $selectedJenisCt === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">FILTER KESULITAN</label>
                    <select class="form-select bg-white" name="level_kesulitan">
                        <option value="">-- Semua Level --</option>
                        @foreach($difficultyLevels as $level)
                            <option value="{{ $level }}" {{ $selectedDifficulty === $level ? 'selected' : '' }}>{{ strtoupper($level) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-filter me-2"></i> Filter</button>
                    <a href="{{ route('admin.batch2ct.index') }}" class="btn btn-outline-secondary px-3" title="Reset"><i class="fas fa-sync-alt"></i></a>
                </div>
            </form>
        </div>
        <div class="table-responsive table-scroll-container">
            <table class="table table-hover mb-0 detail-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 70px;">ID</th>
                        <th>Narasi Soal & Opsi Jawaban</th>
                        <th>Metadata</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($questions as $question)
                        <tr>
                            <td class="text-center text-muted fw-bold">{{ $question->id }}</td>
                            <td>
                                <div class="mb-3 text-dark fw-500" style="line-height: 1.6;">{{ $question->narasi_soal }}</div>
                                <div class="row g-2">
                                    @foreach($question->options as $option)
                                        <div class="col-12">
                                            <div class="option-chip mb-0">
                                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                    <div>
                                                        <span class="badge bg-primary me-2">{{ $option->label_opsi }}</span>
                                                        <span class="text-dark">{{ $option->teks_opsi }}</span>
                                                    </div>
                                                    <div class="d-flex gap-3">
                                                        <span class="small"><span class="legend-dot" style="background:#4f46e5;"></span><span class="text-muted">W:</span> <strong>{{ $option->bobot_web }}</strong></span>
                                                        <span class="small"><span class="legend-dot" style="background:#10b981;"></span><span class="text-muted">M:</span> <strong>{{ $option->bobot_marketing }}</strong></span>
                                                        <span class="small"><span class="legend-dot" style="background:#ef4444;"></span><span class="text-muted">A:</span> <strong>{{ $option->bobot_admin }}</strong></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td style="min-width: 180px;">
                                <div class="mb-2">
                                    <span class="badge-soft badge-soft-class w-100 mb-1 d-flex justify-content-center">{{ $question->jenis_ct }}</span>
                                    <span class="badge-soft badge-soft-industry w-100 d-flex justify-content-center">{{ strtoupper($question->level_kesulitan) }}</span>
                                </div>
                                @if($question->is_active)
                                    <span class="badge bg-success w-100 py-2"><i class="fas fa-check-circle me-1"></i> Aktif</span>
                                @else
                                    <span class="badge bg-secondary w-100 py-2"><i class="fas fa-times-circle me-1"></i> Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-2">
                                    <a href="{{ route('admin.batch2ct.questions.edit', $question->id) }}" class="btn btn-sm btn-light border text-primary">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.batch2ct.questions.delete', $question->id) }}" onsubmit="return confirm('Hapus soal ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-light border text-danger w-100">
                                            <i class="fas fa-trash-alt me-1"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open d-block mb-3 fs-1 opacity-25"></i>
                                Belum ada soal yang tersedia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctTypes = @json($ctTypes);
    const chartAverage = {
        labels: ['Web Programming', 'Digital Marketing', 'Administratif'],
        values: [
            {{ $dashboardSummary['avg_web'] }},
            {{ $dashboardSummary['avg_marketing'] }},
            {{ $dashboardSummary['avg_admin'] }}
        ],
    };

    const recommendationChartData = {
        labels: @json($recommendationCounts->keys()->values()->all()),
        values: @json($recommendationCounts->values()->all()),
    };

    const avgCtx = document.getElementById('avgScoreChart');
    if (avgCtx) {
        const isEmpty = chartAverage.values.every(v => v === 0);
        const displayData = isEmpty ? [3.5, 2.8, 3.2] : chartAverage.values;
        const displayColors = isEmpty ? ['#e2e8f0', '#e2e8f0', '#e2e8f0'] : ['#4f46e5', '#10b981', '#ef4444'];

        new Chart(avgCtx, {
            type: 'bar',
            data: {
                labels: chartAverage.labels,
                datasets: [{
                    label: 'Rata-rata skor',
                    data: displayData,
                    backgroundColor: displayColors,
                    borderRadius: 12,
                    maxBarThickness: 50,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: !isEmpty,
                        padding: 12,
                        borderRadius: 10,
                        backgroundColor: '#1e293b'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 4,
                        grid: { borderDash: [5, 5], color: '#f1f5f9' }
                    },
                    x: { grid: { display: false } }
                }
            },
        });
    }

    const recommendationCtx = document.getElementById('recommendationChart');
    if (recommendationCtx) {
        const isEmpty = recommendationChartData.values.length === 0 || recommendationChartData.values.every(v => v === 0);
        const displayData = isEmpty ? [1] : recommendationChartData.values;
        const displayLabels = isEmpty ? ['Belum ada data'] : recommendationChartData.labels;
        const displayColors = isEmpty ? ['#f1f5f9'] : ['#4f46e5', '#10b981', '#ef4444', '#f59e0b', '#7c3aed', '#0ea5e9'];

        new Chart(recommendationCtx, {
            type: 'doughnut',
            data: {
                labels: displayLabels,
                datasets: [{
                    data: displayData,
                    backgroundColor: displayColors,
                    borderWidth: isEmpty ? 0 : 2
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { 
                        position: 'bottom',
                        display: !isEmpty,
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { size: 11, weight: '600' }
                        }
                    },
                    tooltip: { enabled: !isEmpty }
                },
            },
        });
    }

    function labelByIndex(index) {
        const code = 65 + index;
        return String.fromCharCode(code);
    }

    function appendOptionRow(index, values = {}) {
        const label = values.label || labelByIndex(index);
        const text = values.teks || '';
        const w = Number.isInteger(values.bobot_web) ? values.bobot_web : 0;
        const m = Number.isInteger(values.bobot_marketing) ? values.bobot_marketing : 0;
        const a = Number.isInteger(values.bobot_admin) ? values.bobot_admin : 0;

        const row = `
            <tr data-index="${index}">
                <td class="p-2"><input type="text" class="form-control score-input w-100" data-field="label" name="options[${index}][label]" value="${label}" required></td>
                <td class="p-2"><input type="text" class="form-control bg-light" data-field="teks" name="options[${index}][teks]" value="${text}" required placeholder="Contoh: Sangat Setuju"></td>
                <td class="p-2"><input type="number" min="0" max="4" class="form-control score-input w-100" data-field="bobot_web" name="options[${index}][bobot_web]" value="${w}" required></td>
                <td class="p-2"><input type="number" min="0" max="4" class="form-control score-input w-100" data-field="bobot_marketing" name="options[${index}][bobot_marketing]" value="${m}" required></td>
                <td class="p-2"><input type="number" min="0" max="4" class="form-control score-input w-100" data-field="bobot_admin" name="options[${index}][bobot_admin]" value="${a}" required></td>
                <td class="p-2 text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-option rounded-circle" style="width:32px; height:32px; padding:0;"><i class="fas fa-times"></i></button></td>
            </tr>
        `;

        document.querySelector('#optionTable tbody').insertAdjacentHTML('beforeend', row);
    }


    function reindexRows() {
        const rows = document.querySelectorAll('#optionTable tbody tr');
        rows.forEach((row, idx) => {
            row.dataset.index = idx;
            row.querySelectorAll('input[data-field]').forEach((input) => {
                const field = input.dataset.field;
                input.name = `options[${idx}][${field}]`;
            });

            const labelInput = row.querySelector('input[data-field="label"]');
            if (labelInput && labelInput.value.trim() === '') {
                labelInput.value = labelByIndex(idx);
            }
        });
    }

    appendOptionRow(0, { label: 'A', bobot_web: 1, bobot_marketing: 0, bobot_admin: 0 });
    appendOptionRow(1, { label: 'B', bobot_web: 0, bobot_marketing: 1, bobot_admin: 0 });
    appendOptionRow(2, { label: 'C', bobot_web: 0, bobot_marketing: 0, bobot_admin: 1 });

    document.getElementById('addOptionBtn').addEventListener('click', function () {
        const index = document.querySelectorAll('#optionTable tbody tr').length;
        appendOptionRow(index);
    });

    document.addEventListener('click', function (event) {
        if (!event.target.classList.contains('remove-option')) {
            return;
        }

        const rows = document.querySelectorAll('#optionTable tbody tr');
        if (rows.length <= 3) {
            alert('Minimal harus ada 3 opsi (A, B, C).');
            return;
        }

        event.target.closest('tr').remove();
        reindexRows();
    });

    document.getElementById('ctQuestionForm').addEventListener('submit', function (event) {
        const rows = Array.from(document.querySelectorAll('#optionTable tbody tr'));
        const invalid = rows.find((row) => {
            const w = parseInt(row.querySelector('input[name*="[bobot_web]"]').value || '0', 10);
            const m = parseInt(row.querySelector('input[name*="[bobot_marketing]"]').value || '0', 10);
            const a = parseInt(row.querySelector('input[name*="[bobot_admin]"]').value || '0', 10);
            return Math.max(w, m, a) <= 0;
        });

        if (invalid) {
            event.preventDefault();
            alert('Setiap opsi harus memiliki minimal satu bobot dominan (> 0).');
        }
    });
</script>
@endsection
