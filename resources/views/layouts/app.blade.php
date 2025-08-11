<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ERP System') }} - @yield('title', 'Dashboard')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css">

    <style>
        :root {
            --primary-green: #00A551;
            --dark-green: #008741;
            --light-green: #E8F5E9;
            --sidebar-width: 250px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f5f7fa;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background-color: #fff;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            z-index: 1000;
            transition: all 0.3s;
        }

        .sidebar-logo {
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            background-color: var(--primary-green);
            color: white;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .sidebar-nav .nav-link {
            padding: 12px 20px;
            color: #4a5568;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .sidebar-nav .nav-link:hover {
            background-color: var(--light-green);
            color: var(--primary-green);
            border-left-color: var(--primary-green);
        }

        .sidebar-nav .nav-link.active {
            background-color: var(--light-green);
            color: var(--primary-green);
            border-left-color: var(--primary-green);
            font-weight: 600;
        }

        .sidebar-nav .nav-link i {
            width: 25px;
            margin-right: 10px;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            background-color: #f5f7fa;
        }

        /* Top Navbar */
        .top-navbar {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
            padding: 15px 30px;
        }

        .navbar-user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-green);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        /* Content Area */
        .content-wrapper {
            padding: 30px;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
        }

        .breadcrumb {
            background-color: transparent;
            padding: 0;
            margin: 0;
        }

        /* Cards */
        .card {
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }

        .card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
            padding: 15px 20px;
        }

        /* Buttons */
        .btn-primary {
            background-color: var(--primary-green);
            border-color: var(--primary-green);
        }

        .btn-primary:hover {
            background-color: var(--dark-green);
            border-color: var(--dark-green);
        }

        .btn-outline-primary {
            color: var(--primary-green);
            border-color: var(--primary-green);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-green);
            border-color: var(--primary-green);
        }

        /* Tables */
        .table {
            font-size: 14px;
        }

        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        /* Form Controls */
        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 0.2rem rgba(0, 165, 81, 0.25);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }
        }

        .hover-scroll {
            height: 100vh;
            overflow-y: hidden;
            transition: overflow 0.3s ease;
        }

        .hover-scroll:hover {
            overflow-y: auto;
        }

        .hover-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .hover-scroll::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 4px;
        }

        .hover-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .text-green {
            color: var(--primary-green) !important;
        }
    </style>

    @stack('styles')
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar hover-scroll" id="sidebar">
        <div class="sidebar-logo">
            <i class="fas fa-leaf"></i> ERP SYSTEM
        </div>
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                        href="{{ route('dashboard') }}">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>

                @can('users.view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                        href="{{ route('users.index') }}">
                        <i class="fas fa-users"></i> Users
                    </a>
                </li>
                @endcan

                @can('roles.view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}"
                        href="{{ route('roles.index') }}">
                        <i class="fas fa-user-shield"></i> Roles
                    </a>
                </li>
                @endcan

                @can('permissions.view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}"
                        href="{{ route('permissions.index') }}">
                        <i class="fas fa-key"></i> Permissions
                    </a>
                </li>
                @endcan
                @php
                $role = auth()->user()->getRoleNames()->first();
                $permissions = getCurrentRolePermissions($role);
                @endphp


                @if ($permissions->contains('name', 'staff.view'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('department.*') ? 'active' : '' }}"
                        href="{{ route('department.index') }}">
                        <i class="fas fa-building me-2"></i> Department
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('staff.*') ? 'active' : '' }}"
                        href="{{ route('staff.index') }}">
                        <i class="fas fa-user-friends"></i> Staff
                    </a>
                </li>
                @endif

                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ request()->routeIs('brand.*') || request()->routeIs('model.*') || request()->routeIs('categories.*') || request()->routeIs('uom.*') || request()->routeIs('warehouse.*') || request()->routeIs('customers.*') || request()->routeIs('vendors.*') || request()->routeIs('product.*') ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" href="#masterMenu" role="button"
                        aria-expanded="{{ request()->routeIs('brand.*') || request()->routeIs('model.*') || request()->routeIs('categories.*') || request()->routeIs('uom.*') || request()->routeIs('warehouse.*') || request()->routeIs('customers.*') || request()->routeIs('vendors.*') || request()->routeIs('product.*') ? 'true' : 'false' }}"
                        aria-controls="masterMenu">
                        <span><i class="fas fa-tools me-2"></i> Master</span>
                        <i class="fas fa-chevron-down"></i>
                    </a>


                    <div class="collapse {{ request()->routeIs('brand.*') || request()->routeIs('model.*') || request()->routeIs('categories.*') || request()->routeIs('uom.*') || request()->routeIs('warehouse.*') || request()->routeIs('customers.*') || request()->routeIs('vendors.*') || request()->routeIs('product.*') ? 'show' : '' }}"
                        id="masterMenu">
                        <ul class="nav flex-column ms-3">

                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('brand.*') ? 'active' : '' }}"
                                    href="{{ route('brand.index') }}">
                                    <i class="fas fa-briefcase me-2"></i> Brand
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('model.*') ? 'active' : '' }}"
                                    href="{{ route('model.index') }}">
                                    <i class="fas fa-layer-group me-2"></i> Model
                                </a>
                            </li>


                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}"
                                    href="{{ route('categories.index') }}">
                                    <i class="fas fa-sitemap me-2"></i> Categories
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('uom.*') ? 'active' : '' }}"
                                    href="{{ route('uom.index') }}">
                                    <i class="fas fa-balance-scale me-2"></i> UOM
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('warehouse.*') ? 'active' : '' }}"
                                    href="{{ route('warehouse.index') }}">
                                    <i class="fas fa-warehouse me-2"></i> Warehouse
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}"
                                    href="{{ route('customers.index') }}">
                                    <i class="fas fa-user-tie"></i> Customers
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('product.*') ? 'active' : '' }}"
                                    href="{{ route('product.index') }}">
                                    <i class="fas fa-file-invoice"></i> Products
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vendors.*') ? 'active' : '' }}" href="{{ route('vendors.index') }}">
                                    <i class="fas fa-truck"></i> Vendors
                                </a>
                            </li>



                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('temple-categories.*') ? 'active' : '' }}"
                        href="{{ route('temple-categories.index') }}">
                        <i class="fas fa-place-of-worship"></i> Temple Categories
                    </a>
                </li>


                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center 
        {{ request()->routeIs('leads.*') || request()->routeIs('followups.*') ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" href="#leadMenu" role="button"
                        aria-expanded="{{ request()->routeIs('leads.*') || request()->routeIs('followups.*') ? 'true' : 'false' }}"
                        aria-controls="leadMenu">
                        <span><i class="fas fa-user-plus me-2"></i> Lead Management</span>
                        <i class="fas fa-chevron-down"></i>
                    </a>

                    <div class="collapse {{ request()->routeIs('leads.*') || request()->routeIs('followups.*') ? 'show' : '' }}"
                        id="leadMenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('leads.*') ? 'active' : '' }}"
                                    href="{{ route('leads.index') }}">
                                    <i class="fas fa-user me-2"></i> Lead
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('followups.*') ? 'active' : '' }}"
                                    href="{{ route('followups.index') }}">
                                    <i class="fas fa-calendar-check me-2"></i> Follow-Up
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>


                @if ( $permissions->contains('name', 'sales.taxes.view') ||
                $permissions->contains('name', 'sales.quotations.view') ||
                $permissions->contains('name', 'sales.invoices.view') ||
                $permissions->contains('name', 'sales.delivery_orders.view'))

                @php
                $role = auth()->user()->getRoleNames()->first();
                $permissions = getCurrentRolePermissions($role);
                @endphp

                @if (
                $permissions->contains('name', 'sales.taxes.view') ||
                $permissions->contains('name', 'sales.quotations.view') ||
                $permissions->contains('name', 'sales.invoices.view') ||
                $permissions->contains('name', 'sales.delivery_orders.view')
                )
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center 
                    {{ request()->routeIs('sales.*') ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" href="#salesMenu" role="button"
                        aria-expanded="{{ request()->routeIs('sales.*') ? 'true' : 'false' }}"
                        aria-controls="salesMenu">
                        <span><i class="fas fa-shopping-cart me-2"></i> Sales</span>
                        <i class="fas fa-chevron-down"></i>
                    </a>

                    <div class="collapse {{ request()->routeIs('sales.*') ? 'show' : '' }}" id="salesMenu">
                        <ul class="nav flex-column ms-3">

                            @if ($permissions->contains('name', 'sales.taxes.view'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('sales.taxes.*') ? 'active' : '' }}"
                                    href="{{ route('sales.taxes.index') }}">
                                    <i class="fas fa-percentage me-2"></i> Tax Management
                                </a>
                            </li>

                            @endif

                            @if ($permissions->contains('name', 'sales.quotations.view'))

                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('sales.quotations.*') ? 'active' : '' }}"
                                    href="{{ route('sales.quotations.index') }}">
                                    <i class="fas fa-file-invoice me-2"></i> Quotations
                                </a>
                            </li>
                            @endif


                            @if ($permissions->contains('name', 'sales.invoices.view'))

                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('sales.invoices.*') ? 'active' : '' }}"
                                    href="{{ route('sales.invoices.index') }}">
                                    <i class="fas fa-file-invoice-dollar me-2"></i> Sales Invoices
                                </a>
                            </li>

                            @endif
                            @if ($permissions->contains('name', 'sales.delivery_orders.view'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('sales.delivery-orders.*') ? 'active' : '' }}"
                                    href="{{ route('sales.delivery-orders.index') }}">
                                    <i class="fas fa-truck me-2"></i> Delivery Orders
                                </a>
                            </li>
                            @endif

                        </ul>
                    </div>
                </li>

             @endif

                @if (
                $permissions->contains('name', 'purchases.po.view') ||
                $permissions->contains('name', 'purchases.invoices.view') ||
                $permissions->contains('name', 'purchases.grn.view') ||
                $permissions->contains('name', 'purchases.returns.view')
                )
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center 
                                  {{ request()->routeIs('purchase.*') ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" href="#purchaseMenu" role="button"
                        aria-expanded="{{ request()->routeIs('purchase.*') ? 'true' : 'false' }}"
                        aria-controls="purchaseMenu">
                        <span><i class="fas fa-shopping-bag me-2"></i> Purchases</span>
                        <i class="fas fa-chevron-down"></i>
                    </a>

                    <div class="collapse {{ request()->routeIs('purchase.*') ? 'show' : '' }}" id="purchaseMenu">
                        <ul class="nav flex-column ms-3">

                            @if ($permissions->contains('name', 'purchases.po.view'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('purchase.orders.*') ? 'active' : '' }}"
                                    href="{{ route('purchase.orders.index') }}">
                                    <i class="fas fa-file-alt me-2"></i> Purchase Orders
                                </a>
                            </li>
                            @endif

                            @if ($permissions->contains('name', 'purchases.invoices.view'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('purchase.invoices.*') ? 'active' : '' }}"
                                    href="{{ route('purchase.invoices.index') }}">
                                    <i class="fas fa-file-invoice me-2"></i> Purchase Invoices
                                </a>
                            </li>
                            @endif

                            @if ($permissions->contains('name', 'purchases.grn.view'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('purchase.grn.*') ? 'active' : '' }}"
                                    href="{{ route('purchase.grn.index') }}">
                                    <i class="fas fa-truck-loading me-2"></i> Goods Receipt Notes
                                </a>
                            </li>
                            @endif

                            @if ($permissions->contains('name', 'purchases.returns.view'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('purchase.returns.*') ? 'active' : '' }}"
                                    href="{{ route('purchase.returns.index') }}">
                                    <i class="fas fa-undo me-2"></i> Purchase Returns
                                </a>
                            </li>
                            @endif

                            @if ($permissions->contains('name', 'purchases.reports.view'))
                            <li class="nav-item">
                                <a class="nav-link d-flex justify-content-between align-items-center collapsed"
                                    data-bs-toggle="collapse" href="#purchaseReportsMenu" role="button"
                                    aria-expanded="false" aria-controls="purchaseReportsMenu">
                                    <span><i class="fas fa-chart-bar me-2"></i> Reports</span>
                                    <i class="fas fa-chevron-down"></i>
                                </a>
                                <div class="collapse" id="purchaseReportsMenu">
                                    <ul class="nav flex-column ms-3">
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('purchase.reports.purchase-summary') }}">
                                                <i class="fas fa-chart-pie me-2"></i> Purchase Summary
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('purchase.reports.vendor-performance') }}">
                                                <i class="fas fa-star me-2"></i> Vendor Performance
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('purchase.reports.pending-approvals') }}">
                                                <i class="fas fa-clock me-2"></i> Pending Approvals
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('purchase.reports.grn-status') }}">
                                                <i class="fas fa-clipboard-check me-2"></i> GRN Status
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @endif

                        </ul>
                    </div>
                </li>
                @endif
                <!-- NEW ACCOUNTS MENU -->

                @if (
                $permissions->contains('name', 'accounts.receipt.view') ||
                $permissions->contains('name', 'accounts.payment.view') ||
                $permissions->contains('name', 'accounts.journal.view') ||
                $permissions->contains('name', 'chart_of_accounts.view') ||
                $permissions->contains('name', 'accounts.reports.view')
                )
                {{-- ACCOUNTS MENU WITH PERMISSIONS --}}

                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center 
                  {{ request()->routeIs('chart_of_accounts.*') || request()->routeIs('accounts.*') || request()->routeIs('receipt.*') ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse"
                        href="#accountsMenu"
                        role="button"
                        aria-expanded="{{ request()->routeIs('chart_of_accounts.*') || request()->routeIs('accounts.*') || request()->routeIs('receipt.*') ? 'true' : 'false' }}"
                        aria-controls="accountsMenu">
                        <span><i class="fas fa-calculator me-2"></i> Accounts</span>
                        <i class="fas fa-chevron-down"></i>
                    </a>

                    <div class="collapse 
                  {{ request()->routeIs('chart_of_accounts.*') || request()->routeIs('accounts.*') || request()->routeIs('receipt.*') ? 'show' : '' }}"
                        id="accountsMenu">
                        <ul class="nav flex-column ms-3">

                            @if ($permissions->contains('name', 'chart_of_accounts.view'))
                            {{-- Chart of Accounts --}}
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('chart_of_accounts.*') ? 'active' : '' }}"
                                    href="{{ route('chart_of_accounts.index') }}">
                                    <i class="fas fa-sitemap me-2"></i> Chart of Accounts
                                </a>
                            </li>
                            @endif
                            @if ($permissions->contains('name', 'accounts.receipt.view'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('receipt.*') ? 'active' : '' }}"
                                    href="{{ route('accounts.receipt.list') }}">
                                    <i class="fas fa-receipt me-2"></i> Receipt
                                </a>
                            </li>
                            @endif


                            {{-- Payment Vouchers --}}
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('accounts.payment.*') ? 'active' : '' }}"
                                    href="{{ route('accounts.payment.list') }}">
                                    <i class="fas fa-minus-circle me-2"></i> Payment Vouchers
                                </a>
                            </li>



                            {{-- Journal Entries --}}
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('accounts.journal.*') ? 'active' : '' }}"
                                    href="{{ route('accounts.journal.list') }}">
                                    <i class="fas fa-exchange-alt me-2"></i> Journal
                                </a>
                            </li>


                            {{-- General Ledger --}}
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('accounts.reports.general-ledger') ? 'active' : '' }}"
                                    href="{{ route('accounts.reports.general-ledger') }}">
                                    <i class="fas fa-book me-2"></i> General Ledger
                                </a>
                            </li>
                            {{-- Trial Balance --}}
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('accounts.reports.trial-balance') ? 'active' : '' }}"
                                    href="{{ route('accounts.reports.trial-balance') }}">
                                    <i class="fas fa-balance-scale me-2"></i> Trial Balance
                                </a>
                            </li>
                            {{-- Income Statement --}}
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('accounts.income-statement') ? 'active' : '' }}"
                                    href="{{ route('accounts.income-statement') }}">
                                    <i class="fas fa-chart-line me-2"></i> Income Statement
                                </a>
                            </li>
                            {{-- Balance Sheet --}}
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('accounts.reports.balance-sheet') ? 'active' : '' }}"
                                    href="{{ route('accounts.reports.balance-sheet') }}">
                                    <i class="fas fa-file-invoice-dollar me-2"></i> Balance Sheet
                                </a>
                            </li>
                            {{-- Reconciliation --}}
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('accounts.reconciliation.*') ? 'active' : '' }}"
                                    href="{{ route('accounts.reconciliation.index') }}">
                                    <i class="fas fa-exchange-alt me-2"></i> Reconciliation

                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endif



                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-wrench"></i> Service
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-users-cog"></i> HRM
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-chart-line"></i> Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}">
                        <i class="fas fa-cogs"></i> Settings
                    </a>
                </li>

            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar d-flex justify-content-between align-items-center">
            <button class="btn btn-link d-md-none" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>

            <div class="ms-auto navbar-user-info">
                <div class="dropdown">
                    <button class="btn btn-link dropdown-toggle text-decoration-none text-dark" type="button"
                        data-bs-toggle="dropdown">
                        <div class="d-flex align-items-center gap-2">
                            <div class="user-avatar">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <div class="text-start">
                                <div class="fw-semibold">{{ Auth::user()->name }}</div>
                                <div class="small text-muted">{{ Auth::user()->roles->first()->name ?? 'User' }}</div>
                            </div>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Settings</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>


        <!-- Content Wrapper -->
        <div class="content-wrapper">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @yield('content')
        </div>
    </div>


    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Other Scripts -->
    <!-- <script src="{{ asset('js/accounts.js') }}"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Select2 -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> -->
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        // Close sidebar when clicking outside on mobile
        $(document).on('click', function(e) {
            if ($(window).width() < 768) {
                if (!$(e.target).closest('.sidebar, .btn-link').length) {
                    $('#sidebar').removeClass('show');
                }
            }
        });
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>

    @stack('scripts')
</body>

</html>
