@extends('layouts.app')

@section('styles')
<style>
    .navbar-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        padding: 15px 0;
    }
    .container-main { margin-top: 50px; margin-bottom: 50px; }
    .page-title { color: white; text-align: center; margin-bottom: 40px; }
    .page-title h1 { font-size: 2.5rem; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.2); }
    .card { border: none; border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); transition: all 0.3s ease; }
    .card:hover { transform: translateY(-8px); }
</style>
@endsection

@section('content')
<nav class="navbar navbar-dark navbar-expand-lg navbar-custom">
    <div class="container">
        <span class="navbar-brand"><i class="fas fa-graduation-cap"></i> Assessment PKL</span>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto"></ul>
            <div class="d-flex align-items-center mt-3 mt-lg-0 justify-content-between w-100" style="max-width: 300px; margin-left: auto;">
                <div style="color: white; text-align: left;">
                    <strong>{{ session('student_name') }}</strong><br>
                    <small>{{ session('class_name') }}</small>
                </div>
                <a href="{{ route('logout') }}" class="btn btn-sm btn-outline-light ms-3">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="container-main container">
    <div class="page-title">
        <h1><i class="fas fa-chart-bar"></i> Hasil Asesmen PKL</h1>
        <p style="color: rgba(255,255,255,0.9);">Berikut adalah rangkuman dari hasil asesmen yang telah Anda kerjakan</p>
    </div>
    
    <div class="row mb-4">
        <div class="col-lg-8 offset-lg-2">
            <div class="card shadow mb-4">
                <div class="card-body p-5">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    
                    <h3 class="text-center mb-4"><i class="fas fa-check-circle text-success"></i> Hasil Assessment Anda</h3>
                    
                    <p class="text-center text-muted">
                        Diisi pada: {{ \Carbon\Carbon::parse($submission->submitted_at)->format('d/m/Y H:i') }}
                    </p>
                    
                    <hr>
                    
                    <h5>Skor per Kompetensi:</h5>
                    <div class="row mb-4">
                        @foreach ($scores as $score)
                            @php $percentage = ($score['yes'] / $score['total']) * 100; @endphp
                            <div class="col-md-6 mb-3">
                                <div class="card shadow-sm border">
                                    <div class="card-body">
                                        <h6>{{ $score['name'] }}</h6>
                                        <div class="progress mb-2">
                                            <div class="progress-bar bg-primary" style="width: {{ $percentage }}%">
                                                {{ number_format($percentage, 1) }}%
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ $score['yes'] }}/{{ $score['total'] }} Ya</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @if ($submission->recommendation)
                        <hr>
                        <h5>Rekomendasi Industri:</h5>
                        <div class="alert alert-info mt-3 p-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 15px;">
                            <h5 style="color: white; font-weight: bold;">
                                <i class="fas fa-briefcase"></i> {{ $submission->recommendation->industry->industry_name }}
                            </h5>
                            <p class="mb-3">{{ $submission->recommendation->industry->description }}</p>
                            <div class="mt-3">
                                <small><strong>Kompetensi Dominan:</strong></small><br>
                                <span class="badge bg-light text-dark" style="margin-right: 5px; text-transform: capitalize;">
                                    {{ str_replace('_', ' ', $submission->recommendation->industry->primary_competency) }}
                                </span>
                                <span class="badge bg-warning text-dark" style="text-transform: capitalize;">
                                    {{ str_replace('_', ' ', $submission->recommendation->industry->secondary_competency) }}
                                </span>
                            </div>
                        </div>
                    @endif
                    
                    <div class="text-center mt-4">
                        <a href="{{ route('student.dashboard') }}" class="btn btn-primary px-5 py-2" style="border-radius: 50px; font-weight: bold;">
                            <i class="fas fa-home"></i> Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
