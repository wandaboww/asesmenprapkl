@extends('layouts.admin')

@section('page_title', 'Ranking Lengkap Batch 2 CT')

@section('styles')
<style>
    .table-card {
        border-radius: 24px;
        border: none;
        box-shadow: 0 4px 25px rgba(0,0,0,0.03);
        background: #fff;
        overflow: hidden;
    }

    .rank-badge {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 800;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .rank-1 { background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%); color: #fff; }
    .rank-2 { background: linear-gradient(135deg, #94a3b8 0%, #475569 100%); color: #fff; }
    .rank-3 { background: linear-gradient(135deg, #d97706 0%, #92400e 100%); color: #fff; }
    .rank-n { background: #f1f5f9; color: #64748b; }

    .search-input {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #1e293b;
        border-radius: 10px;
        padding: 8px 14px;
        font-size: 0.85rem;
        transition: border 0.2s;
    }
    .search-input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
    }

    .btn-reset {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.3);
        color: #ef4444;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 8px;
        transition: all 0.2s;
        cursor: pointer;
    }
    .btn-reset:hover {
        background: #ef4444;
        color: #fff;
        border-color: #ef4444;
    }

    .scroll-wrap {
        max-height: 700px;
        overflow-y: auto;
    }
    .scroll-wrap::-webkit-scrollbar { width: 6px; }
    .scroll-wrap::-webkit-scrollbar-track { background: transparent; }
    .scroll-wrap::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }

    .full-table th {
        background: #f8fafc;
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 15px 16px;
        position: sticky;
        top: 0;
        z-index: 2;
        border-bottom: 1px solid #e2e8f0;
    }
    .full-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.85rem;
        vertical-align: middle;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-card mb-5">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-list-ol text-primary me-2"></i>Ranking Lengkap Batch 2 CT</h6>
                <input type="text" class="search-input" id="searchRanking" placeholder="Cari nama siswa...">
            </div>

            <form action="{{ route('admin.batch2ct.ranking-lengkap') }}" method="GET" class="d-flex flex-wrap gap-2 align-items-center bg-light p-3 rounded" id="filterForm">
                <div>
                    <select name="kelas" class="form-select form-select-sm" style="min-width: 150px; border-radius: 8px;">
                        <option value="">-- Semua Kelas --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ request('kelas') == $c->id ? 'selected' : '' }}>
                                {{ $c->class_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    {{-- FIX: $val sekarang adalah string key ('Web Programming', dll.)
                         karena $recommendations sudah associative array dari controller --}}
                    <select name="rekomendasi" class="form-select form-select-sm" style="min-width: 180px; border-radius: 8px;">
                        <option value="">-- Semua Rekomendasi --</option>
                        @foreach($recommendations as $val => $label)
                            <option value="{{ $val }}" {{ request('rekomendasi') === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-primary fw-bold px-3" style="border-radius: 8px;">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                @if(request()->hasAny(['kelas', 'rekomendasi']))
                <a href="{{ route('admin.batch2ct.ranking-lengkap') }}" class="btn btn-sm btn-secondary fw-bold px-3" style="border-radius: 8px;">
                    <i class="fas fa-sync me-1"></i> Reset
                </a>
                @endif
                <div class="ms-auto">
                    <button type="submit" name="export" value="excel" class="btn btn-sm btn-success fw-bold px-3" style="border-radius: 8px;">
                        <i class="fas fa-file-excel me-1"></i> Export Excel
                    </button>
                </div>
            </form>
        </div>
        <div class="scroll-wrap">
            <table class="table full-table table-hover mb-0" id="rankingTable">
                <thead>
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Nama Siswa</th>
                        <th class="d-none d-md-table-cell">Kelas</th>
                        <th class="text-center">W</th>
                        <th class="text-center">M</th>
                        <th class="text-center">A</th>
                        <th class="text-center">Total</th>
                        <th class="d-none d-md-table-cell">Rekomendasi</th>
                        <th class="pe-4 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allRanked as $idx => $r)
                    <tr>
                        <td class="ps-4">
                            <span class="rank-badge {{ $idx === 0 ? 'rank-1' : ($idx === 1 ? 'rank-2' : ($idx === 2 ? 'rank-3' : 'rank-n')) }}">
                                {{ $idx + 1 }}
                            </span>
                        </td>
                        <td class="fw-bold text-dark">{{ optional($r->student)->full_name ?? '-' }}</td>
                        <td class="text-muted d-none d-md-table-cell">{{ optional(optional($r->student)->studentClass)->class_name ?? '-' }}</td>
                        <td class="text-center text-primary fw-bold">{{ $r->total_web }}</td>
                        <td class="text-center text-success fw-bold">{{ $r->total_marketing }}</td>
                        <td class="text-center text-danger fw-bold">{{ $r->total_admin }}</td>
                        <td class="text-center text-warning fw-bold">{{ $r->total_combined }}</td>
                        <td class="d-none d-md-table-cell">
                            @php
                                $recColors = [
                                    'Web Programming'  => '#3b82f6',
                                    'Pemrograman'      => '#3b82f6',
                                    'Digital Marketing'=> '#10b981',
                                    'Administratif'    => '#ef4444',
                                    'Administrasi'     => '#ef4444',
                                ];
                                $rc = $recColors[$r->rekomendasi] ?? '#8b5cf6';
                                $displayRec = match($r->rekomendasi) {
                                    'Pemrograman'  => 'Web Programming',
                                    'Administrasi' => 'Administratif',
                                    default        => $r->rekomendasi,
                                };
                            @endphp
                            <span class="badge" style="background:{{ $rc }}22; color:{{ $rc }}; border:1px solid {{ $rc }}44">
                                {{ $displayRec ?? '-' }}
                            </span>
                        </td>
                        <td class="pe-4 text-end">
                            <button type="button" class="btn-reset"
                                data-bs-toggle="modal"
                                data-bs-target="#resetModal"
                                data-siswa-id="{{ $r->siswa_id }}"
                                data-siswa-name="{{ optional($r->student)->full_name ?? '-' }}">
                                <i class="fas fa-rotate-left me-1"></i>Reset
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">Belum ada data ranking.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- RESET MODAL --}}
<div class="modal fade" id="resetModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>Reset Hasil Asesmen
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.batch2ct.reset-result') }}">
                @csrf
                <input type="hidden" name="siswa_id" id="resetSiswaId">
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin <strong>menghapus seluruh hasil asesmen Batch 2 CT</strong> milik:</p>
                    <div class="bg-light border rounded-3 p-3 text-center my-3">
                        <span class="fw-bold fs-5 text-dark" id="resetSiswaName">—</span>
                    </div>
                    <p class="text-danger small mb-0">
                        <i class="fas fa-warning me-1"></i>Tindakan ini tidak dapat dibatalkan. Siswa perlu mengerjakan ulang asesmen.
                    </p>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger fw-bold">
                        <i class="fas fa-trash-alt me-1"></i>Reset Asesmen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // ── Live search by student name ──────────────────────────────────────────
    document.getElementById('searchRanking').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#rankingTable tbody tr').forEach(function (row) {
            const name = row.cells[1] ? row.cells[1].textContent.toLowerCase() : '';
            row.style.display = name.includes(q) ? '' : 'none';
        });
    });

    // ── Populate reset modal ─────────────────────────────────────────────────
    const resetModal = document.getElementById('resetModal');
    if (resetModal) {
        resetModal.addEventListener('show.bs.modal', function (e) {
            const btn = e.relatedTarget;
            document.getElementById('resetSiswaId').value   = btn.dataset.siswaId;
            document.getElementById('resetSiswaName').textContent = btn.dataset.siswaName;
        });
    }
</script>
@endsection
