<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:permissions.view')->only('index');
        $this->middleware('permission:permissions.create')->only(['create', 'store']);
    }

    /**
     * Display a listing of the permissions.
     */
    public function index()
    {
        $permissions = Permission::orderBy('module')->orderBy('permission')->paginate(20);
        $groupedPermissions = Permission::all()->groupBy('module');
        
        return view('permissions.index', compact('permissions', 'groupedPermissions'));
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create()
    {
        $modules = Permission::distinct()->pluck('module')->filter()->sort();
        return view('permissions.create', compact('modules'));
    }

    /**
     * Store a newly created permission in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'module' => 'required|string|max:100',
            'permission' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        $permissionName = $validated['module'] . '.' . $validated['permission'];

        // Check if permission already exists
        if (Permission::where('name', $permissionName)->exists()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Permission already exists.');
        }

        Permission::create([
            'name' => $permissionName,
            'guard_name' => 'web',
            'module' => $validated['module'],
            'permission' => $validated['permission'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('permissions.index')
            ->with('success', 'Permission created successfully.');
    }
}