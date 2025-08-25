<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PackageService;
use App\Models\Service;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PackageController extends Controller
{
    /**
     * Show the form for creating a new package.
     */
    public function create()
    {
        $services = Service::where('status', 1)->where('item_type','item')->get();

        return view('packages.create', compact('services'));
    }

    /**
     * Store a newly created package in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:packages,code',
            'description' => 'nullable|string',
            'validity_days' => 'nullable|integer|min:1',
            'is_subscription' => 'boolean',
            'subscription_cycle' => 'nullable|in:monthly,quarterly,yearly',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'status' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // Calculate totals first
            $subtotal = 0;
            $validatedItems = [];

            foreach ($request->items as $item) {
                $unitPrice = isset($item['unit_price']) ? (float) $item['unit_price'] : 0;
                $service = Service::findOrFail($item['item_id']);
                
                if ($unitPrice == 0) {
                    $unitPrice = $service->base_price;
                }
                
                $validatedItems[] = [
                    'service_id' => $service->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'total' => $unitPrice * $item['quantity']
                ];
                
                $subtotal += end($validatedItems)['total'];
            }

            // Calculate discount
            $discountPercentage = $request->discount_percentage ?? 0;
            $discountAmount = ($subtotal * $discountPercentage) / 100;
            $finalPrice = $subtotal - $discountAmount;

            // Create package
            $package = Package::create([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'subtotal' => $subtotal,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'package_price' => $finalPrice,
                'validity_days' => $request->validity_days,
                'is_subscription' => $request->is_subscription ?? false,
                'subscription_cycle' => $request->subscription_cycle,
                'status' => $request->status ?? true,
                'created_by' => auth()->id(),
            ]);

            foreach ($validatedItems as $item) {
                PackageService::create([
                    'package_id' => $package->id,
                    'service_id' => $item['service_id'],
                    'product_id' => null,
                    'item_type' => 'item',
                    'quantity' => $item['quantity'],          
                    'amount' => $item['unit_price'],    
                    'discount_percentage' => 0, 
                ]);
            }

            DB::commit();
            return redirect()->route('packages.index')
                ->with('success', 'Package created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error creating package: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Show the form for editing the specified package.
     */
    public function edit(Package $package)
    {
        $services = Service::where('status', 1)->where('item_type','item')->get();
        $package->load(['packageItems.service']);

        return view('packages.edit', compact('package', 'services'));
    }

    /**
     * Update the specified package in storage.
     */
    public function update(Request $request, Package $package)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:packages,code,' . $package->id,
            'description' => 'nullable|string',
            'validity_days' => 'nullable|integer|min:1',
            'is_subscription' => 'boolean',
            'subscription_cycle' => 'nullable|in:monthly,quarterly,yearly',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'status' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // Calculate totals
            $subtotal = 0;
            $validatedItems = [];

            foreach ($request->items as $item) {
                $unitPrice = isset($item['unit_price']) ? (float) $item['unit_price'] : 0;
                $service = Service::findOrFail($item['item_id']);
                
                if ($unitPrice == 0) {
                    $unitPrice = $service->base_price;
                }
                
                $validatedItems[] = [
                    'service_id' => $service->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'total' => $unitPrice * $item['quantity']
                ];
                
                $subtotal += end($validatedItems)['total'];
            }

            // Calculate discount
            $discountPercentage = $request->discount_percentage ?? 0;
            $discountAmount = ($subtotal * $discountPercentage) / 100;
            $finalPrice = $subtotal - $discountAmount;

            // Update package
            $package->update([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'subtotal' => $subtotal,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'package_price' => $finalPrice,
                'validity_days' => $request->validity_days,
                'is_subscription' => $request->is_subscription ?? false,
                'subscription_cycle' => $request->subscription_cycle,
                'status' => $request->status ?? true,
            ]);

            // Delete existing items and create new ones
            $package->packageItems()->delete();

            foreach ($validatedItems as $item) {
                PackageService::create([
                    'package_id' => $package->id,
                    'service_id' => $item['service_id'],
                    'product_id' => null,
                    'item_type' => 'item',
                    'quantity' => $item['quantity'],          
                    'amount' => $item['unit_price'],    
                    'discount_percentage' => 0, 
                ]);
            }

            DB::commit();
            return redirect()->route('packages.show', $package)
                ->with('success', 'Package updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error updating package: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Get services for dropdown
     */
    public function getServices(Request $request)
    {
        $services = Service::where('status', 1)->where('item_type','item')
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->get(['id', 'name', 'code', 'base_price', 'billing_cycle']);

        return response()->json($services->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'code' => $service->code,
                'price' => $service->base_price,
                'billing_cycle' => $service->billing_cycle,
                'formatted_price' => 'RM ' . number_format($service->base_price, 2)
            ];
        }));
    }

    /**
     * Display a listing of packages.
     */
    public function index(Request $request)
    {
        $query = Package::with(['packageItems.item']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Subscription filter
        if ($request->filled('subscription')) {
            $query->where('is_subscription', $request->subscription);
        }

        // Cycle filter
        if ($request->filled('cycle')) {
            $query->where('subscription_cycle', $request->cycle);
        }

        $packages = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('packages.index', compact('packages'));
    }

    /**
     * Display the specified package.
     */
    public function show(Package $package)
    {
        $package->load(['packageItems.item']);
        return view('packages.show', compact('package'));
    }

    /**
     * Remove the specified package from storage.
     */
    public function destroy(Package $package)
    {
        if (!$package->canBeDeleted()) {
            return redirect()->route('packages.index')
                ->with('error', 'Package cannot be deleted as it is being used in quotations or invoices.');
        }

        try {
            $package->packageItems()->delete();
            $package->delete();

            return redirect()->route('packages.index')
                ->with('success', 'Package deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('packages.index')
                ->with('error', 'Error deleting package: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate a package
     */
    public function duplicate(Package $package)
    {
        DB::beginTransaction();
        try {
            // Create new package with duplicated data
            $newPackage = $package->replicate();
            $newPackage->name = $package->name . ' (Copy)';
            $newPackage->code = $package->code . '_COPY_' . time();
            $newPackage->created_by = auth()->id();
            $newPackage->save();

            // Duplicate package items
            foreach ($package->packageItems as $item) {
                $newItem = $item->replicate();
                $newItem->package_id = $newPackage->id;
                $newItem->save();
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'package_id' => $newPackage->id,
                'message' => 'Package duplicated successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error duplicating package: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate package totals (for AJAX)
     */
    public function calculateTotals(Request $request)
    {
        $subtotal = 0;

        if ($request->has('items') && is_array($request->items)) {
            foreach ($request->items as $item) {
                if (!isset($item['item_id'], $item['quantity'])) {
                    continue;
                }

                $quantity = (int) $item['quantity'];
                if ($quantity <= 0) continue;

                $service = Service::find($item['item_id']);
                if ($service) {
                    $unitPrice = isset($item['unit_price']) ? (float) $item['unit_price'] : $service->base_price;
                    $subtotal += $unitPrice * $quantity;
                }
            }
        }

        $discountPercentage = (float) ($request->discount_percentage ?? 0);
        $discountAmount = ($subtotal * $discountPercentage) / 100;
        $finalTotal = $subtotal - $discountAmount;

        return response()->json([
            'subtotal' => $subtotal,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount,
            'final_total' => $finalTotal,
            'formatted' => [
                'subtotal' => 'RM ' . number_format($subtotal, 2),
                'discount_amount' => 'RM ' . number_format($discountAmount, 2),
                'final_total' => 'RM ' . number_format($finalTotal, 2)
            ]
        ]);
    }
}