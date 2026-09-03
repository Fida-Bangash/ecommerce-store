@extends('layouts.front')

@section('title', 'Shop')
@section('meta_description', 'Browse all products.')

@section('content')
<div class="container py-5">

    <h1 class="mb-4">Shop</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('shop') }}" method="GET" class="row g-2 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" value="{{ request('search') }}"
                   class="form-control" placeholder="Search products...">
        </div>
        <div class="col-md-4">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category') == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-dark w-100">Filter</button>
        </div>
        <div class="col-md-2">
            <a href="{{ route('shop') }}" class="btn btn-outline-secondary w-100">Reset</a>
        </div>
    </form>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        @forelse ($products as $product)
            <div class="col">
                @include('partials.product-card', ['product' => $product])
            </div>
        @empty
            <p class="text-muted">No products found.</p>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>

</div>
@endsection
