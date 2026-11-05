@extends('layouts.app')

@section('title', 'Đăng Nhập | AbyssalMind')

@section('content')
<div class="container" style="max-width: 480px; margin-top: 3rem;">
    <div class="bento-card">
        <div style="text-align: center; margin-bottom: 1.75rem;">
            <h1 style="font-size: 1.65rem;">Đăng Nhập</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Tiếp tục hành trình khám phá kiến thức đại dương của bạn.</p>
        </div>

        @if($errors->any())
            <div style="background: rgba(239,68,68,0.15); border: 1px solid #ef4444; color: #fca5a5; padding: 0.85rem; border-radius: 10px; margin-bottom: 1.2rem; font-size: 0.9rem;">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.submit') }}" method="POST" style="display: flex; flex-direction: column; gap: 1.15rem;">
            @csrf
            <div>
                <label for="email" style="display:block; margin-bottom:0.4rem; font-weight:600; font-size:0.9rem;">Địa chỉ Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="email@example.com" autocomplete="email" required autofocus>
            </div>

            <div>
                <label for="password" style="display:block; margin-bottom:0.4rem; font-weight:600; font-size:0.9rem;">Mật khẩu</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Nhập mật khẩu" autocomplete="current-password" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem;">
                Đăng Nhập
            </button>
        </form>

        <p style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-muted);">
            Chưa có tài khoản? <a href="{{ route('register') }}" style="color: var(--cyan-glow);">Đăng ký ngay</a>
        </p>
    </div>
</div>
@endsection
