<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SalesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sales module permissions
        $salesPermissions = [
            // Tax permissions
            'sales.taxes.view' => 'View sales taxes',
            'sales.taxes.create' => 'Create sales taxes',
            'sales.taxes.edit' => 'Edit sales taxes',
            'sales.taxes.delete' => 'Delete sales taxes',

            // Quotation permissions
            'sales.quotations.view' => 'View quotations',
            'sales.quotations.create' => 'Create quotations',
            'sales.quotations.edit' => 'Edit quotations',
            'sales.quotations.delete' => 'Delete quotations',
            'sales.quotations.approve' => 'Approve quotations',
            'sales.quotations.send' => 'Send quotations',
            'sales.quotations.duplicate' => 'Duplicate quotations',
            'sales.quotations.pdf' => 'Generate quotation PDF',

            // Invoice permissions
            'sales.invoices.view' => 'View invoices',
            'sales.invoices.create' => 'Create invoices',
            'sales.invoices.edit' => 'Edit invoices',
            'sales.invoices.cancel' => 'Cancel invoices',
            'sales.invoices.duplicate' => 'Duplicate invoices',
            'sales.invoices.pdf' => 'Generate invoice PDF',
            'sales.invoices.einvoice' => 'Submit e-invoices',

            // Delivery Order permissions
            'sales.delivery_orders.view' => 'View delivery orders',
            'sales.delivery_orders.create' => 'Create delivery orders',
            'sales.delivery_orders.edit' => 'Edit delivery orders',
            'sales.delivery_orders.delete' => 'Delete delivery orders',
            'sales.delivery_orders.pdf' => 'Generate delivery order PDF',
        ];

        // Create permissions
        foreach ($salesPermissions as $name => $description) {
            $parts = explode('.', $name);
            $module = $parts[0] . '.' . $parts[1]; // sales.taxes, sales.quotations, etc.
            $permission = $parts[2]; // view, create, edit, delete, etc.

            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web'
            ], [
                'module' => $module,
                'permission' => $permission,
                'description' => $description
            ]);
        }

        // Assign permissions to Admin role
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo(array_keys($salesPermissions));
        }

        // Create Sales Manager role with all sales permissions
        $salesManagerRole = Role::firstOrCreate([
            'name' => 'Sales Manager',
            'guard_name' => 'web'
        ], [
            'code' => 'sales_manager',
            'description' => 'Sales Manager with full access to sales module',
            'is_system' => false,
            'status' => true
        ]);

        $salesManagerRole->givePermissionTo(array_keys($salesPermissions));

        // Create Sales Staff role with limited permissions
        $salesStaffRole = Role::firstOrCreate([
            'name' => 'Sales Staff',
            'guard_name' => 'web'
        ], [
            'code' => 'sales_staff',
            'description' => 'Sales staff with limited access to sales module',
            'is_system' => false,
            'status' => true
        ]);

        $salesStaffPermissions = [
            'sales.taxes.view',
            'sales.quotations.view',
            'sales.quotations.create',
            'sales.quotations.edit',
            'sales.quotations.send',
            'sales.quotations.pdf',
            'sales.invoices.view',
            'sales.invoices.create',
            'sales.invoices.edit',
            'sales.invoices.pdf',
            'sales.delivery_orders.view',
            'sales.delivery_orders.create',
            'sales.delivery_orders.edit',
            'sales.delivery_orders.pdf',
        ];

        $salesStaffRole->givePermissionTo($salesStaffPermissions);

        // Create Quotation Approver role
        $quotationApproverRole = Role::firstOrCreate([
            'name' => 'Quotation Approver',
            'guard_name' => 'web'
        ], [
            'code' => 'quotation_approver',
            'description' => 'User who can approve quotations',
            'is_system' => false,
            'status' => true
        ]);

        $approverPermissions = [
            'sales.quotations.view',
            'sales.quotations.approve',
            'sales.invoices.view',
        ];

        $quotationApproverRole->givePermissionTo($approverPermissions);

        $this->command->info('Sales module permissions created successfully!');
    }
}