@extends('layouts.admin')

@section('page_title', 'Edit Soal Batch 2 CT')

@section('styles')
<style>
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

    <div class="table-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-section-title mb-0">
                <i class="fas fa-edit text-primary"></i>
                Edit Soal Batch 2 CT #{{ $question->id }}
            </div>
            <a href="{{ route('admin.batch2ct.bank-soal') }}" class="btn btn-light border btn-sm px-3 rounded-pill">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <form method="POST" action="{{ route('admin.batch2ct.questions.update', $question->id) }}" id="editCtQuestionForm">
            @csrf
            @method('PUT')

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">JENIS COMPUTATIONAL THINKING</label>
                    <select class="form-select bg-light" name="jenis_ct" required>
                        @foreach($ctTypes as $type)
                            <option value="{{ $type }}" {{ $question->jenis_ct === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">LEVEL KESULITAN</label>
                    <select class="form-select bg-light" name="level_kesulitan" required>
                        @foreach($difficultyLevels as $level)
                            <option value="{{ $level }}" {{ $question->level_kesulitan === $level ? 'selected' : '' }}>{{ strtoupper($level) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input type="checkbox" class="form-check-input" name="is_active" id="questionActive" value="1" {{ $question->is_active ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold text-dark" for="questionActive">Aktifkan Soal</label>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold small text-muted">NARASI SOAL</label>
                <textarea class="form-control bg-light" name="narasi_soal" rows="4" required>{{ $question->narasi_soal }}</textarea>
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
                    <tbody>
                        @foreach($question->options as $index => $option)
                            <tr data-index="{{ $index }}">
                                <td class="p-2">
                                    <input type="text" class="form-control score-input w-100" data-field="label" name="options[{{ $index }}][label]" value="{{ $option->label_opsi }}" required>
                                </td>
                                <td class="p-2">
                                    <input type="text" class="form-control bg-light" data-field="teks" name="options[{{ $index }}][teks]" value="{{ $option->teks_opsi }}" required>
                                </td>
                                <td class="p-2">
                                    <input type="number" min="0" max="4" class="form-control score-input w-100" data-field="bobot_web" name="options[{{ $index }}][bobot_web]" value="{{ $option->bobot_web }}" required>
                                </td>
                                <td class="p-2">
                                    <input type="number" min="0" max="4" class="form-control score-input w-100" data-field="bobot_marketing" name="options[{{ $index }}][bobot_marketing]" value="{{ $option->bobot_marketing }}" required>
                                </td>
                                <td class="p-2">
                                    <input type="number" min="0" max="4" class="form-control score-input w-100" data-field="bobot_admin" name="options[{{ $index }}][bobot_admin]" value="{{ $option->bobot_admin }}" required>
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-option rounded-circle" style="width:32px; height:32px; padding:0;"><i class="fas fa-times"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="alert alert-info border-0 bg-light py-2 px-3 mb-4">
                <small><i class="fas fa-info-circle me-1"></i> Setiap opsi wajib memiliki minimal satu bobot dominan (nilai > 0).</small>
            </div>

            <button type="submit" class="btn btn-premium w-100 py-3 shadow">
                <i class="fas fa-save me-2"></i> Simpan Perubahan Soal
            </button>
        </form>
    </div>

                <small class="text-muted d-block mb-3">Setiap opsi harus memiliki minimal satu bobot > 0.</small>

                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function labelByIndex(index) {
        const code = 65 + index;
        return String.fromCharCode(code);
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

    function appendOptionRow(index) {
        const label = labelByIndex(index);
        const row = `
            <tr data-index="${index}">
                <td class="p-2"><input type="text" class="form-control score-input w-100" data-field="label" name="options[${index}][label]" value="${label}" required></td>
                <td class="p-2"><input type="text" class="form-control bg-light" data-field="teks" name="options[${index}][teks]" required placeholder="Contoh: Sangat Setuju"></td>
                <td class="p-2"><input type="number" min="0" max="4" class="form-control score-input w-100" data-field="bobot_web" name="options[${index}][bobot_web]" value="0" required></td>
                <td class="p-2"><input type="number" min="0" max="4" class="form-control score-input w-100" data-field="bobot_marketing" name="options[${index}][bobot_marketing]" value="0" required></td>
                <td class="p-2"><input type="number" min="0" max="4" class="form-control score-input w-100" data-field="bobot_admin" name="options[${index}][bobot_admin]" value="0" required></td>
                <td class="p-2 text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-option rounded-circle" style="width:32px; height:32px; padding:0;"><i class="fas fa-times"></i></button></td>
            </tr>
        `;

        document.querySelector('#optionTable tbody').insertAdjacentHTML('beforeend', row);
    }


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
            alert('Minimal 3 opsi diperlukan.');
            return;
        }

        event.target.closest('tr').remove();
        reindexRows();
    });

    document.getElementById('editCtQuestionForm').addEventListener('submit', function (event) {
        const rows = Array.from(document.querySelectorAll('#optionTable tbody tr'));
        const invalid = rows.find((row) => {
            const w = parseInt(row.querySelector('input[data-field="bobot_web"]').value || '0', 10);
            const m = parseInt(row.querySelector('input[data-field="bobot_marketing"]').value || '0', 10);
            const a = parseInt(row.querySelector('input[data-field="bobot_admin"]').value || '0', 10);
            return Math.max(w, m, a) <= 0;
        });

        if (invalid) {
            event.preventDefault();
            alert('Setiap opsi wajib memiliki minimal satu bobot dominan (> 0).');
        }
    });
</script>
@endsection
