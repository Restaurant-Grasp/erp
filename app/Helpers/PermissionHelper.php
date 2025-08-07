<?php


if (!function_exists('getCurrentRolePermissions')) {
    /**
     * Get permissions for current user's role
     * This function is already being used in your views
     */
    function getCurrentRolePermissions($roleName) {
        if (!$roleName) {
            return collect();
        }
        
        $role = \Spatie\Permission\Models\Role::where('name', $roleName)->first();
        
        if (!$role) {
            return collect();
        }
        
        return $role->permissions;
    }
}

if (!function_exists('userCan')) {
    /**
     * Check if current user has specific permission
     */
    function userCan($permission) {
        return auth()->check() && auth()->user()->can($permission);
    }
}

if (!function_exists('userCanAny')) {
    /**
     * Check if current user has any of the specified permissions
     */
    function userCanAny($permissions) {
        if (!auth()->check()) {
            return false;
        }
        
        foreach ($permissions as $permission) {
            if (auth()->user()->can($permission)) {
                return true;
            }
        }
        
        return false;
    }
}

if (!function_exists('getChartOfAccountsPermissions')) {
    /**
     * Get Chart of Accounts specific permissions for current user
     */
    function getChartOfAccountsPermissions() {
        $user = auth()->user();
        
        if (!$user) {
            return [
                'can_view' => false,
                'can_create_group' => false,
                'can_create_ledger' => false,
                'can_edit_group' => false,
                'can_edit_ledger' => false,
                'can_delete_group' => false,
                'can_delete_ledger' => false,
                'can_view_details' => false,
                'can_manage_opening_balance' => false,
            ];
        }
        
        return [
            'can_view' => $user->can('chart_of_accounts.view'),
            'can_create_group' => $user->can('chart_of_accounts.create_group'),
            'can_create_ledger' => $user->can('chart_of_accounts.create_ledger'),
            'can_edit_group' => $user->can('chart_of_accounts.edit_group'),
            'can_edit_ledger' => $user->can('chart_of_accounts.edit_ledger'),
            'can_delete_group' => $user->can('chart_of_accounts.delete_group'),
            'can_delete_ledger' => $user->can('chart_of_accounts.delete_ledger'),
            'can_view_details' => $user->can('chart_of_accounts.view_ledger_details'),
            'can_manage_opening_balance' => $user->can('chart_of_accounts.manage_opening_balance'),
        ];
    }
}

if (!function_exists('getAccountsMenuPermissions')) {
    /**
     * Get Accounts menu permissions for navigation
     */
    function getAccountsMenuPermissions() {
        $user = auth()->user();
        
        if (!$user) {
            return [
                'show_accounts_menu' => false,
                'show_chart_of_accounts' => false,
                'show_journal_entries' => false,
                'show_reports' => false,
                'show_reconciliation' => false,
            ];
        }
        
        // Chart of Accounts permissions
        $chartOfAccountsAccess = $user->can('chart_of_accounts.view');
        
        // Journal Entries permissions
        $journalEntriesAccess = $user->canAny([
            'accounts.receipt.view',
            'accounts.payment.view', 
            'accounts.journal.view'
        ]);
        
        // Reports permissions
        $reportsAccess = $user->can('accounts.reports.view');
        
        // Reconciliation permissions
        $reconciliationAccess = $user->can('accounts.reconciliation.view');
        
        // Show accounts menu if user has any accounting permissions
        $showAccountsMenu = $chartOfAccountsAccess || $journalEntriesAccess || $reportsAccess || $reconciliationAccess;
        
        return [
            'show_accounts_menu' => $showAccountsMenu,
            'show_chart_of_accounts' => $chartOfAccountsAccess,
            'show_journal_entries' => $journalEntriesAccess,
            'show_reports' => $reportsAccess,
            'show_reconciliation' => $reconciliationAccess,
        ];
    }
}