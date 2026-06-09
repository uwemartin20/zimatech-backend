// Full material list from PHP — flat, all shelves
const allMaterials = window.tablarData.flatList;

// Group by shelf for fast lookup: { "A1": [...], "B2": [...] }
const byShelf = {};
allMaterials.forEach(m => {
    const key = m.shelf ?? 'Unbekannt';
    if (!byShelf[key]) byShelf[key] = [];
    byShelf[key].push(m);
});

let currentShelf = null;
let currentShelfMaterials = [];
let selectedMaterial = null;

const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// ─── SHELF SELECTION ──────────────────────────────────────────────────────────

function filterShelves() {
    const q = document.getElementById('shelfSearch').value.toLowerCase();
    document.querySelectorAll('.shelf-tile').forEach(tile => {
        tile.style.display = tile.dataset.shelf.includes(q) ? '' : 'none';
    });
}

function selectShelf(shelf) {
    currentShelf = shelf;
    currentShelfMaterials = byShelf[shelf] ?? [];

    document.getElementById('selectedShelfLabel').innerText = shelf;
    document.getElementById('materialSearch').value = '';
    document.getElementById('shelfStep').classList.add('d-none');
    document.getElementById('materialStep').classList.remove('d-none');

    renderMaterials(currentShelfMaterials);

    // Auto-focus search after short delay (modal animation)
    setTimeout(() => document.getElementById('materialSearch').focus(), 150);
}

function goBackToShelves() {
    currentShelf = null;
    document.getElementById('materialStep').classList.add('d-none');
    document.getElementById('shelfStep').classList.remove('d-none');
    document.getElementById('shelfSearch').value = '';
    filterShelves(); // reset shelf tiles
}

// ─── MATERIAL LIST ────────────────────────────────────────────────────────────

function filterMaterials() {
    const q = document.getElementById('materialSearch').value.toLowerCase();
    const filtered = q
        ? currentShelfMaterials.filter(m => m.name.toLowerCase().includes(q))
        : currentShelfMaterials;
    renderMaterials(filtered);
}

function renderMaterials(materials) {
    const container = document.getElementById('materialList');

    if (materials.length === 0) {
        container.innerHTML = `<div class="text-center text-muted py-5">
            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
            Kein Material gefunden.
        </div>`;
        return;
    }

    container.innerHTML = materials.map(m => {
        const outOfStock = m.quantity <= 0;
        const threshold  = m.threshold ?? 20;
        const badgeClass = outOfStock
            ? 'bg-secondary'
            : m.quantity > threshold ? 'bg-success' : 'bg-danger';
        const badgeText  = outOfStock ? 'Kommt gleich' : m.quantity + ' Stk.';

        if (outOfStock) {
            return `
            <div class="d-flex justify-content-between align-items-center
                        p-3 mb-2 rounded border bg-light text-muted"
                 style="cursor: not-allowed;"
                 onclick="Swal.fire('Nicht verfügbar', 'Bitte warten Sie auf Nachschub.', 'info')">
                <span class="text-decoration-line-through">${m.name}</span>
                <span class="badge ${badgeClass}">${badgeText}</span>
            </div>`;
        }

        return `
        <div class="d-flex justify-content-between align-items-center
                    p-3 mb-2 rounded border material-item"
             onclick="openMaterialModal(${m.id}, '${m.name}', ${m.quantity}, '${m.shelf}')">
            <span class="fw-semibold">${m.name}</span>
            <span class="badge ${badgeClass} fs-6">${badgeText}</span>
        </div>`;
    }).join('');
}

// ─── MODAL ────────────────────────────────────────────────────────────────────

function openMaterialModal(id, name, quantity, shelf) {
    selectedMaterial = { id, name, quantity, shelf };

    document.getElementById('modalMaterialName').innerText = name;
    document.getElementById('modalShelf').innerText        = 'Tablar: ' + shelf;
    document.getElementById('modalAvailable').innerText    = quantity;

    const input = document.getElementById('counterInput');
    input.value = 1;
    input.max   = quantity;

    new bootstrap.Modal(document.getElementById('materialModal')).show();
}

function validateManualInput(input) {
    let val = parseInt(input.value);
    if (isNaN(val) || val < 1)                    input.value = 1;
    if (val > selectedMaterial.quantity)           input.value = selectedMaterial.quantity;
}

function increase() {
    const input = document.getElementById('counterInput');
    const val   = parseInt(input.value);
    if (val < selectedMaterial.quantity) input.value = val + 1;
}

function decrease() {
    const input = document.getElementById('counterInput');
    const val   = parseInt(input.value);
    if (val > 1) input.value = val - 1;
}

// ─── CONFIRM CONSUMPTION ──────────────────────────────────────────────────────

async function confirmConsumption() {
    const amountTaken = parseInt(document.getElementById('counterInput').value);
    if (!selectedMaterial || isNaN(amountTaken)) return;

    const btn = document.querySelector('#materialModal .btn-primary');
    btn.disabled    = true;
    btn.innerHTML   = `<span class="spinner-border spinner-border-sm me-2"></span> Wird gebucht...`;

    try {
        const res = await fetch('/tablar/consume', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify({ material_id: selectedMaterial.id, quantity: amountTaken })
        });

        if (!res.ok) throw new Error(await res.text());

        const data = await res.json();

        // Update local data so re-renders are correct without a page reload
        const m = allMaterials.find(x => x.id === selectedMaterial.id);
        if (m) m.quantity = data.new_quantity;

        // Re-render the current shelf list
        filterMaterials();

        bootstrap.Modal.getInstance(document.getElementById('materialModal')).hide();

        // Refresh name search results if that tab is active
        if (!document.getElementById('nameStep').classList.contains('d-none')) {
            filterByName();
        }

    } catch (e) {
        alert('Fehler beim Buchen: ' + e.message);
    } finally {
        btn.disabled  = false;
        btn.innerHTML = '✅ Material entnommen';
    }
}

// ─── MODE SWITCH ──────────────────────────────────────────────────────────────

function switchMode(mode) {
    if (mode === 'shelf') {
        document.getElementById('shelfStep').classList.remove('d-none');
        document.getElementById('materialStep').classList.add('d-none');
        document.getElementById('nameStep').classList.add('d-none');
        document.getElementById('tabShelf').classList.add('active');
        document.getElementById('tabName').classList.remove('active');
    } else {
        document.getElementById('nameStep').classList.remove('d-none');
        document.getElementById('shelfStep').classList.add('d-none');
        document.getElementById('materialStep').classList.add('d-none');
        document.getElementById('tabName').classList.add('active');
        document.getElementById('tabShelf').classList.remove('active');
        setTimeout(() => document.getElementById('globalNameSearch').focus(), 150);
    }
}

// ─── GLOBAL NAME SEARCH ───────────────────────────────────────────────────────

function filterByName() {
    const q = document.getElementById('globalNameSearch').value.toLowerCase().trim();
    const container = document.getElementById('globalNameResults');

    if (q.length < 1) {
        container.innerHTML = '';
        return;
    }

    const filtered = allMaterials.filter(m => m.name.toLowerCase().includes(q));

    if (filtered.length === 0) {
        container.innerHTML = `<div class="text-center text-muted py-5">
            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
            Kein Material gefunden.
        </div>`;
        return;
    }

    container.innerHTML = filtered.map(m => {
        const outOfStock = m.quantity <= 0;
        const threshold  = m.threshold ?? 20;
        const badgeClass = outOfStock ? 'bg-secondary'
            : m.quantity > threshold ? 'bg-success' : 'bg-danger';
        const badgeText  = outOfStock ? 'Kommt gleich' : m.quantity + ' Stk.';

        // Show shelf as a hint — critical for misplaced materials
        const shelfHint = m.shelf
            ? `<span class="text-muted small ms-2"><i class="bi bi-geo-alt me-1"></i>${m.shelf}</span>`
            : '';

        if (outOfStock) {
            return `
            <div class="d-flex justify-content-between align-items-center
                        p-3 mb-2 rounded border bg-light text-muted"
                style="cursor: not-allowed;"
                onclick="Swal.fire('Nicht verfügbar', 'Bitte warten Sie auf Nachschub.', 'info')">
                <div>
                    <span class="text-decoration-line-through">${m.name}</span>
                    ${shelfHint}
                </div>
                <span class="badge ${badgeClass}">${badgeText}</span>
            </div>`;
        }

        return `
        <div class="d-flex justify-content-between align-items-center
                    p-3 mb-2 rounded border material-item"
                onclick="openMaterialModal(${m.id}, '${m.name}', ${m.quantity}, '${m.shelf ?? ''}')">
            <div>
                <span class="fw-semibold">${m.name}</span>
                ${shelfHint}
            </div>
            <span class="badge ${badgeClass} fs-6">${badgeText}</span>
        </div>`;
    }).join('');
}

// ─── EXPOSE FUNCTIONS CALLED FROM HTML ATTRIBUTES ────────────────────────────
// Vite bundles JS as a module — onclick="fn()" in HTML can't see module-scoped
// functions unless they're explicitly attached to window.

window.filterShelves       = filterShelves;
window.selectShelf         = selectShelf;
window.goBackToShelves     = goBackToShelves;
window.filterMaterials     = filterMaterials;
window.openMaterialModal   = openMaterialModal;
window.validateManualInput = validateManualInput;
window.increase            = increase;
window.decrease            = decrease;
window.confirmConsumption  = confirmConsumption;
window.switchMode          = switchMode;
window.filterByName        = filterByName;