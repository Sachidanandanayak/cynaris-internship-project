<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * MVC Flow:
     * 1. Controller calls Model (Product::with('category')->latest()->paginate(8))
     * 2. Passes collection to Blade View (products.index)
     */
    public function index(): View
    {
        $products = Product::with('category')
            ->latest()
            ->paginate(8);

        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * MVC Flow:
     * 1. Controller fetches available categories from Model (Category::all())
     * 2. Renders create form Blade View (products.create)
     */
    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * MVC Flow:
     * 1. Controller validates HTTP Request inputs
     * 2. Calls Model mass-assignment method (Product::create)
     * 3. Redirects browser back to product index with flash session message
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|numeric|min:0|max:999999.99',
            'description' => 'nullable|string|max:2000',
        ]);

        $product = Product::create($validated);

        return redirect()
            ->route('products.index')
            ->with('success', "Product \"{$product->name}\" created successfully!");
    }

    /**
     * Display the specified resource.
     *
     * DEMONSTRATING ROUTE MODEL BINDING:
     * By type-hinting `Product $product`, Laravel automatically queries
     * the database for the record matching the {product} route URI segment.
     * If no matching record is found, an HTTP 404 response is returned automatically.
     */
    public function show(Product $product): View
    {
        $product->load('category');

        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * DEMONSTRATING ROUTE MODEL BINDING:
     * Automatically injects the target Product model instance.
     */
    public function edit(Product $product): View
    {
        $categories = Category::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * DEMONSTRATING ROUTE MODEL BINDING:
     * Automatically binds the target Product instance, validates input, and updates.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|numeric|min:0|max:999999.99',
            'description' => 'nullable|string|max:2000',
        ]);

        $product->update($validated);

        return redirect()
            ->route('products.index')
            ->with('success', "Product \"{$product->name}\" updated successfully!");
    }

    /**
     * Remove the specified resource from storage.
     *
     * DEMONSTRATING ROUTE MODEL BINDING:
     * Injects the target Product instance and performs soft/hard deletion.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $productName = $product->name;
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', "Product \"{$productName}\" was successfully deleted!");
    }
}
