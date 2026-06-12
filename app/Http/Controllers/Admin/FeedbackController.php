<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        // -----------------------------
        // Base Query (filter-driven)
        // -----------------------------
        $baseQuery = Feedback::query();

        // -----------------------------
        // Filters
        // -----------------------------
        if ($request->filled('machine')) {
            $baseQuery->whereIn('machine', (array) $request->machine);
        }

        if ($request->filled('department')) {
            $baseQuery->whereIn('department', (array) $request->department);
        }

        if ($request->filled('type')) {
            $baseQuery->whereIn('type', (array) $request->type);
        }

        if ($request->filled('has_attachment')) {
            $baseQuery->whereNotNull('attachment');
        }

        if ($request->filled('anonymous')) {
            $baseQuery->whereNull('name');
        }

        // -----------------------------
        // Dataset (table feed)
        // -----------------------------
        $feedbacks = (clone $baseQuery)
            ->latest()
            ->limit(200) // safety for UI performance
            ->get();

        // =========================================================
        // KPI METRICS (computed from filtered dataset)
        // =========================================================

        $totals = (clone $baseQuery)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN solution IS NOT NULL AND solution != '' THEN 1 ELSE 0 END) as with_solution,
                SUM(CASE WHEN solution IS NULL OR solution = '' THEN 1 ELSE 0 END) as without_solution,
                SUM(CASE WHEN name IS NULL THEN 1 ELSE 0 END) as anonymous,
                SUM(CASE WHEN attachment IS NOT NULL THEN 1 ELSE 0 END) as with_attachment
            ")
            ->first();

        $kpi = [
            'total' => (int) $totals->total,
            'with_solution' => (int) $totals->with_solution,
            'without_solution' => (int) $totals->without_solution,
            'anonymous' => (int) $totals->anonymous,
            'with_attachment' => (int) $totals->with_attachment,
        ];

        // =========================================================
        // CHART DATASETS
        // =========================================================

        $byMachine = (clone $baseQuery)
            ->select('machine', DB::raw('COUNT(*) as count'))
            ->whereNotNull('machine')
            ->groupBy('machine')
            ->orderByDesc('count')
            ->get();

        $byDepartment = (clone $baseQuery)
            ->select('department', DB::raw('COUNT(*) as count'))
            ->whereNotNull('department')
            ->groupBy('department')
            ->orderByDesc('count')
            ->get();

        $byErrorCode = (clone $baseQuery)
            ->select('error_code', DB::raw('COUNT(*) as count'))
            ->whereNotNull('error_code')
            ->groupBy('error_code')
            ->orderByDesc('count')
            ->get();

        $solutionStats = (clone $baseQuery)
            ->selectRaw("
                SUM(CASE WHEN solution IS NULL OR solution = '' THEN 1 ELSE 0 END) as no_solution,
                SUM(CASE WHEN solution IS NOT NULL AND solution != '' THEN 1 ELSE 0 END) as has_solution
            ")
            ->first();

        return view('admin.feedback.index', [
            'feedbacks' => $feedbacks,
            'kpi' => $kpi,
            'byMachine' => $byMachine,
            'byDepartment' => $byDepartment,
            'byErrorCode' => $byErrorCode,
            'solutionStats' => $solutionStats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:maschinen,bereiche,sonstiges',

            'problem' => 'required|string',

            'solution' => 'nullable|string',

            'machine' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'errorCode' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',

            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
        ]);

        // Optional: enforce logical constraints
        if ($validated['type'] === 'maschinen' && empty($validated['machine'])) {
            return response()->json([
                'success' => false,
                'message' => 'Machine is required for maschinen type',
            ], 422);
        }

        if ($validated['type'] === 'bereiche' && empty($validated['department'])) {
            return response()->json([
                'success' => false,
                'message' => 'Department is required for bereiche type',
            ], 422);
        }

        $path = null;

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('feedback', 'public');
        }

        $feedback = Feedback::create([
            'type' => $validated['type'],
            'machine' => $validated['machine'] ?? null,
            'department' => $validated['department'] ?? null,
            'error_code' => $validated['errorCode'] ?? null,
            'problem' => $validated['problem'],
            'solution' => $validated['solution'] ?? null,
            'name' => $validated['name'] ?? null,
            'attachment' => $path,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feedback erfolgreich abgegeben',
            'data' => $feedback,
        ], 201);
    }
}
