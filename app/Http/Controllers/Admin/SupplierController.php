<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\ProjectService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::with('services');

        // ✅ Name or Company filter
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('company', 'like', '%' . $request->search . '%');
            });
        }

        // ✅ Service filter
        if ($request->filled('service_id')) {
            $query->whereHas('services', function ($q) use ($request) {
                $q->where('project_services.id', $request->service_id);
            });
        }

        $suppliers = $query->latest()->paginate(10)->appends($request->query());
        $services = ProjectService::all();
        return view('admin.suppliers.index', compact('suppliers', 'services'));
    }

    public function create()
    {
        $services = ProjectService::all();
        return view('admin.suppliers.create', compact('services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'company'      => 'nullable|string|max:255',
            'address'      => 'nullable|string|max:500',
            'phone_number' => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:255',
            'website'      => 'nullable|url|max:255',
            'services'     => 'nullable|array',
        ]);

        $supplier = Supplier::create($request->all());

        // Attach selected services
        if ($request->has('services')) {
            $supplier->services()->sync($request->services);
        }

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier successfully added.');
    }

    public function edit(Supplier $supplier)
    {
        $services = ProjectService::all();
        $selectedServices = $supplier->services()->pluck('service_id')->toArray();

        return view('admin.suppliers.edit', compact('supplier', 'services', 'selectedServices'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'company'      => 'nullable|string|max:255',
            'address'      => 'nullable|string|max:500',
            'phone_number' => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:255',
            'website'      => 'nullable|url|max:255',
            'services'     => 'nullable|array',
        ]);

        $supplier->update($request->all());

        // Sync updated services
        $supplier->services()->sync($request->services ?? []);

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier successfully updated.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier successfully deleted.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load('services', 'offers.projects.status');

        return view('admin.suppliers.show', compact('supplier'));
    }

}
