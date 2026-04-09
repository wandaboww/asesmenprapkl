@extends('layouts.app')

@section('styles')
<style>
    .login-card {
        background: white;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        max-width: 450px;
        width: 100%;
        margin: auto;
    }
    .wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .logo {
        text-align: center;
        margin-bottom: 30px;
    }
    .logo h2 {
        color: #667eea;
        font-weight: bold;
    }
</style>
@endsection

@section('content')
<div class="wrapper">
    <div class="login-card">
        <div class="logo">
            <h2>🎓 Assessment PKL</h2>
            <p class="text-muted">Pemetaan Industri PKL</p>
        </div>
        
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        
        <form method="POST" action="{{ url('/login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Pilih Kelas Anda</label>
                <select class="form-select @error('class_id') is-invalid @enderror" name="class_id" id="classSelect" required>
                    <option value="">-- Pilih Kelas --</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Pilih Nama Anda</label>
                <select class="form-select @error('student_id') is-invalid @enderror" name="student_id" id="studentSelect" required disabled>
                    <option value="">-- Pilih Kelas Terlebih Dahulu --</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 py-2">Masuk</button>
        </form>
        
        <hr class="my-4">
        <div class="text-center">
            <a href="{{ route('admin.login') }}" class="text-muted">Login Admin</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#classSelect').change(function() {
            const classId = $(this).val();
            
            if (classId) {
                $.ajax({
                    url: '{{ url("/api/students-by-class") }}',
                    type: 'GET',
                    data: { class_id: classId },
                    success: function(data) {
                        $('#studentSelect').html('<option value="">-- Pilih Nama --</option>');
                        if (data.length > 0) {
                            data.forEach(function(student) {
                                $('#studentSelect').append(
                                    '<option value="' + student.id + '">' + student.full_name + '</option>'
                                );
                            });
                            $('#studentSelect').prop('disabled', false);
                        } else {
                            $('#studentSelect').html('<option value="">Tidak ada siswa</option>');
                            $('#studentSelect').prop('disabled', true);
                        }
                    }
                });
            } else {
                $('#studentSelect').html('<option value="">-- Pilih Kelas Terlebih Dahulu --</option>');
                $('#studentSelect').prop('disabled', true);
            }
        });
    });
</script>
@endsection
