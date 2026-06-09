@extends('admin.layouts.index')

@section('content')

<div id="alert-container" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; width: auto; min-width: 300px;"></div>

<div class="container py-4">

    <!-- HEADER CARD -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Materialverwaltung (Werkstatt)</h4>

            <!-- ADD BUTTON -->
            <button class="btn btn-secondary" onclick="openAddModal()">
                + Neues Material
            </button>
        </div>

        <div class="card-body">
            <!-- FILTERS -->
            <form id="filterForm" method="GET" action="{{ route('admin.tablar.index') }}">
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">

                            <!-- Name Filter -->
                            <div class="col-md-4">
                                <label class="form-label">Materialname</label>
                                <input
                                    type="text"
                                    name="name"
                                    id="filterName"
                                    class="form-control"
                                    placeholder="z.B. Schraube"
                                    value="{{ request('name') }}"
                                >
                            </div>

                            <!-- Quantity Range -->
                            <div class="col-md-4">
                                <label class="form-label">
                                    Menge (max): <span id="qtyValue">{{ request('max_qty', $maxQuantity) }}</span>
                                </label>
                                <input
                                    type="range"
                                    class="form-range"
                                    min="0"
                                    max="{{ $maxQuantity }}"
                                    value="{{ request('max_qty', $maxQuantity) }}"
                                    id="filterQuantity"
                                    name="max_qty"
                                >
                            </div>

                            <!-- Tablar -->
                            <div class="col-md-3">
                                <label class="form-label">Tablar</label>
                                <input
                                    type="text"
                                    name="shelf"
                                    id="filterShelf"
                                    class="form-control"
                                    placeholder="z.B. A1"
                                    value="{{ request('shelf') }}"
                                >
                            </div>

                            <!-- Submit -->
                            <div class="d-none">
                                <div class="col-md-1">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

                        </div>

                        <!-- Active filters + reset -->
                        @if(request()->hasAny(['name', 'shelf', 'max_qty']))
                        <div class="mt-2">
                            <a href="{{ route('admin.tablar.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i> Filter zurücksetzen
                            </a>
                        </div>
                        @endif

                    </div>
                </div>
            </form>

            <table class="table table-hover align-middle border-top">
                <thead class="table-light">
                    <tr class="text-secondary text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.05em;">
                        <th>Name</th>
                        <th>Menge</th>
                        <th>Fach</th>
                        <th class="text-end">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($materials as $material)
                        <tr class="clickable-row
                            @if(
                                ($material->threshold && $material->quantity <= $material->threshold) ||
                                (!$material->threshold && $material->quantity <= 20)
                            ) table-danger
                            @endif"
                            data-id="{{ $material->id }}"
                            data-name="{{ $material->name }}"
                            data-quantity="{{ $material->quantity }}"
                            data-tablar="{{ $material->tablar ?? '' }}"
                            data-threshold="{{ $material->threshold ?? '' }}"
                            data-type="{{ $material->type ?? '' }}"
                            >
                            <td class="fw-bold text-dark">{{ $material->name }}</td>
                            <td><span class="badge rounded-pill bg-light text-dark border">{{ $material->quantity }} Stk.</span></td>
                            <td class="text-muted small">{{ $material->tablar }}</td>
                            <td class="text-end">
                                <button class="btn btn-outline-dark btn-sm me-1" onclick="openSupplierModal(this)">
                                    <i class="bi bi-info-circle"></i>
                                </button>
                                <button class="btn btn-outline-primary btn-sm me-1" onclick="openEditModal(this)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-sm" onclick="deleteMaterial('{{ $material->id }}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center mt-4">
                {{ $materials->links() }}
            </div>
        </div>
    </div>
</div>

<!-- ADD / EDIT MODAL -->
<div class="modal fade" id="materialModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 id="modalTitle">Material hinzufügen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="materialForm">
                    <input type="hidden" id="materialId">

                    <!-- NAME -->
                    <div class="mb-3">
                        <label class="form-label">
                            Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="name" class="form-control" required>
                    </div>

                    <!-- CURRENT QUANTITY (READ ONLY) -->
                    <div class="mb-2">
                        <label class="form-label">Aktueller Bestand</label>
                        <input type="number" id="currentQuantity" class="form-control bg-light" readonly>
                    </div>

                    <!-- ADD STOCK -->
                    <div class="mb-3">
                        <label class="form-label">
                            Hinzufügen (+ Menge) <span class="text-danger">*</span>
                        </label>
                        <input type="number" id="addQuantity" class="form-control" min="0" value="0">
                    </div>

                    <!-- TABLAR -->
                    <div class="mb-3">
                        <label class="form-label">
                            Fach / Tablar <span class="text-muted">(optional)</span>
                        </label>
                        <input type="text" id="tablar" class="form-control">
                    </div>

                    <!-- THRESHOLD -->
                    <div class="mb-3">
                        <label class="form-label">
                            Mindestbestand <span class="text-muted">(optional)</span>
                        </label>
                        <input type="number" id="threshold" class="form-control" min="0" placeholder="z.B. 50">
                    </div>

                    <!-- TYPE -->
                    <div class="mb-3">
                        <label class="form-label">
                            Typ <span class="text-muted">(optional)</span>
                        </label>
                        <input type="text" id="type" class="form-control" placeholder="z.B. Schrauben, Kunststoff">
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                <button class="btn btn-primary" onclick="saveMaterial()">Speichern</button>
            </div>

        </div>
    </div>
</div>

<!-- SUPPLIER MODAL -->
<div class="modal fade" id="supplierModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Lieferanten für: <span id="supplierModalMaterialName" class="text-muted fw-normal"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- Search & Attach -->
                <div class="card bg-light border-0 mb-4">
                    <div class="card-body py-3">
                        <label class="form-label small fw-bold text-muted mb-2">Bestehenden Lieferanten hinzufügen</label>

                        <!-- Search input -->
                        <div class="position-relative">
                            <input
                                type="text"
                                id="supplierSearchInput"
                                class="form-control form-control-sm"
                                placeholder="Name oder Firma suchen..."
                                autocomplete="off"
                            >
                            <!-- Live results dropdown -->
                            <ul
                                id="supplierSearchResults"
                                class="list-group shadow position-absolute w-100 d-none"
                                style="z-index: 1055; max-height: 200px; overflow-y: auto; top: 100%; left: 0;"
                            ></ul>
                        </div>

                        <!-- Selected supplier badge -->
                        <div id="supplierSearchSelected" class="mt-2 d-none">
                            <span class="badge bg-success fs-6 fw-normal py-2 px-3">
                                <i class="bi bi-check-circle me-1"></i>
                                <span id="supplierSearchSelectedName"></span>
                                <button
                                    type="button"
                                    class="btn-close btn-close-white ms-2"
                                    style="font-size:0.6rem;"
                                    onclick="clearSupplierSelection()"
                                ></button>
                            </span>
                        </div>

                        <!-- Attach button -->
                        <div class="mt-2">
                            <button class="btn btn-success btn-sm" type="button" onclick="attachSupplier()">
                                <i class="bi bi-plus-lg me-1"></i> Zuweisen
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Loading spinner -->
                <div id="supplierLoading" class="text-center py-4">
                    <div class="spinner-border text-secondary" role="status"></div>
                    <p class="text-muted mt-2 mb-0">Lieferanten werden geladen...</p>
                </div>

                <!-- Error -->
                <div id="supplierError" class="alert alert-danger d-none">
                    Fehler beim Verarbeiten der Anfrage.
                </div>

                <!-- Empty state -->
                <div id="supplierEmpty" class="text-center text-muted py-4 d-none">
                    <i class="bi bi-box-seam fs-3 d-block mb-2"></i>
                    Kein Lieferant für dieses Material hinterlegt.
                </div>

                <!-- Attached suppliers list -->
                <ul id="supplierList" class="list-group list-group-flush d-none"></ul>

            </div>

            <div class="modal-footer justify-content-between">
                <a href="{{ route('admin.suppliers.create') }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> Neuer Lieferant
                </a>
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Schließen</button>
            </div>

        </div>
    </div>
</div>

<script>
    window.tablarAdmin = {
        token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        maxQuantity: {{ $maxQuantity }}
    };
</script>
<script src="{{ asset('js/admin/tablar/index.js') }}?v=1.0"></script>

@endsection