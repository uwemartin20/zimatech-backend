<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>
    <link rel="icon" href="{{ asset('images/zimmermann-logo-192.png') }}" type="image/x-icon" />
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add this inside your <head> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    {{-- Custom CSS for personalized layout --}}
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">

</head>
<body>

    <!-- Sidebar -->
    @include('admin.partials.sidebar')

    <!-- Content -->
    <div class="content">
        @include('admin.partials.navbar')
        <div class="container-fluid mt-4">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
