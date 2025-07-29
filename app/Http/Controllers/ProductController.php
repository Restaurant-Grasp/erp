<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categories;
use App\Models\Product;

class ProductController extends Controller
{
    public function categoriesIndex()
    {
        $categoriesList = Categories::with('childrenCategories')->paginate(10);
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

        Categories::create([
            'parent_id' => $request->parent_id,
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'status' => $request->has('is_active') ? 1 : 0,
            'created_by' => auth()->id() ?? null,
        ]);

        return redirect()->route('categories.index')->with('success', 'Category created successfully!');
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
        $categories->update([
            'parent_id' => $request->parent_id,
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'status' => $request->has('is_active') ? 1 : 0,
        ]);
        return redirect()->route('categories.index')->with('success', 'Category updated successfully!');
    }

    public function categoriesDestroy(Categories $categories)
    {
     
        $productCount = Product::where('category_id', $categories->id)->count();
    
        if ($productCount > 0) {
            return redirect()->back()->with('error', 'Cannot delete category. It is assigned to one or more products.');
        }else{
            $categories->delete();
            return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
        }
    
       
    }
    
    
}
