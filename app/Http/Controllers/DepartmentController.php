<?php

namespace App\Http\Controllers;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departmentList = Department::paginate(10);
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
        
        $department = Department::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
        ]);
        return redirect()->route('department.index')->with('success', 'Department created successfully.');
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
        $department->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
        ]);
        return redirect()->route('department.index')->with('success', 'Department updated successfully.');
    }
    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->route('department.index')->with('success', 'Department deleted successfully.');
    }
}
