@extends('layouts.admin')

@section('page_title', 'Ringkasan Batch 2 CT')

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
    
    .form-section-title {
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    @media (max-width: 768px) {
        .card-stat .card-body {
            padding: 20px;
        }
        .card-stat h3 {
            font-size: 1.6rem;
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
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
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
                    <i class="fas fa-chart-bar text-primary"></i>
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
                    <i class="fas fa-chart-pie text-warning"></i>
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
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
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
</script>
@endsection
