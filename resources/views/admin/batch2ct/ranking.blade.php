@extends('layouts.admin')

@section('page_title', 'Ranking Bidang Batch 2 CT')

@section('styles')
<style>
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
        border-radius: 10px;
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .bg-indigo-soft { background-color: #eef2ff; }
    .bg-success-soft { background-color: #f0fdf4; }
    .bg-danger-soft { background-color: #fef2f2; }

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

    .ranking-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.8rem;
    }

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

    .ranking-accordion .accordion-item {
        border: none;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 25px rgba(0,0,0,0.03);
        margin-bottom: 16px;
        background: #fff;
    }

    .ranking-accordion .accordion-button {
        border: none;
        box-shadow: none;
        font-weight: 800;
        color: #1e293b;
        padding: 16px 18px;
        background: #ffffff;
    }

    .ranking-accordion .accordion-button:not(.collapsed) {
        color: #1e293b;
        background: #ffffff;
    }

    .ranking-accordion .accordion-button:focus {
        box-shadow: none;
    }

    .ranking-accordion .accordion-collapse,
    .ranking-accordion .collapsing {
        transition: height 0.45s ease-in-out !important;
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

    .ranking-table thead th {
        background: #f8fafc;
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
    }

    .ranking-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.85rem;
        vertical-align: middle;
    }

    .student-name {
        max-width: none;
        white-space: normal;
        overflow: visible;
        text-overflow: unset;
        line-height: 1.3;
    }

    @media (max-width: 768px) {
        .ranking-table th, .ranking-table td {
            padding: 10px 8px;
            font-size: 0.75rem;
        }
        .rank-badge {
            width: 24px;
            height: 24px;
            font-size: 0.65rem;
        }
        .ranking-avatar {
            width: 24px;
            height: 24px;
            font-size: 0.65rem;
        }
        .btn-reset {
            padding: 4px 6px;
            font-size: 0.65rem;
        }
        .search-input {
            width: 100%;
            margin-top: 10px;
        }

        .student-name {
            max-width: none;
            line-height: 1.25;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="ranking-accordion accordion mb-5" id="rankingAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingWeb">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseWeb" aria-expanded="true" aria-controls="collapseWeb">
                    <span class="d-flex align-items-center gap-2">
                        <span class="p-2 rounded-3 bg-primary bg-opacity-10 text-primary"><i class="fas fa-code"></i></span>
                        Top Web Programming
                    </span>
                </button>
            </h2>
            <div id="collapseWeb" class="accordion-collapse collapse show" aria-labelledby="headingWeb" data-bs-parent="#rankingAccordion">
                <div class="accordion-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 ranking-table">
                            <thead>
                                <tr>
                                    <th class="ps-3" style="width: 60px;">#</th>
                                    <th>Siswa</th>
                                    <th class="pe-3 text-end">Skor Bidang</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rankingWeb as $idx => $item)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="rank-badge {{ $idx < 3 ? 'rank-'.($idx+1) : 'rank-n' }}">
                                                {{ $idx + 1 }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="ranking-avatar">
                                                    {{ substr(optional($item->student)->full_name ?? 'S', 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="text-dark fw-600 student-name">{{ optional($item->student)->full_name ?? '-' }}</div>
                                                    <small class="text-muted">{{ optional(optional($item->student)->studentClass)->class_name ?? '-' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="pe-3 text-end">
                                            <span class="badge bg-indigo-soft text-primary fw-bold">{{ $item->total_web }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-4 text-muted small">Belum ada data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingMarketing">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMarketing" aria-expanded="false" aria-controls="collapseMarketing">
                    <span class="d-flex align-items-center gap-2">
                        <span class="p-2 rounded-3 bg-success bg-opacity-10 text-success"><i class="fas fa-bullhorn"></i></span>
                        Top Digital Marketing
                    </span>
                </button>
            </h2>
            <div id="collapseMarketing" class="accordion-collapse collapse" aria-labelledby="headingMarketing" data-bs-parent="#rankingAccordion">
                <div class="accordion-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 ranking-table">
                            <thead>
                                <tr>
                                    <th class="ps-3" style="width: 60px;">#</th>
                                    <th>Siswa</th>
                                    <th class="pe-3 text-end">Skor Bidang</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rankingMarketing as $idx => $item)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="rank-badge {{ $idx < 3 ? 'rank-'.($idx+1) : 'rank-n' }}">
                                                {{ $idx + 1 }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="ranking-avatar">
                                                    {{ substr(optional($item->student)->full_name ?? 'S', 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="text-dark fw-600 student-name">{{ optional($item->student)->full_name ?? '-' }}</div>
                                                    <small class="text-muted">{{ optional(optional($item->student)->studentClass)->class_name ?? '-' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="pe-3 text-end">
                                            <span class="badge bg-success-soft text-success fw-bold">{{ $item->total_marketing }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-4 text-muted small">Belum ada data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingAdmin">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdmin" aria-expanded="false" aria-controls="collapseAdmin">
                    <span class="d-flex align-items-center gap-2">
                        <span class="p-2 rounded-3 bg-danger bg-opacity-10 text-danger"><i class="fas fa-file-invoice"></i></span>
                        Top Administratif
                    </span>
                </button>
            </h2>
            <div id="collapseAdmin" class="accordion-collapse collapse" aria-labelledby="headingAdmin" data-bs-parent="#rankingAccordion">
                <div class="accordion-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 ranking-table">
                            <thead>
                                <tr>
                                    <th class="ps-3" style="width: 60px;">#</th>
                                    <th>Siswa</th>
                                    <th class="pe-3 text-end">Skor Bidang</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rankingAdmin as $idx => $item)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="rank-badge {{ $idx < 3 ? 'rank-'.($idx+1) : 'rank-n' }}">
                                                {{ $idx + 1 }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="ranking-avatar">
                                                    {{ substr(optional($item->student)->full_name ?? 'S', 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="text-dark fw-600 student-name">{{ optional($item->student)->full_name ?? '-' }}</div>
                                                    <small class="text-muted">{{ optional(optional($item->student)->studentClass)->class_name ?? '-' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="pe-3 text-end">
                                            <span class="badge bg-danger-soft text-danger fw-bold">{{ $item->total_admin }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-4 text-muted small">Belum ada data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@endsection
