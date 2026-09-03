@extends('layouts.admin')

@section('title', 'Category Management')
@section('meta_description', 'Manage product categories')

@section('page_header')
@endsection
@section('page_title', 'Category Management')
@section('page_subtitle', 'Organize and manage your product categories.')

@section('content')

  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h4 class="mb-1">Category Management</h4>
      <p class="text-muted-green mb-0">Organize and manage your product categories.</p>
    </div>
    <a href="{{ route('categories.create') }}" class="btn-quick-action" id="btn-add-category">
      <i class="bi bi-plus-lg"></i>
      <span>Add Category</span>
    </a>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="table-card-custom">

    {{-- Table Header: Search & Filter Controls --}}
    <div class="table-header-control">
      <form method="GET" action="{{ route('categories.index') }}" class="table-search-box">
        <i class="bi bi-search table-search-icon"></i>
        <input
          type="text"
          name="search"
          value="{{ request('search') }}"
          class="table-search-input"
          placeholder="Search categories...">
      </form>

      <div class="table-filter-group">
        <form method="GET" action="{{ route('categories.index') }}" class="d-flex align-items-center gap-2">
          @if (request('search'))
            <input type="hidden" name="search" value="{{ request('search') }}">
          @endif
          <select name="status" class="form-select-custom" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
          </select>
        </form>
      </div>
    </div>

    {{-- Categories Table --}}
    <div class="table-responsive">
      <table class="table-custom">
        <thead>
          <tr>
            <th>Category</th>
            <th>Slug</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($categories as $category)
            <tr>
              <td>
                <div class="table-user-cell">
                  @if ($category->image_url)
                    <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="table-user-avatar">
                  @else
                    <span class="table-user-avatar d-inline-flex align-items-center justify-content-center text-white">
                      <i class="bi bi-image"></i>
                    </span>
                  @endif
                  <span class="table-user-name">{{ $category->name }}</span>
                </div>
              </td>
              <td class="text-muted-green">{{ $category->slug }}</td>
              <td>
                @if ($category->isActive())
                  <span class="badge-table success">Active</span>
                @else
                  <span class="badge-table failed">Inactive</span>
                @endif
              </td>
              <td class="text-end">
                <a href="{{ route('categories.edit', $category) }}" class="table-btn-action" title="Edit Category">
                  <i class="bi bi-pencil"></i>
                </a>
                <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Are you sure you want to delete this category?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="table-btn-action delete" title="Delete Category">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="text-center text-muted-green py-4">No categories found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Table Footer: Pagination --}}
    @if ($categories->hasPages())
      <div class="table-footer-control">
        <span class="table-pagination-info">
          Showing {{ $categories->firstItem() }} to {{ $categories->lastItem() }} of {{ $categories->total() }} categories
        </span>
        {{ $categories->links() }}
      </div>
    @endif

  </div>

@endsection
