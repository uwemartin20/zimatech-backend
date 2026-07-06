<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>
    <link rel="icon" href="{{ asset('images/zimmermann-logo-192.png') }}" type="image/x-icon" />

    <!-- PWA Settings -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#002752">
    
    <!-- iOS support -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="ZiMaTec">
    <link rel="apple-touch-icon" href="{{ asset('images/zimmermann-logo-192.png') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap 5 -->
    <link href="{{ asset('bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Add this inside your <head> -->
    <link rel="stylesheet" href="{{ asset('bootstrap/icons/bootstrap-icons.css') }}">
    {{-- Custom CSS for personalized layout --}}
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('styles')

</head>
<body>

    <!-- Sidebar -->
    @include('admin.partials.sidebar')

    {{-- Mobile sidebar backdrop --}}
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- Content -->
    <div class="content">
        @include('admin.partials.navbar')

        <div class="container-fluid mt-4">
            {{-- ✅ Flash messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" id="logAlert"></button>
                </div>
            @elseif(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" id="logAlert"></button>
                </div>
            @else
                <div id="logAlert">
                </div>
            @endif
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="{{ asset('bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    @stack('scripts')
    
    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('Admin SW registered successfully!', reg))
                    .catch(err => console.error('Admin SW registration failed:', err));
            });
        }

        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');

        function openSidebar() {
            sidebar.classList.add('open');
            backdrop.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('open');
            backdrop.classList.remove('active');
            document.body.style.overflow = '';
        }

        sidebarToggle?.addEventListener('click', openSidebar);
        backdrop?.addEventListener('click', closeSidebar);

        // Mobile search
        const mobileSearchToggle = document.getElementById('mobileSearchToggle');
        const mobileSearchOverlay = document.getElementById('mobileSearchOverlay');
        const mobileSearchClose = document.getElementById('mobileSearchClose');
        const mobileSearchInput = document.getElementById('mobileSearchInput');

        mobileSearchToggle?.addEventListener('click', () => {
            mobileSearchOverlay.classList.add('active');
            mobileSearchInput?.focus();
        });

        mobileSearchClose?.addEventListener('click', () => {
            mobileSearchOverlay.classList.remove('active');
        });
    </script>
</body>
</html>
