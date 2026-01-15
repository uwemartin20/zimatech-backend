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
        $services = ProjectService::orderBy('parent_id')->orderBy('name')->get();
        return view('admin.settings.project-service', compact('services'));
    }

    public function projectServiceShow($id = null)
    {
        $service = $id ? ProjectService::findOrFail($id) : new ProjectService();
        $parentServices = ProjectService::where('id', '!=', $id)
            ->orderBy('name')
            ->get();
        return view('admin.settings.project-service-show', compact('service', 'parentServices'));
    }

    /**
     * Update the given Project service.
     */
    public function projectServiceUpdate(Request $request, $id = null)
    {

        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'color'  => 'nullable|string|max:20',
            'parent_id' => 'nullable|integer|exists:project_services,id',
            'active' => 'nullable',
        ]);

        if ($id && $request->parent_id == $id) {
            return back()->withErrors(['parent_id' => 'A service cannot be its own parent.']);
        }

        if ($id) {
            // Update existing
            $service = ProjectService::findOrFail($id);
            $service->update([
                'name'   => $validated['name'],
                'color'  => $validated['color'] ?? null,
                'active' => $request->has('active'),
                'parent_id' => $validated['parent_id'] ?? null,
            ]);
            $message = 'Project service updated successfully!';
        } else {
            // Create new
            $service = ProjectService::create([
                'name'   => $validated['name'],
                'color'  => $validated['color'] ?? null,
                'active' => $request->has('active'),
                'parent_id' => $validated['parent_id'] ?? null,
            ]);
            $message = 'New project service created successfully!';
        }

        return redirect()->route('admin.settings.project-service')
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
