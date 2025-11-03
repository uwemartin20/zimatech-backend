<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Fräsmaschine Logs')</title>
    <link rel="icon" href="{{ asset('images/zimmermann-logo-192.png') }}" type="image/x-icon" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add this inside your <head> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    @stack('styles')
</head>
<body class="bg-light">

    {{-- ========== NAVBAR ========== --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-light border">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <img src="{{ asset('images/logo-team-zimmermann.png') }}" alt="Company Logo" height="50">
                Zimatec
            </a>

            <div class="ms-auto d-flex align-items-center">

                {{-- When user is logged in --}}
                @auth
                    <a href="{{ route('projects') }}" class="text-decoration-none text-dark p-2">Projects</a>
                    <button class="btn btn-outline-dark me-2" id="runLogBtn" data-url="{{ route('parse.log') }}">
                        Parse Log
                    </button>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-danger">Logout</button>
                    </form>
                @endauth

                {{-- When user is NOT logged in --}}
                @guest
                    <a href="{{ route('login') }}" class="btn btn-outline-dark me-2">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Register</a>
                @endguest
            </div>
        </div>
    </nav>

    {{-- Alert placeholder --}}
    <div class="container mt-3">
        <div id="logAlert"></div>
    </div>

    {{-- ========== MAIN CONTENT ========== --}}
    <main class="container py-4">
        @yield('content')
    </main>

    {{-- ========== FOOTER ========== --}}
    <footer class="bg-dark text-white text-center py-3 mt-4">
        &copy; {{ date('Y') }} Zimatec. Alle Rechte vorbehalten.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
     <script src="{{ asset('js/custom.js') }}"></script> 
    @stack('scripts')
</body>
</html>
