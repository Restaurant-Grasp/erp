<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departmentList = Department::orderBy('id', 'desc')->paginate(10);
        return view('department.index', compact('departmentList'));
    }
    public function create()
    {
        return view('department.create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required|string|max:50|unique:departments,code',
        ]);
        try {
            $department = Department::create([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'created_by' => auth()->id(),
            ]);
            return redirect()->route('department.index')->with('success', 'Department created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error creating department: ' . $e->getMessage());
        }
    }
    public function edit(Department $department)
    {
        $departments = Department::all();
        return view('department.edit', compact('department'));
    }
    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:departments,code,' . $department->id,
        ]);
        try {
            $department->update([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'created_by' => auth()->id(),
            ]);
            return redirect()->route('department.index')->with('success', 'Department updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating department: ' . $e->getMessage());
        }
    }
    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->route('department.index')->with('success', 'Department deleted successfully.');
    }
}
