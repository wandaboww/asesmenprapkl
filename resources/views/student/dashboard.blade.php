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
    .btn-start { background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); border: none; color: white; }
    .btn-start:hover { transform: scale(1.05); color: white; }
    .competency-icon { font-size: 3rem; margin-bottom: 15px; }
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
        <h1><i class="fas fa-chart-bar"></i> Pemetaan Industri PKL</h1>
        <p style="color: rgba(255,255,255,0.9);">Temukan industri yang tepat sesuai kompetensi Anda</p>
    </div>
    
    <div class="row mb-4">
        <div class="col-lg-8 offset-lg-2">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            
            <div class="alert alert-info" role="alert">
                <h4 class="alert-heading"><i class="fas fa-info-circle"></i> Petunjuk</h4>
                <p class="mb-0">Assessment ini terdiri dari 60 pertanyaan yang dibagi menjadi 3 bidang kompetensi. Jawab dengan jujur untuk mendapatkan rekomendasi industri yang akurat!</p>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="competency-icon">💻</div>
                            <h5 class="card-title">Pemrograman Website</h5>
                            <p class="card-text">HTML, CSS, JavaScript, Database</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="competency-icon">📋</div>
                            <h5 class="card-title">Administrasi Perkantoran</h5>
                            <p class="card-text">Data Entry, Laporan, Dokumentasi</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="competency-icon">📱</div>
                            <h5 class="card-title">Digital Marketing</h5>
                            <p class="card-text">Konten Kreatif, Media Sosial, Desain</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4 mb-5">
                @if($already_submitted)
                    <a href="{{ route('student.result') }}" class="btn btn-primary px-5 py-3" style="border-radius: 50px; font-weight: bold; font-size: 1.1rem; box-shadow: 0 4px 15px rgba(0,123,255,0.3);">
                        <i class="fas fa-search"></i> Lihat Hasil Asesmen Anda
                    </a>
                @else
                    <a href="{{ route('student.assessment') }}" class="btn btn-start px-5 py-3" style="border-radius: 50px; font-weight: bold; font-size: 1.1rem; box-shadow: 0 4px 15px rgba(40,167,69,0.3);">
                        <i class="fas fa-play-circle"></i> Mulai Assessment Sekarang
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
