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

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            
            <div class="alert alert-info" role="alert">
                <h4 class="alert-heading"><i class="fas fa-info-circle"></i> Petunjuk</h4>
                <p class="mb-0">Jawab asesmen dengan jujur untuk mendapatkan rekomendasi industri yang akurat.</p>
            </div>

            @if($activeBatch)
                <div class="alert alert-primary" role="alert">
                    <strong>Batch Aktif:</strong> {{ $activeBatch->batch_name }}
                </div>
            @else
                <div class="alert alert-warning" role="alert">
                    Belum ada batch asesmen yang aktif. Silakan hubungi admin.
                </div>
            @endif

            <div class="card shadow-sm mb-4" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <h5 class="mb-3"><i class="fas fa-layer-group"></i> Progress Batch Asesmen</h5>
                    <div class="row">
                        @forelse($batchProgresses as $progress)
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border {{ $progress['batch']->is_active ? 'border-primary' : '' }}" style="box-shadow: 0 4px 14px rgba(0,0,0,0.08);">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0">{{ $progress['batch']->batch_name }}</h6>
                                            <span class="badge bg-{{ $progress['status_class'] }}">{{ $progress['status_label'] }}</span>
                                        </div>

                                        @if($progress['submission'])
                                            <small class="text-muted d-block mb-3">
                                                Sudah submit pada {{ optional($progress['submission']->submitted_at)->format('d/m/Y H:i') }}
                                            </small>

                                            <a href="{{ route('student.result', ['batch_id' => $progress['batch']->id]) }}" class="btn btn-primary btn-sm mt-auto">
                                                <i class="fas fa-search"></i> Lihat Hasil Asesmen
                                            </a>
                                        @elseif($progress['can_start'])
                                            <small class="text-muted d-block mb-3">
                                                Batch aktif dan siap dikerjakan.
                                                @if(!empty($progress['category_hint']))
                                                    Fokus soal: <strong>{{ $progress['category_hint'] }}</strong>.
                                                @endif
                                            </small>

                                            <a href="{{ route('student.assessment', ['batch_id' => $progress['batch']->id]) }}" class="btn btn-start btn-sm mt-auto">
                                                <i class="fas fa-play-circle"></i> Mulai Assessment {{ $progress['batch']->batch_name }}
                                            </a>
                                        @elseif(!$progress['batch']->is_active)
                                            <small class="text-muted d-block mb-3">
                                                Batch ini belum aktif. Menunggu admin mengaktifkan batch.
                                            </small>

                                            <button class="btn btn-secondary btn-sm mt-auto" disabled>
                                                <i class="fas fa-lock"></i> Menunggu Batch Aktif
                                            </button>
                                        @elseif(!empty($progress['blocked_reason']))
                                            <small class="text-muted d-block mb-3">
                                                {{ $progress['blocked_reason'] }}
                                            </small>

                                            <button class="btn btn-secondary btn-sm mt-auto" disabled>
                                                <i class="fas fa-lock"></i> Belum Bisa Dikerjakan
                                            </button>
                                        @elseif(!$progress['has_questions'])
                                            <small class="text-muted d-block mb-3">
                                                Soal pada batch ini belum tersedia.
                                            </small>

                                            <button class="btn btn-secondary btn-sm mt-auto" disabled>
                                                <i class="fas fa-file-alt"></i> Soal Belum Tersedia
                                            </button>
                                        @else
                                            <small class="text-muted d-block mb-3">
                                                Batch belum bisa dikerjakan saat ini.
                                            </small>

                                            <button class="btn btn-secondary btn-sm mt-auto" disabled>
                                                <i class="fas fa-lock"></i> Belum Tersedia
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-warning mb-0">
                                    Belum ada batch asesmen yang dibuat oleh admin.
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="mb-1"><i class="fas fa-brain"></i> Simulasi Batch 2 - Computational Thinking</h5>
                            <p class="text-muted mb-0">Model soal berbobot W-M-A dengan dukungan multi attempt dan rekomendasi PKL.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('student.batch2ct.assessment') }}" class="btn btn-primary">
                                <i class="fas fa-play-circle"></i> Mulai / Ulangi Simulasi CT
                            </a>
                            <a href="{{ route('student.batch2ct.result') }}" class="btn btn-outline-primary">
                                <i class="fas fa-chart-line"></i> Lihat Hasil CT Terakhir
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="competency-icon">💻</div>
                            <h5 class="card-title">Pemrograman</h5>
                            <p class="card-text">HTML, CSS, JavaScript, Database</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="competency-icon">📋</div>
                            <h5 class="card-title">Administrasi</h5>
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
            
        </div>
    </div>
</div>
@endsection
