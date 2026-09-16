@extends('layouts.app')

@section('title', 'Edit Product - ' . $product->name)

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('products.index') }}" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.35rem;">
        &larr; Back to Product Catalog
    </a>
</div>

<div class="card" style="max-width: 720px; margin: 0 auto; padding: 2.25rem;">
    <div style="margin-bottom: 1.75rem;">
        <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.35rem;">
            Edit Product #{{ $product->id }}
        </h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            Demonstrates <code>@method('PUT')</code> method spoofing, <code>@csrf</code> token verification, and Route Model Binding.
        </p>
    </div>

    {{-- Validation Error Summary Alert --}}
    @if ($errors->any())
        <div class="alert alert-error" role="alert" id="validation-errors-alert">
            <strong>Please correct the following errors before saving:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('products.update', $product) }}" method="POST" id="edit-product-form" novalidate>
        {{-- CSRF Protection Directive --}}
        @csrf

        {{-- HTTP Method Spoofing for RESTful PUT Request --}}
        @method('PUT')

        {{-- Product Name Field --}}
        <div class="form-group">
            <label for="name" class="form-label">
                Product Name <span style="color: #ef4444;">*</span>
            </label>
            <input
                type="text"
                id="name"
                name="name"
                class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $product->name) }}"
                required
            >
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Category Selection Field (Relationship) --}}
        <div class="form-group">
            <label for="category_id" class="form-label">
                Category <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 400;">(Eloquent Relationship)</span>
            </label>
            <select
                id="category_id"
                name="category_id"
                class="form-control @error('category_id') is-invalid @enderror"
            >
                <option value="">-- Select a Category (Optional) --</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Price Field --}}
        <div class="form-group">
            <label for="price" class="form-label">
                Price (USD) <span style="color: #ef4444;">*</span>
            </label>
            <input
                type="number"
                id="price"
                name="price"
                step="0.01"
                min="0"
                max="999999.99"
                class="form-control @error('price') is-invalid @enderror"
                value="{{ old('price', $product->price) }}"
                required
            >
            @error('price')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Description Field --}}
        <div class="form-group">
            <label for="description" class="form-label">
                Product Description
            </label>
            <textarea
                id="description"
                name="description"
                rows="4"
                class="form-control @error('description') is-invalid @enderror"
            >{{ old('description', $product->description) }}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Action Buttons --}}
        <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 2rem; padding-top: 1.25rem; border-top: 1px solid var(--border);">
            <a href="{{ route('products.show', $product) }}" class="btn btn-outline" id="btn-cancel-edit">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary" id="btn-submit-edit">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
