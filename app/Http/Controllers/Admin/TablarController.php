<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lager;
use App\Models\Material;
use App\Models\MaterialConsumption;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TablarController extends Controller
{
    public function index(Request $request, int $lager_id)
    {
        $lager = Lager::findOrFail($lager_id);

        $query = Material::with('suppliers')
            ->where('lager_id', $lager_id)
            ->orderBy('name');

        if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$request->name.'%');
        }
        if ($request->filled('code')) {
            $query->where('code', 'like', '%'.$request->code.'%');
        }
        if ($request->filled('shelf')) {
            $query->where('tablar', 'like', '%'.$request->shelf.'%');
        }
        if ($request->filled('max_qty')) {
            $query->where('quantity', '<=', $request->max_qty);
        }

        $materials = $query->paginate(30)->withQueryString();
        $maxQuantity = Material::where('lager_id', $lager_id)->max('quantity') ?? 0;

        $statusTranslations = [
            'notified' => 'Bedarf gemeldet',
            'ordered' => 'Bestellt',
            'blocked' => 'Blockiert',
            'delivered' => 'Geliefert',
        ];

        return view('admin.tablar.index', compact('materials', 'maxQuantity', 'statusTranslations', 'lager'));
    }

    public function show(int $lager_id, int $id)
    {
        $lager = Lager::findOrFail($lager_id);
        $material = Material::where('lager_id', $lager_id)->with('suppliers')->findOrFail($id);

        return view('admin.tablar.show', compact('material', 'lager'));
    }

    public function store(Request $request, int $lager_id)
    {
        Lager::findOrFail($lager_id);

        // Normalize empty strings to null before validation
        $request->merge([
            'code' => $request->input('code') ?: null,
            'threshold' => $request->input('threshold') ?: null,
            'type' => $request->input('type') ?: null,
            'order_status' => $request->input('order_status') ?: null,
            'description' => $request->input('description') ?: null,
        ]);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['nullable', 'string', 'max:64', Rule::unique('materials', 'code')->whereNotNull('code')],
            'description' => 'nullable|string|max:2000',
            'quantity' => 'required|integer|min:0',
            'tablar' => 'nullable|string|max:50',
            'threshold' => 'nullable|integer|min:0',
            'type' => 'nullable|string|max:100',
            'order_status' => 'nullable|in:notified,ordered,blocked,delivered',
            'is_werkzeug' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|max:4096',
        ]);

        $data['lager_id'] = $lager_id;
        $data['is_werkzeug'] = $request->boolean('is_werkzeug');
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('materials', 'public');
        }

        return response()->json(Material::create($data));
    }

    public function update(Request $request, int $lager_id, int $id)
    {
        $material = Material::where('lager_id', $lager_id)->findOrFail($id);

        $request->merge([
            'code' => $request->input('code') ?: null,
            'threshold' => $request->input('threshold') ?: null,
            'type' => $request->input('type') ?: null,
            'order_status' => $request->input('order_status') ?: null,
            'description' => $request->input('description') ?: null,
        ]);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['nullable', 'string', 'max:64', Rule::unique('materials', 'code')->ignore($material->id)->whereNotNull('code')],
            'description' => 'nullable|string|max:2000',
            'quantity' => 'required|integer|min:0',
            'tablar' => 'nullable|string|max:50',
            'threshold' => 'nullable|integer|min:0',
            'type' => 'nullable|string|max:100',
            'order_status' => 'nullable|in:notified,ordered,blocked,delivered',
            'is_werkzeug' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|max:4096',
        ]);

        $data['lager_id'] = $lager_id;
        $data['is_werkzeug'] = $request->boolean('is_werkzeug');
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            if ($material->image) {
                Storage::disk('public')->delete($material->image);
            }
            $data['image'] = $request->file('image')->store('materials', 'public');
        }

        $material->update($data);

        return response()->json($material);
    }

    public function destroy(int $lager_id, int $id)
    {
        $material = Material::where('lager_id', $lager_id)->findOrFail($id);
        $material->delete();

        return response()->json(['success' => true]);
    }

    public function getSuppliers(int $lager_id, int $id)
    {
        $material = Material::where('lager_id', $lager_id)->with('suppliers')->findOrFail($id);

        $suppliers = $material->suppliers
            ->sortByDesc(fn ($s) => $s->pivot->created_at)
            ->values();

        foreach ($suppliers as $index => $supplier) {
            $supplier->setAttribute('is_current', $index === 0);
        }

        return response()->json($suppliers);
    }

    public function attach(Request $request, int $lager_id, Material $material)
    {
        abort_unless($material->lager_id === $lager_id, 404);
        $material->suppliers()->syncWithoutDetaching([$request->input('supplier_id')]);

        return response()->json(['success' => true]);
    }

    public function detach(int $lager_id, Material $material, Supplier $supplier)
    {
        abort_unless($material->lager_id === $lager_id, 404);
        $material->suppliers()->detach($supplier->id);

        return response()->json(['success' => true]);
    }

    public function overview(int $lager_id)
    {
        $lager = Lager::findOrFail($lager_id);

        $totalMaterials = Material::where('lager_id', $lager_id)->count();

        $lowStockMaterials = Material::where('lager_id', $lager_id)
            ->whereNotNull('threshold')->where('threshold', '>', 0)
            ->whereColumn('quantity', '<=', 'threshold')
            ->orderBy('quantity')->get();

        $highestStock = Material::where('lager_id', $lager_id)
            ->orderByDesc('quantity')->take(10)->get();

        $lowestStock = Material::where('lager_id', $lager_id)
            ->orderBy('quantity')->where('quantity', '>', 0)->take(10)->get();

        $topUsed10Days = MaterialConsumption::select('material_id', DB::raw('SUM(quantity) as total_used'))
            ->whereHas('material', fn ($q) => $q->where('lager_id', $lager_id))
            ->where('created_at', '>=', now()->subDays(10))
            ->groupBy('material_id')->orderByDesc('total_used')->with('material')->take(10)->get();

        $topUsed30Days = MaterialConsumption::select('material_id', DB::raw('SUM(quantity) as total_used'))
            ->whereHas('material', fn ($q) => $q->where('lager_id', $lager_id))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('material_id')->orderByDesc('total_used')->with('material')->take(10)->get();

        $recentLogs = MaterialConsumption::with('material')
            ->whereHas('material', fn ($q) => $q->where('lager_id', $lager_id))
            ->orderByDesc('created_at')->paginate(10, ['*'], 'audit_page');

        $shelfActivity = MaterialConsumption::select('materials.tablar', DB::raw('SUM(material_consumption.quantity) as total_used'))
            ->join('materials', 'materials.id', '=', 'material_consumption.material_id')
            ->whereHas('material', fn ($q) => $q->where('lager_id', $lager_id))
            ->groupBy('materials.tablar')->orderByDesc('total_used')->get();

        return view('admin.tablar.overview', compact(
            'lager', 'totalMaterials', 'lowStockMaterials', 'highestStock',
            'lowestStock', 'topUsed10Days', 'topUsed30Days', 'recentLogs', 'shelfActivity'
        ));
    }
}
