@extends('layouts.admin')

@section('page_title', 'Dashboard Overview')

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

    .card-stat:hover .icon-box {
        transform: scale(1.1) rotate(5deg);
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
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 0;
        color: var(--text-main);
    }

    .card-stat .trend {
        font-size: 0.8rem;
        font-weight: 600;
        margin-top: 10px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .chart-panel {
        border-radius: 24px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        background: #fff;
        padding: 30px;
        height: 100%;
        box-shadow: 0 4px 25px rgba(0,0,0,0.03);
        transition: all 0.3s ease;
    }

    .chart-panel:hover {
        box-shadow: 0 8px 35px rgba(0,0,0,0.06);
    }

    .chart-canvas-wrap {
        position: relative;
        height: 320px;
        width: 100%;
    }

    .batch-detail-card {
        border-radius: 24px;
        border: 1px solid #f1f5f9;
        background: #fff;
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .batch-detail-card:hover {
        border-color: var(--primary-color);
        box-shadow: 0 12px 30px rgba(79, 70, 229, 0.08);
    }

    .badge-soft {
        display: inline-flex;
        align-items: center;
        border-radius: 10px;
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.3px;
    }

    .badge-soft-active { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-soft-inactive { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
    .badge-soft-industry { background: #eef2ff; color: #4f46e5; border: 1px solid #e0e7ff; }

    .metric-box {
        background: #f8fafc;
        border-radius: 16px;
        padding: 15px 20px;
        border: 1px solid #f1f5f9;
        transition: all 0.2s ease;
    }

    .metric-box:hover {
        background: #fff;
        border-color: #e2e8f0;
        transform: scale(1.02);
    }

    .section-title {
        font-weight: 800;
        font-size: 1.1rem;
        margin-bottom: 25px;
        color: #1e293b;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .section-title div {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .section-title i {
        width: 32px;
        height: 32px;
        background: #eef2ff;
        color: var(--primary-color);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }

    .table-card {
        border-radius: 24px;
        border: none;
        box-shadow: 0 4px 25px rgba(0,0,0,0.03);
        overflow: hidden;
    }

    .detail-table thead th {
        background: #f8fafc;
        padding: 18px 20px;
        font-weight: 700;
        color: #64748b;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-top: none;
    }

    .detail-table tbody td {
        padding: 18px 20px;
        vertical-align: middle;
        color: #1e293b;
        font-weight: 500;
    }

    .progress-thin {
        height: 6px;
        border-radius: 10px;
        background: #f1f5f9;
    }

    @media (max-width: 768px) {
        .card-stat h3 {
            font-size: 1.8rem;
        }
        .card-stat .card-body {
            padding: 20px;
        }
        .card-stat .icon-box {
            width: 45px;
            height: 45px;
            font-size: 1.2rem;
            margin-bottom: 15px;
        }
        .chart-panel {
            padding: 20px;
        }
        .chart-canvas-wrap {
            height: 250px;
        }
        .section-title {
            font-size: 1rem;
        }
        .detail-table thead th, .detail-table tbody td {
            padding: 12px 10px;
            font-size: 0.8rem;
        }
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

    <div class="row mb-5">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stat">
                <div class="card-body">
                    <div class="icon-box" style="background: #eef2ff; color: #4f46e5;">
                        <i class="fas fa-users"></i>
                    </div>
                    <h6>Total Siswa</h6>
                    <h3>{{ $totalStudents }}</h3>
                    <div class="trend text-primary">
                        <i class="fas fa-info-circle"></i> Terdaftar di sistem
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stat">
                <div class="card-body">
                    <div class="icon-box" style="background: #e0f2fe; color: #0ea5e9;">
                        <i class="fas fa-file-signature"></i>
                    </div>
                    <h6>Total Submission</h6>
                    <h3>{{ $totalSubmissions }}</h3>
                    <div class="trend text-info">
                        <i class="fas fa-check-circle"></i> Berkas terkumpul
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stat">
                <div class="card-body">
                    <div class="icon-box" style="background: #ecfdf5; color: #10b981;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h6>Completion Rate</h6>
                    <h3>{{ number_format($overallCompletionRate, 1) }}%</h3>
                    <div class="progress progress-thin mt-2">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $overallCompletionRate }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stat">
                <div class="card-body">
                    <div class="icon-box" style="background: #fffbeb; color: #f59e0b;">
                        <i class="fas fa-star"></i>
                    </div>
                    <h6>Avg. Skor Rekomendasi</h6>
                    <h3>{{ number_format($overallAverageScore, 1) }}%</h3>
                    <div class="trend text-warning">
                        <i class="fas fa-medal"></i> Rata-rata performa
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-5 g-4">
        @foreach($comparisonBatches as $batch)
            @php $stat = $batchStats[$batch->id] ?? null; @endphp
            <div class="col-lg-6">
                <div class="card batch-detail-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <h5 class="mb-0 fw-800 text-dark">{{ $batch->batch_name }}</h5>
                                    <span class="badge-soft {{ $batch->is_active ? 'badge-soft-active' : 'badge-soft-inactive' }}" style="padding: 0.2rem 0.6rem; font-size: 0.65rem;">
                                        {{ $batch->is_active ? 'Aktif' : 'Selesai' }}
                                    </span>
                                </div>
                                <p class="text-muted small mb-0"><i class="far fa-calendar-alt me-1"></i> Data as of {{ now()->format('d M Y') }}</p>
                            </div>
                            <a href="{{ route('admin.results', ['batch_id' => $batch->id]) }}" class="btn btn-sm btn-premium">
                                <i class="fas fa-eye me-1"></i> Detail
                            </a>
                        </div>
        
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <div class="metric-box">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <i class="fas fa-user-check text-primary fs-xs" style="font-size: 0.8rem;"></i>
                                        </div>
                                        <small class="text-muted fw-bold">Submitted</small>
                                    </div>
                                    <h4 class="mb-0 fw-800">{{ $stat['submitted_count'] ?? 0 }} <small class="text-muted fs-6 fw-normal">Siswa</small></h4>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="metric-box">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="rounded-circle bg-warning bg-opacity-10 p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <i class="fas fa-clock text-warning" style="font-size: 0.8rem;"></i>
                                        </div>
                                        <small class="text-muted fw-bold">Pending</small>
                                    </div>
                                    <h4 class="mb-0 fw-800">{{ $stat['pending_count'] ?? 0 }} <small class="text-muted fs-6 fw-normal">Siswa</small></h4>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="metric-box">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="rounded-circle bg-success bg-opacity-10 p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <i class="fas fa-percentage text-success" style="font-size: 0.8rem;"></i>
                                        </div>
                                        <small class="text-muted fw-bold">Completion</small>
                                    </div>
                                    <h4 class="mb-0 fw-800">{{ number_format((float) ($stat['completion_rate'] ?? 0), 1) }}%</h4>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="metric-box">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="rounded-circle bg-info bg-opacity-10 p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <i class="fas fa-medal text-info" style="font-size: 0.8rem;"></i>
                                        </div>
                                        <small class="text-muted fw-bold">Avg. Score</small>
                                    </div>
                                    <h4 class="mb-0 fw-800">{{ number_format((float) ($stat['avg_recommendation_score'] ?? 0), 1) }}%</h4>
                                </div>
                            </div>
                        </div>
        
                        <div class="pt-3 border-top">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="fas fa-trophy text-warning"></i>
                                <small class="fw-bold text-dark">Top Industry Recommendations</small>
                            </div>
                            @if(!empty($stat['industry_counts']))
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach(array_slice($stat['industry_counts'], 0, 3, true) as $industryName => $industryCount)
                                        <div class="badge-soft badge-soft-industry">
                                            <span class="me-2">{{ $industryName }}</span>
                                            <span class="badge bg-white text-primary border shadow-sm">{{ $industryCount }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="py-2 px-3 bg-light rounded-3 text-muted small">
                                    <i class="fas fa-info-circle me-1"></i> Belum ada data rekomendasi industri.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row mb-5 g-4">
        <div class="col-lg-6">
            <div class="chart-panel">
                <div class="section-title">
                    <div>
                        <i class="fas fa-chart-bar"></i>
                        Progress Pengerjaan Per Batch
                    </div>
                </div>
                <div class="chart-canvas-wrap">
                    <canvas id="completionChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="chart-panel">
                <div class="section-title">
                    <div>
                        <i class="fas fa-chart-line"></i>
                        Trend Skor Rekomendasi
                    </div>
                </div>
                <div class="chart-canvas-wrap">
                    <canvas id="avgScoreChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="chart-panel">
                <div class="section-title">
                    <div>
                        <i class="fas fa-bullseye"></i>
                        Profil Kompetensi Batch
                    </div>
                </div>
                <div class="chart-canvas-wrap">
                    <canvas id="competencyChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="chart-panel">
                <div class="section-title">
                    <div>
                        <i class="fas fa-industry"></i>
                        Distribusi Industri
                    </div>
                </div>
                <div class="chart-canvas-wrap">
                    <canvas id="industryCompareChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card table-card mb-5">
        <div class="card-header bg-white border-bottom py-4 px-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fas fa-table me-2 text-primary"></i> Rekap Performa Kelas</h5>
                <button class="btn btn-sm btn-light border">Export Data</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 detail-table">
                <thead>
                    <tr>
                        <th>Kelas</th>
                        <th class="text-center">Total Siswa</th>
                        @foreach($comparisonBatches as $batch)
                            <th class="text-center">{{ $batch->batch_name }}</th>
                        @endforeach
                        <th class="text-center">Admin</th>
                        <th class="text-center">Digital</th>
                        <th class="text-center">IT</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classStats as $classRow)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3 bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                        <i class="fas fa-chalkboard text-muted"></i>
                                    </div>
                                    <strong>{{ $classRow['class']->class_name }}</strong>
                                </div>
                            </td>
                            <td class="text-center"><span class="badge bg-light text-dark fw-bold">{{ $classRow['total_students'] }}</span></td>
                            @foreach($comparisonBatches as $batch)
                                @php $batchClassStat = $classRow['per_batch'][$batch->id] ?? null; @endphp
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center">
                                        <span class="mb-1">{{ $batchClassStat['submitted_count'] ?? 0 }} / {{ $classRow['total_students'] }}</span>
                                        <div class="progress w-100 progress-thin" style="max-width: 60px;">
                                            <div class="progress-bar bg-primary" style="width: {{ $batchClassStat['completion_rate'] ?? 0 }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            @endforeach
                            <td class="text-center">
                                <span class="text-primary fw-bold">{{ $classRow['competency_counts']['Administrasi'] ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                <span class="text-info fw-bold">{{ $classRow['competency_counts']['Digital Marketing'] ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                <span class="text-success fw-bold">{{ $classRow['competency_counts']['Pemrograman'] ?? 0 }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 4 + $comparisonBatches->count() }}" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open d-block mb-2 fs-2"></i>
                                Belum ada data kelas yang tersedia.
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
    const completionChartData = @json($completionChart);
    const avgScoreChartData = @json($avgScoreChart);
    const competencyCompareChartData = @json($competencyCompareChart);
    const industryCompareChartData = @json($industryCompareChart);

    const palette = ['#4f46e5', '#10b981', '#f59e0b', '#0ea5e9', '#ec4899', '#8b5cf6'];
    
    // Global Chart.js Defaults
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.color = '#64748b';
    Chart.defaults.plugins.tooltip.padding = 12;
    Chart.defaults.plugins.tooltip.borderRadius = 12;
    Chart.defaults.plugins.tooltip.backgroundColor = '#1e293b';
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.padding = 20;

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
                        backgroundColor: '#4f46e5',
                        borderRadius: 10,
                        maxBarThickness: 40,
                    },
                    {
                        label: 'Belum Mengerjakan',
                        data: completionChartData.pending,
                        backgroundColor: '#e2e8f0',
                        borderRadius: 10,
                        maxBarThickness: 40,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', align: 'end' },
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { 
                        grid: { borderDash: [5, 5], color: '#f1f5f9' },
                        beginAtZero: true
                    }
                }
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
                        borderColor: '#4f46e5',
                        backgroundColor: (context) => {
                            const ctx = context.chart.ctx;
                            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                            gradient.addColorStop(0, 'rgba(79, 70, 229, 0.2)');
                            gradient.addColorStop(1, 'rgba(79, 70, 229, 0)');
                            return gradient;
                        },
                        tension: 0.4,
                        fill: true,
                        pointRadius: 6,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#4f46e5',
                        pointBorderWidth: 2,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        min: 0,
                        max: 100,
                        grid: { borderDash: [5, 5], color: '#f1f5f9' }
                    },
                },
                plugins: {
                    legend: { display: false },
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
                    backgroundColor: palette[index % palette.length] + '22',
                    pointBackgroundColor: palette[index % palette.length],
                    pointBorderColor: '#fff',
                    pointRadius: 4,
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
                        grid: { color: '#f1f5f9' },
                        angleLines: { color: '#f1f5f9' },
                        ticks: { display: false }
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
                    maxBarThickness: 30,
                })),
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', align: 'end' },
                },
                scales: {
                    x: { 
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], color: '#f1f5f9' }
                    },
                    y: { grid: { display: false } }
                },
            },
        });
    }
</script>
@endsection
