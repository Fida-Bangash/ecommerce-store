@extends('layouts.admin')

@section('title', 'Edit Category')
@section('meta_description', 'Update category information')

@section('page_header')
@endsection
@section('page_title', 'Edit Category')
@section('page_subtitle', 'Update the details of this category.')
@section('page_header_actions')
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="{{ route('categories.index') }}" class="text-decoration-none text-muted-green">Categories</a></li>
      <li class="breadcrumb-item active text-main" aria-current="page">Edit Category</li>
    </ol>
  </nav>
@endsection

@section('content')

    <form method="POST" action="{{ route('categories.update', $category) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('categories._form')
    </form>

@endsection
