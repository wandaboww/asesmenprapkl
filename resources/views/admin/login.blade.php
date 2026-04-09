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
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    }
    .logo { text-align: center; margin-bottom: 30px; }
    .logo h2 { color: #1e3c72; font-weight: bold; }
</style>
@endsection

@section('content')
<div class="wrapper">
    <div class="login-card">
        <div class="logo">
            <h2>🛡️ Admin Panel</h2>
            <p class="text-muted">Assessment Pemetaan PKL</p>
        </div>
        
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        
        <form method="POST" action="{{ route('admin.login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required autocomplete="off">
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 py-2">Login Admin</button>
        </form>
        
        <hr class="my-4">
        <div class="text-center">
            <a href="{{ route('login') }}" class="text-muted">Kembali ke Portal Login Siswa</a>
        </div>
    </div>
</div>
@endsection
