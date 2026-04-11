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

                    <p class="text-center text-muted mb-0">
                        Batch: {{ $submission->batch ? $submission->batch->batch_name : '-' }}
                    </p>
                    
                    <hr>
                    
                    <h5>Skor per Kompetensi:</h5>
                    <div class="row mb-4">
                        @foreach ($scores as $score)
                            <div class="col-md-6 mb-3">
                                <div class="card shadow-sm border">
                                    <div class="card-body">
                                        <h6>{{ $score['name'] }}</h6>
                                        <div class="progress mb-2">
                                            <div class="progress-bar bg-primary" style="width: {{ $score['percentage'] }}%">
                                                {{ number_format($score['percentage'], 1) }}%
                                            </div>
                                        </div>
                                        <small class="text-muted">Skor {{ number_format($score['obtained'], 1) }} / {{ number_format($score['max'], 1) }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @if ($submission->recommendation)
                        @php
                            $competencyLabelMap = [
                                'administrasi' => 'Administrasi',
                                'administration' => 'Administrasi',
                                'digital_marketing' => 'Digital Marketing',
                                'digital marketing' => 'Digital Marketing',
                                'marketing' => 'Digital Marketing',
                                'pemrograman' => 'Pemrograman',
                                'programming' => 'Pemrograman',
                            ];

                            $primaryKey = strtolower((string) ($submission->recommendation->industry->primary_competency ?? ''));
                            $secondaryKey = strtolower((string) ($submission->recommendation->industry->secondary_competency ?? ''));
                            $primaryLabel = $competencyLabelMap[$primaryKey] ?? ucwords(str_replace('_', ' ', $primaryKey));
                            $secondaryLabel = $competencyLabelMap[$secondaryKey] ?? ucwords(str_replace('_', ' ', $secondaryKey));
                        @endphp

                        <hr>
                        <h5>Rekomendasi Industri:</h5>
                        <div class="alert alert-info mt-3 p-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 15px;">
                            <h5 style="color: white; font-weight: bold;">
                                <i class="fas fa-briefcase"></i> {{ $submission->recommendation->industry->display_industry_name }}
                            </h5>
                            <p class="mb-3">{{ $submission->recommendation->industry->display_industry_description }}</p>
                            <div class="mt-3">
                                <small><strong>Kompetensi Dominan:</strong></small><br>
                                <span class="badge bg-light text-dark" style="margin-right: 5px; text-transform: capitalize;">
                                    {{ $primaryLabel }}
                                </span>
                                <span class="badge bg-warning text-dark" style="text-transform: capitalize;">
                                    {{ $secondaryLabel }}
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
