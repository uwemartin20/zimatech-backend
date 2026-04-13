@extends('admin.layouts.index')

@section('content')
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
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <div class="row g-3">

                        <!-- Name Filter -->
                        <div class="col-md-4">
                            <label class="form-label">Materialname</label>
                            <input type="text" id="filterName" class="form-control" placeholder="z.B. Schraube">
                        </div>

                        <!-- Quantity Range -->
                        <div class="col-md-4">
                            <label class="form-label">
                                Menge (max): <span id="qtyValue">500</span>
                            </label>
                            <input type="range" class="form-range" min="0" max="500" value="500" id="filterQuantity">
                        </div>

                        <!-- Tablar -->
                        <div class="col-md-4">
                            <label class="form-label">Tablar</label>
                            <input type="text" id="filterShelf" class="form-control" placeholder="z.B. A1">
                        </div>

                    </div>
                </div>
            </div>

            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Name</th>
                        <th>Menge</th>
                        <th>Fach</th>
                        <th>Status</th>
                        <th class="text-end">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($materials as $material)
                        <tr class="clickable-row"
                            data-name="{{ strtolower($material['name']) }}"
                            data-quantity="{{ $material['quantity'] }}"
                            data-shelf="{{ strtolower($material['shelf']) }}"
                            onclick="openMaterialModal('{{ $material['name'] }}', {{ $material['quantity'] }}, '{{ $material['shelf'] }}')">

                            <td>{{ $material['name'] }}</td>
                            <td>{{ $material['quantity'] }}</td>
                            <td>{{ $material['shelf'] }}</td>
                            <td>
                                @if($material['quantity'] < 70)
                                    <span class="badge bg-danger">Niedrig</span>
                                @else
                                    <span class="badge bg-success">OK</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-filter"
                                    onclick="openEditModal({{ json_encode($material) }})">
                                    ✏️
                                </button>

                                <button class="btn btn-sm btn-danger"
                                    onclick="deleteMaterial('{{ $material['name'] }}')">
                                    🗑️
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" id="name" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Menge</label>
                        <input type="number" id="quantity" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fach / Tablar</label>
                        <input type="text" id="shelf" class="form-control">
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

<script>
let editMode = false;

// OPEN ADD
function openAddModal() {
    editMode = false;

    document.getElementById('modalTitle').innerText = "Neues Material";
    document.getElementById('materialForm').reset();
    document.getElementById('materialId').value = "";

    new bootstrap.Modal(document.getElementById('materialModal')).show();
}

// OPEN EDIT
function openEditModal(material) {
    editMode = true;

    document.getElementById('modalTitle').innerText = "Material bearbeiten";

    document.getElementById('materialId').value = material.id;
    document.getElementById('name').value = material.name;
    document.getElementById('quantity').value = material.quantity;
    document.getElementById('shelf').value = material.shelf;

    new bootstrap.Modal(document.getElementById('materialModal')).show();
}

// SAVE (SIMULATED)
function saveMaterial() {
    let name = document.getElementById('name').value;
    let quantity = document.getElementById('quantity').value;
    let shelf = document.getElementById('shelf').value;

    if (!name || !quantity || !shelf) {
        alert("Bitte alle Felder ausfüllen");
        return;
    }

    if (editMode) {
        alert("Material aktualisiert: " + name);
    } else {
        alert("Material hinzugefügt: " + name);
    }

    // TODO: replace with AJAX later
}

// DELETE (SIMULATED)
function deleteMaterial(name) {
    if (confirm(`Material "${name}" wirklich löschen?`)) {
        alert("Gelöscht: " + name);
        // TODO: AJAX delete later
    }
}

// FILTER ELEMENTS
const filterName = document.getElementById('filterName');
const filterQuantity = document.getElementById('filterQuantity');
const filterShelf = document.getElementById('filterShelf');
const qtyValue = document.getElementById('qtyValue');

// UPDATE SLIDER LABEL
filterQuantity.addEventListener('input', () => {
    qtyValue.innerText = filterQuantity.value;
    applyFilters();
});

// INPUT EVENTS
filterName.addEventListener('keyup', applyFilters);
filterShelf.addEventListener('keyup', applyFilters);

// MAIN FILTER FUNCTION
function applyFilters() {
    const name = filterName.value.toLowerCase();
    const maxQty = parseInt(filterQuantity.value);
    const shelf = filterShelf.value.toLowerCase();

    const rows = document.querySelectorAll('.clickable-row');

    rows.forEach(row => {
        const rowName = row.dataset.name;
        const rowQty = parseInt(row.dataset.quantity);
        const rowShelf = row.dataset.shelf;

        const matchName = rowName.includes(name);
        const matchQty = rowQty <= maxQty;
        const matchShelf = rowShelf.includes(shelf);

        if (matchName && matchQty && matchShelf) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

@endsection