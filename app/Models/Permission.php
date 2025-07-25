<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'guard_name',
        'module',
        'permission',
        'description'
    ];

    /**
     * Get permissions grouped by module
     */
    public static function getGroupedByModule()
    {
        return static::orderBy('module')
            ->orderBy('permission')
            ->get()
            ->groupBy('module');
    }

    /**
     * Get distinct modules
     */
    public static function getModules()
    {
        return static::distinct()
            ->pluck('module')
            ->filter()
            ->sort()
            ->values();
    }

    /**
     * Create a new permission with module and permission separated
     */
    public static function createPermission($module, $permission, $description = null)
    {
        return static::create([
            'name' => $module . '.' . $permission,
            'guard_name' => 'web',
            'module' => $module,
            'permission' => $permission,
            'description' => $description
        ]);
    }

    /**
     * Find permission by module and permission
     */
    public static function findByModuleAndPermission($module, $permission)
    {
        return static::where('module', $module)
            ->where('permission', $permission)
            ->first();
    }
}