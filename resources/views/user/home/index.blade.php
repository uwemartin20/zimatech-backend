@extends('user.layouts.index')

@section('title', 'Welcome to Your CMS')

@section('content')
    <div class="container py-5 text-center">
        <div class="row justify-content-center">
            <div class="col-md-8">

                <h1 class="display-5 fw-bold mb-3">Welcome to <span class="text-primary">Zimatech GmbH</span></h1>
                <p class="lead text-muted mb-4">
                    Manage projects, users, and settings seamlessly from one place.
                </p>

                <div class="d-flex justify-content-center gap-3">
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-primary px-4">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Login
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-person-plus me-1"></i> Register
                        </a>
                    @else
                        <a href="{{ route('projects') }}" class="btn btn-success px-4">
                            <i class="bi bi-speedometer2 me-1"></i> Go to Projects
                        </a>

                        @if(Auth::user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-warning px-4">
                                <i class="bi bi-gear-wide-connected me-1"></i> Admin Dashboard
                            </a>
                        @endif
                    @endguest
                </div>

            </div>
        </div>
    </div>
@endsection
