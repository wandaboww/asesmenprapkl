@extends('layouts.admin')

@section('page_title', 'Kelola Soal Batch 2 CT')

@section('styles')
<style>
    .instruction-card {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: #ffffff;
        border-radius: 24px;
        padding: 35px;
        border: none;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(79, 70, 229, 0.2);
    }

    .instruction-card .guide-title {
        color: #ffffff;
        font-weight: 800;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .instruction-card .guide-text {
        color: rgba(255, 255, 255, 0.95);
        font-size: 1rem;
        line-height: 1.6;
    }

    .instruction-card ul {
        list-style: none;
        padding-left: 0;
    }

    .instruction-card ul li {
        position: relative;
        padding-left: 28px;
        margin-bottom: 12px;
        color: #ffffff;
        font-weight: 500;
    }

    .instruction-card ul li::before {
        content: '\f058';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        left: 0;
        top: 2px;
        color: #10b981;
        background: white;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: 1.1rem;
    }
    
    .btn-white-glass {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        transition: all 0.3s;
    }

    .btn-white-glass:hover {
        background: white;
        color: #4f46e5;
        transform: translateY(-2px);
    }

    .table-card {
        border-radius: 24px;
        border: none;
        box-shadow: 0 4px 25px rgba(0,0,0,0.03);
        background: #fff;
        overflow: hidden;
    }

    .form-section-title {
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .metric-table th {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        background: #f8fafc;
        padding: 12px 15px !important;
    }

    .metric-table td {
        padding: 12px 15px !important;
        vertical-align: middle;
    }

    .score-input {
        border-radius: 10px;
        text-align: center;
        font-weight: 700;
        border: 1px solid #e2e8f0;
    }

    @media (max-width: 768px) {
        .instruction-card {
            padding: 20px;
            margin-bottom: 20px;
        }
        .instruction-card .guide-title {
            font-size: 1.1rem;
        }
        .instruction-card .guide-text {
            font-size: 0.85rem;
        }
        .instruction-card ul li {
            font-size: 0.85rem;
            margin-bottom: 8px;
        }
        .table-card {
            padding: 15px !important;
        }
        .form-section-title {
            font-size: 1rem;
            margin-bottom: 15px;
        }
        .metric-table th, .metric-table td {
            padding: 8px 10px !important;
            font-size: 0.75rem;
        }
        .score-input {
            padding: 6px;
        }
        .btn-white-glass {
            width: 100%;
            margin-top: 10px;
        }
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

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="instruction-card">
        <div class="row align-items-center">
            <div class="col-md-9">
                <h4 class="guide-title mb-3"><i class="fas fa-info-circle me-2"></i> Panduan Pengelolaan Soal Batch 2 (CT)</h4>
                <p class="guide-text mb-4">Soal Batch 2 menggunakan metode <strong>Weighted Scoring</strong> untuk memetakan minat siswa ke 3 bidang utama: <strong>Web Programming (W)</strong>, <strong>Digital Marketing (M)</strong>, dan <strong>Administratif (A)</strong>.</p>
                <ul class="mb-0">
                    <li>Setiap opsi jawaban wajib memiliki bobot skor (0-4) pada satu atau lebih bidang.</li>
                    <li>Siswa akan direkomendasikan ke bidang dengan akumulasi skor tertinggi di akhir tes.</li>
                    <li>Gunakan level kesulitan untuk menyeimbangkan variasi soal Computational Thinking.</li>
                </ul>
            </div>
            <div class="col-md-3 text-md-end mt-4 mt-md-0">
                <button class="btn btn-white-glass rounded-pill px-4 py-2 fw-bold" data-bs-toggle="collapse" data-bs-target="#ctGuideDetails">
                    <i class="fas fa-chevron-down me-2"></i> Detail Import
                </button>
            </div>
        </div>
        <div class="collapse mt-4" id="ctGuideDetails">
            <div class="p-4 bg-white bg-opacity-10 rounded-4 border border-white border-opacity-20">
                <p class="small mb-2 fw-bold text-white"><i class="fas fa-file-excel me-2"></i> Tips Import Data:</p>
                <p class="small mb-0 text-white opacity-90">Pastikan file Excel mengikuti format yang disediakan. Bobot skor harus berupa angka bulat antara 0 sampai 4. Anda dapat mengunduh contoh format melalui tombol Export di bawah untuk dijadikan referensi.</p>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="table-card p-4">
                <div class="form-section-title">
                    <i class="fas fa-file-import text-primary"></i>
                    Import Data Soal
                </div>
                <p class="text-muted small mb-4">Gunakan form di bawah untuk menambahkan soal secara massal melalui file Excel.</p>
                
                <form method="POST" action="{{ route('admin.batch2ct.import.excel') }}" enctype="multipart/form-data">
                    @csrf
                    <label class="form-label text-muted small fw-bold">IMPORT EXCEL (.XLSX)</label>
                    <div class="input-group">
                        <input type="file" name="excel_file" class="form-control bg-light" accept=".xlsx" required>
                        <button class="btn btn-success" type="submit">Import</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="table-card p-4 h-100 d-flex flex-column">
                <div class="form-section-title">
                    <i class="fas fa-file-export text-warning"></i>
                    Export Data Soal
                </div>
                <p class="text-muted small mb-4">Gunakan fitur export untuk backup atau mengunduh template format soal.</p>
                <div class="d-grid gap-3 mt-auto">
                    <a href="{{ route('admin.batch2ct.export.excel') }}" class="btn btn-outline-success border-2 fw-bold">
                        <i class="fas fa-file-excel me-2"></i> Download Excel
                    </a>
                </div>
                <div class="alert alert-light mt-4 mb-0 py-2 border">
                    <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Format export: satu baris per opsi jawaban.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="table-card p-4 mb-5">
        <div class="form-section-title">
            <i class="fas fa-plus-circle text-primary"></i>
            Tambah Soal Batch 2 CT
        </div>
        <form method="POST" action="{{ route('admin.batch2ct.questions.store') }}" id="ctQuestionForm">
            @csrf
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">JENIS COMPUTATIONAL THINKING</label>
                    <select class="form-select bg-light" name="jenis_ct" required>
                        <option value="">-- Pilih Jenis CT --</option>
                        @foreach($ctTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">LEVEL KESULITAN</label>
                    <select class="form-select bg-light" name="level_kesulitan" required>
                        @foreach($difficultyLevels as $level)
                            <option value="{{ $level }}">{{ strtoupper($level) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input type="checkbox" class="form-check-input" name="is_active" id="ctQuestionActive" value="1" checked>
                        <label class="form-check-label fw-bold text-dark" for="ctQuestionActive">Aktifkan Soal</label>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold small text-muted">NARASI SOAL</label>
                <textarea class="form-control bg-light" name="narasi_soal" rows="4" required placeholder="Tuliskan narasi soal dengan konteks Computational Thinking..."></textarea>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <label class="form-label fw-bold small text-muted mb-0">OPSI JAWABAN & BOBOT SKOR (W-M-A)</label>
                <button type="button" class="btn btn-sm btn-outline-primary fw-bold" id="addOptionBtn">
                    <i class="fas fa-plus me-1"></i> Tambah Opsi
                </button>
            </div>

            <div class="table-responsive mb-4 rounded-3 border">
                <table class="table metric-table mb-0" id="optionTable">
                    <thead>
                        <tr>
                            <th style="width: 100px;">Label</th>
                            <th>Teks Opsi</th>
                            <th class="text-center" style="width: 110px;">Bobot W</th>
                            <th class="text-center" style="width: 110px;">Bobot M</th>
                            <th class="text-center" style="width: 110px;">Bobot A</th>
                            <th class="text-center" style="width: 80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="alert alert-info border-0 bg-light py-2 px-3 mb-4">
                <small><i class="fas fa-info-circle me-1"></i> Setiap opsi wajib memiliki minimal satu bobot dominan (nilai > 0).</small>
            </div>

            <button type="submit" class="btn btn-premium w-100 py-3 shadow">
                <i class="fas fa-save me-2"></i> Simpan Soal Batch 2 CT
            </button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function labelByIndex(index) {
        const code = 65 + index;
        return String.fromCharCode(code);
    }

    function appendOptionRow(index, values = {}) {
        const label = values.label || labelByIndex(index);
        const text = values.teks || '';
        const w = Number.isInteger(values.bobot_web) ? values.bobot_web : 0;
        const m = Number.isInteger(values.bobot_marketing) ? values.bobot_marketing : 0;
        const a = Number.isInteger(values.bobot_admin) ? values.bobot_admin : 0;

        const row = `
            <tr data-index="${index}">
                <td class="p-2"><input type="text" class="form-control score-input w-100" data-field="label" name="options[${index}][label]" value="${label}" required></td>
                <td class="p-2"><input type="text" class="form-control bg-light" data-field="teks" name="options[${index}][teks]" value="${text}" required placeholder="Contoh: Sangat Setuju"></td>
                <td class="p-2"><input type="number" min="0" max="4" class="form-control score-input w-100" data-field="bobot_web" name="options[${index}][bobot_web]" value="${w}" required></td>
                <td class="p-2"><input type="number" min="0" max="4" class="form-control score-input w-100" data-field="bobot_marketing" name="options[${index}][bobot_marketing]" value="${m}" required></td>
                <td class="p-2"><input type="number" min="0" max="4" class="form-control score-input w-100" data-field="bobot_admin" name="options[${index}][bobot_admin]" value="${a}" required></td>
                <td class="p-2 text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-option rounded-circle" style="width:32px; height:32px; padding:0;"><i class="fas fa-times"></i></button></td>
            </tr>
        `;

        document.querySelector('#optionTable tbody').insertAdjacentHTML('beforeend', row);
    }

    function reindexRows() {
        const rows = document.querySelectorAll('#optionTable tbody tr');
        rows.forEach((row, idx) => {
            row.dataset.index = idx;
            row.querySelectorAll('input[data-field]').forEach((input) => {
                const field = input.dataset.field;
                input.name = `options[${idx}][${field}]`;
            });

            const labelInput = row.querySelector('input[data-field="label"]');
            if (labelInput && labelInput.value.trim() === '') {
                labelInput.value = labelByIndex(idx);
            }
        });
    }

    appendOptionRow(0, { label: 'A', bobot_web: 1, bobot_marketing: 0, bobot_admin: 0 });
    appendOptionRow(1, { label: 'B', bobot_web: 0, bobot_marketing: 1, bobot_admin: 0 });
    appendOptionRow(2, { label: 'C', bobot_web: 0, bobot_marketing: 0, bobot_admin: 1 });

    document.getElementById('addOptionBtn').addEventListener('click', function () {
        const index = document.querySelectorAll('#optionTable tbody tr').length;
        appendOptionRow(index);
    });

    document.addEventListener('click', function (event) {
        if (!event.target.classList.contains('remove-option')) {
            return;
        }

        const rows = document.querySelectorAll('#optionTable tbody tr');
        if (rows.length <= 3) {
            alert('Minimal harus ada 3 opsi (A, B, C).');
            return;
        }

        event.target.closest('tr').remove();
        reindexRows();
    });

    document.getElementById('ctQuestionForm').addEventListener('submit', function (event) {
        const rows = Array.from(document.querySelectorAll('#optionTable tbody tr'));
        const invalid = rows.find((row) => {
            const w = parseInt(row.querySelector('input[name*="[bobot_web]"]').value || '0', 10);
            const m = parseInt(row.querySelector('input[name*="[bobot_marketing]"]').value || '0', 10);
            const a = parseInt(row.querySelector('input[name*="[bobot_admin]"]').value || '0', 10);
            return Math.max(w, m, a) <= 0;
        });

        if (invalid) {
            event.preventDefault();
            alert('Setiap opsi harus memiliki minimal satu bobot dominan (> 0).');
        }
    });
</script>
@endsection
