@extends('admin.layouts.index')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Edit Lieferantenangebot</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.projects.offers.update', $offer) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Lieferant</label>
                        <select name="supplier_id" class="form-select" required>
                            <option value="">-- Lieferant wählen --</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ $supplier->id === $offer->supplier_id ? 'selected' : '' }}>{{ $supplier->name }} ({{ $supplier->company }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Bauteil</label>
                        <select name="bauteil_id" class="form-select" required>
                            <option value="">-- Bauteil wählen --</option>
                            @foreach ($bauteile as $bauteil)
                                <option value="{{ $bauteil->id }}" {{ $bauteil->id === $offer->bauteil_id ? 'selected' : '' }}>{{ $bauteil->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="parent_offer_id" class="form-label">Elternangebot</label>
                        <select name="parent_offer_id" id="parent_offer_id" class="form-select">
                            <option value="">— Kein Elternangebot —</option>
                            @foreach($offers as $pOffer)
                                <option value="{{ $pOffer->id }}" 
                                    {{ old('parent_offer_id', $offer->parent_offer_id ?? '') == $pOffer->id ? 'selected' : '' }}>
                                    {{ $pOffer->offer_number }} 
                                    — {{ $pOffer->supplier->name ?? '' }} / {{ $pOffer->bauteil->name ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="project_service_id" class="form-label">Angebot Leistung</label>
                        <select name="project_service_id" id="project_service_id" class="form-select">
                            <option value="">— Keine Leistung —</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" 
                                    {{ old('project_service_id', $offer->project_service_id ?? '') == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }} 
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Datum</label>
                        <input type="date" name="date" value="{{ old('date', $offer->date) }}" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Preis (€)</label>
                        <input type="number" step="0.01" name="price" value="{{ old('price', $offer->price) }}" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Dauer (Tage)</label>
                        <input type="number" name="duration" value="{{ old('duration', $offer->duration) }}" class="form-control">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Stückzahl</label>
                        <input type="number" name="pieces_to_develop" value="{{ old('pieces_to_develop', $offer->pieces_to_develop) }}" class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Beschreibung</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $offer->description) }}</textarea>
                </div>

                <button type="submit" class="btn btn-wechsel">Speichern</button>
                <a href="{{ route('admin.projects.offers') }}" class="btn btn-secondary">Abbrechen</a>
            </form>
        </div>
    </div>
</div>
@endsection
