@extends('user.layouts.index')

@section('title', 'Tablar Übersicht')

@section('content')
<div class="container py-4" style="max-width: 720px;">

    <!-- HEADER -->
    <div class="text-center mb-4">
        <h1 class="fw-bold fs-2">🏭 Hochregal - Materialerfassung</h1>
        <p class="text-muted mb-0">Tablar wählen, oder Name suchen</p>
    </div>

    <!-- MODE TABS -->
    <ul class="nav nav-pills nav-fill mb-4" id="searchModeTabs">
        <li class="nav-item">
            <button class="nav-link active" id="tabShelf" onclick="switchMode('shelf')">
                <i class="bi bi-grid-3x3-gap me-1"></i> Nach Tablar
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="tabName" onclick="switchMode('name')">
                <i class="bi bi-search me-1"></i> Nach Name
            </button>
        </li>
    </ul>

    <!-- STEP 1: SHELF SELECTOR -->
    <div id="shelfStep">
        <p class="text-muted small text-uppercase fw-semibold mb-2 ms-1">Tablar wählen</p>
        <input
            type="text"
            id="shelfSearch"
            class="form-control mb-3"
            placeholder="Tablar suchen... z.B. A1"
            oninput="filterShelves()"
        >
        <div id="shelfGrid" class="row g-2">
            @foreach($shelves as $shelf)
                <div class="col-4 col-md-3 shelf-tile" data-shelf="{{ strtolower($shelf) }}">
                    <button
                        class="btn btn-outline-secondary w-100 py-3 fw-bold fs-5"
                        onclick="selectShelf('{{ $shelf }}')"
                    >
                        {{ $shelf }}
                    </button>
                </div>
            @endforeach
        </div>
    </div>

    <!-- NAME SEARCH (hidden by default) -->
    <div id="nameStep" class="d-none">
        <p class="text-muted small text-uppercase fw-semibold mb-2 ms-1">Nach Materialname suchen</p>
        <input
            type="text"
            id="globalNameSearch"
            class="form-control mb-3"
            placeholder="z.B. Schraube, Kabel..."
            oninput="filterByName()"
            autofocus
        >
        <div id="globalNameResults"></div>
    </div>


    <!-- STEP 2: MATERIAL LIST (hidden until shelf selected) -->
    <div id="materialStep" class="d-none">

        <!-- Back button + current shelf label -->
        <div class="d-flex align-items-center mb-3">
            <button class="btn btn-sm btn-outline-secondary me-3" onclick="goBackToShelves()">
                ← Zurück
            </button>
            <div>
                <span class="text-muted small text-uppercase fw-semibold">Tablar</span>
                <span id="selectedShelfLabel" class="fs-5 fw-bold ms-2"></span>
            </div>
        </div>

        <!-- Search within shelf -->
        <input
            type="text"
            id="materialSearch"
            class="form-control mb-3"
            placeholder="Material suchen..."
            oninput="filterMaterials()"
            autofocus
        >

        <!-- Material list -->
        <div id="materialList"></div>

    </div>

</div>

<!-- CONSUMPTION MODAL -->
<div class="modal fade" id="materialModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4">

            <h3 id="modalMaterialName" class="mb-1 fs-4"></h3>
            <p class="text-muted mb-1" id="modalShelf"></p>
            <p class="text-muted small mb-4">
                Verfügbar: <strong id="modalAvailable"></strong> Stk.
            </p>

            <!-- COUNTER -->
            <div class="d-flex justify-content-center align-items-center mb-4">
                <button class="btn btn-lg btn-outline-danger px-4" onclick="decrease()">−</button>
                <input
                    type="number"
                    id="counterInput"
                    class="form-control form-control-lg mx-3 text-center fw-bold fs-2"
                    style="width: 110px;"
                    value="1"
                    min="1"
                    oninput="validateManualInput(this)"
                >
                <button class="btn btn-lg btn-outline-success px-4" onclick="increase()">+</button>
            </div>

            <button class="btn btn-primary btn-lg w-100" onclick="confirmConsumption()">
                ✅ Material entnommen
            </button>

            <button class="btn btn-link text-muted mt-2" data-bs-dismiss="modal">Abbrechen</button>

        </div>
    </div>
</div>

<script>
    window.tablarData = {
        flatList: @json($flatList)
    };
</script>

<script src="{{ asset('js/user/tablar/index.js') }}"></script>

<style>
.material-item {
    cursor: pointer;
    transition: background-color 0.15s;
}
.material-item:hover {
    background-color: #f0f4ff;
}
</style>

@endsection