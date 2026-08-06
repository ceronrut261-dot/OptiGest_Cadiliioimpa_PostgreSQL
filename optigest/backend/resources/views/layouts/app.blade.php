<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'OptiGest') | Constru Fontanería Cadiliompa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <button class="btn btn-outline-light d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMobile">
                <i class="bi bi-list"></i>
            </button>
            <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">OptiGest</a>
            <span class="navbar-text text-white-50 d-none d-md-inline ms-2">Constru Fontanería Cadiliompa</span>

            <div class="ms-auto d-flex align-items-center gap-3">
                @auth
                    <a href="{{ route('asistente.index') }}" class="btn btn-sm btn-outline-light">
                        <i class="bi bi-robot"></i> Asistente
                    </a>
                    <span class="text-white small d-none d-sm-inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-sm btn-light">Salir</button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>

    <div class="d-flex" style="margin-top:56px;">
        <aside class="d-none d-lg-block bg-light border-end" style="width:230px; min-height:calc(100vh - 56px);">
            @include('layouts.partials.menu')
        </aside>

        <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMobile">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title">Menú</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body p-0">
                @include('layouts.partials.menu')
            </div>
        </div>

        <main class="flex-grow-1 p-3 p-md-4">
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('contenido')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @yield('scripts')
</body>
</html>
