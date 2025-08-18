<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Models;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Uom;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class MasterController extends Controller
{
    //Brand-index
    public function brandIndex()
    {
        $brandList = Brand::orderBy('id', 'desc')->paginate(10);
        return view('brand.index', compact('brandList'));
    }

    //Brand-create
    public function brandCreate()
    {
        return view('brand.create');
    }

    //Brand-store
    public function brandStore(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:100|unique:brands,name',
            'code' => 'nullable|string|max:50|unique:brands,code',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,svg,gif|max:2048',
        ]);
        DB::beginTransaction();
        try {
            $filename = null;

            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $originalName = $file->getClientOriginalName();
                $folderPath = public_path('assets/brands');
                if (!file_exists($folderPath)) {
                    mkdir($folderPath, 0755, true);
                }
                $file->move($folderPath, $originalName);
                $filename = 'brands/' . $originalName;
            }
            Brand::create([
                'name' => $request->name,
                'code' => $request->code,
                'logo' => $filename,
                'created_by' => auth()->id(),
            ]);

            DB::commit();
            return redirect()->route('brand.index')->with('success', 'Brand created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error creating brand: ' . $e->getMessage());
        }
    }
    //Brand-edit
    public function brandEdit(Brand $brand)
    {
        $brands = Brand::all();
        return view('brand.edit', compact('brand'));
    }

    //Brand-update
    public function brandUpdate(Request $request, Brand $brand)
    {
        $request->validate([
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,svg,gif|max:2048',
        ]);
        DB::beginTransaction();

        try {
            $filename = $brand->logo;


            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $originalName = $file->getClientOriginalName();

                $folderPath = public_path('assets/brands');
                if (!file_exists($folderPath)) {
                    mkdir($folderPath, 0755, true);
                }
                if ($brand->logo && file_exists(public_path('assets/' . $brand->logo))) {
                    unlink(public_path('assets/' . $brand->logo));
                }
                $file->move($folderPath, $originalName);

                $filename = 'brands/' . $originalName;
            }

            $brand->update([
                'name' => $request->name,
                'code' => $request->code,
                'logo' => $filename,
                'created_by' => auth()->id(),
            ]);
            DB::commit();
            return redirect()->route('brand.index')->with('success', 'Brand updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error updating brand: ' . $e->getMessage());
        }
    }

    //Brand-destroy
    public function brandDestroy(Brand $brand)
    {
        $brand->models()->delete();
        $brand->delete();
        return redirect()->route('brand.index')->with('success', 'Brand deleted successfully.');
    }


    //Model-index
    public function modelIndex()
    {
        $modelList = Models::orderBy('id', 'desc')->paginate(10);
        return view('model.index', compact('modelList'));
    }

    //Model-create
    public function modelCreate()
    {
        $brands = Brand::all();
        return view('model.create', compact('brands'));
    }
    //Model-store
    public function modelStore(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:100|unique:models,name',
            'code' => 'string|max:50|unique:models,code',
            'brand_id' => 'required|exists:brands,id',
        ]);
        DB::beginTransaction();

        try {
            Models::create([
                'name' => $request->name,
                'brand_id' => $request->brand_id,
                'code' => $request->code,
                'specifications' => $request->specifications,
                'created_by' => auth()->id(),
            ]);


            DB::commit();
            return redirect()->route('model.index')->with('success', 'Model created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error creating model: ' . $e->getMessage());
        }
    }
    //Model-edit
    public function modelEdit(Models $model)
    {
        $brands = Brand::all();
        return view('model.edit', compact('brands', 'model'));
    }
    //Model-update
    public function modelUpdate(Request $request, Models $model)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:models,name,' . $model->id,
            'code' => 'nullable|string|max:50|unique:models,code,' . $model->id,
            'brand_id' => 'required|exists:brands,id',
        ]);
        DB::beginTransaction();

        try {
            $model->update([
                'name' => $request->name,
                'brand_id' => $request->brand_id,
                'code' => $request->code,
                'specifications' => $request->specifications,
            ]);

            DB::commit();
            return redirect()->route('model.index')->with('success', 'Model updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error updating model: ' . $e->getMessage());
        }
    }
    //Model-destroy
    public function modelDestroy(models $model)
    {
        $model->delete();
        return redirect()->route('model.index')->with('success', 'Model deleted successfully.');
    }

    //UOM-index
    public function uomIndex()
    {
        $uomList = Uom::orderBy('id', 'desc')->paginate(10);
        return view('uom.index', compact('uomList'));
    }


    //UOM-create
    public function uomCreate()
    {
        return view('uom.create');
    }

    //UOM-store
    public function uomStore(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            Uom::create([
                'name' => $request->name,
                'status' => $request->has('is_active') ? 1 : 0,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            DB::commit();
            return redirect()->route('uom.index')->with('success', 'UOM created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error creating UOM: ' . $e->getMessage());
        }
    }
    //UOM-edit
    public function uomEdit(Uom $uom)
    {
        $uoms = Uom::all();
        return view('uom.edit', compact('uoms', 'uom'));
    }
    public function uomUpdate(Request $request, Uom $uom)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        DB::beginTransaction();

        try {
            $uom->update([
                'name' => $request->name,
                'status' => $request->has('is_active') ? 1 : 0,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
            DB::commit();
            return redirect()->route('uom.index')->with('success', 'UOM updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error updating UOM: ' . $e->getMessage());
        }
    }
    //UOM-destroy
    public function uomDestroy(Uom $uom)
    {
        $productCount = Product::where('uom_id', $uom->id)->count();

        if ($productCount > 0) {
            return redirect()->back()->with('error', 'Cannot delete UOM. It is assigned to one or more products.');
        } else {
            $uom->delete();
            return redirect()->route('uom.index')->with('success', 'UOM deleted successfully.');
        }
    }

    //warehouse-index
    public function warehouseIndex()
    {
        $warehouseList = Warehouse::orderBy('id', 'desc')->paginate(10);
        return view('warehouse.index', compact('warehouseList'));
    }
    //warehouse-create
    public function warehouseCreate()
    {
        return view('warehouse.create');
    }
    public function warehouseStore(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        DB::beginTransaction();
        try {
            Warehouse::create([
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->has('is_active') ? 1 : 0,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            DB::commit();
            return redirect()->route('warehouse.index')->with('success', 'Warehouse created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error creating warehouse: ' . $e->getMessage());
        }
    }
    public function warehouseEdit(Warehouse $warehouse)
    {
        $warehouses = Warehouse::all();
        return view('warehouse.edit', compact('warehouses', 'warehouse'));
    }
    public function warehouseUpdate(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        DB::beginTransaction();
        try {
            $warehouse->update([
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->has('is_active') ? 1 : 0,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            DB::commit();
            return redirect()->route('warehouse.index')->with('success', 'Warehouse updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error updating warehouse: ' . $e->getMessage());
        }
    }
    public function warehouseDestroy(Warehouse $warehouse)
    {
        $warehouse->delete();

        return redirect()->route('warehouse.index')->with('success', 'Warehouse deleted successfully.');
    }
}
