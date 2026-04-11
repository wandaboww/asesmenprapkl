@extends('layouts.app')

@section('styles')
<style>
    body { background: #f8f9fa; }
    .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .question-card { transition: all 0.3s; border-radius: 10px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    .answer-option { cursor: pointer; padding: 15px; border: 2px solid #dee2e6; border-radius: 10px; transition: all 0.3s; }
    .answer-option:hover { border-color: #0d6efd; background-color: #f8f9fa; }
    .answer-option.selected { border-color: #0d6efd; background-color: #cfe2ff; }
    .category-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
</style>
@endsection

@section('content')
<nav class="navbar navbar-dark navbar-expand-lg">
    <div class="container">
        <span class="navbar-brand"><i class="fas fa-graduation-cap"></i> Assessment PKL</span>
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
    @if(session('error'))
        <div class="row justify-content-center mb-3">
            <div class="col-md-10">
                <div class="alert alert-danger">{{ session('error') }}</div>
            </div>
        </div>
    @endif

    <div class="row justify-content-center mb-3">
        <div class="col-md-10">
            <div class="alert alert-info">
                <strong>Batch Asesmen:</strong> {{ $selectedBatch->batch_name }}
            </div>

            @if(!empty($forcedCategory))
                <div class="alert alert-warning mb-0">
                    <strong>Mode Batch 2:</strong> Soal yang ditampilkan khusus bidang <strong>{{ $forcedCategory->display_name }}</strong> berdasarkan rekomendasi hasil Batch 1.
                </div>
            @endif
        </div>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-md-10">
            <form id="assessmentForm" method="POST" action="{{ route('student.assessment.submit') }}">
                @csrf
                <input type="hidden" name="batch_id" value="{{ $selectedBatch->id }}">
                @php $question_number = 1; @endphp
                @foreach ($categories as $category)
                    @continue($category->questions->isEmpty())

                    <div class="category-header">
                        <h4>{{ $category->icon }} {{ $category->display_name }}</h4>
                        <p class="mb-0">Jawab pertanyaan berikut sesuai dengan minat dan kemampuan Anda</p>
                    </div>
                    
                    @foreach ($category->questions as $q)
                        <div class="card mb-4 question-card">
                            <div class="card-body">
                                <h6 class="mb-3">{{ $question_number }}. {{ $q->question_text }}</h6>
                                
                                <div class="row g-2">
                                    @foreach($q->options as $option)
                                        <div class="col-md-6">
                                            <div class="answer-option" data-question="{{ $q->id }}" data-option="{{ $option->id }}">
                                                <input type="radio" name="q_{{ $q->id }}" value="{{ $option->id }}" id="q{{ $q->id }}_opt{{ $option->id }}" required hidden>
                                                <label for="q{{ $q->id }}_opt{{ $option->id }}" class="w-100 mb-0" style="cursor: pointer;">
                                                    <strong>{{ $option->option_text }}</strong>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @php $question_number++; @endphp
                    @endforeach
                @endforeach
                
                <div class="card bg-light shadow-sm">
                    <div class="card-body text-center">
                        <p class="mb-3">Pastikan semua pertanyaan sudah dijawab sebelum submit!</p>
                        <button type="submit" class="btn btn-success btn-lg px-5">Submit Assessment</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $('.answer-option').click(function() {
        const questionId = $(this).data('question');
        const optionId = $(this).data('option');
        
        $(`[data-question="${questionId}"]`).removeClass('selected');
        $(this).addClass('selected');
        $(`input[name="q_${questionId}"][value="${optionId}"]`).prop('checked', true);
    });
    
    $('#assessmentForm').submit(function(e) {
        const totalQuestions = new Set($('input[type="radio"]').map(function() {
            return $(this).attr('name');
        }).get()).size;
        const answeredQuestions = $('input[type="radio"]:checked').length;
        
        if (answeredQuestions < totalQuestions) {
            e.preventDefault();
            alert('Harap jawab semua pertanyaan!');
            return false;
        }
        
        return confirm('Apakah Anda yakin ingin submit? Anda tidak bisa mengubah jawaban setelah submit.');
    });
</script>
@endsection
