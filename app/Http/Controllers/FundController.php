<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fund;
use App\Models\Entry;
use DB;
use Auth;

class FundController extends Controller
{
    /**
     * Display a listing of funds.
     */
    public function index()
    {
         $funds = Fund::orderBy('name', 'asc')->get();
        return view('accounts.funds.index', compact('funds'));
    }
    
    /**
     * Show the form for creating a new fund.
     */
    public function create()
    {
        return view('accounts.funds.create');
    }
    
    /**
     * Store a newly created fund.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:250|unique:funds,code',
            'name' => 'required|string|max:350',
            'description' => 'nullable|string'
        ]);
        
        DB::beginTransaction();
        
        try {
            $fund = new Fund();
            $fund->code = $request->code;
            $fund->name = $request->name;
            $fund->description = $request->description;
            $fund->save();
            
            DB::commit();
            
            return redirect()->route('funds.index')
                ->with('success', 'Fund created successfully!');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error creating fund: ' . $e->getMessage())
                        ->withInput();
        }
    }
    
    /**
     * Show the form for editing the specified fund.
     */
    public function edit($id)
    {
        $fund = Fund::findOrFail($id);
        return view('accounts.funds.edit', compact('fund'));
    }
    
    /**
     * Update the specified fund.
     */
    public function update(Request $request, $id)
    {
        $fund = Fund::findOrFail($id);
        
        $request->validate([
            'code' => 'required|string|max:250|unique:funds,code,' . $id,
            'name' => 'required|string|max:350',
            'description' => 'nullable|string'
        ]);
        
        DB::beginTransaction();
        
        try {
            $fund->code = $request->code;
            $fund->name = $request->name;
            $fund->description = $request->description;
            $fund->save();
            
            DB::commit();
            
            return redirect()->route('funds.index')
                ->with('success', 'Fund updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error updating fund: ' . $e->getMessage())
                        ->withInput();
        }
    }
    
    /**
     * Remove the specified fund.
     */
  
public function destroy($id)
{
    $fund = Fund::findOrFail($id);

    // Check: Default fund
    if ($fund->id == 1) {
        return redirect()->back()->with('error', 'Default fund cannot be deleted!');
    }

    // Check: Fund has entries
    if ($fund->entries()->exists()) {
        return redirect()->back()->with('error', 'Cannot delete fund with existing entries!');
    }

    // Check: Last fund
    if (Fund::count() <= 1) {
        return redirect()->back()->with('error', 'Cannot delete the last fund in the system!');
    }

    DB::beginTransaction();

    try {
        $fund->delete();

        DB::commit();
        return redirect()->route('funds.index')->with('success', 'Fund deleted successfully!');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Error deleting fund: ' . $e->getMessage());
    }
}
}