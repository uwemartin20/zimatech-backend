<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = EmailTemplate::latest()->paginate(10);
        return view('admin.settings.email_templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.settings.email_templates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'template_type' => 'required|in:project_offers,inquiry,support,help,supplier_offers',
            'note' => 'nullable|string',
            'active' => 'required|boolean',
        ]);

        EmailTemplate::create($validated);

        return redirect()->route('admin.settings.email_templates.index')
                         ->with('success', 'Email Template created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $emailTemplate = EmailTemplate::findOrFail($id);

        return view('admin.settings.email_templates.show', compact('emailTemplate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('admin.settings.email_templates.edit', compact('emailTemplate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $emailTemplate = EmailTemplate::findOrFail($id);

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'template_type' => 'required|in:project_offers,inquiry,support,help,supplier_offers',
            'note' => 'nullable|string',
            'active' => 'required|boolean',
        ]);

        $emailTemplate->update($validated);

        return redirect()->route('admin.settings.email_templates.index')
                         ->with('success', 'Email Template updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $emailTemplate = EmailTemplate::findOrFail($id);

        $emailTemplate->delete();

        return redirect()->route('admin.settings.email_templates.index')
                         ->with('success', 'Email Template deleted successfully.');
    }
}
