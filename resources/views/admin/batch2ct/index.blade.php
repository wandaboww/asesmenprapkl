@extends('layouts.app')

@section('styles')
<style>
    body { background: #f4f6f9; }
    .navbar { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); padding: 15px 0; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .navbar-brand { font-weight: 700; font-size: 1.4rem; }
    .nav-link { font-weight: 500; transition: 0.3s; }
    .nav-link:hover { transform: translateY(-2px); color: #fff !important; }
    .card { border-radius: 14px; border: none; box-shadow: 0 8px 24px rgba(0,0,0,0.06); }
    .score-input { max-width: 90px; }
    .chart-wrap { position: relative; height: 320px; }
    .option-chip {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 8px 10px;
        margin-bottom: 6px;
        background: #f8fafc;
        font-size: 0.84rem;
    }
    .legend-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 5px;
    }

    @media (max-width: 768px) {
        .navbar-collapse { padding-top: 15px; }
        .admin-user-info {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255,255,255,0.1);
            width: 100%;
            justify-content: space-between;
        }
        .chart-wrap { height: 260px; }
    }
</style>
@endsection

@section('content')
<nav class="navbar navbar-dark navbar-expand-lg">
    <div class="container-fluid">
        <span class="navbar-brand"><i class="fas fa-shield-alt"></i> Admin Panel</span>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.results') }}">Hasil Asesmen</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.questions') }}">Kelola Soal Batch 1</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('admin.batch2ct.index') }}">Kelola Soal Batch 2 CT</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.students') }}">Kelola Siswa</a>
                </li>
            </ul>
            <div class="d-flex align-items-center admin-user-info">
                <span class="text-white me-3"><strong><i class="fas fa-user-circle"></i> {{ session('admin_name') }}</strong></span>
                <a href="{{ route('admin.logout') }}" class="btn btn-sm btn-outline-light"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid mt-4 mb-5">
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

    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <small>Total Soal CT</small>
                    <h3 class="mb-0">{{ $dashboardSummary['total_questions'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <small>Soal Aktif</small>
                    <h3 class="mb-0">{{ $dashboardSummary['active_questions'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <small>Siswa Tervalidasi</small>
                    <h3 class="mb-0">{{ $dashboardSummary['evaluated_students'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <small>Rata-rata Skor Total</small>
                    <h6 class="mb-0">W {{ number_format($dashboardSummary['avg_web'], 1) }} | M {{ number_format($dashboardSummary['avg_marketing'], 1) }} | A {{ number_format($dashboardSummary['avg_admin'], 1) }}</h6>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0"><i class="fas fa-chart-column"></i> Rata-rata Skor W-M-A</h6>
                </div>
                <div class="card-body">
                    <div class="chart-wrap">
                        <canvas id="avgScoreChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0"><i class="fas fa-chart-pie"></i> Distribusi Rekomendasi PKL</h6>
                </div>
                <div class="card-body">
                    <div class="chart-wrap">
                        <canvas id="recommendationChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0">Ranking Web Programming</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Siswa</th>
                                <th>Skor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rankingWeb as $idx => $item)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ optional($item->student)->full_name ?? '-' }}</td>
                                    <td><strong>{{ $item->total_web }}</strong></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">Belum ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0">Ranking Digital Marketing</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Siswa</th>
                                <th>Skor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rankingMarketing as $idx => $item)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ optional($item->student)->full_name ?? '-' }}</td>
                                    <td><strong>{{ $item->total_marketing }}</strong></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">Belum ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0">Ranking Administratif</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Siswa</th>
                                <th>Skor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rankingAdmin as $idx => $item)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ optional($item->student)->full_name ?? '-' }}</td>
                                    <td><strong>{{ $item->total_admin }}</strong></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">Belum ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0"><i class="fas fa-file-import"></i> Import Soal Batch 2 CT</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.batch2ct.import.json') }}" enctype="multipart/form-data" class="mb-3">
                        @csrf
                        <label class="form-label">Import JSON</label>
                        <div class="input-group">
                            <input type="file" name="json_file" class="form-control" accept=".json,.txt" required>
                            <button class="btn btn-outline-primary" type="submit">Import JSON</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('admin.batch2ct.import.excel') }}" enctype="multipart/form-data">
                        @csrf
                        <label class="form-label">Import Excel (.xlsx)</label>
                        <div class="input-group">
                            <input type="file" name="excel_file" class="form-control" accept=".xlsx" required>
                            <button class="btn btn-outline-success" type="submit">Import Excel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0"><i class="fas fa-file-export"></i> Export Soal Batch 2 CT</h6>
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <a href="{{ route('admin.batch2ct.export.json') }}" class="btn btn-outline-primary mb-2">
                        <i class="fas fa-file-code"></i> Export JSON
                    </a>
                    <a href="{{ route('admin.batch2ct.export.excel') }}" class="btn btn-outline-success">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                    <small class="text-muted mt-3">Format export: satu baris per opsi jawaban.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-plus-circle"></i> Tambah Soal Batch 2 CT</h6>
            <small class="text-muted">Metode Weighted Scoring W-M-A</small>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.batch2ct.questions.store') }}" id="ctQuestionForm">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Jenis Computational Thinking</label>
                        <select class="form-select" name="jenis_ct" required>
                            <option value="">-- Pilih Jenis CT --</option>
                            @foreach($ctTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Level Kesulitan</label>
                        <select class="form-select" name="level_kesulitan" required>
                            @foreach($difficultyLevels as $level)
                                <option value="{{ $level }}">{{ strtoupper($level) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" name="is_active" id="ctQuestionActive" value="1" checked>
                            <label class="form-check-label" for="ctQuestionActive">Soal aktif</label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Narasi Soal</label>
                    <textarea class="form-control" name="narasi_soal" rows="3" required placeholder="Tuliskan narasi soal dengan konteks Computational Thinking"></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">Opsi Jawaban + Bobot (W, M, A)</label>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addOptionBtn">
                        <i class="fas fa-plus"></i> Tambah Opsi
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="optionTable">
                        <thead>
                            <tr>
                                <th style="width: 90px;">Label</th>
                                <th>Teks Opsi</th>
                                <th style="width: 110px;">Bobot W</th>
                                <th style="width: 110px;">Bobot M</th>
                                <th style="width: 110px;">Bobot A</th>
                                <th style="width: 70px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <small class="text-muted d-block mb-3">Validasi: setiap opsi wajib punya minimal satu bobot dominan (nilai > 0).</small>

                <button type="submit" class="btn btn-primary w-100">Simpan Soal Batch 2 CT</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0"><i class="fas fa-book"></i> Bank Soal Batch 2 CT</h6>
        </div>
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.batch2ct.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Filter Jenis CT</label>
                    <select class="form-select" name="jenis_ct">
                        <option value="">-- Semua Jenis CT --</option>
                        @foreach($ctTypes as $type)
                            <option value="{{ $type }}" {{ $selectedJenisCt === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Filter Kesulitan</label>
                    <select class="form-select" name="level_kesulitan">
                        <option value="">-- Semua Level --</option>
                        @foreach($difficultyLevels as $level)
                            <option value="{{ $level }}" {{ $selectedDifficulty === $level ? 'selected' : '' }}>{{ strtoupper($level) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                    <a href="{{ route('admin.batch2ct.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>Soal</th>
                        <th style="width: 220px;">Jenis CT</th>
                        <th style="width: 110px;">Level</th>
                        <th style="width: 120px;">Status</th>
                        <th style="width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($questions as $question)
                        <tr>
                            <td>{{ $question->id }}</td>
                            <td>
                                <div class="mb-2">{{ \Illuminate\Support\Str::limit($question->narasi_soal, 140) }}</div>
                                @foreach($question->options as $option)
                                    <div class="option-chip">
                                        <strong>{{ $option->label_opsi }}</strong> - {{ $option->teks_opsi }}
                                        <span class="ms-2">
                                            <span class="legend-dot" style="background:#2563eb;"></span>W {{ $option->bobot_web }}
                                        </span>
                                        <span class="ms-2">
                                            <span class="legend-dot" style="background:#16a34a;"></span>M {{ $option->bobot_marketing }}
                                        </span>
                                        <span class="ms-2">
                                            <span class="legend-dot" style="background:#dc2626;"></span>A {{ $option->bobot_admin }}
                                        </span>
                                    </div>
                                @endforeach
                            </td>
                            <td>{{ $question->jenis_ct }}</td>
                            <td><span class="badge bg-light text-dark">{{ strtoupper($question->level_kesulitan) }}</span></td>
                            <td>
                                <span class="badge {{ $question->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $question->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.batch2ct.questions.edit', $question->id) }}" class="btn btn-sm btn-outline-primary mb-1">Edit</a>
                                <form method="POST" action="{{ route('admin.batch2ct.questions.delete', $question->id) }}" onsubmit="return confirm('Hapus soal ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-3 text-muted">Belum ada soal Batch 2 CT.</td>
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
        new Chart(avgCtx, {
            type: 'bar',
            data: {
                labels: chartAverage.labels,
                datasets: [{
                    label: 'Rata-rata skor',
                    data: chartAverage.values,
                    backgroundColor: ['#2563eb', '#16a34a', '#dc2626'],
                    borderRadius: 10,
                    maxBarThickness: 55,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                },
            },
        });
    }

    const recommendationCtx = document.getElementById('recommendationChart');
    if (recommendationCtx) {
        new Chart(recommendationCtx, {
            type: 'doughnut',
            data: {
                labels: recommendationChartData.labels,
                datasets: [{
                    data: recommendationChartData.values,
                    backgroundColor: ['#2563eb', '#16a34a', '#dc2626', '#f59e0b', '#7c3aed', '#0ea5e9'],
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
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
                <td><input type="text" class="form-control" data-field="label" name="options[${index}][label]" value="${label}" required></td>
                <td><input type="text" class="form-control" data-field="teks" name="options[${index}][teks]" value="${text}" required></td>
                <td><input type="number" min="0" max="4" class="form-control score-input" data-field="bobot_web" name="options[${index}][bobot_web]" value="${w}" required></td>
                <td><input type="number" min="0" max="4" class="form-control score-input" data-field="bobot_marketing" name="options[${index}][bobot_marketing]" value="${m}" required></td>
                <td><input type="number" min="0" max="4" class="form-control score-input" data-field="bobot_admin" name="options[${index}][bobot_admin]" value="${a}" required></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger remove-option">Hapus</button></td>
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
