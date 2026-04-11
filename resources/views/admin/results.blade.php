@extends('layouts.app')

@section('styles')
<style>
    body { background: #f4f6f9; }
    .navbar { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); padding: 15px 0; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .navbar-brand { font-weight: 700; font-size: 1.4rem; }
    .nav-link { font-weight: 500; transition: 0.3s; }
    .nav-link:hover { transform: translateY(-2px); color: #fff !important; }

    .badge-soft {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 0.35rem 0.65rem;
        font-size: 0.74rem;
        font-weight: 700;
        border: 1px solid transparent;
        line-height: 1;
    }

    .badge-soft-class {
        background: #e0f2fe;
        color: #0c4a6e;
        border-color: #bae6fd;
    }

    .badge-soft-class-pplg1 {
        background: #dbeafe;
        color: #1e3a8a;
        border-color: #bfdbfe;
    }

    .badge-soft-class-pplg2 {
        background: #dcfce7;
        color: #166534;
        border-color: #bbf7d0;
    }

    .badge-soft-class-pplg3 {
        background: #fef3c7;
        color: #92400e;
        border-color: #fde68a;
    }

    .badge-soft-status-done {
        background: #dcfce7;
        color: #166534;
        border-color: #bbf7d0;
    }

    .badge-soft-status-wait {
        background: #f1f5f9;
        color: #475569;
        border-color: #e2e8f0;
    }

    .badge-soft-industry {
        background: #dbeafe;
        color: #1d4ed8;
        border-color: #bfdbfe;
        justify-content: flex-start;
    }

    .badge-soft-competency {
        background: #ecfeff;
        color: #0f766e;
        border-color: #bae6fd;
    }

    .badge-soft-competency-alt {
        background: #fef3c7;
        color: #92400e;
        border-color: #fde68a;
    }

    .badge-batch-tone-1 {
        background: #e8f5e9;
        color: #1b5e20;
        border-color: #c8e6c9;
    }

    .badge-batch-tone-2 {
        background: #e3f2fd;
        color: #0d47a1;
        border-color: #bbdefb;
    }

    .badge-batch-tone-3 {
        background: #fff3e0;
        color: #bf360c;
        border-color: #ffe0b2;
    }

    .badge-batch-tone-4 {
        background: #f3e5f5;
        color: #6a1b9a;
        border-color: #e1bee7;
    }

    .badge-batch-default {
        background: #eceff1;
        color: #37474f;
        border-color: #cfd8dc;
    }

    .score-text {
        color: #0f766e;
        font-weight: 700;
    }

    .result-meta-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px;
        background: #f8fafc;
    }

    .score-progress {
        height: 8px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }

    .score-progress .progress-bar {
        background: linear-gradient(90deg, #2563eb 0%, #0ea5e9 100%);
    }

    .recommendation-panel {
        border: 1px solid #dbeafe;
        background: #eff6ff;
        border-radius: 12px;
        padding: 14px;
    }

    .action-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
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
                    <a class="nav-link active" href="{{ route('admin.results') }}">Hasil Asesmen</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.questions') }}">Kelola Soal</a>
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
                <label class="form-label">Batch Asesmen</label>
                <select name="batch_id" class="form-select">
                    @foreach($batches as $batch)
                        <option value="{{ $batch->id }}" {{ $selectedBatchId == $batch->id ? 'selected' : '' }}>
                            {{ $batch->batch_name }}{{ $batch->is_active ? ' (Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Terapkan Filter</button>
                <a href="{{ route('admin.results') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    @if($isBatchTwoView)
        <div class="alert alert-info">
            <strong>Mode Ranking Batch 2:</strong> Peringkat dihitung per bidang rekomendasi Batch 1. Siswa hanya dibandingkan dengan siswa pada bidang yang sama.
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-chart-table"></i> Hasil Assessment Siswa - {{ $selectedBatch ? $selectedBatch->batch_name : 'Tanpa Batch' }}</h5>
            <a href="{{ route('admin.export', request()->only(['class_id', 'status', 'batch_id'])) }}" class="btn btn-success btn-sm">
                <i class="fas fa-download"></i> Export Excel
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Batch</th>
                        <th>Kelas</th>
                        <th>Nama Lengkap</th>
                        @if($isBatchTwoView)
                            <th>Bidang Acuan Batch 1</th>
                            <th>Peringkat Batch 2</th>
                        @endif
                        <th>Status</th>
                        <th>Rekomendasi Industri</th>
                        <th>Skor</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $index => $student)
                    @php
                        $submission = $student->selected_submission;
                        $recommendation = $submission ? $submission->recommendation : null;
                        $industry = $recommendation ? $recommendation->industry : null;
                        $rowBatch = $submission && $submission->batch ? $submission->batch : $selectedBatch;
                        $batchBadgeClass = 'badge-batch-default';
                        $classNameNormalized = strtolower(trim((string) optional($student->studentClass)->class_name));
                        $classBadgeClass = match ($classNameNormalized) {
                            '11 pplg 1' => 'badge-soft-class-pplg1',
                            '11 pplg 2' => 'badge-soft-class-pplg2',
                            '11 pplg 3' => 'badge-soft-class-pplg3',
                            default => 'badge-soft-class',
                        };

                        if ($rowBatch) {
                            $batchTone = (($rowBatch->id - 1) % 4) + 1;
                            $batchBadgeClass = 'badge-batch-tone-' . $batchTone;
                        }
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            @if($rowBatch)
                                <span class="badge-soft {{ $batchBadgeClass }}">{{ $rowBatch->batch_name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td><span class="badge-soft {{ $classBadgeClass }}">{{ $student->studentClass->class_name }}</span></td>
                        <td>{{ $student->full_name }}</td>
                        @if($isBatchTwoView)
                            <td>
                                @if($student->batch_one_recommendation_field)
                                    <span class="badge-soft badge-soft-competency">{{ $student->batch_one_recommendation_field }}</span>
                                @else
                                    <span class="text-muted">Belum ada hasil Batch 1</span>
                                @endif
                            </td>
                            <td>
                                @if($student->batch_two_rank)
                                    <span class="badge-soft badge-soft-industry">#{{ $student->batch_two_rank }} / {{ $student->batch_two_rank_total }}</span>
                                @elseif($submission)
                                    <span class="text-muted">Tidak dapat diranking</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        @endif
                        <td>
                            @if($submission)
                                <span class="badge-soft badge-soft-status-done">Selesai</span>
                            @else
                                <span class="badge-soft badge-soft-status-wait">Belum</span>
                            @endif
                        </td>
                        <td>
                            @if($industry)
                                <div class="d-flex flex-column gap-1">
                                    <span class="badge-soft badge-soft-industry">{{ $industry->display_industry_name }}</span>
                                    <div class="d-flex flex-wrap gap-1">
                                        @if($student->recommendation_primary_label)
                                            <span class="badge-soft badge-soft-competency">{{ $student->recommendation_primary_label }}</span>
                                        @endif
                                        @if($student->recommendation_secondary_label && $student->recommendation_secondary_label !== $student->recommendation_primary_label)
                                            <span class="badge-soft badge-soft-competency-alt">{{ $student->recommendation_secondary_label }}</span>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($recommendation)
                                <span class="score-text">{{ number_format($recommendation->score, 1) }}%</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($submission)
                                <div class="action-wrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#resultModal{{ $submission->id }}">
                                        <i class="fas fa-eye"></i> Lihat Hasil
                                    </button>

                                    <form action="{{ route('admin.students.reset', $student->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus hasil asesmen siswa ini? Siswa ini harus mengulang kembali ujiannya dari awal.');">
                                        @csrf
                                        <input type="hidden" name="batch_id" value="{{ $selectedBatchId }}">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-undo"></i> Reset Hasil</button>
                                    </form>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $isBatchTwoView ? 10 : 8 }}" class="text-center py-4">Belum ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach($students as $student)
        @php
            $submission = $student->selected_submission;
            $recommendation = $submission ? $submission->recommendation : null;
            $industry = $recommendation ? $recommendation->industry : null;
            $sortedScores = collect($student->category_scores ?? [])->sortByDesc('percentage')->values();
        @endphp

        @if($submission)
            <div class="modal fade" id="resultModal{{ $submission->id }}" tabindex="-1" aria-labelledby="resultModalLabel{{ $submission->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title" id="resultModalLabel{{ $submission->id }}">
                                <i class="fas fa-file-alt text-primary"></i> Detail Hasil Asesmen - {{ $student->full_name }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <div class="result-meta-card">
                                        <small class="text-muted d-block">Nama Siswa</small>
                                        <strong>{{ $student->full_name }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="result-meta-card">
                                        <small class="text-muted d-block">Kelas</small>
                                        <strong>{{ $student->studentClass->class_name }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="result-meta-card">
                                        <small class="text-muted d-block">Batch</small>
                                        <strong>{{ optional($submission->batch)->batch_name ?? '-' }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="result-meta-card">
                                        <small class="text-muted d-block">Status</small>
                                        <span class="badge-soft badge-soft-status-done">Selesai</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="result-meta-card">
                                        <small class="text-muted d-block">Skor Rekomendasi</small>
                                        <strong class="score-text">{{ $recommendation ? number_format($recommendation->score, 1) . '%' : '-' }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="result-meta-card">
                                        <small class="text-muted d-block">Tanggal Submit</small>
                                        <strong>{{ optional($submission->submitted_at)->format('d/m/Y H:i') ?? '-' }}</strong>
                                    </div>
                                </div>

                                @if($isBatchTwoView)
                                    <div class="col-md-4">
                                        <div class="result-meta-card">
                                            <small class="text-muted d-block">Bidang Acuan Batch 1</small>
                                            <strong>{{ $student->batch_one_recommendation_field ?? '-' }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="result-meta-card">
                                            <small class="text-muted d-block">Peringkat Batch 2</small>
                                            <strong>
                                                @if($student->batch_two_rank)
                                                    #{{ $student->batch_two_rank }} / {{ $student->batch_two_rank_total }}
                                                @else
                                                    -
                                                @endif
                                            </strong>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="card border-0 bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="mb-3"><i class="fas fa-chart-pie text-primary"></i> Skor Per Kategori Kompetensi</h6>

                                    @if($sortedScores->isNotEmpty())
                                        @foreach($sortedScores as $score)
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span>{{ $score['name'] ?? '-' }}</span>
                                                    <strong>{{ number_format((float) ($score['percentage'] ?? 0), 1) }}%</strong>
                                                </div>
                                                <div class="score-progress">
                                                    <div class="progress-bar" role="progressbar" style="width: {{ max(0, min(100, (float) ($score['percentage'] ?? 0))) }}%"></div>
                                                </div>
                                                <small class="text-muted">Skor {{ number_format((float) ($score['obtained'] ?? 0), 1) }} / {{ number_format((float) ($score['max'] ?? 0), 1) }}</small>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted mb-0">Belum ada data skor kategori kompetensi.</p>
                                    @endif
                                </div>
                            </div>

                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="mb-3"><i class="fas fa-briefcase text-primary"></i> Rekomendasi Industri</h6>

                                    @if($industry)
                                        <div class="recommendation-panel">
                                            <h6 class="mb-2">{{ $industry->display_industry_name }}</h6>

                                            @if($industry->display_industry_description)
                                                <p class="mb-2 text-muted">{{ $industry->display_industry_description }}</p>
                                            @endif

                                            <div class="d-flex flex-wrap gap-1 mb-2">
                                                @if($student->recommendation_primary_label)
                                                    <span class="badge-soft badge-soft-competency">{{ $student->recommendation_primary_label }}</span>
                                                @endif
                                                @if($student->recommendation_secondary_label && $student->recommendation_secondary_label !== $student->recommendation_primary_label)
                                                    <span class="badge-soft badge-soft-competency-alt">{{ $student->recommendation_secondary_label }}</span>
                                                @endif
                                            </div>

                                            @if($student->dominant_primary_label)
                                                <small class="text-muted d-block">
                                                    Kompetensi dominan siswa: {{ $student->dominant_primary_label }}{{ $student->dominant_secondary_label ? ' dan ' . $student->dominant_secondary_label : '' }}.
                                                </small>
                                            @endif
                                        </div>
                                    @else
                                        <p class="text-muted mb-0">Belum ada rekomendasi industri untuk submission ini.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</div>
@endsection
