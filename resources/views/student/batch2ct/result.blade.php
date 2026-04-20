@extends('layouts.app')

@section('styles')
<style>
    body { background: #f8fafc; }
    .navbar { background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%); }
    .card { border-radius: 14px; border: none; box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
    .result-badge { font-size: 1rem; border-radius: 999px; padding: 0.5rem 1rem; }
    .chart-wrap { position: relative; height: 320px; }
</style>
@endsection

@section('content')
<nav class="navbar navbar-dark navbar-expand-lg">
    <div class="container">
        <span class="navbar-brand"><i class="fas fa-brain"></i> Hasil Batch 2 CT</span>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto"></ul>
            <div class="d-flex align-items-center mt-3 mt-lg-0 justify-content-between w-100" style="max-width: 300px; margin-left: auto;">
                <div style="color: white; text-align: left;">
                    <strong>{{ $student->full_name }}</strong><br>
                    <small>{{ optional($student->studentClass)->class_name }}</small>
                </div>
                <a href="{{ route('logout') }}" class="btn btn-sm btn-outline-light ms-3">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-4 mb-5">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="alert alert-info d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <strong>Attempt #{{ $result->attempt_no }}</strong>
                    <span class="d-block">Submit: {{ optional($result->submitted_at)->format('d/m/Y H:i') }}</span>
                </div>
                <div class="mt-2 mt-lg-0 d-flex gap-2">
                    <a href="{{ route('student.batch2ct.assessment') }}" class="btn btn-sm btn-outline-primary">Simulasi Ulang</a>
                    <a href="{{ route('student.dashboard') }}" class="btn btn-sm btn-outline-secondary">Kembali Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <small>Total Web</small>
                    <h3 class="mb-0">{{ $result->total_web }}</h3>
                    <small>{{ number_format((float) $result->persen_web, 2) }}%</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <small>Total Marketing</small>
                    <h3 class="mb-0">{{ $result->total_marketing }}</h3>
                    <small>{{ number_format((float) $result->persen_marketing, 2) }}%</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <small>Total Admin</small>
                    <h3 class="mb-0">{{ $result->total_admin }}</h3>
                    <small>{{ number_format((float) $result->persen_admin, 2) }}%</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body text-center">
            <h5 class="mb-2">Rekomendasi PKL</h5>
            <span class="badge bg-warning text-dark result-badge">{{ $result->rekomendasi }}</span>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0"><i class="fas fa-chart-column"></i> Grafik Skor W-M-A</h6>
                </div>
                <div class="card-body">
                    <div class="chart-wrap">
                        <canvas id="totalChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0"><i class="fas fa-chart-pie"></i> Persentase Kecenderungan</h6>
                </div>
                <div class="card-body">
                    <div class="chart-wrap">
                        <canvas id="percentChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0">Riwayat Attempt</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Attempt</th>
                        <th>Skor W</th>
                        <th>Skor M</th>
                        <th>Skor A</th>
                        <th>Rekomendasi</th>
                        <th>Waktu Submit</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attempts as $attempt)
                        <tr>
                            <td>#{{ $attempt->attempt_no }}</td>
                            <td>{{ $attempt->total_web }}</td>
                            <td>{{ $attempt->total_marketing }}</td>
                            <td>{{ $attempt->total_admin }}</td>
                            <td>{{ $attempt->rekomendasi }}</td>
                            <td>{{ optional($attempt->submitted_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('student.batch2ct.result', ['result' => $attempt->id]) }}" class="btn btn-sm btn-outline-primary">Lihat</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">Belum ada riwayat attempt.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0">Snapshot Jawaban Attempt #{{ $result->attempt_no }}</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Jenis CT</th>
                        <th>Soal</th>
                        <th>Opsi Dipilih</th>
                        <th>Bobot W</th>
                        <th>Bobot M</th>
                        <th>Bobot A</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($result->jawaban_json ?? [] as $idx => $answer)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>{{ $answer['jenis_ct'] ?? '-' }}</td>
                            <td>{{ $answer['narasi_soal'] ?? '-' }}</td>
                            <td><strong>{{ $answer['label_opsi'] ?? '-' }}</strong> - {{ $answer['teks_opsi'] ?? '-' }}</td>
                            <td>{{ $answer['bobot_web'] ?? 0 }}</td>
                            <td>{{ $answer['bobot_marketing'] ?? 0 }}</td>
                            <td>{{ $answer['bobot_admin'] ?? 0 }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">Data jawaban belum tersedia.</td>
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
    const chartData = @json($chartData);

    const totalCtx = document.getElementById('totalChart');
    if (totalCtx) {
        new Chart(totalCtx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Total Skor',
                    data: chartData.totals,
                    backgroundColor: ['#2563eb', '#16a34a', '#dc2626'],
                    borderRadius: 8,
                    maxBarThickness: 50,
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

    const percentCtx = document.getElementById('percentChart');
    if (percentCtx) {
        new Chart(percentCtx, {
            type: 'doughnut',
            data: {
                labels: chartData.labels,
                datasets: [{
                    data: chartData.percents,
                    backgroundColor: ['#2563eb', '#16a34a', '#dc2626'],
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
</script>
@endsection
