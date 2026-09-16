@extends('layouts.app')

@section('title', $product->name . ' - Product Details')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('products.index') }}" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.35rem;">
        &larr; Back to Product Catalog
    </a>
</div>

<div class="card" style="padding: 2.25rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <h1 style="font-size: 2rem; font-weight: 800; color: var(--text-main);">{{ $product->name }}</h1>
                @if ($product->category)
                    <span class="badge-category" style="font-size: 0.85rem; padding: 0.25rem 0.75rem;">
                        {{ $product->category->name }}
                    </span>
                @endif
            </div>
            <p style="color: var(--text-muted); font-size: 0.9rem;">
                Product ID: <strong>#{{ $product->id }}</strong> &bull;
                Slug: <code>{{ \Illuminate\Support\Str::slug($product->name) }}</code>
            </p>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Price</div>
            <div style="font-size: 2.25rem; font-weight: 800; color: #059669;">
                ${{ number_format((float)$product->price, 2) }}
            </div>
        </div>
    </div>

    <hr style="border: 0; border-top: 1px solid var(--border); margin: 1.25rem 0 1.75rem;">

    <div style="margin-bottom: 2rem;">
        <h3 style="font-size: 1rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 0.5rem;">
            Description
        </h3>
        <div style="font-size: 1.05rem; line-height: 1.7; color: var(--text-main); background: #f8fafc; padding: 1.25rem; border-radius: 8px; border: 1px solid var(--border);">
            {{ $product->description ?: 'No detailed description provided for this product.' }}
        </div>
    </div>

    {{-- Category Relationship Information --}}
    @if ($product->category)
        <div style="margin-bottom: 2rem; background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 8px; padding: 1.25rem;">
            <h4 style="font-size: 0.95rem; color: #3730a3; margin-bottom: 0.35rem; font-weight: 700;">
                Category Details (Eloquent BelongsTo Relationship)
            </h4>
            <p style="font-size: 0.875rem; color: #4338ca;">
                <strong>Category:</strong> {{ $product->category->name }} &bull;
                <strong>Slug:</strong> <code>{{ $product->category->slug }}</code>
            </p>
            @if ($product->category->description)
                <p style="font-size: 0.825rem; color: #4338ca; margin-top: 0.25rem;">
                    {{ $product->category->description }}
                </p>
            @endif
        </div>
    @endif

    {{-- Metadata Timestamps --}}
    <div style="display: flex; gap: 2rem; flex-wrap: wrap; margin-bottom: 2rem; font-size: 0.85rem; color: var(--text-muted);">
        <div>
            <strong>Created:</strong> {{ $product->created_at ? $product->created_at->format('F d, Y h:i A') : 'N/A' }}
        </div>
        <div>
            <strong>Last Updated:</strong> {{ $product->updated_at ? $product->updated_at->format('F d, Y h:i A') : 'N/A' }}
        </div>
    </div>

    {{-- Action Buttons --}}
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; padding-top: 1.25rem; border-top: 1px solid var(--border);">
        <a href="{{ route('products.index') }}" class="btn btn-outline" id="btn-back-to-products">
            &larr; Back to Catalog
        </a>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('products.edit', $product) }}" class="btn btn-primary" id="btn-edit-product">
                Edit Product
            </a>
            <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this product?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" id="btn-delete-product">
                    Delete Product
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Route Model Binding Educational Card --}}
<div class="card" style="background: #f0fdf4; border-color: #bbf7d0;">
    <h3 style="font-size: 1.05rem; color: #166534; margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.4rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        Route Model Binding in Action
    </h3>
    <p style="font-size: 0.875rem; color: #15803d; line-height: 1.6;">
        The route signature is <code>/products/{product}</code>, and the controller method declares <code>show(Product $product)</code>.
        Laravel's router matched the URI parameter with the type-hinted Eloquent model, queried the database for <code>Product::where('id', $id)->firstOrFail()</code> under the hood, and automatically injected the <strong>{{ $product->name }}</strong> instance into the controller!
    </p>
</div>
@endsection
