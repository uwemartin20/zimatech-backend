<?php

namespace App\Http\Controllers\Admin\Settings;

use Illuminate\Http\Request;
use App\Models\MachineStatus;
use App\Http\Controllers\Controller;

class MachineSettingsController extends Controller
{
    /**
     * Display all machine statuses.
     */
    public function machineStatus()
    {
        $statuses = MachineStatus::all();
        return view('admin.settings.machine-status', compact('statuses'));
    }

    public function machineStatusShow($id = null)
    {
        $status = $id ? MachineStatus::findOrFail($id) : new MachineStatus();
        return view('admin.settings.machine-status-show', compact('status'));
    }

    /**
     * Update the given machine status.
     */
    public function machineStatusUpdate(Request $request, $id = null)
    {

        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'color'  => 'nullable|string|max:20',
        ]);

        if ($id) {
            // Update existing
            $status = MachineStatus::findOrFail($id);
            $status->update([
                'name'   => $validated['name'],
                'color'  => $validated['color'] ?? null,
                'active' => $request->has('active'),
            ]);
            $message = 'Machine status updated successfully!';
        } else {
            // Create new
            $status = MachineStatus::create([
                'name'   => $validated['name'],
                'color'  => $validated['color'] ?? null,
                'active' => $request->has('active'),
            ]);
            $message = 'New machine status created successfully!';
        }

        return redirect()->route('admin.settings.machine-status.show', $status->id)
                         ->with('success', $message);
    }

    /**
     * Toggle active/inactive.
     */
    public function toggleMachineStatus(Request $request, $id)
    {
        $status = MachineStatus::findOrFail($id);
        $status->active = !$status->active;
        $status->save();

        return response()->json(['success' => true, 'active' => $status->active]);
    }

    /**
     * Delete machine status.
     */
    public function deleteMachineStatus($id)
    {
        MachineStatus::findOrFail($id)->delete();
        return back()->with('success', 'Machine status deleted successfully.');
    }
}
