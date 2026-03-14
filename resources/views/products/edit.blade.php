@extends('layouts.app')

@section('page_title', 'Edit Product')
@section('page_subtitle', 'Update product information and reorder rules')

@section('content')
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card-soft form-card">
            <div class="section-title">Update Product</div>
            <form method="POST" action="{{ route('products.update', $product) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Product Name</label>
                    <input class="form-control" name="name" value="{{ $product->name }}" required />
                </div>
                <div class="mb-3">
                    <label class="form-label">SKU / Code</label>
                    <input class="form-control" name="sku" value="{{ $product->sku }}" required />
                </div>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <input class="form-control" name="category" value="{{ $product->category }}" />
                </div>
                <div class="mb-3">
                    <label class="form-label">Unit of Measure</label>
                    <input class="form-control" name="unit_of_measure" value="{{ $product->unit_of_measure }}" />
                </div>
                <div class="mb-3">
                    <label class="form-label">Reorder Point</label>
                    <input class="form-control" name="reorder_point" value="{{ $product->reorder_point }}" type="number" min="0" />
                </div>
                <button class="btn btn-primary-custom w-100">Update Product</button>
            </form>
            <a href="{{ route('products.index') }}" class="btn btn-outline-custom w-100 mt-3">Back to Products</a>
        </div>
    </div>
</div>
@endsection
