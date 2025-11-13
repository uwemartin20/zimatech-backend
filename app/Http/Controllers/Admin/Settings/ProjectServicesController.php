<?php

namespace App\Http\Controllers\Admin\Settings;

use Illuminate\Http\Request;
use App\Models\ProjectService;
use App\Http\Controllers\Controller;

class ProjectServicesController extends Controller
{
    /**
     * Display all Project services.
     */
    public function projectService()
    {
        $services = ProjectService::all();
        return view('admin.settings.project-service', compact('services'));
    }

    public function projectServiceShow($id = null)
    {
        $service = $id ? ProjectService::findOrFail($id) : new ProjectService();
        return view('admin.settings.project-service-show', compact('service'));
    }

    /**
     * Update the given Project service.
     */
    public function projectServiceUpdate(Request $request, $id = null)
    {

        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'color'  => 'nullable|string|max:20',
        ]);

        if ($id) {
            // Update existing
            $service = ProjectService::findOrFail($id);
            $service->update([
                'name'   => $validated['name'],
                'color'  => $validated['color'] ?? null,
                'active' => $request->has('active'),
            ]);
            $message = 'Project service updated successfully!';
        } else {
            // Create new
            $service = ProjectService::create([
                'name'   => $validated['name'],
                'color'  => $validated['color'] ?? null,
                'active' => $request->has('active'),
            ]);
            $message = 'New project service created successfully!';
        }

        return redirect()->route('admin.settings.project-service.show', $service->id)
                         ->with('success', $message);
    }

    /**
     * Toggle active/inactive.
     */
    public function toggleProjectService(Request $request, $id)
    {
        $service = ProjectService::findOrFail($id);
        $service->active = !$service->active;
        $service->save();

        return response()->json(['success' => true, 'active' => $service->active]);
    }

    /**
     * Delete Project service.
     */
    public function deleteProjectService($id)
    {
        ProjectService::findOrFail($id)->delete();
        return back()->with('success', 'Project service deleted successfully.');
    }
}
