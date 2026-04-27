@extends('layouts.admin')

@section('page_title', 'Edit Soal Assessment')

@section('styles')
<style>
    .card { border-radius: 18px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
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

    <div class="card">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-edit"></i> Edit Soal</h5>
            <a href="{{ route('admin.questions', ['batch_id' => $question->batch_id]) }}" class="btn btn-sm btn-outline-secondary">Kembali ke Kelola Soal</a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.questions.update', $question->id) }}" id="editQuestionForm">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Batch</label>
                        <select class="form-select" name="batch_id" required>
                            @foreach($batches as $batch)
                                <option value="{{ $batch->id }}" {{ $question->batch_id == $batch->id ? 'selected' : '' }}>
                                    {{ $batch->batch_name }}{{ $batch->is_active ? ' (Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kategori Kompetensi</label>
                        <select class="form-select" name="category_id" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $question->category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Teks Soal</label>
                    <textarea class="form-control" name="question_text" rows="3" required>{{ $question->question_text }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Urutan Soal</label>
                        <input type="number" min="1" class="form-control" name="question_order" value="{{ $question->question_order }}">
                    </div>
                    <div class="col-md-6 mb-3 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="1" id="questionActive" name="is_active" {{ $question->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="questionActive">Soal aktif</label>
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
                    @foreach($question->options as $index => $option)
                        <div class="option-row" data-index="{{ $index }}">
                            <input type="hidden" class="option-id" name="option_id[{{ $index }}]" value="{{ $option->id }}">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label small">Teks Opsi</label>
                                    <input type="text" class="form-control option-text" name="option_text[{{ $index }}]" value="{{ $option->option_text }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Skor</label>
                                    <input type="number" class="form-control option-score" step="0.01" name="option_score[{{ $index }}]" value="{{ $option->option_score }}" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Urutan</label>
                                    <input type="number" class="form-control option-order" min="1" name="option_order[{{ $index }}]" value="{{ $option->option_order }}">
                                </div>
                                <div class="col-md-2">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input option-active" type="checkbox" name="option_active[{{ $index }}]" value="1" {{ $option->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label small">Aktif</label>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger mt-2 remove-option">Hapus Opsi</button>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-primary mt-3">Simpan Perubahan Soal</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function reindexOptionRows() {
        $('#optionsContainer .option-row').each(function(index) {
            $(this).attr('data-index', index);
            $(this).find('.option-id').attr('name', 'option_id[' + index + ']');
            $(this).find('.option-text').attr('name', 'option_text[' + index + ']');
            $(this).find('.option-score').attr('name', 'option_score[' + index + ']');
            $(this).find('.option-order').attr('name', 'option_order[' + index + ']');
            $(this).find('.option-active').attr('name', 'option_active[' + index + ']');
        });
    }

    $('#addOptionBtn').on('click', function() {
        const index = $('#optionsContainer .option-row').length;
        const newRow = `
            <div class="option-row" data-index="${index}">
                <input type="hidden" class="option-id" name="option_id[${index}]" value="">
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small">Teks Opsi</label>
                        <input type="text" class="form-control option-text" name="option_text[${index}]" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Skor</label>
                        <input type="number" class="form-control option-score" step="0.01" name="option_score[${index}]" value="0" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Urutan</label>
                        <input type="number" class="form-control option-order" min="1" name="option_order[${index}]" value="${index + 1}">
                    </div>
                    <div class="col-md-2">
                        <div class="form-check mt-4">
                            <input class="form-check-input option-active" type="checkbox" name="option_active[${index}]" value="1" checked>
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
