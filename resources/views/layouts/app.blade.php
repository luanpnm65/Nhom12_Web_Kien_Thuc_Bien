<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AbyssalMind - Đấu Trường Kiến Thức Đại Dương')</title>
    <link rel="stylesheet" href="{{ asset('css/ocean-app.css') }}">
</head>
<body>
    <nav class="ocean-nav">
        <a href="{{ route('welcome') }}" class="brand-logo">AbyssalMind</a>

        <div class="nav-links">
            <a href="{{ route('welcome') }}" class="nav-link {{ request()->routeIs('welcome') ? 'active' : '' }}">Khám phá</a>
            <a href="{{ route('leaderboard') }}" class="nav-link {{ request()->routeIs('leaderboard') ? 'active' : '' }}">Bảng xếp hạng</a>

            @auth
                <a href="{{ route('quiz.index') }}" class="nav-link {{ request()->routeIs('quiz.index', 'quiz.play', 'quiz.run') ? 'active' : '' }}">Chọn bài làm</a>
                <a href="{{ route('quiz.history') }}" class="nav-link {{ request()->routeIs('quiz.history') ? 'active' : '' }}">Lịch sử</a>
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Bảng điều khiển</a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.index') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">Quản trị</a>
                @endif
                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-outline" style="padding: 0.45rem 0.95rem; font-size: 0.85rem;">Đăng xuất</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="nav-link">Đăng nhập</a>
                <a href="{{ route('register') }}" class="btn btn-primary" style="padding: 0.45rem 1.05rem; font-size: 0.86rem;">Đăng ký</a>
            @endauth
        </div>
    </nav>

    @if(session('success'))
        <div class="container" style="padding-bottom: 0;">
            <div class="flash-bar success" role="status">{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="container" style="padding-bottom: 0;">
            <div class="flash-bar error" role="alert">{{ session('error') }}</div>
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
