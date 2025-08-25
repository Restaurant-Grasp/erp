<?php

namespace App\Http\Controllers;



use App\Models\Staff;
use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    public function index()
    {
        $staffList = Staff::with('department', 'user')->orderBy('staff.id', 'desc')->paginate(10);
        return view('staff.index', compact('staffList'));
    }

    public function create()
    {
        $departments = Department::all();
        $roles = Role::all();
        return view('staff.create', compact('departments', 'roles'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255|unique:users,name',
            'email' => 'required|email|max:255|unique:users,email',
            'department_id' => 'required|exists:departments,id',
            'role' => 'required|exists:roles,id',
            'employee_id' => 'required|string|max:255|unique:staff,employee_id',
            'password' => 'required|string',
        ]);
        DB::beginTransaction();
        try {
            $password = $request->password;

            $staff = Staff::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'department_id' => $request->department_id,
                'designation' => $request->designation,
                'employee_id' => $request->employee_id,
                'address' => $request->address,
                'created_by' => auth()->id(),
            ]);

            $user = User::create([
                'name' => $request->name,
                'username' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($password),
                'role_id' => $request->role,
                'staff_id' => $staff->id,
                'created_by' => auth()->id(),
            ]);
            $staff->update([
                'user_id' => $user->id,
            ]);
            $user->assignRole(Role::find($request->role)->name);
            DB::commit();

            Mail::raw("Your account has been created.\nEmail: {$user->email}\nPassword: {$password}", function ($message) use ($user) {
                $message->to($user->email)->subject('Your Staff Login Credentials');
            });

            return redirect()->route('staff.index')->with('success', 'Staff created successfully.1');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Error creating staff: ' . $e->getMessage());
        }
    }

    public function edit(Staff $staff)
    {
        $departments = Department::all();
        $roles = Role::all();

        return view('staff.edit', compact('staff', 'departments', 'roles'));
    }

    public function update(Request $request, Staff $staff)
    {
        $userId = Staff::join('users', 'users.staff_id', '=', 'staff.id')
            ->where('staff.id', $staff->id)
            ->value('users.id');

        $request->validate([
            'name' => 'required|string|max:255|unique:users,name,' . $userId,
            'email' => 'required|email|max:255|unique:users,email,' . $userId,
            'department_id' => 'required|exists:departments,id',
            'role' => 'required|exists:roles,id',
            'employee_id' => 'required|string|max:255|unique:staff,employee_id,' . $staff->id,
        ]);
        DB::beginTransaction();
        try {
            $user = $staff->user;
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->name,
                'created_by' => auth()->id(),

            ]);

            $user->syncRoles([Role::find($request->role)->name]);

            $staff->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'department_id' => $request->department_id,
                'designation' => $request->designation,
                'address' => $request->address,
                'employee_id' => $request->employee_id,
                'created_by' => auth()->id(),
            ]);
            DB::commit();

            return redirect()->route('staff.index')->with('success', 'Staff updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Error updating staff: ' . $e->getMessage());
        }
    }

    public function destroy(Staff $staff)
    {
        if ($staff->user) {
            $staff->user->delete();
        }
        $staff->delete();

        return redirect()->route('staff.index')->with('success', 'Staff deleted successfully.');
    }
}
