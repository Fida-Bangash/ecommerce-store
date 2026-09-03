@extends('layouts.admin')

@section('title', 'Add Product')
@section('meta_description', 'Create a new product')

@section('page_header')
@endsection
@section('page_title', 'Add Product')
@section('page_subtitle', 'Create a new product for your store.')
@section('page_header_actions')
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none text-muted-green">Products</a></li>
      <li class="breadcrumb-item active text-main" aria-current="page">Add Product</li>
    </ol>
  </nav>
@endsection

@section('content')

    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
        @csrf
        @include('products._form')
    </form>

@endsection
