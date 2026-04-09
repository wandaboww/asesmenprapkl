@extends('layouts.app')

@section('styles')
<style>
    body { background: #f4f6f9; }
    .navbar { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); padding: 15px 0; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .navbar-brand { font-weight: 700; font-size: 1.4rem; }
    .nav-link { font-weight: 500; transition: 0.3s; }
    .nav-link:hover { transform: translateY(-2px); color: #fff !important; }
    
    @media (max-width: 768px) {
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
                    <a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('admin.results') }}">Hasil Asesmen</a>
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

    <div class="card shadow-sm border-0 mb-4 p-3">
        <form method="GET" action="{{ route('admin.results') }}" class="row align-items-end g-3">
            <div class="col-md-4">
                <label class="form-label">Filter Kelas</label>
                <select name="class_id" class="form-select">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->class_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status Asesmen</label>
                <select name="status" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai Mengerjakan</option>
                    <option value="belum" {{ request('status') == 'belum' ? 'selected' : '' }}>Belum Mengerjakan</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Terapkan Filter</button>
                <a href="{{ route('admin.results') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-chart-table"></i> Hasil Assessment Siswa</h5>
            <a href="{{ route('admin.export') }}" class="btn btn-success btn-sm">
                <i class="fas fa-download"></i> Export Excel
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Kelas</th>
                        <th>Nama Lengkap</th>
                        <th>Status</th>
                        <th>Rekomendasi Industri</th>
                        <th>Skor</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $index => $student)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><span class="badge bg-info text-dark">{{ $student->studentClass->class_name }}</span></td>
                        <td>{{ $student->full_name }}</td>
                        <td>
                            @if($student->submission)
                                <span class="badge bg-success">Selesai</span>
                            @else
                                <span class="badge bg-secondary">Belum</span>
                            @endif
                        </td>
                        <td>
                            @if($student->submission && $student->submission->recommendation)
                                <span class="badge bg-primary">{{ $student->submission->recommendation->industry->industry_name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($student->submission && $student->submission->recommendation)
                                <strong class="text-success">{{ number_format($student->submission->recommendation->score, 1) }}%</strong>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($student->submission)
                                <form action="{{ route('admin.students.reset', $student->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus hasil asesmen siswa ini? Siswa ini harus mengulang kembali ujiannya dari awal.');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-undo"></i> Reset Hasil</button>
                                </form>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">Belum ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
