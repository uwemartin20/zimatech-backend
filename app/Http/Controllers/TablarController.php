<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialConsumption;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TablarController extends Controller
{
    public $lager;

    public function __construct()
    {
        $this->lager = 2;
    }

    public function index()
    {
        $materials = Material::where("lager_id", $this->lager)->orderBy('tablar')->orderBy('name')->get();

        $flatList = $materials->map(fn ($m) => [
            'id' => $m->id,
            'name' => $m->name,
            'quantity' => $m->quantity,
            'shelf' => $m->tablar,
            'threshold' => $m->threshold,
            'type' => $m->type,
            'image' => $m->image,
            'order_status' => $m->order_status,
            'status' => $m->status,
        ])->values();

        $shelves = $materials->pluck('tablar')->unique()->sort()->values();

        // German translation dictionary for database statuses
        $statusTranslations = [
            'notified'  => 'Bedarf gemeldet',
            'ordered'   => 'Bestellt',
            'blocked'   => 'Blockiert',
            'delivered' => 'Geliefert',
        ];

        return view('user.tablar.index', compact('flatList', 'shelves', 'statusTranslations'));
    }

    /**
     * Record material consumption (real-time, concurrency-safe)
     */
    public function consume(Request $request)
    {
        $data = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $material = DB::transaction(function () use ($data) {
            $material = Material::lockForUpdate()->findOrFail($data['material_id']);

            if ($material->quantity < $data['quantity']) {
                abort(400, 'Nicht genügend Bestand');
            }

            $material->decrement('quantity', $data['quantity']);

            MaterialConsumption::create([
                'material_id' => $material->id,
                'quantity' => $data['quantity'],
                'consumption_type' => 'use',
                'consumption_time' => now(),
            ]);

            // 🔥 Determine threshold
            $threshold = $material->threshold ?? 20;

            // 🔥 Check low stock AFTER decrement
            if ($material->quantity <= $threshold) {

                // Optional: prevent duplicate spam notifications
                $alreadyExists = Notification::where('type', 'low_stock')
                    ->where('message', 'like', '%'.$material->name.'%')
                    ->whereDate('created_at', now()->toDateString())
                    ->exists();

                if (! $alreadyExists) {
                    Notification::create([
                        'user_id' => null,
                        'type' => 'low_stock',
                        'message' => "{$material->name} ist im Lager fast leer. Bitte nachbestellen.",
                        'url' => route('admin.tablar.index'), // adjust if needed
                    ]);
                }
            }

            return $material->fresh();
        });

        return response()->json([
            'success' => true,
            'new_quantity' => $material->quantity,
        ]);
    }

    /**
     * Record material return (real-time, concurrency-safe)
     */
    public function return(Request $request)
    {
        $data = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $material = DB::transaction(function () use ($data) {
            $material = Material::lockForUpdate()->findOrFail($data['material_id']);

            if ($material->quantity < $data['quantity']) {
                abort(400, 'Nicht genügend Bestand');
            }

            $material->increment('quantity', $data['quantity']);

            MaterialConsumption::create([
                'material_id' => $material->id,
                'quantity' => $data['quantity'],
                'consumption_type' => 'return',
                'consumption_time' => now(),
            ]);

            // 🔥 Determine threshold
            $threshold = $material->threshold ?? 5;

            // 🔥 Check low stock AFTER decrement
            if ($material->quantity <= $threshold) {

                // Optional: prevent duplicate spam notifications
                $exists = Notification::where('type', 'low_stock')
                    ->where('message', 'like', '%'.$material->name.'%')
                    ->whereDate('created_at', now()->toDateString())
                    ->first();

                if ($exists) {
                    Notification::delete($exists->id);
                }
            }

            return $material->fresh();
        });

        return response()->json([
            'success' => true,
            'new_quantity' => $material->quantity,
        ]);
    }

    /**
     * Handle order request for a material
     */
    public function orderRequest($materialId)
    {
        $material = Material::findOrFail($materialId);

        // update order_status as notified
        if($material->order_status == null) {
            $material->order_status = 'notified';
            $material->save();

            return response()->json(['message' => 'Bestellung angefragt.', 'order_status' => $material->order_status], 200);
        } else {
            return response()->json(['message' => 'Bestellung bereits angefragt.'], 400);
        }
    }
}
