@extends('layouts.app')

@section('title', 'Đăng Ký | AbyssalMind')

@section('content')
<div class="auth-page" style="--hero-image: url('{{ asset('images/ocean-hero.jpg') }}');">
    <div class="auth-card">
        <div>
            <span class="hero-eyebrow" style="background: rgba(22,104,196,0.08); border-color: rgba(22,104,196,0.18); color: var(--blue);">Bắt đầu hành trình</span>
            <h1 style="margin-top: 0.9rem;">Tạo tài khoản</h1>
            <p class="auth-intro">Lưu trữ điểm số và tham gia bảng xếp hạng đại dương.</p>
        </div>

        @if($errors->any())
            <div class="flash-bar error" style="margin-top: 1.25rem;">
                <ul style="padding-left: 1.1rem;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.submit') }}" method="POST">
            @csrf
            <div>
                <label for="name">Họ và tên</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control" placeholder="Nguyễn Văn A" autocomplete="name" required>
            </div>

            <div>
                <label for="email">Địa chỉ email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="email@example.com" autocomplete="email" required>
            </div>

            <div>
                <label for="password">Mật khẩu (tối thiểu 6 ký tự)</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Nhập mật khẩu" autocomplete="new-password" required>
            </div>

            <div>
                <label for="password_confirmation">Nhập lại mật khẩu</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Nhập lại mật khẩu" autocomplete="new-password" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.35rem;">Đăng ký</button>
        </form>

        <p class="auth-alt">
            Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a>
        </p>
    </div>
</div>
@endsection
