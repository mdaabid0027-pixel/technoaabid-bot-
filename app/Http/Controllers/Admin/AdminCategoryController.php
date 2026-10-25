<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCategoryController extends Controller
{
    /**
     * Display a listing of categories and show creation form.
     */
    public function index()
    {
        $categories = Category::with(['products' => function($query) {
            $query->withCount(['keys as available_keys_count' => function($q) {
                $q->where('is_sold', false);
            }]);
        }])
        ->withCount('products')
        ->latest()
        ->get();

        $totalKeysInStock = \App\Models\Key::where('is_sold', false)->count();

        return view('admin.categories.index', compact('categories', 'totalKeysInStock'));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
        ]);

        Category::create([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
        ]);

        return back()->with('success', 'Category created successfully.');
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,' . $id],
        ]);

        $category->update([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
        ]);

        return back()->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return back()->with('success', 'Category deleted successfully.');
    }
}
