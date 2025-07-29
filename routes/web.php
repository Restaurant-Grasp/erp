<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\MasterController;
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

// Only allow access to users with roles: super_admin or hr_manager
Route::middleware(['superadminandhrmanager.access'])->group(function () {

    // Staff Routes
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/index', [StaffController::class, 'index'])->name('index');
        Route::get('/create', [StaffController::class, 'create'])->name('create');
        Route::post('/store', [StaffController::class, 'store'])->name('store');
        Route::get('/edit/{staff}', [StaffController::class, 'edit'])->name('edit');
        Route::put('/update/{staff}', [StaffController::class, 'update'])->name('update');
        Route::delete('/destroy/{staff}', [StaffController::class, 'destroy'])->name('destroy');
    });

    // Department Routes
    Route::prefix('department')->name('department.')->group(function () {
        Route::get('/index', [DepartmentController::class, 'index'])->name('index');
        Route::get('/create', [DepartmentController::class, 'create'])->name('create');
        Route::post('/store', [DepartmentController::class, 'store'])->name('store');
        Route::get('/edit/{department}', [DepartmentController::class, 'edit'])->name('edit');
        Route::put('/update/{department}', [DepartmentController::class, 'update'])->name('update');
        Route::delete('/destroy/{department}', [DepartmentController::class, 'destroy'])->name('destroy');
    });
});
//Master-Brand Routes
Route::prefix('brand')->name('brand.')->group(function () {
    Route::get('/index', [MasterController::class, 'brandIndex'])->name('index');
    Route::get('/create', [MasterController::class, 'brandCreate'])->name('create');
    Route::post('/store', [MasterController::class, 'brandStore'])->name('store');
    Route::get('/edit/{brand}', [MasterController::class, 'brandEdit'])->name('edit');
    Route::put('/update/{brand}', [MasterController::class, 'brandUpdate'])->name('update');
    Route::delete('/destroy/{brand}', [MasterController::class, 'brandDestroy'])->name('destroy');
});

//Master-Model Routes
Route::prefix('model')->name('model.')->group(function () {
    Route::get('/index', [MasterController::class, 'modelIndex'])->name('index');
    Route::get('/create', [MasterController::class, 'modelCreate'])->name('create');
    Route::post('/store', [MasterController::class, 'modelStore'])->name('store');
    Route::get('/edit/{model}', [MasterController::class, 'modelEdit'])->name('edit');
    Route::put('/update/{model}', [MasterController::class, 'modelUpdate'])->name('update');
    Route::delete('/destroy/{model}', [MasterController::class, 'modelDestroy'])->name('destroy');
});


//Product-categories Routes
Route::prefix('categories')->name('categories.')->group(function () {
    Route::get('/index', [ProductController::class, 'categoriesIndex'])->name('index');
    Route::get('/create', [ProductController::class, 'categoriesCreate'])->name('create');
    Route::post('/store', [ProductController::class, 'categoriesStore'])->name('store');
    Route::get('/edit/{categories}', [ProductController::class, 'categoriesEdit'])->name('edit');
    Route::put('/update/{categories}', [ProductController::class, 'categoriesUpdate'])->name('update');
    Route::delete('/destroy/{categories}', [ProductController::class, 'categoriesDestroy'])->name('destroy');

});