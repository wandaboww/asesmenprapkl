@extends('layouts.admin')

@section('page_title', 'Kelola Data Siswa')

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

    .table-card {
        border-radius: 24px;
        border: none;
        box-shadow: 0 4px 25px rgba(0,0,0,0.03);
        background: #fff;
        overflow: hidden;
    }

    .search-input-group {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 15px;
        padding: 5px 15px;
        transition: all 0.3s;
    }

    .search-input-group:focus-within {
        background: #fff;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }

    .search-input-group input {
        background: transparent;
        border: none;
        padding: 10px 5px;
        font-size: 0.95rem;
        width: 100%;
        outline: none;
    }

    .student-avatar {
        width: 45px;
        height: 45px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
        background: var(--primary-gradient);
        color: #fff;
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2);
    }

    .badge-class {
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .bg-pplg1 { background: #eef2ff; color: #4f46e5; }
    .bg-pplg2 { background: #ecfdf5; color: #10b981; }
    .bg-pplg3 { background: #fff7ed; color: #f59e0b; }

    .btn-action {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #64748b;
    }

    .btn-action:hover {
        background: #fef2f2;
        color: #ef4444;
        border-color: #fecaca;
        transform: scale(1.05);
    }

    .import-zone {
        border: 2px dashed #e2e8f0;
        border-radius: 20px;
        padding: 30px;
        text-align: center;
        transition: all 0.3s;
        background: #f8fafc;
    }

    .import-zone:hover {
        border-color: var(--primary-color);
        background: rgba(79, 70, 229, 0.02);
    }

    .instruction-card {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: #fff;
        border-radius: 24px;
        padding: 30px;
        position: relative;
        overflow: hidden;
    }

    .instruction-card::after {
        content: '\f121';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        right: -20px;
        bottom: -20px;
        font-size: 8rem;
        opacity: 0.1;
        transform: rotate(-15deg);
    }

</style>
@endsection

@section('content')
<div class="container-fluid">
    
    <!-- Success & Error Alerts -->
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center">
            <i class="fas fa-check-circle me-3 fs-4"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center">
            <i class="fas fa-exclamation-circle me-3 fs-4"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <!-- Header Stats -->
    <div class="row mb-5">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card card-stat h-100">
                <div class="card-body">
                    <div class="icon-box" style="background: #eef2ff; color: #4f46e5;">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h6>Total Siswa Terdaftar</h6>
                    <h3>{{ number_format(count($students)) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card card-stat h-100">
                <div class="card-body">
                    <div class="icon-box" style="background: #ecfdf5; color: #10b981;">
                        <i class="fas fa-school"></i>
                    </div>
                    <h6>Total Kelas</h6>
                    <h3>{{ count($classes) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-12 mb-4">
            <div class="instruction-card h-100">
                <h5 class="fw-bold mb-3">Pusat Bantuan</h5>
                <p class="small opacity-75 mb-4">Gunakan fitur import massal untuk mendaftarkan siswa secara cepat. Pastikan format file sesuai dengan template yang disediakan.</p>
                <a href="{{ route('admin.students.template') }}" class="btn btn-light btn-sm rounded-pill px-4 fw-bold">
                    <i class="fas fa-download me-2"></i> Download Template
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Import Form -->
        <div class="col-lg-4 mb-5">
            <div class="table-card p-4 h-100">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-file-import fs-5"></i>
                    </div>
                    <h5 class="mb-0 fw-bold">Import Massal</h5>
                </div>
                
                <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="import-zone mb-4" id="dropZone">
                        <i class="fas fa-cloud-upload-alt fs-1 text-primary mb-3"></i>
                        <h6 class="fw-bold text-dark">Klik untuk pilih file</h6>
                        <p class="small text-muted mb-3">Format file yang didukung: .xlsx</p>
                        <input type="file" name="excel_file" id="fileInput" class="d-none" accept=".xlsx" required>
                        <div id="fileNameDisplay" class="badge bg-primary d-none py-2 px-3"></div>
                    </div>
                    <button type="submit" class="btn btn-premium w-100 py-3">
                        <i class="fas fa-check-circle me-2"></i> Proses & Registrasi
                    </button>
                </form>

                <div class="alert alert-light mt-4 border border-dashed py-3">
                    <small class="text-muted d-block mb-2"><i class="fas fa-info-circle me-1"></i> Catatan:</small>
                    <ul class="small text-muted mb-0 ps-3">
                        <li>Siswa dengan nama yang sama di kelas yang sama akan diperbarui datanya.</li>
                        <li>Pastikan kolom 'Nama Kelas' sesuai (misal: 11 PPLG 1).</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Students List -->
        <div class="col-lg-8 mb-5">
            <div class="table-card h-100">
                <div class="p-4 border-bottom bg-white d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-2 rounded-3 bg-indigo-soft text-primary">
                            <i class="fas fa-users fs-5"></i>
                        </div>
                        <h5 class="mb-0 fw-bold">Database Siswa</h5>
                    </div>
                    
                    <form method="GET" action="{{ route('admin.students') }}" class="d-flex gap-2">
                        <select name="class_id" class="form-select border-0 bg-light rounded-pill px-4" style="width: 180px;">
                            <option value="">Semua Kelas</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->class_name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="fas fa-filter"></i>
                        </button>
                        <a href="{{ route('admin.students') }}" class="btn btn-light rounded-pill px-4 border">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3" style="width: 80px;">NO</th>
                                <th class="py-3">SISWA</th>
                                <th class="py-3">KELAS</th>
                                <th class="py-3 text-center" style="width: 100px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $index => $student)
                            @php
                                $className = $student->studentClass->class_name;
                                $badgeClass = match(true) {
                                    str_contains($className, '1') => 'bg-pplg1',
                                    str_contains($className, '2') => 'bg-pplg2',
                                    str_contains($className, '3') => 'bg-pplg3',
                                    default => 'bg-light'
                                };
                            @endphp
                            <tr>
                                <td class="ps-4 fw-bold text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="student-avatar">
                                            {{ substr($student->full_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $student->full_name }}</div>
                                            <div class="small text-muted">ID: #{{ str_pad($student->id, 5, '0', STR_PAD_LEFT) }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-class {{ $badgeClass }}">
                                        <i class="fas fa-hashtag me-1 opacity-50"></i> {{ $student->studentClass->class_name }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('admin.students.delete', $student->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus siswa ini? Semua data asesmennya akan terhapus permanen.');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action" title="Hapus Siswa">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="fas fa-users-slash fs-1 text-muted opacity-25 mb-3"></i>
                                        <p class="text-muted fw-bold">Belum ada data siswa yang ditemukan.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($students instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="p-4 border-top">
                    {{ $students->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const fileNameDisplay = document.getElementById('fileNameDisplay');

        dropZone.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                fileNameDisplay.textContent = 'File terpilih: ' + this.files[0].name;
                fileNameDisplay.classList.remove('d-none');
            }
        });

        // Simple Drag and Drop feedback
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = 'var(--primary-color)';
            dropZone.style.background = 'rgba(79, 70, 229, 0.05)';
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.style.borderColor = '#e2e8f0';
            dropZone.style.background = '#f8fafc';
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = '#e2e8f0';
            dropZone.style.background = '#f8fafc';
            
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                fileNameDisplay.textContent = 'File siap diunggah: ' + e.dataTransfer.files[0].name;
                fileNameDisplay.classList.remove('d-none');
            }
        });
    });
</script>
@endsection
