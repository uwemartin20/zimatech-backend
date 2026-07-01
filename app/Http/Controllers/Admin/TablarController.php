<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialConsumption;
use App\Models\Supplier;
use App\Models\Lager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use function Psy\debug;

class TablarController extends Controller
{
    public $leger_id;

    public function __construct()
    {
        $this->lager_id = 2;
    }
    public function index(Request $request)
    {
        $query = Material::with('suppliers')->where('lager_id', $this->lager_id)->orderBy('name');

        if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$request->name.'%');
        }

        if ($request->filled('shelf')) {
            $query->where('tablar', 'like', '%'.$request->shelf.'%');
        }

        if ($request->filled('max_qty')) {
            $query->where('quantity', '<=', $request->max_qty);
        }

        $materials = $query->paginate(5)->withQueryString();

        $maxQuantity = Material::max('quantity') ?? 0;

        // German translation dictionary for database statuses
        $statusTranslations = [
            'notified'  => 'Bedarf gemeldet',
            'ordered'   => 'Bestellt',
            'blocked'   => 'Blockiert',
            'delivered' => 'Geliefert',
        ];

        return view('admin.tablar.index', compact('materials', 'maxQuantity', 'statusTranslations'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'tablar' => 'nullable|string|max:50',
            'threshold' => 'nullable|integer|min:0',
            'type' => 'nullable|string|max:100',
            'order_status' => 'nullable|in:notified,ordered,blocked,delivered',
            'is_werkzeug' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|max:4096',
        ]);

        $data['lager_id'] = $this->lager_id;
        $data['is_werkzeug'] = $request->boolean('is_werkzeug');
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('materials', 'public');
        }

        $material = Material::create($data);

        return response()->json($material);
    }

    public function update(Request $request, $id)
    {
        $material = Material::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'tablar' => 'nullable|string|max:50',
            'threshold' => 'nullable|integer|min:0',
            'type' => 'nullable|string|max:100',
            'order_status' => 'nullable|in:notified,ordered,blocked,delivered',
            'is_werkzeug' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|max:4096',
        ]);

        $data['lager_id'] = $this->lager_id;
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

    public function destroy($id)
    {
        $material = Material::findOrFail($id);
        $material->delete();

        return response()->json(['success' => true]);
    }

    public function getSuppliers($id)
    {
        $material = Material::where('lager_id', $this->lager_id)->with('suppliers')->findOrFail($id);

        $suppliers = $material->suppliers
            ->sortByDesc(fn ($supplier) => $supplier->pivot->created_at)
            ->values();

        foreach ($suppliers as $index => $supplier) {
            $supplier->setAttribute('is_current', $index === 0);
        }

        return response()->json($suppliers);
    }

    public function attach(Request $request, Material $material)
    {
        // Sync Without Detaching avoids duplicate rows in the pivot table setup
        $material->suppliers()->syncWithoutDetaching([$request->input('supplier_id')]);

        return response()->json(['success' => true]);
    }

    public function detach(Material $material, Supplier $supplier)
    {
        $material->suppliers()->detach($supplier->id);

        return response()->json(['success' => true]);
    }

    public function overview()
    {
        // =========================
        // STOCK METRICS
        // =========================

        $totalMaterials = Material::where('lager_id', $this->lager_id)->count();

        $lowStockMaterials = Material::where('lager_id', $this->lager_id)
            ->whereColumn('quantity', '<=', DB::raw('COALESCE(threshold, 20)'))
            ->orderBy('quantity')
            ->get();

        $highestStock = Material::where('lager_id', $this->lager_id)->orderByDesc('quantity')
            ->take(10)
            ->get();

        $lowestStock = Material::where('lager_id', $this->lager_id)->orderBy('quantity')
            ->where('quantity', '>', 0)
            ->take(10)
            ->get();

        // =========================
        // USAGE ANALYTICS
        // =========================

        $topUsed10Days = MaterialConsumption::select(
            'material_id',
            DB::raw('SUM(quantity) as total_used')
        )
            ->whereHas('material', function ($query) {
                $query->where('lager_id', $this->lager_id);
            })
            ->where('created_at', '>=', now()->subDays(10))
            ->groupBy('material_id')
            ->orderByDesc('total_used')
            ->with('material')
            ->take(10)
            ->get();

        $topUsed30Days = MaterialConsumption::select(
            'material_id',
            DB::raw('SUM(quantity) as total_used')
        )
            ->whereHas('material', function ($query) {
                $query->where('lager_id', $this->lager_id);
            })
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('material_id')
            ->orderByDesc('total_used')
            ->with('material')
            ->take(10)
            ->get();

        // =========================
        // RECENT ACTIVITY (AUDIT)
        // =========================

        $recentLogs = MaterialConsumption::with('material')
            ->whereHas('material', function ($query) {
                $query->where('lager_id', $this->lager_id);
            })
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'audit_page');

        // =========================
        // DISTRIBUTION BY SLOTS (TABLAR USAGE)
        // =========================

        $shelfActivity = MaterialConsumption::select(
            'materials.tablar',
            DB::raw('SUM(material_consumption.quantity) as total_used')
        )
            ->join('materials', 'materials.id', '=', 'material_consumption.material_id')
            ->whereHas('material', function ($query) {
                $query->where('lager_id', $this->lager_id);
            })
            ->groupBy('materials.tablar')
            ->orderByDesc('total_used')
            ->get();

        // =========================
        // RETURN VIEW
        // =========================

        return view('admin.tablar.overview', compact(
            'totalMaterials',
            'lowStockMaterials',
            'highestStock',
            'lowestStock',
            'topUsed10Days',
            'topUsed30Days',
            'recentLogs',
            'shelfActivity'
        ));
    }
}
