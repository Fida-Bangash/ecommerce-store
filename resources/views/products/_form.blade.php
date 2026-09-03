@php
    $product = $product ?? null;
@endphp

<div class="row g-4">

    {{-- Basic Information --}}
    <div class="col-lg-8">
        <div class="card p-4 border-light shadow-sm mb-4">
            <h5 class="mb-3">Product Information</h5>

            <div class="mb-3">
                <label for="name" class="form-label-custom">Product Name</label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    class="form-control-custom @error('name') is-invalid-custom @enderror"
                    placeholder="e.g. Wireless Headphones"
                    value="{{ old('name', $product->name ?? '') }}"
                    required>
                @error('name')
                    <div class="form-feedback-custom text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="slug" class="form-label-custom">Slug</label>
                        <input
                            type="text"
                            name="slug"
                            id="slug"
                            class="form-control-custom @error('slug') is-invalid-custom @enderror"
                            placeholder="Leave empty to auto-generate"
                            value="{{ old('slug', $product->slug ?? '') }}">
                        @error('slug')
                            <div class="form-feedback-custom text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="category_id" class="form-label-custom">Category</label>
                        <select
                            name="category_id"
                            id="category_id"
                            class="form-select-custom @error('category_id') is-invalid-custom @enderror"
                            required>
                            <option value="">Select a category</option>
                            @foreach ($categories as $category)
                                <option
                                    value="{{ $category->id }}"
                                    @selected((int) old('category_id', $product->category_id ?? '') === $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="form-feedback-custom text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-0">
                <label for="description" class="form-label-custom">Description</label>
                <textarea
                    name="description"
                    id="description"
                    rows="5"
                    class="form-control-custom @error('description') is-invalid-custom @enderror"
                    placeholder="Write a short description of the product...">{{ old('description', $product->description ?? '') }}</textarea>
                @error('description')
                    <div class="form-feedback-custom text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Product Images --}}
        <div class="card p-4 border-light shadow-sm">
            <h5 class="mb-3">Product Images</h5>

            <div class="image-upload-box" id="image-upload-box">
                <input
                    type="file"
                    name="images[]"
                    id="images"
                    accept="image/png, image/jpeg, image/webp"
                    class="image-upload-input"
                    multiple
                    hidden>

                <label for="images" class="image-upload-placeholder" id="image-upload-placeholder">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <span class="image-upload-title">Click to upload or drag &amp; drop</span>
                    <span class="image-upload-hint">PNG, JPG or WEBP &middot; up to 2MB each &middot; multiple allowed</span>
                </label>
            </div>

            @error('images')
                <div class="form-feedback-custom text-danger mt-2">{{ $message }}</div>
            @enderror
            @error('images.*')
                <div class="form-feedback-custom text-danger mt-2">{{ $message }}</div>
            @enderror

            @if ($product?->images->isNotEmpty())
                <div class="mb-2 mt-4">
                    <label class="form-label-custom">Current Images</label>
                    <small class="text-muted-green d-block mb-2">Click the &times; on an image to remove it when you save.</small>
                </div>
                <div class="image-preview-grid" id="existing-image-grid">
                    @foreach ($product->images as $image)
                        <div class="image-preview-item" id="existing-image-{{ $image->id }}">
                            <img src="{{ $image->image_url }}" alt="Product image">
                            <button
                                type="button"
                                class="image-preview-remove"
                                title="Remove image"
                                data-existing-remove="{{ $image->id }}">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            <input type="checkbox" name="remove_images[]" value="{{ $image->id }}" hidden>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mb-0 mt-4" id="new-image-grid-wrapper" style="display:none;">
                <label class="form-label-custom">New Images</label>
                <div class="image-preview-grid" id="new-image-grid"></div>
            </div>
        </div>
    </div>

    {{-- Pricing, Stock & Status --}}
    <div class="col-lg-4">
        <div class="card p-4 border-light shadow-sm">
            <h5 class="mb-3">Pricing &amp; Stock</h5>

            <div class="mb-3">
                <label for="regular_price" class="form-label-custom">Regular Price</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="regular_price"
                    id="regular_price"
                    class="form-control-custom @error('regular_price') is-invalid-custom @enderror"
                    placeholder="0.00"
                    value="{{ old('regular_price', $product->regular_price ?? '') }}"
                    required>
                @error('regular_price')
                    <div class="form-feedback-custom text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="discount_price" class="form-label-custom">Discount Price</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="discount_price"
                    id="discount_price"
                    class="form-control-custom @error('discount_price') is-invalid-custom @enderror"
                    placeholder="Optional"
                    value="{{ old('discount_price', $product->discount_price ?? '') }}">
                @error('discount_price')
                    <div class="form-feedback-custom text-danger">{{ $message }}</div>
                @enderror
                <small class="text-muted-green">Must be lower than the regular price.</small>
            </div>

            <div class="mb-3">
                <label for="stock_quantity" class="form-label-custom">Stock Quantity</label>
                <input
                    type="number"
                    min="0"
                    name="stock_quantity"
                    id="stock_quantity"
                    class="form-control-custom @error('stock_quantity') is-invalid-custom @enderror"
                    placeholder="0"
                    value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}"
                    required>
                @error('stock_quantity')
                    <div class="form-feedback-custom text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-0">
                <label for="status" class="form-label-custom">Status</label>
                <select name="status" id="status" class="form-select-custom @error('status') is-invalid-custom @enderror">
                    <option value="active" @selected(old('status', $product->status ?? 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $product->status ?? 'active') === 'inactive')>Inactive</option>
                </select>
                @error('status')
                    <div class="form-feedback-custom text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    {{-- Product Variants (Size / Color) --}}
    <div class="col-12">
        <div class="card p-4 border-light shadow-sm">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div>
                    <h5 class="mb-1">Variants</h5>
                    <small class="text-muted-green d-block">
                        Add size and/or color options. Each combination gets its own stock quantity.
                        Leave both size and color empty on a row to skip it.
                    </small>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="add-variant-row">
                    <i class="bi bi-plus-lg"></i> Add Variant
                </button>
            </div>

            @error('variants')
                <div class="form-feedback-custom text-danger mb-2">{{ $message }}</div>
            @enderror

            <div id="variant-rows">
                @php
                    $oldVariants = old('variants', $product?->variants->map(fn ($v) => [
                        'id' => $v->id,
                        'size' => $v->size,
                        'color' => $v->color,
                        'color_hex' => $v->color_hex,
                        'stock_quantity' => $v->stock_quantity,
                        'extra_price' => $v->extra_price,
                    ])->all() ?? []);
                @endphp

                @foreach ($oldVariants as $index => $variant)
                    <div class="variant-row row g-2 align-items-end mb-3" data-variant-row>
                        @if (!empty($variant['id']))
                            <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant['id'] }}">
                        @endif
                        <div class="col-6 col-md-3">
                            <label class="form-label-custom">Size</label>
                            <input type="text" name="variants[{{ $index }}][size]"
                                   class="form-control-custom" placeholder="e.g. 42, M, L"
                                   value="{{ $variant['size'] ?? '' }}">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label-custom">Color</label>
                            <input type="text" name="variants[{{ $index }}][color]"
                                   class="form-control-custom" placeholder="e.g. Black"
                                   value="{{ $variant['color'] ?? '' }}">
                        </div>
                        <div class="col-4 col-md-2">
                            <label class="form-label-custom">Swatch</label>
                            <input type="color" name="variants[{{ $index }}][color_hex]"
                                   class="form-control form-control-color w-100"
                                   value="{{ $variant['color_hex'] ?? '#000000' }}">
                        </div>
                        <div class="col-4 col-md-2">
                            <label class="form-label-custom">Stock</label>
                            <input type="number" min="0" name="variants[{{ $index }}][stock_quantity]"
                                   class="form-control-custom" placeholder="0"
                                   value="{{ $variant['stock_quantity'] ?? 0 }}">
                        </div>
                        <div class="col-4 col-md-1">
                            <label class="form-label-custom">+ Price</label>
                            <input type="number" step="0.01" min="0" name="variants[{{ $index }}][extra_price]"
                                   class="form-control-custom" placeholder="0.00"
                                   value="{{ $variant['extra_price'] ?? '' }}">
                        </div>
                        <div class="col-12 col-md-1 d-flex justify-content-end justify-content-md-center">
                            <button type="button" class="btn btn-outline-danger btn-sm" data-remove-variant title="Remove variant">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <template id="variant-row-template">
                <div class="variant-row row g-2 align-items-end mb-3" data-variant-row>
                    <div class="col-6 col-md-3">
                        <label class="form-label-custom">Size</label>
                        <input type="text" name="variants[__INDEX__][size]"
                               class="form-control-custom" placeholder="e.g. 42, M, L">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label-custom">Color</label>
                        <input type="text" name="variants[__INDEX__][color]"
                               class="form-control-custom" placeholder="e.g. Black">
                    </div>
                    <div class="col-4 col-md-2">
                        <label class="form-label-custom">Swatch</label>
                        <input type="color" name="variants[__INDEX__][color_hex]"
                               class="form-control form-control-color w-100" value="#000000">
                    </div>
                    <div class="col-4 col-md-2">
                        <label class="form-label-custom">Stock</label>
                        <input type="number" min="0" name="variants[__INDEX__][stock_quantity]"
                               class="form-control-custom" placeholder="0" value="0">
                    </div>
                    <div class="col-4 col-md-1">
                        <label class="form-label-custom">+ Price</label>
                        <input type="number" step="0.01" min="0" name="variants[__INDEX__][extra_price]"
                               class="form-control-custom" placeholder="0.00">
                    </div>
                    <div class="col-12 col-md-1 d-flex justify-content-end justify-content-md-center">
                        <button type="button" class="btn btn-outline-danger btn-sm" data-remove-variant title="Remove variant">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div class="col-12 d-flex justify-content-end gap-2">
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn-quick-action">
            {{ $product ? 'Update Product' : 'Save Product' }}
        </button>
    </div>

</div>

@push('styles')
<style>
    .image-upload-box {
        position: relative;
        border: 1.5px dashed rgba(11, 19, 15, 0.15);
        border-radius: var(--radius-lg);
        background-color: #FBFCFC;
        transition: all 0.2s ease-in-out;
    }

    .image-upload-box.dragover {
        border-color: var(--brand-forest-medium);
        background-color: rgba(180, 241, 5, 0.06);
    }

    .image-upload-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        padding: 2rem 1rem;
        margin: 0;
        cursor: pointer;
        text-align: center;
    }

    .image-upload-placeholder i {
        font-size: 1.75rem;
        color: var(--brand-forest-medium);
        margin-bottom: 0.25rem;
    }

    .image-upload-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-main);
    }

    .image-upload-hint {
        font-size: 0.75rem;
        color: var(--text-muted-green);
    }

    .image-preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
        gap: 0.75rem;
    }

    .image-preview-item {
        position: relative;
        border: 1px solid rgba(11, 19, 15, 0.1);
        border-radius: var(--radius-md);
        overflow: hidden;
        aspect-ratio: 1 / 1;
        background-color: #FBFCFC;
        transition: opacity 0.2s ease-in-out;
    }

    .image-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .image-preview-item.marked-for-removal {
        opacity: 0.35;
    }

    .image-preview-remove {
        position: absolute;
        top: 0.4rem;
        right: 0.4rem;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        border: none;
        background-color: rgba(11, 19, 15, 0.75);
        color: #FFFFFF;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        cursor: pointer;
        transition: background-color 0.2s ease-in-out;
    }

    .image-preview-remove:hover {
        background-color: var(--sys-red);
    }

    .image-preview-item.marked-for-removal .image-preview-remove {
        background-color: var(--sys-red);
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const box = document.getElementById('image-upload-box');
        const input = document.getElementById('images');
        const newGridWrapper = document.getElementById('new-image-grid-wrapper');
        const newGrid = document.getElementById('new-image-grid');

        if (!box || !input) {
            return;
        }

        // Holds the files currently staged for upload so individual
        // files can be removed before the form is submitted.
        let stagedFiles = [];

        function syncInputFiles() {
            const dataTransfer = new DataTransfer();
            stagedFiles.forEach((file) => dataTransfer.items.add(file));
            input.files = dataTransfer.files;
        }

        function renderNewImages() {
            newGrid.innerHTML = '';

            if (stagedFiles.length === 0) {
                newGridWrapper.style.display = 'none';
                return;
            }

            newGridWrapper.style.display = 'block';

            stagedFiles.forEach((file, index) => {
                const reader = new FileReader();

                reader.onload = (event) => {
                    const item = document.createElement('div');
                    item.className = 'image-preview-item';
                    item.innerHTML = `
                        <img src="${event.target.result}" alt="New product image">
                        <button type="button" class="image-preview-remove" title="Remove image">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    `;

                    item.querySelector('.image-preview-remove').addEventListener('click', function () {
                        stagedFiles.splice(index, 1);
                        syncInputFiles();
                        renderNewImages();
                    });

                    newGrid.appendChild(item);
                };

                reader.readAsDataURL(file);
            });
        }

        function addFiles(fileList) {
            Array.from(fileList).forEach((file) => stagedFiles.push(file));
            syncInputFiles();
            renderNewImages();
        }

        input.addEventListener('change', function () {
            if (input.files.length) {
                addFiles(input.files);
            }
        });

        ['dragover', 'dragenter'].forEach((eventName) => {
            box.addEventListener(eventName, function (event) {
                event.preventDefault();
                box.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach((eventName) => {
            box.addEventListener(eventName, function (event) {
                event.preventDefault();
                box.classList.remove('dragover');
            });
        });

        box.addEventListener('drop', function (event) {
            if (event.dataTransfer.files.length) {
                addFiles(event.dataTransfer.files);
            }
        });

        // Toggle removal of existing (already saved) product images.
        document.querySelectorAll('[data-existing-remove]').forEach((button) => {
            button.addEventListener('click', function () {
                const wrapper = document.getElementById('existing-image-' + button.dataset.existingRemove);
                const checkbox = wrapper.querySelector('input[type="checkbox"]');

                checkbox.checked = !checkbox.checked;
                wrapper.classList.toggle('marked-for-removal', checkbox.checked);
            });
        });
    })();

    // Variant (size/color) rows: add / remove dynamically.
    (function () {
        const rowsWrapper = document.getElementById('variant-rows');
        const template = document.getElementById('variant-row-template');
        const addButton = document.getElementById('add-variant-row');

        if (!rowsWrapper || !template || !addButton) {
            return;
        }

        let newRowCounter = 0;

        function bindRemove(row) {
            const removeBtn = row.querySelector('[data-remove-variant]');
            removeBtn.addEventListener('click', function () {
                row.remove();
            });
        }

        // Bind remove buttons for rows already rendered server-side.
        rowsWrapper.querySelectorAll('[data-variant-row]').forEach(bindRemove);

        addButton.addEventListener('click', function () {
            const index = 'new_' + (newRowCounter++);
            const html = template.innerHTML.replaceAll('__INDEX__', index);

            const wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            const row = wrapper.firstElementChild;

            rowsWrapper.appendChild(row);
            bindRemove(row);
        });
    })();
</script>
@endpush
