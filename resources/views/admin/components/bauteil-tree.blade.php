<div class="list-group ms-3">
    @foreach($bauteile as $bauteil)
        <div class="list-group-item mb-2 p-2 border rounded shadow-sm d-flex align-items-center justify-content-between flex-wrap">
            
            <div class="d-flex align-items-center gap-3">
                {{-- Image --}}
                @if($bauteil->image)
                    <img src="{{ asset('storage/' . $bauteil->image) }}" alt="{{ $bauteil->name }}" width="60" height="60" class="rounded border">
                @else
                    <div class="bg-secondary text-white rounded d-flex align-items-center justify-content-center" style="width:60px; height:60px;">
                        <i class="bi bi-box-seam"></i>
                    </div>
                @endif

                {{-- Name & link --}}
                <div>
                    <a href="{{ route('admin.bauteile.show', $bauteil->id) }}" class="h6 text-decoration-none text-dark">
                        {{ $bauteil->name }}
                    </a>

                    {{-- Badges --}}
                    @if($bauteil->is_werkzeug)
                        <span class="badge bg-primary ms-1">Werkzeug</span>
                    @endif
                    @if($bauteil->is_baugruppe)
                        <span class="badge bg-success ms-1">Baugruppe</span>
                    @endif
                    {{-- Home icon for in-house production --}}
                    @if($bauteil->in_house_production)
                        <i class="bi bi-house-door-fill text-warning ms-2" title="In-house Produktion"></i>
                    @endif
                </div>
            </div>

            {{-- Right Section: Action buttons --}}
            <div class="d-flex align-items-center gap-2">
                {{-- Child Count --}}
                @if($bauteil->children->count())
                    <span class="badge bg-secondary">{{ $bauteil->children->count() }} Kinder</span>
                @endif

                {{-- Edit --}}
                <a href="{{ route('admin.bauteile.edit', $bauteil->id) }}" 
                   class="btn btn-sm btn-outline-primary" title="Bearbeiten">
                    <i class="bi bi-pencil"></i>
                </a>

                {{-- Delete --}}
                <form action="{{ route('admin.bauteile.destroy', $bauteil->id) }}" 
                      method="POST" class="d-inline"
                      onsubmit="return confirm('Bauteil wirklich löschen?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Löschen">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        </div>

        {{-- Recursive call for children --}}
        @if($bauteil->children->count())
            <div class="ms-4">
                @include('admin.components.bauteil-tree', ['bauteile' => $bauteil->children])
            </div>
        @endif
    @endforeach
</div>
