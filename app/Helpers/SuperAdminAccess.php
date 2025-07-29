<?php
use App\Models\Role;
use Illuminate\Support\Facades\Auth;

if (!function_exists('getCurrentRolePermissions')) {
    function getCurrentRolePermissions($role)
    {
        $user = Auth::user();

        if ($user) {
      
            if ($role) {
                return Role::where('roles.name', $role)
                    ->join('role_has_permissions', 'roles.id', '=', 'role_has_permissions.role_id')
                    ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                    ->select('permissions.*')
                    ->get();
            }
        }

        return collect(); // empty collection
    }
}


