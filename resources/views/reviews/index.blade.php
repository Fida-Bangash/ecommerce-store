@extends('layouts.admin')

@section('title', 'Reviews & Ratings')
@section('meta_description', 'Manage customer product reviews and ratings')

@section('page_header')
@endsection
@section('page_title', 'Reviews & Ratings')
@section('page_subtitle', 'Approve or remove customer reviews submitted on product pages.')

@section('content')

  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h4 class="mb-1">Reviews & Ratings</h4>
      <p class="text-muted-green mb-0">Approve or remove customer reviews submitted on product pages.</p>
    </div>
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
      <form method="GET" action="{{ route('reviews.index') }}" class="table-search-box">
        <i class="bi bi-search table-search-icon"></i>
        <input
          type="text"
          name="search"
          value="{{ request('search') }}"
          class="table-search-input"
          placeholder="Search by product or reviewer...">
        <input type="hidden" name="status" value="{{ $statusFilter }}">
      </form>

      <div class="table-filter-group">
        <form method="GET" action="{{ route('reviews.index') }}" class="d-flex align-items-center gap-2">
          @if (request('search'))
            <input type="hidden" name="search" value="{{ request('search') }}">
          @endif

          <select name="status" class="form-select-custom" onchange="this.form.submit()">
            <option value="" @selected($statusFilter === '')>All Reviews</option>
            <option value="pending" @selected($statusFilter === 'pending')>Pending</option>
            <option value="approved" @selected($statusFilter === 'approved')>Approved</option>
          </select>
        </form>
      </div>
    </div>

    {{-- Reviews Table --}}
    <div class="table-responsive">
      <table class="table-custom">
        <thead>
          <tr>
            <th>Product</th>
            <th>Reviewer</th>
            <th>Rating</th>
            <th>Review</th>
            <th>Status</th>
            <th>Date</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($reviews as $review)
            <tr>
              <td class="table-user-name">{{ $review->product->name }}</td>
              <td class="text-muted-green">{{ $review->reviewer_name }}</td>
              <td>
                <div class="d-flex gap-1 text-warning">
                  @for ($i = 1; $i <= 5; $i++)
                    <i class="bi {{ $i <= $review->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                  @endfor
                </div>
              </td>
              <td class="text-muted-green">{{ \Illuminate\Support\Str::limit($review->comment, 40) }}</td>
              <td>
                @if ($review->isApproved())
                  <span class="badge-table success">Approved</span>
                @else
                  <span class="badge-table pending">Pending</span>
                @endif
              </td>
              <td class="text-muted-green">{{ $review->created_at->format('d M Y, h:i A') }}</td>
              <td class="text-end">
                <button
                  type="button"
                  class="table-btn-action js-view-review-btn"
                  title="Review Details"
                  data-bs-toggle="modal"
                  data-bs-target="#reviewDetailsModal"
                  data-details-url="{{ route('reviews.show', $review) }}">
                  <i class="bi bi-eye"></i>
                </button>

                @unless ($review->isApproved())
                  <form action="{{ route('reviews.approve', $review) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="table-btn-action" title="Approve Review">
                      <i class="bi bi-check-lg"></i>
                    </button>
                  </form>
                @endunless

                <form action="{{ route('reviews.destroy', $review) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Are you sure you want to delete this review?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="table-btn-action delete" title="Delete Review">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted-green py-4">No reviews found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Table Footer: Pagination --}}
    @if ($reviews->hasPages())
      <div class="table-footer-control">
        <span class="table-pagination-info">
          Showing {{ $reviews->firstItem() }} to {{ $reviews->lastItem() }} of {{ $reviews->total() }} reviews
        </span>
        {{ $reviews->links() }}
      </div>
    @endif

  </div>

  {{-- Review Details Modal (shared across all review rows) --}}
  <div class="modal fade" id="reviewDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4">
        <div class="modal-header">
          <h5 class="modal-title">Review for <span id="reviewDetailsProduct">—</span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="reviewDetailsBody" class="text-center text-muted-green py-4">Loading...</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const modalEl = document.getElementById('reviewDetailsModal');
        if (!modalEl) {
          return;
        }

        const productEl = document.getElementById('reviewDetailsProduct');
        const bodyEl = document.getElementById('reviewDetailsBody');

        modalEl.addEventListener('show.bs.modal', function (event) {
          const btn = event.relatedTarget;
          if (!btn) {
            return;
          }

          productEl.textContent = '—';
          bodyEl.innerHTML = '<div class="text-center text-muted-green py-4">Loading...</div>';

          fetch(btn.dataset.detailsUrl, {
            headers: { 'Accept': 'application/json' },
          })
            .then(function (response) { return response.json(); })
            .then(function (data) {
              productEl.textContent = data.product_name;

              const badgeClass = data.status === 'approved' ? 'success' : 'pending';

              let starsHtml = '';
              for (let i = 1; i <= 5; i++) {
                starsHtml += '<i class="bi ' + (i <= data.rating ? 'bi-star-fill' : 'bi-star') + ' text-warning"></i>';
              }

              bodyEl.innerHTML = ''
                + '<div class="d-flex justify-content-between align-items-start mb-3">'
                +   '<div>'
                +     '<div class="fw-bold">' + data.reviewer_name + '</div>'
                +     '<div class="text-muted-green" style="font-size:0.8rem;">' + data.date + '</div>'
                +   '</div>'
                +   '<span class="badge-table ' + badgeClass + '">' + data.status_label + '</span>'
                + '</div>'
                + '<div class="mb-3">' + starsHtml + '</div>'
                + '<p class="mb-0">' + data.comment + '</p>';
            })
            .catch(function () {
              bodyEl.innerHTML = '<div class="text-center text-danger py-4">Could not load review details.</div>';
            });
        });
      });
    </script>
  @endpush

@endsection
