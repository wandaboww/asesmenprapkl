@extends('layouts.app')

@section('styles')
<style>
    body { background: #f4f6f9; }
    .navbar { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); padding: 15px 0; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .navbar-brand { font-weight: 700; font-size: 1.4rem; }
    .nav-link { font-weight: 500; transition: 0.3s; }
    .nav-link:hover { transform: translateY(-2px); color: #fff !important; }

    .card-stat {
        border-radius: 15px;
        border: none;
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
        position: relative;
    }

    .card-stat:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.12);
    }

    .card-stat .card-body {
        position: relative;
        z-index: 2;
        padding: 22px;
    }

    .card-stat h6 {
        font-size: 0.95rem;
        opacity: 0.95;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .card-stat h3 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0;
    }

    .card-stat::after {
        content: '';
        position: absolute;
        top: -26px;
        right: -18px;
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: rgba(255,255,255,0.14);
        z-index: 1;
    }

    .card-stat::before {
        content: '';
        position: absolute;
        bottom: -25px;
        left: -15px;
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: rgba(255,255,255,0.1);
        z-index: 1;
    }

    .chart-panel {
        border-radius: 15px;
        border: none;
        box-shadow: 0 8px 25px rgba(0,0,0,0.06);
        background: #fff;
        padding: 20px;
        height: 100%;
    }

    .chart-canvas-wrap {
        position: relative;
        height: 320px;
    }

    .batch-detail-card {
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        height: 100%;
    }

    .badge-soft {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.3rem 0.6rem;
        font-size: 0.72rem;
        font-weight: 700;
        border: 1px solid transparent;
    }

    .badge-soft-active {
        background: #dcfce7;
        color: #166534;
        border-color: #bbf7d0;
    }

    .badge-soft-inactive {
        background: #f1f5f9;
        color: #475569;
        border-color: #e2e8f0;
    }

    .badge-soft-complete {
        background: #dcfce7;
        color: #166534;
        border-color: #bbf7d0;
    }

    .badge-soft-pending {
        background: #fef3c7;
        color: #92400e;
        border-color: #fde68a;
    }

    .badge-soft-industry {
        background: #dbeafe;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .metric-box {
        background: #f8fafc;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        padding: 10px 12px;
    }

    .detail-table th {
        white-space: nowrap;
        background: #f8fafc;
        font-size: 0.82rem;
    }

    .detail-table td {
        font-size: 0.84rem;
        vertical-align: middle;
        white-space: nowrap;
    }

    .section-title {
        font-weight: 700;
        margin-bottom: 14px;
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

        .chart-canvas-wrap { height: 260px; }
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
                    <a class="nav-link active" href="{{ route('admin.dashboard') }}">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.results') }}">Hasil Asesmen</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.questions') }}">Kelola Soal</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.batch2ct.index') }}">Kelola Soal Batch 2 CT</a>
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

    @if($comparisonBatches->count() < 2)
        <div class="alert alert-warning">
            Dashboard perbandingan memerlukan minimal 2 batch. Saat ini terdeteksi {{ $comparisonBatches->count() }} batch.
        </div>
    @else
        <div class="alert alert-info d-flex flex-wrap gap-2 align-items-center">
            <span><strong>Mode Dashboard:</strong> Perbandingan tanpa filter batch.</span>
            @foreach($comparisonBatches as $batch)
                <span class="badge-soft {{ $batch->is_active ? 'badge-soft-active' : 'badge-soft-inactive' }}">
                    {{ $batch->batch_name }}{{ $batch->is_active ? ' (Aktif)' : '' }}
                </span>
            @endforeach
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card card-stat text-white" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                <div class="card-body">
                    <h6><i class="fas fa-users"></i> Total Siswa</h6>
                    <h3>{{ $totalStudents }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stat text-white" style="background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%);">
                <div class="card-body">
                    <h6><i class="fas fa-file-signature"></i> Total Submission</h6>
                    <h3>{{ $totalSubmissions }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stat text-white" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
                <div class="card-body">
                    <h6><i class="fas fa-chart-line"></i> Completion Rate</h6>
                    <h3>{{ number_format($overallCompletionRate, 1) }}%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stat text-white" style="background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%);">
                <div class="card-body">
                    <h6><i class="fas fa-star"></i> Rata-rata Skor Rekomendasi</h6>
                    <h3>{{ number_format($overallAverageScore, 1) }}%</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        @foreach($comparisonBatches as $batch)
            @php $stat = $batchStats[$batch->id] ?? null; @endphp
            <div class="col-lg-6 mb-3">
                <div class="card batch-detail-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="mb-1">{{ $batch->batch_name }}</h5>
                                <span class="badge-soft {{ $batch->is_active ? 'badge-soft-active' : 'badge-soft-inactive' }}">
                                    {{ $batch->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </div>
                            <a href="{{ route('admin.results', ['batch_id' => $batch->id]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt"></i> Lihat Detail Hasil
                            </a>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <div class="metric-box">
                                    <small class="text-muted d-block">Sudah Mengerjakan</small>
                                    <strong>{{ $stat['submitted_count'] ?? 0 }}</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="metric-box">
                                    <small class="text-muted d-block">Belum Mengerjakan</small>
                                    <strong>{{ $stat['pending_count'] ?? 0 }}</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="metric-box">
                                    <small class="text-muted d-block">Completion Rate</small>
                                    <strong>{{ number_format((float) ($stat['completion_rate'] ?? 0), 1) }}%</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="metric-box">
                                    <small class="text-muted d-block">Avg Skor Rekomendasi</small>
                                    <strong>{{ number_format((float) ($stat['avg_recommendation_score'] ?? 0), 1) }}%</strong>
                                </div>
                            </div>
                        </div>

                        <div>
                            <small class="text-muted d-block mb-1">Top Rekomendasi Industri</small>
                            @if(!empty($stat['industry_counts']))
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach(array_slice($stat['industry_counts'], 0, 3, true) as $industryName => $industryCount)
                                        <span class="badge-soft badge-soft-industry">{{ $industryName }} ({{ $industryCount }})</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted">Belum ada data rekomendasi industri.</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row mb-4 g-3">
        <div class="col-lg-3">
            <div class="chart-panel">
                <h6 class="section-title"><i class="fas fa-chart-bar text-primary"></i> Komparasi Progress Pengerjaan</h6>
                <div class="chart-canvas-wrap">
                    <canvas id="completionChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="chart-panel">
                <h6 class="section-title"><i class="fas fa-chart-line text-primary"></i> Rata-rata Skor Rekomendasi</h6>
                <div class="chart-canvas-wrap">
                    <canvas id="avgScoreChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="chart-panel">
                <h6 class="section-title"><i class="fas fa-bullseye text-primary"></i> Rata-rata Skor Kompetensi per Batch</h6>
                <div class="chart-canvas-wrap">
                    <canvas id="competencyChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="chart-panel">
                <h6 class="section-title"><i class="fas fa-industry text-primary"></i> Distribusi Rekomendasi Industri</h6>
                <div class="chart-canvas-wrap">
                    <canvas id="industryCompareChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0"><i class="fas fa-school"></i> Rekap Per Kelas</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 detail-table">
                <thead>
                    <tr>
                        <th>Kelas</th>
                        <th>Total Siswa</th>
                        @foreach($comparisonBatches as $batch)
                            <th>{{ $batch->batch_name }} - Submit</th>
                            <th>{{ $batch->batch_name }} - Completion</th>
                        @endforeach
                        <th>Administrasi</th>
                        <th>Digital Marketing</th>
                        <th>Pemrograman</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classStats as $classRow)
                        <tr>
                            <td><strong>{{ $classRow['class']->class_name }}</strong></td>
                            <td>{{ $classRow['total_students'] }}</td>
                            @foreach($comparisonBatches as $batch)
                                @php $batchClassStat = $classRow['per_batch'][$batch->id] ?? null; @endphp
                                <td>{{ $batchClassStat['submitted_count'] ?? 0 }} / {{ $classRow['total_students'] }}</td>
                                <td>{{ number_format((float) ($batchClassStat['completion_rate'] ?? 0), 1) }}%</td>
                            @endforeach
                            <td>{{ $classRow['competency_counts']['Administrasi'] ?? 0 }}</td>
                            <td>{{ $classRow['competency_counts']['Digital Marketing'] ?? 0 }}</td>
                            <td>{{ $classRow['competency_counts']['Pemrograman'] ?? 0 }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 5 + ($comparisonBatches->count() * 2) }}" class="text-center py-3">Belum ada data kelas.</td>
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
    const completionChartData = @json($completionChart);
    const avgScoreChartData = @json($avgScoreChart);
    const competencyCompareChartData = @json($competencyCompareChart);
    const industryCompareChartData = @json($industryCompareChart);

    const palette = ['#2563eb', '#059669', '#f59e0b', '#7c3aed', '#dc2626', '#0ea5e9'];

    const completionCtx = document.getElementById('completionChart');
    if (completionCtx) {
        new Chart(completionCtx, {
            type: 'bar',
            data: {
                labels: completionChartData.labels,
                datasets: [
                    {
                        label: 'Sudah Mengerjakan',
                        data: completionChartData.submitted,
                        backgroundColor: '#16a34a',
                        borderRadius: 8,
                        maxBarThickness: 46,
                    },
                    {
                        label: 'Belum Mengerjakan',
                        data: completionChartData.pending,
                        backgroundColor: '#f59e0b',
                        borderRadius: 8,
                        maxBarThickness: 46,
                    },
                ],
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

    const avgScoreCtx = document.getElementById('avgScoreChart');
    if (avgScoreCtx) {
        new Chart(avgScoreCtx, {
            type: 'line',
            data: {
                labels: avgScoreChartData.labels,
                datasets: [
                    {
                        label: 'Avg Skor Rekomendasi (%)',
                        data: avgScoreChartData.scores,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.15)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                    },
                },
                plugins: {
                    legend: { position: 'bottom' },
                },
            },
        });
    }

    const competencyCtx = document.getElementById('competencyChart');
    if (competencyCtx) {
        new Chart(competencyCtx, {
            type: 'radar',
            data: {
                labels: competencyCompareChartData.labels,
                datasets: competencyCompareChartData.datasets.map((dataset, index) => ({
                    label: dataset.label,
                    data: dataset.data,
                    borderColor: palette[index % palette.length],
                    backgroundColor: palette[index % palette.length] + '33',
                    pointBackgroundColor: palette[index % palette.length],
                    pointRadius: 3,
                    borderWidth: 2,
                })),
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        suggestedMin: 0,
                        suggestedMax: 100,
                    },
                },
                plugins: {
                    legend: { position: 'bottom' },
                },
            },
        });
    }

    const industryCtx = document.getElementById('industryCompareChart');
    if (industryCtx) {
        new Chart(industryCtx, {
            type: 'bar',
            data: {
                labels: industryCompareChartData.labels,
                datasets: industryCompareChartData.datasets.map((dataset, index) => ({
                    label: dataset.label,
                    data: dataset.data,
                    backgroundColor: palette[index % palette.length],
                    borderRadius: 8,
                    maxBarThickness: 44,
                })),
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                        },
                    },
                },
            },
        });
    }
</script>
@endsection
