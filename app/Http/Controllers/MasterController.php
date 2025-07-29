<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Models;
use App\Models\Brand;


class MasterController extends Controller
{
    //Brand-index
    public function brandIndex()
    {
        $brandList = Brand::paginate(10);
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
            'code' => 'string|max:50|unique:brands,code',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,svg,gif|max:2048',
        ]);
    
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
        ]);
    
        return redirect()->route('brand.index')->with('success', 'Brand created successfully.');
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
        'name' => 'required|string|max:100|unique:brands,name',
        'code' => 'string|max:50|unique:brands,code',
        'logo' => 'nullable|image|mimes:jpg,jpeg,png,svg,gif|max:2048',
    ]);

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
    ]);

    return redirect()->route('brand.index')->with('success', 'Brand updated successfully.');
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
        $modelList = Models::paginate(10);
        return view('model.index', compact('modelList'));
    }

    //Model-create
    public function modelCreate()
    {
        $brands = Brand::all();
        return view('model.create',compact('brands'));
    }
    //Model-store
    public function modelStore(Request $request)
    {
        
        $request->validate([
            'name' => 'required|string|max:100|unique:models,name',
            'code' => 'string|max:50|unique:models,code',
            'brand_id' => 'required|exists:brands,id',
        ]);

        Models::create([
            'name' => $request->name,
            'brand_id' => $request->brand_id,
            'code' => $request->code,
            'specifications' => $request->specifications,
        ]);
    
        return redirect()->route('model.index')->with('success', 'Model created successfully.');
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

    $model->update([
        'name' => $request->name,
        'brand_id' => $request->brand_id,
        'code' => $request->code,
        'specifications' => $request->specifications,
    ]);

    return redirect()->route('model.index')->with('success', 'Model updated successfully.');
}
    //Model-destroy
    public function modelDestroy(models $model)
    {
        $model->delete();
        return redirect()->route('model.index')->with('success', 'Model deleted successfully.');
    }
}
