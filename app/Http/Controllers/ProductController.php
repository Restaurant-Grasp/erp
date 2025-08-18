<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categories;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Uom;
use App\Models\Models;
use App\Models\Warehouse;
use App\Models\InventoryTransaction;
use App\Models\Ledger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class ProductController extends Controller
{
    public function categoriesIndex()
    {
        $categoriesList = Categories::with('childrenCategories')->orderBy('id', 'desc')->paginate(10);
        return view('categories.index', compact('categoriesList'));
    }

    public function categoriesCreate()
    {
        $categories = Categories::whereNull('parent_id')->with('childrenCategories')->get();
        return view('categories.create', compact('categories'));
    }

    public function categoriesStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:categories,code',
            'parent_id' => 'nullable|exists:categories,id',
        ]);
        DB::beginTransaction();
        try {
            Categories::create([
                'parent_id' => $request->parent_id,
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'status' => $request->has('is_active') ? 1 : 0,
                'created_by' => auth()->id(),
            ]);

            DB::commit();
            return redirect()->route('categories.index')->with('success', 'Category created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error creating category: ' . $e->getMessage());
        }
    }

    public function categoriesEdit(Categories $categories)
    {
        $allCategories = Categories::where('id', '!=', $categories->id)
            ->with('childrenCategories')
            ->get();

        return view('categories.edit', [
            'category' => $categories,
            'allCategories' => $allCategories,
        ]);
    }
    public function categoriesUpdate(Request $request, Categories $categories)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:categories,code,' . $categories->id,
            'parent_id' => 'nullable|exists:categories,id|not_in:' . $categories->id,
        ]);
        DB::beginTransaction();
        try {
            $categories->update([
                'parent_id' => $request->parent_id,
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'status' => $request->has('is_active') ? 1 : 0,
                'created_by' => auth()->id(),
            ]);
            DB::commit();
            return redirect()->route('categories.index')->with('success', 'Category updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error updating category: ' . $e->getMessage());
        }
    }

    public function categoriesDestroy(Categories $categories)
    {

        $productCount = Product::where('category_id', $categories->id)->count();

        if ($productCount > 0) {
            return redirect()->back()->with('error', 'Cannot delete category. It is assigned to one or more products.');
        } else {
            $categories->delete();
            return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
        }
    }
    //Product
    public function productIndex()
    {
        $productList = Product::orderBy('id', 'desc')->paginate(10);
        $warehouses = Warehouse::where('status',1)->get();
        return view('product.index', compact('productList', 'warehouses'));
    }

    public function productCreate()
    {
        $brands = Brand::all();
        $models = Models::all();
        $uoms = Uom::all();
        $ledgers = Ledger::whereHas('group', function($query) {
			$query->where('pd', 1);
		})->get();
        $categories = Categories::whereNull('parent_id')->with('childrenCategories')->get();
        return view('product.create', compact('brands', 'categories', 'models', 'uoms', 'ledgers'));
    }

    public function productStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required',
            'brand_id' => 'required',
            'model_id' => 'required',
            'uom_id' => 'required',
        ]);
        DB::beginTransaction();

        try {
            $product = Product::create([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'ledger_id' => $request->ledger_id,
                'brand_id' => $request->brand_id,
                'model_id' => $request->model_id,
                'uom_id' => $request->uom_id,
                'cost_price' => $request->cost_price,
                'is_active' => $request->has('is_active') ? 1 : 0,
                'min_stock_level' => $request->min_stock_level ?? 0,
                'reorder_level' => $request->reorder_level ?? 0,
                'description' => $request->description,
                'created_by' => auth()->id(),
            ]);
            $product->update([
                'product_code' => 'PRD' . str_pad($product->id, 6, '0', STR_PAD_LEFT)
            ]);
            DB::commit();
            return redirect()->route('product.index')->with('success', 'Product created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error creating product: ' . $e->getMessage());
        }
    }
    public function productEdit(Product $product)
    {
        $brands = Brand::all();
        $models = Models::all();
        $uoms = Uom::all();
        $ledgers = Ledger::whereHas('group', function($query) {
			$query->where('pd', 1);
		})->get();
        $categories = Categories::whereNull('parent_id')->with('childrenCategories')->get();

        return view('product.edit', compact('product', 'brands', 'categories', 'models', 'uoms', 'ledgers'));
    }
    public function productUpdate(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'product_code' => 'required|string|max:50|unique:products,product_code,' . $product->id,
            'category_id' => 'required',
            'brand_id' => 'required',
            'model_id' => 'required',
            'uom_id' => 'required',
        ]);
        DB::beginTransaction();

        try {
            $product->update([
                'name' => $request->name,
                'product_code' => $request->product_code,
                'ledger_id' => $request->ledger_id,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'model_id' => $request->model_id,
                'uom_id' => $request->uom_id,
                'cost_price' => $request->cost_price,
                'is_active' => $request->has('is_active') ? 1 : 0,
                'min_stock_level' => $request->min_stock_level ?? 0,
                'reorder_level' => $request->reorder_level ?? 0,
                'description' => $request->description,
                'created_by' => auth()->id(),
            ]);

            DB::commit();
            return redirect()->route('product.index')->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error updating product: ' . $e->getMessage());
        }
    }
    public function productDestroy(Product $product)
    {
        $product->delete();
        return redirect()->route('product.index')->with('success', 'Product deleted successfully.');
    }
    public function addQuantity(Request $request)
    {
              dd( $request->all());
        $request->validate([
            'quantity' => 'required',
            'warehouse_id' => 'required'
        ]);
        DB::beginTransaction();

        try {
            $product = Product::findOrFail($request->product_id);
      
            $unitCost = $request->price;
            $totalCost = $unitCost * $request->quantity;

            $existingTransaction = InventoryTransaction::where('product_id', $request->product_id)
                ->where('warehouse_id', $request->warehouse_id)
                ->first();
            if ($existingTransaction) {
                InventoryTransaction::where('id', $existingTransaction->id)
                    ->update([
                        'transaction_date' => Carbon::now(),
                        'quantity' =>  $request->quantity,
                        'transaction_type' => 'opening',
                        'reference_type' => 'opening',
                        'total_cost' => $totalCost,
                        'warehouse_id' => $request->warehouse_id,
                        'created_by' => auth()->id(),
                    ]);
            } else {
                InventoryTransaction::create([
                    'transaction_date' => Carbon::now(),
                    'transaction_type' => 'opening',
                    'reference_type' => 'opening',
                    'product_id' => $request->product_id,
                    'quantity' => $request->quantity,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                    'warehouse_id' => $request->warehouse_id,
                    'created_by' => auth()->id(),
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Opening quantity saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
}
