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
use App\Http\Controllers\LeadController;
use App\Http\Controllers\TempleCategoryController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\FollowUpTemplateController;
use App\Http\Controllers\CommunicationHistoryController;




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

  // Lead CRUD Routes
    Route::resource('leads', LeadController::class);
    
    // Lead Conversion Routes
    Route::get('leads/{lead}/convert', [LeadController::class, 'convertToCustomer'])
        ->name('leads.convert')
        ->middleware('permission:leads.convert');
    
    Route::post('leads/{lead}/convert', [LeadController::class, 'processConversion'])
        ->name('leads.process-conversion')
        ->middleware('permission:leads.convert');
    
    // Lead Document Routes
    Route::get('leads/{lead}/documents/{document}/download', [LeadController::class, 'downloadDocument'])
        ->name('leads.documents.download')
        ->middleware('permission:leads.view');
    
    Route::delete('leads/{lead}/documents/{document}', [LeadController::class, 'deleteDocument'])
        ->name('leads.documents.delete')
        ->middleware('permission:leads.edit');
    
    Route::post('leads/{lead}/documents', [LeadController::class, 'uploadDocument'])
        ->name('leads.documents.upload')
        ->middleware('permission:leads.edit');
    
    // Lead Activity Routes
    Route::post('leads/{lead}/activities', [LeadController::class, 'storeActivity'])
        ->name('leads.activities.store')
        ->middleware('permission:leads.edit');
    
    // Temple Category Routes (for lead management)
    Route::prefix('temple-categories')->name('temple-categories.')->group(function () {
        Route::get('/', [TempleCategoryController::class, 'index'])->name('index');
        Route::post('/', [TempleCategoryController::class, 'store'])->name('store');
        Route::put('/{templeCategory}', [TempleCategoryController::class, 'update'])->name('update');
        Route::delete('/{templeCategory}', [TempleCategoryController::class, 'destroy'])->name('destroy');
    })->middleware('permission:temple_categories.manage');
    

    
    // Follow-up CRUD Routes
    Route::resource('followups', FollowUpController::class);
    
    // Follow-up Special Actions
    Route::get('followups/{followup}/complete', [FollowUpController::class, 'complete'])
        ->name('followups.complete')
        ->middleware('permission:followups.complete');
    
    Route::post('followups/{followup}/complete', [FollowUpController::class, 'markComplete'])
        ->name('followups.mark-complete')
        ->middleware('permission:followups.complete');
    
    Route::patch('followups/{followup}/reschedule', [FollowUpController::class, 'reschedule'])
        ->name('followups.reschedule')
        ->middleware('permission:followups.edit');
    
    // Follow-up Calendar View
    Route::get('followups/calendar/view', [FollowUpController::class, 'calendar'])
        ->name('followups.calendar')
        ->middleware('permission:followups.view');
    
    // Follow-up Template Routes
    Route::prefix('followup-templates')->name('followup-templates.')->group(function () {
        Route::get('/', [FollowUpTemplateController::class, 'index'])->name('index');
        Route::post('/', [FollowUpTemplateController::class, 'store'])->name('store');
        Route::put('/{followupTemplate}', [FollowUpTemplateController::class, 'update'])->name('update');
        Route::delete('/{followupTemplate}', [FollowUpTemplateController::class, 'destroy'])->name('destroy');
    })->middleware('permission:followup_templates.manage');
    
    // Communication History Routes
    Route::prefix('communication-history')->name('communication-history.')->group(function () {
        Route::get('/', [CommunicationHistoryController::class, 'index'])
            ->name('index')
            ->middleware('permission:communication_history.view');
        
        Route::post('/', [CommunicationHistoryController::class, 'store'])
            ->name('store')
            ->middleware('permission:communication_history.create');
    });


    
    // Quick access routes from other modules
    Route::get('leads/create-from-contact/{phone}', [LeadController::class, 'createFromContact'])
        ->name('leads.create-from-contact');
    
    Route::get('followups/create-for-lead/{lead}', [FollowUpController::class, 'createForLead'])
        ->name('followups.create-for-lead');
    
    Route::get('followups/create-for-customer/{customer}', [FollowUpController::class, 'createForCustomer'])
        ->name('followups.create-for-customer');
    // Follow-up routes
    Route::resource('followups', FollowUpController::class);
    Route::get('followups/{followup}/complete', [FollowUpController::class, 'complete'])
        ->name('followups.complete');
    Route::post('followups/{followup}/complete', [FollowUpController::class, 'markComplete'])
        ->name('followups.mark-complete');
    Route::patch('followups/{followup}/reschedule', [FollowUpController::class, 'reschedule'])
        ->name('followups.reschedule');
    Route::get('followups/calendar/view', [FollowUpController::class, 'calendar'])
        ->name('followups.calendar');
    
    // Follow-up Template routes
    Route::resource('followup-templates', FollowUpTemplateController::class)->except(['show', 'create', 'edit']);
    

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


//Master-categories Routes
Route::prefix('categories')->name('categories.')->group(function () {
    Route::get('/index', [ProductController::class, 'categoriesIndex'])->name('index');
    Route::get('/create', [ProductController::class, 'categoriesCreate'])->name('create');
    Route::post('/store', [ProductController::class, 'categoriesStore'])->name('store');
    Route::get('/edit/{categories}', [ProductController::class, 'categoriesEdit'])->name('edit');
    Route::put('/update/{categories}', [ProductController::class, 'categoriesUpdate'])->name('update');
    Route::delete('/destroy/{categories}', [ProductController::class, 'categoriesDestroy'])->name('destroy');

});

//Master-uom Routes
Route::prefix('uom')->name('uom.')->group(function () {
    Route::get('/index', [MasterController::class, 'uomIndex'])->name('index');
    Route::get('/create', [MasterController::class, 'uomCreate'])->name('create');
    Route::post('/store', [MasterController::class, 'uomStore'])->name('store');
    Route::get('/edit/{uom}', [MasterController::class, 'uomEdit'])->name('edit');
    Route::put('/update/{uom}', [MasterController::class, 'uomUpdate'])->name('update');
    Route::delete('/destroy/{uom}', [MasterController::class, 'uomDestroy'])->name('destroy');
});

//Master-warehouse Routes
Route::prefix('warehouse')->name('warehouse.')->group(function () {
    Route::get('/index', [MasterController::class, 'warehouseIndex'])->name('index');
    Route::get('/create', [MasterController::class, 'warehouseCreate'])->name('create');
    Route::post('/store', [MasterController::class, 'warehouseStore'])->name('store');
    Route::get('/edit/{warehouse}', [MasterController::class, 'warehouseEdit'])->name('edit');
    Route::put('/update/{warehouse}', [MasterController::class, 'warehouseUpdate'])->name('update');
    Route::delete('/destroy/{warehouse}', [MasterController::class, 'warehouseDestroy'])->name('destroy');
});

//Product Routes
Route::prefix('product')->name('product.')->group(function () {
    Route::get('/index', [ProductController::class, 'productIndex'])->name('index');
    Route::get('/create', [ProductController::class, 'productCreate'])->name('create');
    Route::post('/store', [ProductController::class, 'productStore'])->name('store');
    Route::get('/edit/{product}', [ProductController::class, 'productEdit'])->name('edit');
    Route::put('/update/{product}', [ProductController::class, 'productUpdate'])->name('update');
    Route::delete('/destroy/{product}', [ProductController::class, 'productDestroy'])->name('destroy');
   
     //Add quantity route
     Route::post('/add-quantity', [ProductController::class, 'addQuantity'])->name('addQuantity');
    
});
 