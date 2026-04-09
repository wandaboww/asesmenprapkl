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
        transform: translateY(-5px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.15);
    }
    .card-stat .card-body { position: relative; z-index: 2; padding: 25px; }
    .card-stat h6 { font-size: 1rem; opacity: 0.9; margin-bottom: 10px; font-weight: 600;}
    .card-stat h3 { font-size: 2.5rem; font-weight: 700; margin-bottom: 0; }
    
    /* Decorative circles for stat cards */
    .card-stat::after {
        content: ''; position: absolute; top: -20px; right: -20px; 
        width: 100px; height: 100px; border-radius: 50%;
        background: rgba(255,255,255,0.15); z-index: 1;
    }
    .card-stat::before {
        content: ''; position: absolute; bottom: -30px; left: -10px; 
        width: 80px; height: 80px; border-radius: 50%;
        background: rgba(255,255,255,0.1); z-index: 1;
    }

    .chart-container-wrapper {
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.06);
        background: #ffffff;
        padding: 30px;
        border: none;
    }

    @media (max-width: 768px) {
        .card-stat-col { margin-bottom: 20px; }
        .chart-container-wrapper { padding: 15px; }
        .navbar-collapse { padding-top: 15px; }
        .admin-user-info { margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.1); width: 100%; justify-content: space-between; }
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


    
    <div class="row mb-4">
        <div class="col-md-4 card-stat-col">
            <div class="card card-stat text-white" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                <div class="card-body">
                    <h6><i class="fas fa-users"></i> Total Siswa (Sesuai Filter)</h6>
                    <h3>{{ count($students) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 card-stat-col">
            <div class="card card-stat text-white" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
                <div class="card-body">
                    <h6><i class="fas fa-check-circle"></i> Sudah Mengerjakan</h6>
                    <h3>{{ $students->filter(function($s) { return $s->submission !== null; })->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 card-stat-col">
            <div class="card card-stat text-white" style="background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);">
                <div class="card-body">
                    <h6><i class="fas fa-exclamation-circle"></i> Belum Mengerjakan</h6>
                    <h3>{{ $students->filter(function($s) { return $s->submission === null; })->count() }}</h3>
                </div>
            </div>
        </div>
    </div>

    @if(count($chartLabels) > 0)
    <div class="card chart-container-wrapper mb-4">
        <h5 class="mb-4 font-weight-bold shadow-sm-text"><i class="fas fa-chart-pie text-primary"></i> Ringkasan Rekomendasi Industri</h5>
        <div style="height: 350px; width: 100%; display: flex; justify-content: center;">
            <canvas id="industryChart"></canvas>
        </div>
    </div>
    @endif


</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    @if(count($chartLabels) > 0)
    const ctx = document.getElementById('industryChart').getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                data: {!! json_encode($chartData) !!},
                backgroundColor: [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: window.innerWidth < 768 ? 'bottom' : 'right',
                    labels: { padding: 20, font: { size: 13 } }
                }
            }
        }
    });
    @endif
</script>
@endsection
