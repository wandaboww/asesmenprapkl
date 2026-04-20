@extends('layouts.app')

@section('styles')
<style>
    body { background: #f8fafc; }
    .navbar { background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%); }
    .question-card { border-radius: 12px; border: none; box-shadow: 0 6px 18px rgba(0,0,0,0.08); }
    .answer-option {
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px;
        cursor: pointer;
        transition: 0.2s ease;
        height: 100%;
    }
    .answer-option:hover { border-color: #2563eb; background: #eff6ff; }
    .answer-option.selected { border-color: #2563eb; background: #dbeafe; }
    .ct-badge { border-radius: 999px; padding: 0.3rem 0.7rem; font-size: 0.74rem; font-weight: 700; background: #e2e8f0; color: #1e293b; }
</style>
@endsection

@section('content')
<nav class="navbar navbar-dark navbar-expand-lg">
    <div class="container">
        <span class="navbar-brand"><i class="fas fa-brain"></i> Batch 2 CT Assessment</span>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto"></ul>
            <div class="d-flex align-items-center mt-3 mt-lg-0 justify-content-between w-100" style="max-width: 300px; margin-left: auto;">
                <div style="color: white; text-align: left;">
                    <strong>{{ session('student_name') }}</strong><br>
                    <small>{{ session('class_name') }}</small>
                </div>
                <a href="{{ route('logout') }}" class="btn btn-sm btn-outline-light ms-3">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-4 mb-5">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row justify-content-center mb-3">
        <div class="col-lg-10">
            <div class="alert alert-info d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <strong>Attempt ke-{{ $nextAttempt }}</strong>
                    <span class="d-block">Total soal: {{ $questions->count() }} | Metode: Weighted Scoring (W-M-A)</span>
                </div>
                <div class="mt-2 mt-lg-0">
                    @if($latestResult)
                        <a href="{{ route('student.batch2ct.result', ['result' => $latestResult->id]) }}" class="btn btn-sm btn-outline-primary">Lihat Hasil Terakhir</a>
                    @endif
                    <a href="{{ route('student.dashboard') }}" class="btn btn-sm btn-outline-secondary">Kembali Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center mb-3">
        <div class="col-lg-10">
            <form method="GET" action="{{ route('student.batch2ct.assessment') }}" class="card card-body mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Filter Jenis CT</label>
                        <select class="form-select" name="jenis_ct">
                            <option value="">Semua Jenis CT</option>
                            @foreach($ctTypes as $type)
                                <option value="{{ $type }}" {{ $selectedJenisCt === $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Randomisasi Soal</label>
                        <select class="form-select" name="random">
                            <option value="1" {{ $randomize ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ !$randomize ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button class="btn btn-outline-primary w-100" type="submit">Terapkan</button>
                        <a href="{{ route('student.batch2ct.assessment') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>

            <form method="POST" action="{{ route('student.batch2ct.submit') }}" id="batch2CtForm">
                @csrf

                @foreach($questions as $index => $question)
                    <input type="hidden" name="question_ids[]" value="{{ $question->id }}">

                    <div class="card question-card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0">{{ $index + 1 }}. {{ $question->narasi_soal }}</h6>
                                <span class="ct-badge">{{ $question->jenis_ct }} | {{ strtoupper($question->level_kesulitan) }}</span>
                            </div>

                            <div class="row g-2 mt-1">
                                @foreach($question->options as $option)
                                    <div class="col-md-4">
                                        <div class="answer-option" data-question="{{ $question->id }}" data-option="{{ $option->id }}">
                                            <input type="radio" name="q_{{ $question->id }}" value="{{ $option->id }}" id="q{{ $question->id }}_o{{ $option->id }}" hidden required>
                                            <label for="q{{ $question->id }}_o{{ $option->id }}" class="mb-0 w-100" style="cursor:pointer;">
                                                <strong>{{ $option->label_opsi }}</strong>. {{ $option->teks_opsi }}
                                            </label>
                                            <small class="d-block mt-2 text-muted">Bobot: W {{ $option->bobot_web }} | M {{ $option->bobot_marketing }} | A {{ $option->bobot_admin }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <p class="mb-3">Pastikan seluruh soal sudah dijawab sebelum submit.</p>
                        <button type="submit" class="btn btn-success btn-lg px-5">Submit Batch 2 CT</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('.answer-option').forEach((optionEl) => {
        optionEl.addEventListener('click', function () {
            const questionId = this.dataset.question;
            const optionId = this.dataset.option;

            document.querySelectorAll(`[data-question="${questionId}"]`).forEach((el) => {
                el.classList.remove('selected');
            });

            this.classList.add('selected');
            const radio = document.querySelector(`input[name="q_${questionId}"][value="${optionId}"]`);
            if (radio) {
                radio.checked = true;
            }
        });
    });

    document.getElementById('batch2CtForm').addEventListener('submit', function (event) {
        const allGroups = new Set(Array.from(document.querySelectorAll('input[type="radio"]')).map((input) => input.name));
        const checked = document.querySelectorAll('input[type="radio"]:checked').length;

        if (checked < allGroups.size) {
            event.preventDefault();
            alert('Harap jawab semua soal sebelum submit.');
            return;
        }

        if (!confirm('Yakin submit attempt ini?')) {
            event.preventDefault();
        }
    });
</script>
@endsection
