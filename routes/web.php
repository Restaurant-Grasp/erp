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
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ChartOfAccountsController;
use App\Http\Controllers\EntriesController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ReconciliationController;
use App\Http\Controllers\FundController;
use App\Http\Controllers\IncomeStatementController;

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
            ->name('index');

        Route::post('/', [CommunicationHistoryController::class, 'store'])
            ->name('store');
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

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings', [SettingsController::class, 'store'])->name('settings.store');
    Route::delete('/settings/{setting}', [SettingsController::class, 'destroy'])->name('settings.destroy');

    // Chart of Accounts Routes with Permissions
    Route::prefix('chart-of-accounts')->name('chart_of_accounts.')->group(function () {

        // Main Chart of Accounts (requires view permission)
        Route::get('/', [ChartOfAccountsController::class, 'index'])
            ->name('index')
            ->middleware('permission:chart_of_accounts.view');

        Route::get('/tree-data', [ChartOfAccountsController::class, 'getTreeData'])
            ->name('tree_data')
            ->middleware('permission:chart_of_accounts.view');

        Route::get('/summary-totals', [ChartOfAccountsController::class, 'getSummaryTotals'])
            ->name('summary_totals')
            ->middleware('permission:chart_of_accounts.view');

        // Group Management Routes
        Route::prefix('group')->name('group.')->group(function () {
            Route::get('/create', [ChartOfAccountsController::class, 'createGroup'])
                ->name('create')
                ->middleware('permission:chart_of_accounts.create_group');

            Route::post('/', [ChartOfAccountsController::class, 'storeGroup'])
                ->name('store')
                ->middleware('permission:chart_of_accounts.create_group');

            Route::get('/{id}/edit', [ChartOfAccountsController::class, 'editGroup'])
                ->name('edit')
                ->middleware('permission:chart_of_accounts.edit_group');

            Route::put('/{id}', [ChartOfAccountsController::class, 'updateGroup'])
                ->name('update')
                ->middleware('permission:chart_of_accounts.edit_group');

            Route::delete('/{id}', [ChartOfAccountsController::class, 'deleteGroup'])
                ->name('delete')
                ->middleware('permission:chart_of_accounts.delete_group');

            Route::get('/{id}/details', [ChartOfAccountsController::class, 'getGroupDetails'])
                ->name('details')
                ->middleware('permission:chart_of_accounts.view_ledger_details');
        });

        // Ledger Management Routes
        Route::prefix('ledger')->name('ledger.')->group(function () {
            Route::get('/create', [ChartOfAccountsController::class, 'createLedger'])
                ->name('create')
                ->middleware('permission:chart_of_accounts.create_ledger');

            Route::post('/store', [ChartOfAccountsController::class, 'storeLedger'])
                ->name('store')
                ->middleware('permission:chart_of_accounts.create_ledger');

            Route::get('/{id}/edit', [ChartOfAccountsController::class, 'editLedger'])
                ->name('edit')
                ->middleware('permission:chart_of_accounts.edit_ledger');

            Route::put('/{id}/update', [ChartOfAccountsController::class, 'updateLedger'])
                ->name('update')
                ->middleware('permission:chart_of_accounts.edit_ledger');

            Route::delete('/{id}', [ChartOfAccountsController::class, 'deleteLedger'])
                ->name('delete')
                ->middleware('permission:chart_of_accounts.delete_ledger');

            Route::get('/{id}/details', [ChartOfAccountsController::class, 'getLedgerDetails'])
                ->name('details')
                ->middleware('permission:chart_of_accounts.view_ledger_details');

            Route::get('/{id}/view', [ChartOfAccountsController::class, 'viewLedger'])
                ->name('view')
                ->middleware('permission:chart_of_accounts.view_ledger_details');
        });
    });

    // Legacy group routes for backward compatibility (add permissions)
    Route::prefix('group')->group(function () {
        Route::get('/{id}/details', [ChartOfAccountsController::class, 'getGroupDetails'])
            ->middleware('permission:chart_of_accounts.view_ledger_details');
    });

    // Accounts Menu Routes (already exist, just add permissions where missing)
    Route::prefix('accounts')->name('accounts.')->group(function () {

        // Journal Entries Routes (add permissions if needed)
        Route::prefix('receipt')->name('receipt.')->group(function () {
            Route::get('/', [EntriesController::class, 'receiptList'])->name('list');
            Route::get('/add', [EntriesController::class, 'addReceipt'])
                ->name('add');
            Route::post('/store', [EntriesController::class, 'storeReceipt'])
                ->name('store');
            Route::get('/edit/{id}', [EntriesController::class, 'editReceipt'])
                ->name('edit');
            Route::put('/update/{id}', [EntriesController::class, 'updateReceipt'])
                ->name('update');
            Route::get('/view/{id}', [EntriesController::class, 'viewReceipt'])
                ->name('view');
            Route::get('/copy/{id}', [EntriesController::class, 'copyReceipt'])
                ->name('copy');
            Route::get('/print/{id}', [EntriesController::class, 'printReceipt'])
                ->name('print');
        });

        Route::prefix('payment')->name('payment.')->group(function () {
            Route::get('/', [EntriesController::class, 'paymentList'])
                ->name('list')
                ->middleware('permission:accounts.payment.view');
            Route::get('/add', [EntriesController::class, 'addPayment'])
                ->name('add')
                ->middleware('permission:accounts.payment.create');
            Route::post('/store', [EntriesController::class, 'storePayment'])
                ->name('store')
                ->middleware('permission:accounts.payment.create');
            Route::get('/edit/{id}', [EntriesController::class, 'editPayment'])
                ->name('edit')
                ->middleware('permission:accounts.payment.edit');
            Route::put('/update/{id}', [EntriesController::class, 'updatePayment'])
                ->name('update')
                ->middleware('permission:accounts.payment.edit');
            Route::get('/view/{id}', [EntriesController::class, 'viewPayment'])
                ->name('view')
                ->middleware('permission:accounts.payment.view');
            Route::get('/copy/{id}', [EntriesController::class, 'copyPayment'])
                ->name('copy')
                ->middleware('permission:accounts.payment.create');
            Route::get('/print/{id}', [EntriesController::class, 'printPayment'])
                ->name('print')
                ->middleware('permission:accounts.payment.view');
        });

        Route::prefix('journal')->name('journal.')->group(function () {
            Route::get('/', [EntriesController::class, 'journalList'])
                ->name('list')
                ->middleware('permission:accounts.journal.view');
            Route::get('/add', [EntriesController::class, 'addJournal'])
                ->name('add')
                ->middleware('permission:accounts.journal.create');
            Route::post('/store', [EntriesController::class, 'storeJournal'])
                ->name('store')
                ->middleware('permission:accounts.journal.create');
            Route::get('/edit/{id}', [EntriesController::class, 'editJournal'])
                ->name('edit')
                ->middleware('permission:accounts.journal.edit');
            Route::put('/update/{id}', [EntriesController::class, 'updateJournal'])
                ->name('update')
                ->middleware('permission:accounts.journal.edit');
            Route::get('/view/{id}', [EntriesController::class, 'viewJournal'])
                ->name('view')
                ->middleware('permission:accounts.journal.view');
            Route::get('/copy/{id}', [EntriesController::class, 'copyJournal'])
                ->name('copy')
                ->middleware('permission:accounts.journal.create');
            Route::get('/print/{id}', [EntriesController::class, 'printJournal'])
                ->name('print')
                ->middleware('permission:accounts.journal.view');

        });
          Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('/general-ledger', [ReportsController::class, 'generalLedger'])->name('general-ledger');
                Route::get('/search-ledgers', [ReportsController::class, 'searchLedgers'])->name('search-ledgers');
                Route::get('/trial-balance', [ReportsController::class, 'trialBalance'])->name('trial-balance');
                Route::get('/balance-sheet', [ReportsController::class, 'balanceSheet'])->name('balance-sheet');
            });

            Route::get('/income-statement', [IncomeStatementController::class, 'index'])
                ->name('income-statement');
        // Bank Reconciliation
        Route::prefix('reconciliation')->name('reconciliation.')->group(function () {
            Route::get('/', [ReconciliationController::class, 'index'])
                ->name('index')
                ->middleware('permission:accounts.reconciliation.view');
            Route::get('/create', [ReconciliationController::class, 'create'])
                ->name('create')
                ->middleware('permission:accounts.reconciliation.create');
            Route::post('/start', [ReconciliationController::class, 'start'])
                ->name('start')
                ->middleware('permission:accounts.reconciliation.create');
            Route::get('/{id}/process', [ReconciliationController::class, 'process'])
                ->name('process')
                ->middleware('permission:accounts.reconciliation.edit');
            Route::post('/{id}/update-items', [ReconciliationController::class, 'updateItems'])
                ->name('update-items')
                ->middleware('permission:accounts.reconciliation.edit');
            Route::post('/{id}/update-balance', [ReconciliationController::class, 'updateBalance'])
                ->name('update-balance')
                ->middleware('permission:accounts.reconciliation.edit');
            Route::post('/{id}/investigation-note', [ReconciliationController::class, 'addInvestigationNote'])
                ->name('investigation-note')
                ->middleware('permission:accounts.reconciliation.edit');
            Route::post('/{id}/adjustment', [ReconciliationController::class, 'createAdjustment'])
                ->name('adjustment')
                ->middleware('permission:accounts.reconciliation.edit');
            Route::put('/{id}/finalize', [ReconciliationController::class, 'finalize'])
                ->name('finalize')
                ->middleware('permission:accounts.reconciliation.finalize');
            Route::put('/{id}/lock', [ReconciliationController::class, 'lock'])
                ->name('lock')
                ->middleware('permission:accounts.reconciliation.finalize');
            Route::get('/{id}/view', [ReconciliationController::class, 'view'])
                ->name('view')
                ->middleware('permission:accounts.reconciliation.view');
            Route::get('/{id}/report', [ReconciliationController::class, 'report'])
                ->name('report')
                ->middleware('permission:accounts.reconciliation.view');
            Route::delete('/{id}', [ReconciliationController::class, 'destroy'])
                ->name('destroy')
                ->middleware('permission:accounts.reconciliation.delete');
        });
    });
    // Enhanced Fund Management Routes
    Route::prefix('funds')->name('funds.')->group(function () {
        Route::get('/', [FundController::class, 'index'])->name('index')->middleware('permission:chart_of_accounts.view');
        Route::get('/create', [FundController::class, 'create'])->name('create')
            ->middleware('permission:chart_of_accounts.create_group');
        Route::post('/', [FundController::class, 'store'])->name('store')
            ->middleware('permission:chart_of_accounts.create_group');
        Route::get('/{id}', [FundController::class, 'show'])->name('show'); // New: Fund details
        Route::get('/{id}/edit', [FundController::class, 'edit'])->name('edit')
            ->middleware('permission:chart_of_accounts.edit_group');
        Route::put('/{id}', [FundController::class, 'update'])->name('update')->middleware('permission:chart_of_accounts.edit_group');
        Route::delete('delete/{id}', [FundController::class, 'destroy'])->name('destroy');

        // New Fund Analysis Routes
        Route::get('/analysis/comparison', [FundController::class, 'comparison'])->name('comparison');
        Route::get('/analysis/utilization', [FundController::class, 'utilization'])->name('utilization');
        Route::get('/analysis/trend', [FundController::class, 'trend'])->name('trend');
        Route::get('/export/comparison', [FundController::class, 'exportComparison'])->name('export.comparison');
        Route::get('/export/utilization', [FundController::class, 'exportUtilization'])->name('export.utilization');

        // Fund Transaction Routes
        Route::get('/{id}/transactions', [FundController::class, 'transactions'])->name('transactions');
        Route::get('/{id}/entries/{type}', [FundController::class, 'entriesByType'])->name('entries.type');
    });

    // Enhanced Financial Reports Routes
    Route::prefix('financial-reports')->name('financial.reports.')->group(function () {

        // Executive Dashboard Reports
        Route::get('/executive-dashboard', [ReportsController::class, 'executiveDashboard'])->name('executive.dashboard');
        Route::get('/financial-summary', [ReportsController::class, 'financialSummary'])->name('financial.summary');
        Route::get('/kpi-dashboard', [ReportsController::class, 'kpiDashboard'])->name('kpi.dashboard');

        // Cash Flow Reports
        Route::get('/cash-flow', [ReportsController::class, 'cashFlow'])->name('cash.flow');
        Route::get('/cash-flow/monthly', [ReportsController::class, 'monthlyCashFlow'])->name('cash.flow.monthly');
        Route::get('/cash-flow/fund-wise', [ReportsController::class, 'fundWiseCashFlow'])->name('cash.flow.fund.wise');

        // Ratio Analysis
        Route::get('/ratio-analysis', [ReportsController::class, 'ratioAnalysis'])->name('ratio.analysis');
        Route::get('/financial-ratios', [ReportsController::class, 'financialRatios'])->name('financial.ratios');
        Route::get('/trend-analysis', [ReportsController::class, 'trendAnalysis'])->name('trend.analysis');

        // Comparative Reports
        Route::get('/comparative-balance-sheet', [ReportsController::class, 'comparativeBalanceSheet'])->name('comparative.balance.sheet');
        Route::get('/comparative-income-statement', [ReportsController::class, 'comparativeIncomeStatement'])->name('comparative.income.statement');
        Route::get('/year-over-year', [ReportsController::class, 'yearOverYear'])->name('year.over.year');

        // Advanced Analytics
        Route::get('/budget-vs-actual', [ReportsController::class, 'budgetVsActual'])->name('budget.vs.actual');
        Route::get('/variance-analysis', [ReportsController::class, 'varianceAnalysis'])->name('variance.analysis');
        Route::get('/forecast-report', [ReportsController::class, 'forecastReport'])->name('forecast.report');
    });

    // API Routes for Financial Data
    Route::prefix('api/financial')->name('api.financial.')->group(function () {
        Route::get('/summary-totals', [ChartOfAccountsController::class, 'getSummaryTotals'])->name('summary.totals');
        Route::get('/fund-performance', [FundController::class, 'getFundPerformance'])->name('fund.performance');
        Route::get('/monthly-trends', [ReportsController::class, 'getMonthlyTrends'])->name('monthly.trends');
        Route::get('/kpi-metrics', [ReportsController::class, 'getKPIMetrics'])->name('kpi.metrics');
        Route::get('/cash-flow-data', [ReportsController::class, 'getCashFlowData'])->name('cash.flow.data');
        Route::get('/ratio-calculations', [ReportsController::class, 'getRatioCalculations'])->name('ratio.calculations');
    });

    // Enhanced Chart of Accounts Routes
    Route::prefix('chart-of-accounts')->name('chart_of_accounts.')->group(function () {


        // New Analysis Routes
        Route::get('/analysis/structure', [ChartOfAccountsController::class, 'structureAnalysis'])->name('analysis.structure');
        Route::get('/analysis/utilization', [ChartOfAccountsController::class, 'utilizationAnalysis'])->name('analysis.utilization');
        Route::get('/export/structure', [ChartOfAccountsController::class, 'exportStructure'])->name('export.structure');

        // Bulk Operations
        Route::post('/bulk/activate', [ChartOfAccountsController::class, 'bulkActivate'])->name('bulk.activate');
        Route::post('/bulk/deactivate', [ChartOfAccountsController::class, 'bulkDeactivate'])->name('bulk.deactivate');
        Route::post('/bulk/export', [ChartOfAccountsController::class, 'bulkExport'])->name('bulk.export');
    });

    // Settings for Financial Module
    Route::prefix('settings/financial')->name('settings.financial.')->group(function () {
        Route::get('/', [SettingsController::class, 'financialSettings'])->name('index');
        Route::post('/accounting', [SettingsController::class, 'updateAccountingSettings'])->name('accounting');
        Route::post('/reporting', [SettingsController::class, 'updateReportingSettings'])->name('reporting');
        Route::post('/taxes', [SettingsController::class, 'updateTaxSettings'])->name('taxes');
        Route::post('/currencies', [SettingsController::class, 'updateCurrencySettings'])->name('currencies');
    });
     // Customer Routes
    Route::resource('customers', CustomerController::class);
    Route::get('customers/{customer}/statement', [CustomerController::class, 'statement'])->name('customers.statement');
    Route::get('customers/search', [CustomerController::class, 'search'])->name('customers.search');
    
    // Lead Conversion Routes (Update existing lead routes)
    Route::get('leads/{lead}/convert', [LeadController::class, 'convertToCustomer'])->name('leads.convert');
    Route::post('leads/{lead}/convert', [LeadController::class, 'processConversion'])->name('leads.process-conversion');
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
