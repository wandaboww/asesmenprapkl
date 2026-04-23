@extends('layouts.admin')

@section('page_title', 'Hasil Assessment Siswa')

@section('styles')
<style>
    .filter-card {
        border-radius: 24px;
        border: none;
        box-shadow: 0 4px 25px rgba(0,0,0,0.03);
        background: #fff;
        padding: 30px;
        margin-bottom: 30px;
    }

    .table-card {
        border-radius: 24px;
        border: none;
        box-shadow: 0 4px 25px rgba(0,0,0,0.03);
        background: #fff;
        overflow: hidden;
    }

    .badge-soft {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 10px;
        padding: 0.5rem 0.9rem;
        font-size: 0.75rem;
        font-weight: 700;
        line-height: 1;
        border: 1px solid transparent;
    }

    .badge-soft-class { background: #eef2ff; color: #4f46e5; border-color: #e0e7ff; }
    .badge-soft-status-done { background: #ecfdf5; color: #10b981; border-color: #d1fae5; }
    .badge-soft-status-wait { background: #f8fafc; color: #64748b; border-color: #f1f5f9; }
    .badge-soft-industry { background: #fff7ed; color: #f59e0b; border-color: #ffedd5; }
    .badge-soft-competency { background: #f0f9ff; color: #0ea5e9; border-color: #e0f2fe; }
    .badge-soft-competency-alt { background: #fdf2f8; color: #db2777; border-color: #fce7f3; }
    
    .badge-batch-tone-1 { background: #f5f3ff; color: #7c3aed; border-color: #ede9fe; }
    .badge-batch-tone-2 { background: #f0fdf4; color: #16a34a; border-color: #dcfce7; }
    .badge-batch-tone-3 { background: #fff1f2; color: #e11d48; border-color: #ffe4e6; }
    .badge-batch-tone-4 { background: #f0f9ff; color: #0284c7; border-color: #e0f2fe; }

    .detail-table thead th {
        background: #f8fafc;
        padding: 20px;
        font-weight: 700;
        color: #64748b;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-top: none;
    }

    .detail-table tbody td {
        padding: 20px;
        vertical-align: middle;
        color: #1e293b;
        font-weight: 500;
    }

    .student-avatar {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #475569;
        font-size: 0.9rem;
    }

    .score-text {
        font-weight: 800;
        color: var(--primary-color);
        font-size: 1rem;
    }

    .action-wrap {
        display: flex;
        gap: 8px;
    }

    .result-meta-card {
        background: #f8fafc;
        border-radius: 16px;
        padding: 15px;
        border: 1px solid #f1f5f9;
        height: 100%;
    }

    .score-progress {
        height: 8px;
        border-radius: 10px;
        background: #f1f5f9;
        overflow: hidden;
        margin-top: 8px;
    }

    .score-progress .progress-bar {
        background: var(--primary-gradient);
        border-radius: 10px;
    }

    .recommendation-panel {
        background: #fff;
        border-radius: 20px;
        padding: 20px;
        border: 1px solid #eef2ff;
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.05);
    }

    .btn-premium-sm {
        padding: 8px 16px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.8rem;
        transition: all 0.3s;
    }

</style>
@endsection

@section('content')
<div class="container-fluid">
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="filter-card">
        <form method="GET" action="{{ route('admin.results') }}" class="row g-4">
            <div class="col-lg-3 col-md-6">
                <label class="form-label fw-bold text-muted small uppercase mb-2">Filter Kelas</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-school text-muted"></i></span>
                    <select name="class_id" class="form-select bg-light border-start-0">
                        <option value="">Semua Kelas</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->class_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label fw-bold text-muted small uppercase mb-2">Status Asesmen</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-check-circle text-muted"></i></span>
                    <select name="status" class="form-select bg-light border-start-0">
                        <option value="">Semua Status</option>
                        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai Mengerjakan</option>
                        <option value="belum" {{ request('status') == 'belum' ? 'selected' : '' }}>Belum Mengerjakan</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label fw-bold text-muted small uppercase mb-2">Batch Asesmen</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-layer-group text-muted"></i></span>
                    <select name="batch_id" class="form-select bg-light border-start-0">
                        @foreach($batches as $batch)
                            <option value="{{ $batch->id }}" {{ $selectedBatchId == $batch->id ? 'selected' : '' }}>
                                {{ $batch->batch_name }}{{ $batch->is_active ? ' (Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-premium flex-grow-1"><i class="fas fa-filter me-2"></i> Filter</button>
                <a href="{{ route('admin.results') }}" class="btn btn-light border p-2 px-3" title="Reset"><i class="fas fa-sync-alt"></i></a>
            </div>
        </form>
    </div>

    @if($isBatchTwoView)
        <div class="alert alert-info">
            <strong>Mode Ranking Batch 2:</strong> Peringkat dihitung per bidang rekomendasi Batch 1. Siswa hanya dibandingkan dengan siswa pada bidang yang sama.
        </div>
    @endif

    <div class="table-card">
        <div class="card-header bg-white border-bottom py-4 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="mb-1 fw-bold text-dark"><i class="fas fa-users-cog me-2 text-primary"></i> Data Hasil Asesmen</h5>
                <p class="text-muted small mb-0">Menampilkan data siswa untuk <strong>{{ $selectedBatch ? $selectedBatch->batch_name : 'Semua Batch' }}</strong></p>
            </div>
            <a href="{{ route('admin.export', request()->only(['class_id', 'status', 'batch_id'])) }}" class="btn btn-success btn-premium-sm px-4">
                <i class="fas fa-file-excel me-2"></i> Export ke Excel
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 detail-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 60px;">No</th>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        @if($isBatchTwoView)
                            <th>Acuan B1</th>
                            <th>Rank B2</th>
                        @endif
                        <th>Status</th>
                        <th>Rekomendasi</th>
                        <th class="text-center">Skor</th>
                        <th class="text-center">Aksi</th>
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
                        
                        if ($rowBatch) {
                            $batchTone = (($rowBatch->id - 1) % 4) + 1;
                            $batchBadgeClass = 'badge-batch-tone-' . $batchTone;
                        }

                        $initials = collect(explode(' ', $student->full_name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('');
                    @endphp
                    <tr>
                        <td class="text-center text-muted fw-bold">{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="student-avatar me-3 {{ $batchBadgeClass }} bg-opacity-10 border border-current">
                                    {{ $initials }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $student->full_name }}</div>
                                    <div class="text-muted small"><span class="badge-soft {{ $batchBadgeClass }} py-0 px-1" style="font-size: 0.6rem;">{{ $rowBatch->batch_name ?? '-' }}</span></div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge-soft badge-soft-class">{{ $student->studentClass->class_name }}</span></td>
                        @if($isBatchTwoView)
                            <td>
                                @if($student->batch_one_recommendation_field)
                                    <span class="badge-soft badge-soft-competency">{{ $student->batch_one_recommendation_field }}</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                @if($student->batch_two_rank)
                                    <span class="badge-soft badge-soft-industry">#{{ $student->batch_two_rank }} <small class="ms-1">/{{ $student->batch_two_rank_total }}</small></span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        @endif
                        <td>
                            @if($submission)
                                <span class="badge-soft badge-soft-status-done"><i class="fas fa-check-circle"></i> Selesai</span>
                            @else
                                <span class="badge-soft badge-soft-status-wait"><i class="fas fa-clock"></i> Belum</span>
                            @endif
                        </td>
                        <td>
                            @if($industry)
                                <div class="d-flex flex-column gap-1">
                                    <span class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $industry->display_industry_name }}</span>
                                    <div class="d-flex flex-wrap gap-1">
                                        @if($student->recommendation_primary_label)
                                            <span class="badge-soft badge-soft-competency" style="padding: 0.1rem 0.4rem; font-size: 0.6rem;">{{ $student->recommendation_primary_label }}</span>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <span class="text-muted small">Belum ada hasil</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($recommendation)
                                <span class="score-text">{{ number_format($recommendation->score, 1) }}%</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                @if($submission)
                                    <button type="button" class="btn btn-sm btn-light border text-primary" data-bs-toggle="modal" data-bs-target="#resultModal{{ $submission->id }}" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <form action="{{ route('admin.students.reset', $student->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Reset hasil asesmen ini?');">
                                        @csrf
                                        <input type="hidden" name="batch_id" value="{{ $selectedBatchId }}">
                                        <button type="submit" class="btn btn-sm btn-light border text-danger" title="Reset Hasil">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $isBatchTwoView ? 10 : 8 }}" class="text-center py-5 text-muted">
                            <i class="fas fa-search d-block mb-3 fs-1 opacity-25"></i>
                            Tidak ada data siswa yang ditemukan.
                        </td>
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
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <div class="result-meta-card">
                                        <div class="text-muted small uppercase fw-bold mb-1">Nama Siswa</div>
                                        <div class="fw-bold text-dark">{{ $student->full_name }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="result-meta-card">
                                        <div class="text-muted small uppercase fw-bold mb-1">Kelas</div>
                                        <div class="fw-bold text-dark">{{ $student->studentClass->class_name }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="result-meta-card">
                                        <div class="text-muted small uppercase fw-bold mb-1">Batch</div>
                                        <div class="fw-bold text-dark">{{ optional($submission->batch)->batch_name ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="result-meta-card">
                                        <div class="text-muted small uppercase fw-bold mb-1">Status</div>
                                        <span class="badge-soft badge-soft-status-done">Selesai</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="result-meta-card">
                                        <div class="text-muted small uppercase fw-bold mb-1">Skor Rekomendasi</div>
                                        <div class="score-text">{{ $recommendation ? number_format($recommendation->score, 1) . '%' : '-' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="result-meta-card">
                                        <div class="text-muted small uppercase fw-bold mb-1">Tanggal Submit</div>
                                        <div class="fw-bold text-dark small">{{ optional($submission->submitted_at)->format('d M Y, H:i') ?? '-' }}</div>
                                    </div>
                                </div>

                                @if($isBatchTwoView)
                                    <div class="col-md-6">
                                        <div class="result-meta-card">
                                            <div class="text-muted small uppercase fw-bold mb-1">Bidang Acuan Batch 1</div>
                                            <div class="fw-bold text-dark">{{ $student->batch_one_recommendation_field ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="result-meta-card">
                                            <div class="text-muted small uppercase fw-bold mb-1">Peringkat Batch 2</div>
                                            <div class="fw-bold text-dark">
                                                @if($student->batch_two_rank)
                                                    <span class="badge-soft badge-soft-industry">#{{ $student->batch_two_rank }} <small class="ms-1">/{{ $student->batch_two_rank_total }}</small></span>
                                                @else
                                                    -
                                                @endif
                                            </div>
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
