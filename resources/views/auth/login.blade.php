@extends('layouts.app')

@section('title', 'Đăng Nhập | AbyssalMind')

@section('content')
<div class="auth-page" style="--hero-image: url('{{ asset('images/ocean-hero.jpg') }}');">
    <div class="auth-card">
        <div>
            <span class="hero-eyebrow" style="background: rgba(22,104,196,0.08); border-color: rgba(22,104,196,0.18); color: var(--blue);">Chào mừng trở lại</span>
            <h1 style="margin-top: 0.9rem;">Đăng nhập</h1>
            <p class="auth-intro">Tiếp tục hành trình khám phá kiến thức đại dương của bạn.</p>
        </div>

        @if($errors->any())
            <div class="flash-bar error" style="margin-top: 1.25rem;">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('login.submit') }}" method="POST">
            @csrf
            <div>
                <label for="email">Địa chỉ email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="email@example.com" autocomplete="email" required autofocus>
            </div>

            <div>
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Nhập mật khẩu" autocomplete="current-password" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.35rem;">Đăng nhập</button>
        </form>

        <p class="auth-alt">
            Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký ngay</a>
        </p>
    </div>
</div>
@endsection
