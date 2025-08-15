<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\Ledger;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    // Service Index
    public function serviceIndex()
    {
        $serviceList = Service::with(['serviceType', 'ledger'])
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('service.index', compact('serviceList'));
    }

    // Service Create
    public function serviceCreate()
    {
        $serviceTypes = ServiceType::where('status', 1)->get();
        $ledgers = Ledger::orderBy('name')->get();
        
        return view('service.create', compact('serviceTypes', 'ledgers'));
    }

    // Service Store
    public function serviceStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:services,code',
            'service_type_id' => 'required|exists:service_types,id',
            'ledger_id' => 'required|exists:ledgers,id',
            'item_type' => 'required|in:service,product',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:one-time,monthly,quarterly,yearly',
        ]);

        DB::beginTransaction();
        try {
            Service::create([
                'name' => $request->name,
                'code' => $request->code,
                'service_type_id' => $request->service_type_id,
                'ledger_id' => $request->ledger_id,
                'item_type' => $request->item_type,
                'description' => $request->description,
                'base_price' => $request->base_price,
                'is_recurring' => $request->has('is_recurring') ? 1 : 0,
                'billing_cycle' => $request->billing_cycle,
                'status' => $request->has('status') ? 1 : 0,
                'created_by' => auth()->id(),
            ]);

            DB::commit();
            return redirect()->route('service.index')->with('success', 'Service created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error creating service: ' . $e->getMessage());
        }
    }

    // Service Edit
    public function serviceEdit(Service $service)
    {
        $serviceTypes = ServiceType::where('status', 1)->get();
        $ledgers = Ledger::orderBy('name')->get();
        
        return view('service.edit', compact('service', 'serviceTypes', 'ledgers'));
    }

    // Service Update
    public function serviceUpdate(Request $request, Service $service)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:services,code,' . $service->id,
            'service_type_id' => 'required|exists:service_types,id',
            'ledger_id' => 'required|exists:ledgers,id',
            'item_type' => 'required|in:service,product',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:one-time,monthly,quarterly,yearly',
        ]);

        DB::beginTransaction();
        try {
            $service->update([
                'name' => $request->name,
                'code' => $request->code,
                'service_type_id' => $request->service_type_id,
                'ledger_id' => $request->ledger_id,
                'item_type' => $request->item_type,
                'description' => $request->description,
                'base_price' => $request->base_price,
                'is_recurring' => $request->has('is_recurring') ? 1 : 0,
                'billing_cycle' => $request->billing_cycle,
                'status' => $request->has('status') ? 1 : 0,
            ]);

            DB::commit();
            return redirect()->route('service.index')->with('success', 'Service updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error updating service: ' . $e->getMessage());
        }
    }

    // Service Destroy
    public function serviceDestroy(Service $service)
    {
        try {
            // Check if service is used in any transactions/quotations/invoices
            // Add your business logic here to prevent deletion if in use
            
            $service->delete();
            return redirect()->route('service.index')->with('success', 'Service deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting service: ' . $e->getMessage());
        }
    }

    // AJAX: Get services by type
    public function getServicesByType(Request $request)
    {
        $serviceTypeId = $request->get('service_type_id');
        $services = Service::where('service_type_id', $serviceTypeId)
            ->where('status', 1)
            ->select('id', 'name', 'code', 'base_price', 'billing_cycle')
            ->get();

        return response()->json($services);
    }

    // AJAX: Get service details
    public function getServiceDetails(Request $request)
    {
        $serviceId = $request->get('service_id');
        $service = Service::with(['serviceType', 'ledger'])
            ->find($serviceId);

        if (!$service) {
            return response()->json(['error' => 'Service not found'], 404);
        }

        return response()->json([
            'id' => $service->id,
            'name' => $service->name,
            'code' => $service->code,
            'description' => $service->description,
            'base_price' => $service->base_price,
            'billing_cycle' => $service->billing_cycle,
            'is_recurring' => $service->is_recurring,
            'service_type' => $service->serviceType->name ?? '',
            'ledger' => $service->ledger->name ?? '',
            'ledger_id' => $service->ledger_id,
        ]);
    }
}