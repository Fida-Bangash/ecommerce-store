@extends('layouts.admin')

@section('title', 'Add Category')
@section('meta_description', 'Create a new product category')

@section('page_header')
@endsection
@section('page_title', 'Add Category')
@section('page_subtitle', 'Create a new category to organize your products.')
@section('page_header_actions')
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="{{ route('categories.index') }}" class="text-decoration-none text-muted-green">Categories</a></li>
      <li class="breadcrumb-item active text-main" aria-current="page">Add Category</li>
    </ol>
  </nav>
@endsection

@section('content')

    <form method="POST" action="{{ route('categories.store') }}" enctype="multipart/form-data">
        @csrf
        @include('categories._form')
    </form>

@endsection
