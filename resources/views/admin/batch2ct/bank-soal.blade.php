@extends('layouts.admin')

@section('page_title', 'Bank Soal Batch 2 CT')

@section('styles')
<style>
    .table-card {
        border-radius: 24px;
        border: none;
        box-shadow: 0 4px 25px rgba(0,0,0,0.03);
        background: #fff;
        overflow: hidden;
    }

    .option-chip {
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 12px 18px;
        margin-bottom: 10px;
        background: #f8fafc;
        font-size: 0.85rem;
        transition: all 0.2s;
    }

    .option-chip:hover {
        border-color: var(--primary-color);
        background: #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .legend-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 6px;
    }

    .badge-soft {
        display: inline-flex;
        align-items: center;
        border-radius: 10px;
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .badge-soft-class { background-color: #eef2ff; color: #4f46e5; border: 1px solid #e0e7ff; }
    .badge-soft-industry { background-color: #f8fafc; color: #475569; border: 1px solid #e2e8f0; }

    .table-scroll-container {
        max-height: 800px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: var(--primary-color) #f1f5f9;
    }

    .table-scroll-container::-webkit-scrollbar {
        width: 6px;
    }

    .table-scroll-container::-webkit-scrollbar-track {
        background: #f1f5f9;
    }

    .table-scroll-container::-webkit-scrollbar-thumb {
        background-color: var(--primary-color);
        border-radius: 20px;
    }

    .table-scroll-container thead {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #fff;
    }
    
    .detail-table thead th {
        background: #f8fafc;
        padding: 18px 20px;
        font-weight: 700;
        color: #64748b;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-top: none;
    }

    .detail-table tbody td {
        padding: 18px 20px;
        vertical-align: middle;
        color: #1e293b;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .card-header {
            padding: 15px !important;
        }
        .p-4 {
            padding: 15px !important;
        }
        .detail-table thead th, .detail-table tbody td {
            padding: 12px 10px;
            font-size: 0.8rem;
        }
        .option-chip {
            padding: 10px;
            font-size: 0.75rem;
        }
        .badge-soft {
            font-size: 0.7rem;
            padding: 4px 8px;
        }
        .btn-sm {
            padding: 4px 8px;
            font-size: 0.75rem;
        }
        /* Hide ID and Metadata on mobile to save space */
        .detail-table th:nth-child(1), .detail-table td:nth-child(1),
        .detail-table th:nth-child(3), .detail-table td:nth-child(3) {
            display: none;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-card mb-5">
        <div class="card-header bg-white border-bottom py-4 px-4 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1 fw-bold text-dark"><i class="fas fa-database me-2 text-primary"></i> Bank Soal Batch 2 CT</h5>
                <p class="text-muted small mb-0">Koleksi soal Computational Thinking yang terdaftar di sistem.</p>
            </div>
            @if($questions->count() > 0)
            <form method="POST" action="{{ route('admin.batch2ct.questions.delete-all') }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus SEMUA soal? Tindakan ini tidak dapat dibatalkan.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash-alt me-1"></i> Hapus Semua Soal
                </button>
            </form>
            @endif
        </div>
        <div class="p-4 border-bottom bg-light bg-opacity-50">
            <form method="GET" action="{{ route('admin.batch2ct.bank-soal') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">FILTER JENIS CT</label>
                    <select class="form-select bg-white" name="jenis_ct">
                        <option value="">-- Semua Jenis CT --</option>
                        @foreach($ctTypes as $type)
                            <option value="{{ $type }}" {{ $selectedJenisCt === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">FILTER KESULITAN</label>
                    <select class="form-select bg-white" name="level_kesulitan">
                        <option value="">-- Semua Level --</option>
                        @foreach($difficultyLevels as $level)
                            <option value="{{ $level }}" {{ $selectedDifficulty === $level ? 'selected' : '' }}>{{ strtoupper($level) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-filter me-2"></i> Filter</button>
                    <a href="{{ route('admin.batch2ct.bank-soal') }}" class="btn btn-outline-secondary px-3" title="Reset"><i class="fas fa-sync-alt"></i></a>
                </div>
            </form>
        </div>
        <div class="table-responsive table-scroll-container">
            <table class="table table-hover mb-0 detail-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 70px;">ID</th>
                        <th>Narasi Soal & Opsi Jawaban</th>
                        <th>Metadata</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($questions as $question)
                        <tr>
                            <td class="text-center text-muted fw-bold">{{ $question->id }}</td>
                            <td>
                                <div class="mb-3 text-dark fw-500" style="line-height: 1.6;">{{ $question->narasi_soal }}</div>
                                <div class="row g-2">
                                    @foreach($question->options as $option)
                                        <div class="col-12">
                                            <div class="option-chip mb-0">
                                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                    <div>
                                                        <span class="badge bg-primary me-2">{{ $option->label_opsi }}</span>
                                                        <span class="text-dark">{{ $option->teks_opsi }}</span>
                                                    </div>
                                                    <div class="d-flex gap-3">
                                                        <span class="small"><span class="legend-dot" style="background:#4f46e5;"></span><span class="text-muted">W:</span> <strong>{{ $option->bobot_web }}</strong></span>
                                                        <span class="small"><span class="legend-dot" style="background:#10b981;"></span><span class="text-muted">M:</span> <strong>{{ $option->bobot_marketing }}</strong></span>
                                                        <span class="small"><span class="legend-dot" style="background:#ef4444;"></span><span class="text-muted">A:</span> <strong>{{ $option->bobot_admin }}</strong></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td style="min-width: 180px;">
                                <div class="mb-2">
                                    <span class="badge-soft badge-soft-class w-100 mb-1 d-flex justify-content-center">{{ $question->jenis_ct }}</span>
                                    <span class="badge-soft badge-soft-industry w-100 d-flex justify-content-center">{{ strtoupper($question->level_kesulitan) }}</span>
                                </div>
                                @if($question->is_active)
                                    <span class="badge bg-success w-100 py-2"><i class="fas fa-check-circle me-1"></i> Aktif</span>
                                @else
                                    <span class="badge bg-secondary w-100 py-2"><i class="fas fa-times-circle me-1"></i> Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-2">
                                    <a href="{{ route('admin.batch2ct.questions.edit', $question->id) }}" class="btn btn-sm btn-light border text-primary">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.batch2ct.questions.delete', $question->id) }}" onsubmit="return confirm('Hapus soal ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-light border text-danger w-100">
                                            <i class="fas fa-trash-alt me-1"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open d-block mb-3 fs-1 opacity-25"></i>
                                Belum ada soal yang tersedia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
