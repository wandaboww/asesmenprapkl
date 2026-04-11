@extends('layouts.app')

@section('styles')
<style>
    body { background: #f4f6f9; }
    .navbar { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); padding: 15px 0; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .navbar-brand { font-weight: 700; font-size: 1.4rem; }
    .nav-link { font-weight: 500; transition: 0.3s; }
    .nav-link:hover { transform: translateY(-2px); color: #fff !important; }
    
    .card { border-radius: 15px; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.06); }

    .badge-class-soft {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.35rem 0.65rem;
        font-size: 0.74rem;
        font-weight: 700;
        border: 1px solid transparent;
        line-height: 1;
        background: #e2e8f0;
        color: #334155;
        border-color: #cbd5e1;
    }

    .badge-class-pplg1 {
        background: #dbeafe;
        color: #1e3a8a;
        border-color: #bfdbfe;
    }

    .badge-class-pplg2 {
        background: #dcfce7;
        color: #166534;
        border-color: #bbf7d0;
    }

    .badge-class-pplg3 {
        background: #fef3c7;
        color: #92400e;
        border-color: #fde68a;
    }
    
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
                    <a class="nav-link" href="{{ route('admin.results') }}">Hasil Asesmen</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.questions') }}">Kelola Soal</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('admin.students') }}">Kelola Siswa</a>
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

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0"><i class="fas fa-file-import"></i> Import Data Siswa</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Upload file Excel untuk meregistrasi siswa massal. Semua nama kelas baru akan otomatis dibuatkan.</p>
                    <a href="{{ route('admin.students.template') }}" class="btn btn-sm btn-outline-primary mb-3">Download Template Excel</a>
                    
                    <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <input class="form-control" type="file" name="excel_file" required accept=".xlsx">
                        </div>
                        <button class="btn btn-primary w-100">Upload & Import</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4 p-3">
                <form method="GET" action="{{ route('admin.students') }}" class="row align-items-end g-3">
                    <div class="col-md-8">
                        <label class="form-label">Filter Kelas</label>
                        <select name="class_id" class="form-select">
                            <option value="">-- Semua Kelas --</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Terapkan</button>
                        <a href="{{ route('admin.students') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>

            <div class="card">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0"><i class="fas fa-users"></i> Daftar Siswa Terdaftar</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Kelas</th>
                                <th>Nama Lengkap</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $index => $student)
                            @php
                                $classNameNormalized = strtolower(trim((string) optional($student->studentClass)->class_name));
                                $classBadgeClass = match ($classNameNormalized) {
                                    '11 pplg 1' => 'badge-class-pplg1',
                                    '11 pplg 2' => 'badge-class-pplg2',
                                    '11 pplg 3' => 'badge-class-pplg3',
                                    default => '',
                                };
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><span class="badge-class-soft {{ $classBadgeClass }}">{{ $student->studentClass->class_name }}</span></td>
                                <td>{{ $student->full_name }}</td>
                                <td>
                                    <form action="{{ route('admin.students.delete', $student->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus siswa ini? Data hasil assessmentnya juga akan terhapus.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">Belum ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
