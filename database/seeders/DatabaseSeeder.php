<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // User Management
            ['module' => 'users', 'permission' => 'view', 'description' => 'View users'],
            ['module' => 'users', 'permission' => 'create', 'description' => 'Create users'],
            ['module' => 'users', 'permission' => 'edit', 'description' => 'Edit users'],
            ['module' => 'users', 'permission' => 'delete', 'description' => 'Delete users'],
            
            // Role Management
            ['module' => 'roles', 'permission' => 'view', 'description' => 'View roles'],
            ['module' => 'roles', 'permission' => 'create', 'description' => 'Create roles'],
            ['module' => 'roles', 'permission' => 'edit', 'description' => 'Edit roles'],
            ['module' => 'roles', 'permission' => 'delete', 'description' => 'Delete roles'],
            
            // Permission Management
            ['module' => 'permissions', 'permission' => 'view', 'description' => 'View permissions'],
            ['module' => 'permissions', 'permission' => 'create', 'description' => 'Create permissions'],
            ['module' => 'permissions', 'permission' => 'edit', 'description' => 'Edit permissions'],
            ['module' => 'permissions', 'permission' => 'delete', 'description' => 'Delete permissions'],
            
            // Customer Management
            ['module' => 'customers', 'permission' => 'view', 'description' => 'View customers'],
            ['module' => 'customers', 'permission' => 'create', 'description' => 'Create customers'],
            ['module' => 'customers', 'permission' => 'edit', 'description' => 'Edit customers'],
            ['module' => 'customers', 'permission' => 'delete', 'description' => 'Delete customers'],
            
            // Vendor Management
            ['module' => 'vendors', 'permission' => 'view', 'description' => 'View vendors'],
            ['module' => 'vendors', 'permission' => 'create', 'description' => 'Create vendors'],
            ['module' => 'vendors', 'permission' => 'edit', 'description' => 'Edit vendors'],
            ['module' => 'vendors', 'permission' => 'delete', 'description' => 'Delete vendors'],
            
            // Product Management
            ['module' => 'products', 'permission' => 'view', 'description' => 'View products'],
            ['module' => 'products', 'permission' => 'create', 'description' => 'Create products'],
            ['module' => 'products', 'permission' => 'edit', 'description' => 'Edit products'],
            ['module' => 'products', 'permission' => 'delete', 'description' => 'Delete products'],
            
            // Sales Management
            ['module' => 'quotations', 'permission' => 'view', 'description' => 'View quotations'],
            ['module' => 'quotations', 'permission' => 'create', 'description' => 'Create quotations'],
            ['module' => 'quotations', 'permission' => 'edit', 'description' => 'Edit quotations'],
            ['module' => 'quotations', 'permission' => 'delete', 'description' => 'Delete quotations'],
            ['module' => 'quotations', 'permission' => 'approve', 'description' => 'Approve quotations'],
            
            ['module' => 'invoices', 'permission' => 'view', 'description' => 'View invoices'],
            ['module' => 'invoices', 'permission' => 'create', 'description' => 'Create invoices'],
            ['module' => 'invoices', 'permission' => 'edit', 'description' => 'Edit invoices'],
            ['module' => 'invoices', 'permission' => 'delete', 'description' => 'Delete invoices'],
            
            // Purchase Management
            ['module' => 'purchase_orders', 'permission' => 'view', 'description' => 'View purchase orders'],
            ['module' => 'purchase_orders', 'permission' => 'create', 'description' => 'Create purchase orders'],
            ['module' => 'purchase_orders', 'permission' => 'edit', 'description' => 'Edit purchase orders'],
            ['module' => 'purchase_orders', 'permission' => 'delete', 'description' => 'Delete purchase orders'],
            ['module' => 'purchase_orders', 'permission' => 'approve', 'description' => 'Approve purchase orders'],
            
            // HRM
            ['module' => 'staff', 'permission' => 'view', 'description' => 'View staff'],
            ['module' => 'staff', 'permission' => 'create', 'description' => 'Create staff'],
            ['module' => 'staff', 'permission' => 'edit', 'description' => 'Edit staff'],
            ['module' => 'staff', 'permission' => 'delete', 'description' => 'Delete staff'],
            
            ['module' => 'payroll', 'permission' => 'view', 'description' => 'View payroll'],
            ['module' => 'payroll', 'permission' => 'process', 'description' => 'Process payroll'],
            
            ['module' => 'attendance', 'permission' => 'view', 'description' => 'View attendance'],
            ['module' => 'attendance', 'permission' => 'mark', 'description' => 'Mark attendance'],
            
            // Service Management
            ['module' => 'service_tickets', 'permission' => 'view', 'description' => 'View service tickets'],
            ['module' => 'service_tickets', 'permission' => 'create', 'description' => 'Create service tickets'],
            ['module' => 'service_tickets', 'permission' => 'edit', 'description' => 'Edit service tickets'],
            ['module' => 'service_tickets', 'permission' => 'delete', 'description' => 'Delete service tickets'],
            ['module' => 'service_tickets', 'permission' => 'assign', 'description' => 'Assign service tickets'],
            
            // Reports
            ['module' => 'reports', 'permission' => 'sales', 'description' => 'View sales reports'],
            ['module' => 'reports', 'permission' => 'purchase', 'description' => 'View purchase reports'],
            ['module' => 'reports', 'permission' => 'inventory', 'description' => 'View inventory reports'],
            ['module' => 'reports', 'permission' => 'hr', 'description' => 'View HR reports'],
            ['module' => 'reports', 'permission' => 'service', 'description' => 'View service reports'],
            
            // Settings
            ['module' => 'settings', 'permission' => 'view', 'description' => 'View settings'],
            ['module' => 'settings', 'permission' => 'edit', 'description' => 'Edit settings'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                [
                    'name' => $permission['module'] . '.' . $permission['permission'],
                    'guard_name' => 'web'
                ],
                [
                    'module' => $permission['module'],
                    'permission' => $permission['permission'],
                    'description' => $permission['description']
                ]
            );
        }

        // Create roles
        $roles = [
            [
                'name' => 'super_admin',
                'description' => 'Full system access',
                'permissions' => Permission::all()
            ],
            [
                'name' => 'admin',
                'description' => 'Administrative access',
                'permissions' => Permission::whereNotIn('module', ['settings'])->get()
            ],
            [
                'name' => 'sales_manager',
                'description' => 'Sales team management',
                'permissions' => Permission::whereIn('module', ['customers', 'quotations', 'invoices', 'reports'])
                    ->whereIn('permission', ['view', 'create', 'edit', 'approve'])
                    ->get()
            ],
            [
                'name' => 'sales_executive',
                'description' => 'Sales operations',
                'permissions' => Permission::whereIn('module', ['customers', 'quotations', 'invoices'])
                    ->whereIn('permission', ['view', 'create', 'edit'])
                    ->get()
            ],
            [
                'name' => 'accounts_manager',
                'description' => 'Accounting management',
                'permissions' => Permission::whereIn('module', ['invoices', 'purchase_orders', 'payroll', 'reports'])
                    ->get()
            ],
            [
                'name' => 'hr_manager',
                'description' => 'Human resources management',
                'permissions' => Permission::whereIn('module', ['staff', 'payroll', 'attendance', 'reports'])
                    ->where('module', 'reports')->where('permission', 'hr')
                    ->get()
            ],
            [
                'name' => 'service_manager',
                'description' => 'Service operations management',
                'permissions' => Permission::whereIn('module', ['service_tickets', 'customers', 'products'])
                    ->get()
            ],
            [
                'name' => 'service_technician',
                'description' => 'Service execution',
                'permissions' => Permission::whereIn('module', ['service_tickets'])
                    ->whereIn('permission', ['view', 'edit'])
                    ->get()
            ],
            [
                'name' => 'viewer',
                'description' => 'Read-only access',
                'permissions' => Permission::where('permission', 'view')->get()
            ]
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleData['name'], 'guard_name' => 'web'],
                ['description' => $roleData['description']]
            );
            $role->syncPermissions($roleData['permissions']);
        }

        // Create default users
        $users = [
            [
                'username' => 'admin',
                'name' => 'Super Admin',
                'email' => 'admin@erp.com',
                'password' => Hash::make('password'),
                'is_active' => 1,
                'role' => 'super_admin'
            ],
            [
                'username' => 'sales_manager',
                'name' => 'Sales Manager',
                'email' => 'sales.manager@erp.com',
                'password' => Hash::make('password'),
                'is_active' => 1,
                'role' => 'sales_manager'
            ],
            [
                'username' => 'hr_manager',
                'name' => 'HR Manager',
                'email' => 'hr@erp.com',
                'password' => Hash::make('password'),
                'is_active' => 1,
                'role' => 'hr_manager'
            ],
            [
                'username' => 'user',
                'name' => 'John Doe',
                'email' => 'user@erp.com',
                'password' => Hash::make('password'),
                'is_active' => 1,
                'role' => 'sales_executive'
            ]
        ];

        foreach ($users as $userData) {
            $roleName = $userData['role'];
            unset($userData['role']);
            
            // Get the role
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $userData['role_id'] = $role->id; // Set legacy role_id
            }
            
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
            
            // Assign Spatie role
            $user->assignRole($roleName);
        }

        // Create sample company
        \DB::table('company')->insertOrIgnore([
            'id' => 1,
            'name' => 'ERP Solutions Sdn Bhd',
            'registration_no' => '123456-X',
            'address_line1' => '123, Jalan Technology',
            'address_line2' => 'Cyberjaya',
            'city' => 'Kuala Lumpur',
            'state' => 'Selangor',
            'postcode' => '63000',
            'country' => 'Malaysia',
            'phone' => '+60 3-1234 5678',
            'email' => 'info@erpsolutions.com',
            'website' => 'www.erpsolutions.com',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Add more permissions for extended modules if needed
        $additionalPermissions = [
            ['module' => 'accounting', 'permission' => 'view', 'description' => 'View accounting'],
            ['module' => 'accounting', 'permission' => 'manage', 'description' => 'Manage accounting'],
            ['module' => 'subscriptions', 'permission' => 'view', 'description' => 'View subscriptions'],
            ['module' => 'subscriptions', 'permission' => 'manage', 'description' => 'Manage subscriptions'],
        ];

        foreach ($additionalPermissions as $permission) {
            Permission::firstOrCreate(
                [
                    'name' => $permission['module'] . '.' . $permission['permission'],
                    'guard_name' => 'web'
                ],
                [
                    'module' => $permission['module'],
                    'permission' => $permission['permission'],
                    'description' => $permission['description']
                ]
            );
        }

        // Give super admin all permissions including new ones
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->syncPermissions(Permission::all());
        }
    }
}