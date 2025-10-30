<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Fräsmaschine Logs')</title>
    <link rel="icon" href="images/zimmermann-logo-192.png" type="image/x-icon" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    @stack('styles')
</head>
<body class="bg-light">

    {{-- ========== NAVBAR ========== --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-seconday border">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="{{ asset('images/logo-team-zimmermann.png') }}" alt="Company Logo" height="50">
                Zimatec
            </a>

            <button class="btn btn-outline-dark ms-auto" id="runLogBtn" data-url="{{ route('parse.log') }}">Parse Log</button>
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
