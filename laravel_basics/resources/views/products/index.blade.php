@extends('layouts.app')

@section('title', 'Product Catalog - MVC Architecture')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--text-main);">Product Catalog</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">
            Demonstrating Laravel MVC Architecture, Eloquent Relationships, and Resource Routing.
        </p>
    </div>
    <a href="{{ route('products.create') }}" class="btn btn-primary" id="btn-create-product">
        + Add New Product
    </a>
</div>

{{-- Session Flash Messages --}}
@if (session('success'))
    <div class="alert alert-success" role="alert" id="alert-success">
        <strong>Success:</strong> {{ session('success') }}
    </div>
@endif

{{-- Products Data Table --}}
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="data-table" id="products-table">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Created</th>
                    <th style="text-align: right; min-width: 180px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr id="product-row-{{ $product->id }}">
                        <td><strong>#{{ $product->id }}</strong></td>
                        <td>
                            <a href="{{ route('products.show', $product) }}" style="text-decoration: none; color: var(--primary); font-weight: 600;">
                                {{ $product->name }}
                            </a>
                            @if ($product->description)
                                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem; max-width: 360px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $product->description }}
                                </p>
                            @endif
                        </td>
                        <td>
                            @if ($product->category)
                                <span class="badge-category">{{ $product->category->name }}</span>
                            @else
                                <span style="color: var(--text-muted); font-size: 0.8rem;">Uncategorized</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge-price">${{ number_format((float)$product->price, 2) }}</span>
                        </td>
                        <td style="font-size: 0.825rem; color: var(--text-muted);">
                            {{ $product->created_at ? $product->created_at->format('M d, Y') : 'N/A' }}
                        </td>
                        <td style="text-align: right;">
                            <div class="action-group" style="justify-content: flex-end;">
                                <a href="{{ route('products.show', $product) }}" class="btn btn-outline btn-sm" id="btn-view-{{ $product->id }}">
                                    View
                                </a>
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-secondary btn-sm" id="btn-edit-{{ $product->id }}">
                                    Edit
                                </a>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" id="btn-delete-{{ $product->id }}">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                            No products found in the catalog.
                            <br>
                            <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm" style="margin-top: 0.75rem;">
                                Create First Product
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination links --}}
    @if ($products->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border); background: #f8fafc;">
            {{ $products->links() }}
        </div>
    @endif
</div>

{{-- MVC Architecture Educational Card --}}
<div class="card" style="margin-top: 2rem; background: #faf5ff; border-color: #e9d5ff;">
    <h3 style="font-size: 1.1rem; color: #6b21a8; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        MVC Request Lifecycle for this Page
    </h3>
    <ol style="margin-left: 1.25rem; font-size: 0.875rem; color: #581c87; line-height: 1.6;">
        <li><strong>Browser</strong> sends an HTTP GET request to <code>/products</code>.</li>
        <li><strong>Router</strong> maps <code>/products</code> to <code>ProductController@index</code> via <code>Route::resource()</code>.</li>
        <li><strong>Controller</strong> calls <code>Product::with('category')->latest()->paginate(8)</code> to query SQLite via Eloquent Model.</li>
        <li><strong>Model</strong> resolves the data and loads the <code>belongsTo(Category::class)</code> relationship.</li>
        <li><strong>Controller</strong> passes the paginated product collection to <code>resources/views/products/index.blade.php</code>.</li>
        <li><strong>Blade Engine</strong> compiles the template, escapes all dynamic variables, and sends the final HTML HTTP response back to the browser.</li>
    </ol>
</div>
@endsection
