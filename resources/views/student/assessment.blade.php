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
    
    <div class="row justify-content-center">
        <div class="col-md-10">
            <form id="assessmentForm" method="POST" action="{{ route('student.assessment.submit') }}">
                @csrf
                @php $question_number = 1; @endphp
                @foreach ($categories as $category)
                    <div class="category-header">
                        <h4>{{ $category->icon }} {{ $category->category_name }}</h4>
                        <p class="mb-0">Jawab pertanyaan berikut sesuai dengan minat dan kemampuan Anda</p>
                    </div>
                    
                    @foreach ($category->questions as $q)
                        <div class="card mb-4 question-card">
                            <div class="card-body">
                                <h6 class="mb-3">{{ $question_number }}. {{ $q->question_text }}</h6>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <div class="answer-option" data-question="{{ $q->id }}" data-answer="Ya">
                                            <input type="radio" name="q_{{ $q->id }}" value="Ya" id="q{{ $q->id }}_ya" required hidden>
                                            <label for="q{{ $q->id }}_ya" class="w-100 mb-0" style="cursor: pointer;">
                                                <strong>✅ Ya</strong>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="answer-option" data-question="{{ $q->id }}" data-answer="Tidak">
                                            <input type="radio" name="q_{{ $q->id }}" value="Tidak" id="q{{ $q->id }}_tidak" required hidden>
                                            <label for="q{{ $q->id }}_tidak" class="w-100 mb-0" style="cursor: pointer;">
                                                <strong>❌ Tidak</strong>
                                            </label>
                                        </div>
                                    </div>
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
        const answer = $(this).data('answer');
        
        $(`[data-question="${questionId}"]`).removeClass('selected');
        $(this).addClass('selected');
        $(`input[name="q_${questionId}"][value="${answer}"]`).prop('checked', true);
    });
    
    $('#assessmentForm').submit(function(e) {
        const totalQuestions = $('input[type="radio"]').length / 2;
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
