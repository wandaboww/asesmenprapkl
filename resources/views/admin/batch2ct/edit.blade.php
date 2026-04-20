@extends('layouts.app')

@section('styles')
<style>
    body { background: #f4f6f9; }
    .navbar { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); padding: 15px 0; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .navbar-brand { font-weight: 700; font-size: 1.4rem; }
    .nav-link { font-weight: 500; transition: 0.3s; }
    .nav-link:hover { transform: translateY(-2px); color: #fff !important; }
    .card { border-radius: 14px; border: none; box-shadow: 0 8px 24px rgba(0,0,0,0.06); }

    @media (max-width: 768px) {
        .navbar-collapse { padding-top: 15px; }
        .admin-user-info {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255,255,255,0.1);
            width: 100%;
            justify-content: space-between;
        }
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
                    <a class="nav-link active" href="{{ route('admin.batch2ct.index') }}">Kelola Soal Batch 2 CT</a>
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

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-edit"></i> Edit Soal Batch 2 CT</h5>
            <a href="{{ route('admin.batch2ct.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.batch2ct.questions.update', $question->id) }}" id="editCtQuestionForm">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Jenis CT</label>
                        <select class="form-select" name="jenis_ct" required>
                            @foreach($ctTypes as $type)
                                <option value="{{ $type }}" {{ $question->jenis_ct === $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Level Kesulitan</label>
                        <select class="form-select" name="level_kesulitan" required>
                            @foreach($difficultyLevels as $level)
                                <option value="{{ $level }}" {{ $question->level_kesulitan === $level ? 'selected' : '' }}>{{ strtoupper($level) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" name="is_active" id="questionActive" value="1" {{ $question->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="questionActive">Soal aktif</label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Narasi Soal</label>
                    <textarea class="form-control" name="narasi_soal" rows="3" required>{{ $question->narasi_soal }}</textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">Opsi Jawaban + Bobot (W, M, A)</label>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addOptionBtn">
                        <i class="fas fa-plus"></i> Tambah Opsi
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="optionTable">
                        <thead>
                            <tr>
                                <th style="width: 90px;">Label</th>
                                <th>Teks Opsi</th>
                                <th style="width: 110px;">Bobot W</th>
                                <th style="width: 110px;">Bobot M</th>
                                <th style="width: 110px;">Bobot A</th>
                                <th style="width: 70px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($question->options as $index => $option)
                                <tr data-index="{{ $index }}">
                                    <td>
                                        <input type="text" class="form-control" data-field="label" name="options[{{ $index }}][label]" value="{{ $option->label_opsi }}" required>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" data-field="teks" name="options[{{ $index }}][teks]" value="{{ $option->teks_opsi }}" required>
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="4" class="form-control" data-field="bobot_web" name="options[{{ $index }}][bobot_web]" value="{{ $option->bobot_web }}" required>
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="4" class="form-control" data-field="bobot_marketing" name="options[{{ $index }}][bobot_marketing]" value="{{ $option->bobot_marketing }}" required>
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="4" class="form-control" data-field="bobot_admin" name="options[{{ $index }}][bobot_admin]" value="{{ $option->bobot_admin }}" required>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-option">Hapus</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
                <td><input type="text" class="form-control" data-field="label" name="options[${index}][label]" value="${label}" required></td>
                <td><input type="text" class="form-control" data-field="teks" name="options[${index}][teks]" required></td>
                <td><input type="number" min="0" max="4" class="form-control" data-field="bobot_web" name="options[${index}][bobot_web]" value="0" required></td>
                <td><input type="number" min="0" max="4" class="form-control" data-field="bobot_marketing" name="options[${index}][bobot_marketing]" value="0" required></td>
                <td><input type="number" min="0" max="4" class="form-control" data-field="bobot_admin" name="options[${index}][bobot_admin]" value="0" required></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger remove-option">Hapus</button></td>
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
