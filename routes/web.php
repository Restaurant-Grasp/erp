<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect root to login or dashboard
Route::get('/', function () {
    return Auth::check() ? redirect('/dashboard') : redirect('/login');
});

// Authentication Routes
Auth::routes(['register' => false]); // Disable registration

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // User Management
    Route::resource('users', UserController::class);
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

    // Role Management
    Route::resource('roles', RoleController::class);
    Route::post('roles/{role}/duplicate', [RoleController::class, 'duplicate'])->name('roles.duplicate');

    // Permission Management
    Route::resource('permissions', PermissionController::class)->only(['index', 'create', 'store']);

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Placeholder routes for future modules
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', function () {
            return view('coming-soon', ['module' => 'Customers']);
        })->name('index');
    });

    Route::prefix('vendors')->name('vendors.')->group(function () {
        Route::get('/', function () {
            return view('coming-soon', ['module' => 'Vendors']);
        })->name('index');
    });

    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', function () {
            return view('coming-soon', ['module' => 'Products']);
        })->name('index');
    });

    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/quotations', function () {
            return view('coming-soon', ['module' => 'Quotations']);
        })->name('quotations.index');
        
        Route::get('/invoices', function () {
            return view('coming-soon', ['module' => 'Invoices']);
        })->name('invoices.index');
    });

    Route::prefix('purchases')->name('purchases.')->group(function () {
        Route::get('/orders', function () {
            return view('coming-soon', ['module' => 'Purchase Orders']);
        })->name('orders.index');
    });

    Route::prefix('hrm')->name('hrm.')->group(function () {
        Route::get('/staff', function () {
            return view('coming-soon', ['module' => 'Staff Management']);
        })->name('staff.index');
        
        Route::get('/payroll', function () {
            return view('coming-soon', ['module' => 'Payroll']);
        })->name('payroll.index');
        
        Route::get('/attendance', function () {
            return view('coming-soon', ['module' => 'Attendance']);
        })->name('attendance.index');
    });

    Route::prefix('service')->name('service.')->group(function () {
        Route::get('/tickets', function () {
            return view('coming-soon', ['module' => 'Service Tickets']);
        })->name('tickets.index');
        
        Route::get('/amc', function () {
            return view('coming-soon', ['module' => 'AMC Contracts']);
        })->name('amc.index');
    });

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', function () {
            return view('coming-soon', ['module' => 'Reports']);
        })->name('index');
    });
});