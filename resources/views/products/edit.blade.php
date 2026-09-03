@extends('layouts.admin')

@section('title', 'Edit Product')
@section('meta_description', 'Update product information')

@section('page_header')
@endsection
@section('page_title', 'Edit Product')
@section('page_subtitle', 'Update the details of this product.')
@section('page_header_actions')
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none text-muted-green">Products</a></li>
      <li class="breadcrumb-item active text-main" aria-current="page">Edit Product</li>
    </ol>
  </nav>
@endsection

@section('content')

    <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('products._form')
    </form>

@endsection
