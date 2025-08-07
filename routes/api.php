<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TempleCategoryController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\FollowUpTemplateController;
use App\Http\Controllers\CommunicationHistoryController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth')->get('/user', function (Request $request) {
    return $request->user();
});

    
    /*
    |--------------------------------------------------------------------------
    | Lead Management API Routes
    |--------------------------------------------------------------------------
    */
    
    // Lead Search API
    Route::get('leads/search', [LeadController::class, 'search'])
        ->name('api.leads.search');
    
    // Lead Statistics API
    Route::get('leads/stats', [LeadController::class, 'getStatistics'])
        ->name('api.leads.stats');
    
    // Lead Activities API
    Route::get('leads/{lead}/activities', [LeadController::class, 'getActivities'])
        ->name('api.leads.activities');
    
    // Temple Categories API
    Route::get('temple-categories', [TempleCategoryController::class, 'getAll'])
        ->name('api.temple-categories.all');
    
    Route::get('temple-categories/active', [TempleCategoryController::class, 'getActive'])
        ->name('api.temple-categories.active');
    
    // Lead Quick Create API
    Route::post('leads/quick-create', [LeadController::class, 'quickCreate'])
        ->name('api.leads.quick-create');
    
    // Lead Status Update API
    Route::patch('leads/{lead}/status', [LeadController::class, 'updateStatus'])
        ->name('api.leads.update-status');
    
    /*
    |--------------------------------------------------------------------------
    | Follow-up Management API Routes
    |--------------------------------------------------------------------------
    */
    
    // Follow-up Dashboard Stats
    Route::get('followups/stats', [FollowUpController::class, 'getStats'])
        ->name('api.followups.stats');
    
    // Follow-up Calendar Events
    Route::get('followups/calendar-events', [FollowUpController::class, 'getCalendarEvents'])
        ->name('api.followups.calendar-events');
    
    // Follow-up Quick Create
    Route::post('followups/quick-create', [FollowUpController::class, 'quickCreate'])
        ->name('api.followups.quick-create');
    
    // Follow-up Templates API
    Route::get('followup-templates', [FollowUpTemplateController::class, 'getAll'])
        ->name('api.followup-templates.all');
    
    Route::get('followup-templates/{id}/preview', [FollowUpTemplateController::class, 'preview'])
        ->name('api.followup-templates.preview');
    
    // Communication History API
    Route::get('communication-history/entity/{type}/{id}', [CommunicationHistoryController::class, 'getByEntity'])
        ->name('api.communication-history.by-entity');
    
    Route::post('communication-history/quick-log', [CommunicationHistoryController::class, 'quickLog'])
        ->name('api.communication-history.quick-log');
    
    /*
    |--------------------------------------------------------------------------
    | Search and Autocomplete APIs
    |--------------------------------------------------------------------------
    */
    
    // Universal Search
    Route::get('search', function (Request $request) {
        $query = $request->get('q', '');
        $type = $request->get('type', 'all');
        
        $results = [];
        
        if ($type === 'all' || $type === 'leads') {
            $leads = \App\Models\Lead::where(function($q) use ($query) {
                $q->where('lead_no', 'like', "%{$query}%")
                  ->orWhere('company_name', 'like', "%{$query}%")
                  ->orWhere('contact_person', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('mobile', 'like', "%{$query}%");
            })
            ->whereNotIn('lead_status', ['won', 'lost'])
            ->limit(10)
            ->get(['id', 'lead_no', 'company_name', 'contact_person', 'lead_status']);
            
            foreach ($leads as $lead) {
                $results[] = [
                    'type' => 'lead',
                    'id' => $lead->id,
                    'title' => $lead->lead_no . ' - ' . ($lead->company_name ?: $lead->contact_person),
                    'subtitle' => 'Lead - ' . ucfirst($lead->lead_status),
                    'url' => route('leads.show', $lead)
                ];
            }
        }
        
        if ($type === 'all' || $type === 'customers') {
            $customers = \App\Models\Customer::where(function($q) use ($query) {
                $q->where('customer_code', 'like', "%{$query}%")
                  ->orWhere('company_name', 'like', "%{$query}%")
                  ->orWhere('contact_person', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->where('status', 'active')
            ->limit(10)
            ->get(['id', 'customer_code', 'company_name', 'contact_person']);
            
            foreach ($customers as $customer) {
                $results[] = [
                    'type' => 'customer',
                    'id' => $customer->id,
                    'title' => $customer->customer_code . ' - ' . $customer->company_name,
                    'subtitle' => 'Customer',
                    'url' => route('customers.show', $customer)
                ];
            }
        }
        
        return response()->json($results);
    })->name('api.search');
    
    // Entity-specific search endpoints
    Route::get('leads', function (Request $request) {
        $query = $request->get('q', '');
        
        $leads = \App\Models\Lead::where(function($q) use ($query) {
            if ($query) {
                $q->where('lead_no', 'like', "%{$query}%")
                  ->orWhere('company_name', 'like', "%{$query}%")
                  ->orWhere('contact_person', 'like', "%{$query}%");
            }
        })
        ->whereNotIn('lead_status', ['won', 'lost'])
        ->whereNull('converted_to_customer_id')
        ->limit(20)
        ->get(['id', 'lead_no', 'company_name', 'contact_person', 'mobile', 'email']);

        return response()->json($leads);
    })->name('api.leads');
    
    Route::get('customers', function (Request $request) {
        $query = $request->get('q', '');
        
        $customers = \App\Models\Customer::where(function($q) use ($query) {
            if ($query) {
                $q->where('customer_code', 'like', "%{$query}%")
                  ->orWhere('company_name', 'like', "%{$query}%")
                  ->orWhere('contact_person', 'like', "%{$query}%");
            }
        })
        ->where('status', 'active')
        ->limit(20)
        ->get(['id', 'customer_code', 'company_name', 'contact_person', 'mobile', 'email']);

        return response()->json($customers);
    })->name('api.customers');
    /*
    |--------------------------------------------------------------------------
    | Dashboard Widget APIs
    |--------------------------------------------------------------------------
    */
    
    // Lead Widget Data
    Route::get('widgets/leads', function () {
        $stats = [
            'total_leads' => \App\Models\Lead::count(),
            'new_leads_this_month' => \App\Models\Lead::whereMonth('created_at', now()->month)->count(),
            'qualified_leads' => \App\Models\Lead::where('lead_status', 'qualified')->count(),
            'conversion_rate' => \App\Models\Lead::whereNotNull('converted_to_customer_id')->count() / max(\App\Models\Lead::count(), 1) * 100
        ];
        
        return response()->json($stats);
    })->name('api.widgets.leads');
    
    // Follow-up Widget Data
    Route::get('widgets/followups', function () {
        $user = auth()->user();
        $query = \App\Models\FollowUp::query();
        
        if (!$user->hasRole(['super_admin', 'admin'])) {
            $query->where('assigned_to', $user->staff_id);
        }
        
        $stats = [
            'overdue' => (clone $query)->overdue()->count(),
            'today' => (clone $query)->whereDate('scheduled_date', today())->where('status', 'scheduled')->count(),
            'this_week' => (clone $query)->upcoming(7)->count(),
            'completed_this_month' => (clone $query)->where('status', 'completed')
                ->whereMonth('completed_date', now()->month)->count()
        ];
        
        return response()->json($stats);
    })->name('api.widgets.followups');
    
    /*
    |--------------------------------------------------------------------------
    | Notification APIs
    |--------------------------------------------------------------------------
    */
    
    // Get user notifications
    Route::get('notifications/followups', function () {
        $user = auth()->user();
        
        $notifications = \App\Models\FollowUp::where('assigned_to', $user->staff_id)
            ->where('status', 'scheduled')
            ->where('scheduled_date', '<=', now()->addDays(3))
            ->orderBy('scheduled_date', 'asc')
            ->limit(10)
            ->get(['id', 'subject', 'scheduled_date', 'priority', 'follow_up_type']);
        
        return response()->json($notifications);
    })->name('api.notifications.followups');


/*
|--------------------------------------------------------------------------
| Public API Routes (if needed)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Public lead submission (for website integration)
    Route::post('leads/submit', [LeadController::class, 'publicSubmit'])
        ->name('api.public.leads.submit')
        ->middleware('throttle:10,1'); // Rate limiting
});