<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    /**
     * Display all machines.
     */
    public function index()
    {
        $machines = Machine::latest()->get();
        return view('admin.settings.machines.index', compact('machines'));
    }

    /**
     * Show create/edit form.
     */
    public function show($id = null)
    {
        $machine = $id ? Machine::findOrFail($id) : new Machine();
        return view('admin.settings.machines.show', compact('machine'));
    }

    /**
     * Create or update machine.
     */
    public function update(Request $request, $id = null)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        if ($id) {
            $machine = Machine::findOrFail($id);
            $machine->update([
                'name'        => $validated['name'],
                'description' => $validated['description'] ?? null,
                'active'      => $request->has('active'),
            ]);
            $message = 'Machine updated successfully!';
        } else {
            $machine = Machine::create([
                'name'        => $validated['name'],
                'description' => $validated['description'] ?? null,
                'active'      => $request->has('active'),
            ]);
            $message = 'New machine created successfully!';
        }

        return redirect()
            ->route('admin.settings.machines.show', $machine->id)
            ->with('success', $message);
    }

    /**
     * Toggle active/inactive.
     */
    public function toggle($id)
    {
        $machine = Machine::findOrFail($id);
        $machine->active = !$machine->active;
        $machine->save();

        return response()->json([
            'success' => true,
            'active'  => $machine->active
        ]);
    }

    /**
     * Delete machine.
     */
    public function delete($id)
    {
        Machine::findOrFail($id)->delete();
        return back()->with('success', 'Machine deleted successfully.');
    }
}
