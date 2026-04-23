@extends('layouts.admin')

@section('page_title', 'Kelola Soal Assessment')

@section('styles')
<style>
    .card { border-radius: 18px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.05); transition: all 0.3s; }
    .card:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.08); }
    .card-header { background-color: #fff !important; border-bottom: 1px solid #f1f5f9 !important; padding: 20px !important; }
    .card-header h5 { font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 10px; }
    .card-header h5 i { color: var(--primary-color); }

    .option-row {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 15px;
        background: #f8fafc;
        transition: all 0.2s;
    }

    .option-row:hover {
        border-color: var(--primary-color);
        background: #fff;
    }

    .form-label { font-weight: 600; color: #475569; font-size: 0.85rem; }
    .form-control, .form-select { border-radius: 10px; padding: 10px 15px; border: 1px solid #e2e8f0; }
    .form-control:focus, .form-select:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
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

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0"><i class="fas fa-layer-group"></i> Tambah Batch Asesmen</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.batches.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nama Batch</label>
                            <input type="text" class="form-control" name="batch_name" placeholder="Contoh: Batch 2" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="description" rows="2" placeholder="Opsional"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" name="start_date">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control" name="end_date">
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" id="isActiveBatch" name="is_active">
                            <label class="form-check-label" for="isActiveBatch">
                                Jadikan batch aktif
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Simpan Batch</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Daftar Batch</h5>
                </div>
                <div class="card-body">
                    @forelse($batches as $batch)
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <strong>{{ $batch->batch_name }}</strong>
                                    @if($batch->is_active)
                                        <span class="badge bg-success ms-2">Aktif</span>
                                    @endif
                                </div>
                                @if(!$batch->is_active)
                                    <form method="POST" action="{{ route('admin.batches.activate', $batch->id) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success">Aktifkan</button>
                                    </form>
                                @endif
                            </div>

                            <div class="small text-muted mb-2">
                                {{ $batch->description ?: 'Tanpa deskripsi' }}
                            </div>

                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#editBatch{{ $batch->id }}">
                                Edit Batch
                            </button>

                            <div class="collapse mt-3" id="editBatch{{ $batch->id }}">
                                <form method="POST" action="{{ route('admin.batches.update', $batch->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="mb-2">
                                        <label class="form-label small">Nama Batch</label>
                                        <input type="text" class="form-control form-control-sm" name="batch_name" value="{{ $batch->batch_name }}" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Deskripsi</label>
                                        <textarea class="form-control form-control-sm" name="description" rows="2">{{ $batch->description }}</textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-2">
                                            <label class="form-label small">Mulai</label>
                                            <input type="date" class="form-control form-control-sm" name="start_date" value="{{ $batch->start_date ? $batch->start_date->format('Y-m-d') : '' }}">
                                        </div>
                                        <div class="col-6 mb-2">
                                            <label class="form-label small">Selesai</label>
                                            <input type="date" class="form-control form-control-sm" name="end_date" value="{{ $batch->end_date ? $batch->end_date->format('Y-m-d') : '' }}">
                                        </div>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="batchActive{{ $batch->id }}" {{ $batch->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="batchActive{{ $batch->id }}">Set aktif</label>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-primary">Simpan Perubahan</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Belum ada batch asesmen.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-8 mb-4">
            <div class="card mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0"><i class="fas fa-file-excel"></i> Import / Export Soal</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <a href="{{ route('admin.questions.template') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-download"></i> Download Template Import
                        </a>
                        <a href="{{ route('admin.questions.export', ['batch_id' => $selectedBatchId]) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-file-export"></i> Export Soal Batch Dipilih
                        </a>
                    </div>

                    <form method="POST" action="{{ route('admin.questions.import') }}" enctype="multipart/form-data" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-md-9">
                            <label class="form-label">Upload File Excel Soal</label>
                            <input type="file" class="form-control" name="excel_file" accept=".xlsx" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">Import</button>
                        </div>
                    </form>
                    <small class="text-muted d-block mt-2">Format kolom import/export: 1 soal = 1 baris (opsi jawaban berada pada kolom Opsi 1, Opsi 2, dst). Gunakan file Excel .xlsx.</small>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Tambah Soal Baru</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.questions.store') }}" id="createQuestionForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Batch</label>
                                <select class="form-select" name="batch_id" required>
                                    @foreach($batches as $batch)
                                        <option value="{{ $batch->id }}" {{ $selectedBatchId == $batch->id ? 'selected' : '' }}>
                                            {{ $batch->batch_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori Kompetensi</label>
                                <select class="form-select" name="category_id" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Teks Soal</label>
                            <textarea class="form-control" name="question_text" rows="3" required placeholder="Contoh: Seberapa tertarik Anda mengembangkan aplikasi web?"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Urutan Soal</label>
                                <input type="number" min="1" class="form-control" name="question_order" placeholder="Opsional">
                            </div>
                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" value="1" id="questionActive" name="is_active" checked>
                                    <label class="form-check-label" for="questionActive">
                                        Soal aktif
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Opsi Jawaban + Skor</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addOptionBtn">
                                <i class="fas fa-plus"></i> Tambah Opsi
                            </button>
                        </div>
                        <div id="optionsContainer">
                            <div class="option-row" data-index="0">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-5">
                                        <label class="form-label small">Teks Opsi</label>
                                        <input type="text" class="form-control" name="option_text[0]" value="Ya" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Skor</label>
                                        <input type="number" class="form-control" step="0.01" name="option_score[0]" value="1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Urutan</label>
                                        <input type="number" class="form-control" min="1" name="option_order[0]" value="1">
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-check mt-4">
                                            <input class="form-check-input" type="checkbox" name="option_active[0]" value="1" checked>
                                            <label class="form-check-label small">Aktif</label>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger mt-2 remove-option">Hapus Opsi</button>
                            </div>
                            <div class="option-row" data-index="1">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-5">
                                        <label class="form-label small">Teks Opsi</label>
                                        <input type="text" class="form-control" name="option_text[1]" value="Tidak" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Skor</label>
                                        <input type="number" class="form-control" step="0.01" name="option_score[1]" value="0" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Urutan</label>
                                        <input type="number" class="form-control" min="1" name="option_order[1]" value="2">
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-check mt-4">
                                            <input class="form-check-input" type="checkbox" name="option_active[1]" value="1" checked>
                                            <label class="form-check-label small">Aktif</label>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger mt-2 remove-option">Hapus Opsi</button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3 w-100">Simpan Soal</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0"><i class="fas fa-book"></i> Bank Soal ({{ $selectedBatch ? $selectedBatch->batch_name : 'Tanpa Batch' }})</h5>
                </div>
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('admin.questions') }}" class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label">Filter Batch</label>
                            <select class="form-select" name="batch_id">
                                @foreach($batches as $batch)
                                    <option value="{{ $batch->id }}" {{ $selectedBatchId == $batch->id ? 'selected' : '' }}>
                                        {{ $batch->batch_name }}{{ $batch->is_active ? ' (Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Filter Kategori</label>
                            <select class="form-select" name="category_id">
                                <option value="">-- Semua Kategori --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ $selectedCategoryId == $category->id ? 'selected' : '' }}>{{ $category->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-primary w-100" type="submit">Filter</button>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    @forelse($questions as $question)
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="badge bg-info text-dark me-2">{{ $question->category->display_name }}</span>
                                    <span class="badge {{ $question->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $question->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    <h6 class="mt-2 mb-1">{{ $question->question_order ? $question->question_order . '. ' : '' }}{{ $question->question_text }}</h6>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.questions.edit', $question->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="POST" action="{{ route('admin.questions.delete', $question->id) }}" onsubmit="return confirm('Yakin ingin menghapus soal ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </div>
                            </div>

                            <div class="mt-2">
                                @foreach($question->options as $option)
                                    <div class="small mb-1">
                                        <span class="badge {{ $option->is_active ? 'bg-primary' : 'bg-light text-dark' }} me-2">Skor {{ number_format($option->option_score, 2) }}</span>
                                        {{ $option->option_order ? $option->option_order . '. ' : '' }}{{ $option->option_text }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Belum ada soal pada filter ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function reindexOptionRows() {
        $('#optionsContainer .option-row').each(function(index) {
            $(this).attr('data-index', index);
            $(this).find('input[name^="option_text"]').attr('name', 'option_text[' + index + ']');
            $(this).find('input[name^="option_score"]').attr('name', 'option_score[' + index + ']');
            $(this).find('input[name^="option_order"]').attr('name', 'option_order[' + index + ']').val(index + 1);
            $(this).find('input[name^="option_active"]').attr('name', 'option_active[' + index + ']');
        });
    }

    $('#addOptionBtn').on('click', function() {
        const index = $('#optionsContainer .option-row').length;
        const newRow = `
            <div class="option-row" data-index="${index}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small">Teks Opsi</label>
                        <input type="text" class="form-control" name="option_text[${index}]" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Skor</label>
                        <input type="number" class="form-control" step="0.01" name="option_score[${index}]" value="0" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Urutan</label>
                        <input type="number" class="form-control" min="1" name="option_order[${index}]" value="${index + 1}">
                    </div>
                    <div class="col-md-2">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="option_active[${index}]" value="1" checked>
                            <label class="form-check-label small">Aktif</label>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger mt-2 remove-option">Hapus Opsi</button>
            </div>
        `;

        $('#optionsContainer').append(newRow);
    });

    $(document).on('click', '.remove-option', function() {
        if ($('#optionsContainer .option-row').length <= 2) {
            alert('Minimal harus ada 2 opsi jawaban.');
            return;
        }

        $(this).closest('.option-row').remove();
        reindexOptionRows();
    });
</script>
@endsection
