<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectService;
use App\Models\SupplierOffer;
use App\Models\Supplier;
use App\Models\Bauteil;
use Illuminate\Http\Request;

class SupplierOfferController extends Controller
{
    public function index(Request $request)
    {
        $query = SupplierOffer::with(['supplier', 'bauteil', 'service', 'project']);

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('bauteil_id')) {
            $query->where('bauteil_id', $request->bauteil_id);
        }

        if ($request->filled('project_service_id')) {
            $query->where('project_service_id', $request->project_service_id);
        }

        if ($request->filled('has_project')) {
            if ($request->has_project == '1') {
                $query->whereHas('project');
            } elseif ($request->has_project == '0') {
                $query->whereDoesntHave('project');
            }
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $offers = $query->paginate(10);

        $suppliers = Supplier::orderBy('name')->get();
        $bauteile = Bauteil::orderBy('name')->get();
        $services = ProjectService::orderBy('name')->get();

        return view('admin.projects.offers.index', compact('offers', 'suppliers', 'bauteile', 'services'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $bauteile  = Bauteil::all();
        $offers = SupplierOffer::all();
        $services = ProjectService::all();

        return view('admin.projects.offers.create', compact('suppliers', 'bauteile', 'offers', 'services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'        => 'required|exists:suppliers,id',
            'bauteil_id'         => 'required|exists:bauteile,id',
            'parent_offer_id'    => 'nullable|exists:supplier_offers,id',
            'project_service_id' => 'nullable|exists:project_services,id',
            'date'               => 'required|date',
            'price'              => 'required|numeric',
            'description'        => 'nullable|string',
            'duration'           => 'nullable|integer',
            'pieces_to_develop'  => 'nullable|integer',
        ]);

        // Get last offer number for this supplier & bauteil
        $lastOffer = SupplierOffer::where('supplier_id', $request->supplier_id)
                        ->where('bauteil_id', $request->bauteil_id)
                        ->orderByDesc('offer_number') // assuming numeric part only
                        ->first();

        if ($lastOffer) {
            $lastOfferNumber = $lastOffer->offer_number;
            $parts = explode('-', $lastOfferNumber);
            $lastNumber = (int) end($parts);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $offerNumber = $request->supplier_id . '-' . $request->bauteil_id . '-' . str_pad($newNumber, 2, '0', STR_PAD_LEFT);

        SupplierOffer::create([
            'supplier_id'        => $request->supplier_id,
            'bauteil_id'         => $request->bauteil_id,
            'parent_offer_id'    => $request->parent_offer_id,
            'project_service_id' => $request->project_service_id,
            'date'               => $request->date,
            'price'              => $request->price,
            'offer_number'       => $offerNumber,
            'description'        => $request->description,
            'duration'           => $request->duration,
            'pieces_to_develop'  => $request->pieces_to_develop,
        ]);

        return redirect()->route('admin.projects.offers')->with('success', 'Lieferantenangebot erfolgreich erstellt.');
    }

    public function show(SupplierOffer $offer)
    {
        $offer->load(['supplier', 'bauteil', 'parentOffer']);

        return view('admin.projects.offers.show', compact('offer'));
    }

    public function edit(SupplierOffer $offer)
    {
        $suppliers = Supplier::all();
        $bauteile  = Bauteil::all();
        $offers = SupplierOffer::where('id', '!=', $offer->id)->get();
        $services = ProjectService::all();

        return view('admin.projects.offers.edit', compact('offer', 'suppliers', 'bauteile', 'offers', 'services'));
    }

    public function update(Request $request, SupplierOffer $offer)
    {
        $request->validate([
            'supplier_id'        => 'required|exists:suppliers,id',
            'bauteil_id'         => 'required|exists:bauteile,id',
            'parent_offer_id'    => 'nullable|exists:supplier_offers,id',
            'project_service_id' => 'nullable|exists:project_services,id',
            'date'               => 'required|date',
            'price'              => 'required|numeric',
            'description'        => 'nullable|string',
            'duration'           => 'nullable|integer',
            'pieces_to_develop'  => 'nullable|integer',
        ]);

        $offer->update($request->all());

        return redirect()->route('admin.projects.offers')->with('success', 'Angebot aktualisiert.');
    }

    public function destroy(SupplierOffer $offer)
    {
        $offer->delete();

        return redirect()->route('admin.projects.offers')->with('success', 'Angebot gelöscht.');
    }
}
