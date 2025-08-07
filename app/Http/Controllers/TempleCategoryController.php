<?php

namespace App\Http\Controllers;

use App\Models\TempleCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TempleCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:temple_categories.manage');
    }

    /**
     * Display a listing of temple categories.
     */
    public function index()
    {
        $categories = TempleCategory::withCount('leads')
            ->orderBy('name')
            ->paginate(15);
            
        return view('temple-categories.index', compact('categories'));
    }

    /**
     * Store a newly created temple category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:temple_categories,name',
            'description' => 'nullable|string|max:500'
        ]);

        $validated['created_by'] = Auth::id();
        $validated['status'] = 1;

        TempleCategory::create($validated);

        return redirect()->route('temple-categories.index')
            ->with('success', 'Temple category created successfully.');
    }

    /**
     * Update the specified temple category.
     */
    public function update(Request $request, TempleCategory $templeCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:temple_categories,name,' . $templeCategory->id,
            'description' => 'nullable|string|max:500',
            'status' => 'required|boolean'
        ]);

        $templeCategory->update($validated);

        return redirect()->route('temple-categories.index')
            ->with('success', 'Temple category updated successfully.');
    }

    /**
     * Remove the specified temple category.
     */
    public function destroy(TempleCategory $templeCategory)
    {
        if ($templeCategory->leads()->count() > 0) {
            return redirect()->route('temple-categories.index')
                ->with('error', 'Cannot delete category with associated leads.');
        }

        $templeCategory->delete();

        return redirect()->route('temple-categories.index')
            ->with('success', 'Temple category deleted successfully.');
    }
    public function getActive()
{
    $categories = TempleCategory::active()
        ->orderBy('name')
        ->get(['id', 'name']);
        
    return response()->json($categories);
}
}
