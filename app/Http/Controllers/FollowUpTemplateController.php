<?php

namespace App\Http\Controllers;

use App\Models\FollowUpTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowUpTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:followup_templates.manage');
    }

    /**
     * Display templates
     */
    public function index()
    {
        $templates = FollowUpTemplate::orderBy('category')
            ->orderBy('name')
            ->paginate(20);
            
        return view('followup-templates.index', compact('templates'));
    }

    /**
     * Store new template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'follow_up_type' => 'required|in:phone_call,email,whatsapp,in_person_meeting,video_call,other',
            'subject' => 'nullable|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:initial_contact,demo_followup,proposal_followup,general,closing'
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = 1;

        FollowUpTemplate::create($validated);

        return redirect()->route('followup-templates.index')
            ->with('success', 'Template created successfully.');
    }

    /**
     * Update template
     */
    public function update(Request $request, FollowUpTemplate $followupTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'follow_up_type' => 'required|in:phone_call,email,whatsapp,in_person_meeting,video_call,other',
            'subject' => 'nullable|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:initial_contact,demo_followup,proposal_followup,general,closing',
            'is_active' => 'required|boolean'
        ]);

        $followupTemplate->update($validated);

        return redirect()->route('followup-templates.index')
            ->with('success', 'Template updated successfully.');
    }

    /**
     * Delete template
     */
    public function destroy(FollowUpTemplate $followupTemplate)
    {
        if ($followupTemplate->followUps()->count() > 0) {
            return redirect()->route('followup-templates.index')
                ->with('error', 'Cannot delete template with associated follow-ups.');
        }

        $followupTemplate->delete();

        return redirect()->route('followup-templates.index')
            ->with('success', 'Template deleted successfully.');
    }
}