{{-- Usage: @include('partials.add-to-cart-button', ['product' => $product]) --}}
<form action="{{ route('cart.store') }}" method="POST" class="add-to-cart-form">
    @csrf
    <input type="hidden" name="product_id" value="{{ $product->id }}">
    <input type="hidden" name="quantity" value="1">

    <button
        type="submit"
        class="btn btn-dark w-100 rounded-pill d-inline-flex align-items-center justify-content-center gap-2"
        {{ $product->stock_quantity <= 0 ? 'disabled' : '' }}
    >
        <svg width="16" height="16"><use href="#cart"></use></svg>
        {{ $product->stock_quantity <= 0 ? 'Out of Stock' : 'Add to Cart' }}
    </button>
</form>
