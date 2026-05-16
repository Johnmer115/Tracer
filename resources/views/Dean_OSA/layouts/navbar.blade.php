<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AU-SARF')</title>

    <link rel="icon"       type="image/png" href="{{ asset('image/logo/arellano_logo.png') }}">
    <link rel="shortcut icon"               href="{{ asset('image/logo/arellano_logo.png') }}">

    {{-- Bootstrap 5 CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Tabler Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">

    {{-- Project CSS --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/searchable-select.css') }}">

    {{-- Per-page extra CSS --}}
    @stack('styles')
</head>
<body>

    {{-- Sidebar is injected by each user-type layout --}}
    @yield('sidebar')

    <div class="main">

        {{-- ── Top Bar ── --}}
        <header class="topbar">
            <button class="menu-toggle" id="menuToggle" type="button" aria-label="Toggle sidebar">
                <i class="fas fa-bars"></i>
            </button>

            <div class="flex-1">
                <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
            </div>

            {{-- Extra topbar items (optional per user-type layout) --}}
            @yield('topbar-right')

            {{-- ── User (top-right corner) ── --}}
            <div class="tu-wrap dropdown">
                <button class="tu-trigger dropdown-toggle" type="button"
                        id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    {{-- Text: name + role (right-aligned) --}}
                    <div class="tu-text">
                        <span class="tu-name">{{ auth()->user()->username }}</span>
                        <span class="tu-role">{{ auth()->user()->usertype }}</span>
                    </div>
                    {{-- Avatar circle --}}
                    <div class="tu-avatar">
                        {{ strtoupper(substr(auth()->user()->username, 0, 1)) }}
                    </div>
                </button>

                <div class="dropdown-menu dropdown-menu-end tu-dropdown" aria-labelledby="userDropdown">
                    <div class="tu-dd-row">
                        <div class="tu-dd-avatar">
                            {{ strtoupper(substr(auth()->user()->username, 0, 1)) }}
                        </div>
                        <div>
                            <div class="tu-dd-name">{{ auth()->user()->username }}</div>
                            <div class="tu-dd-role">{{ auth()->user()->usertype }}</div>
                        </div>
                    </div>
                    <div class="tu-dd-sep"></div>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="tu-logout-btn">
                            <i class="fas fa-sign-out-alt"></i> Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </header>



        {{-- ── Page Content ── --}}
        <div class="content">
            @yield('content')
        </div>
    </div>

    {{-- Bootstrap 5 JS bundle (includes Popper) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Shared sidebar / notification JS --}}
    <script src="{{ asset('js/searchable-select.js') }}"></script>
    <script src="{{ asset('js/layout.js') }}"></script>

    {{-- Per-page extra scripts --}}
    @stack('scripts')

</body>
</html>
