@php
    $category = $category ?? null;
@endphp

<div class="row g-4">

    {{-- Basic Information --}}
    <div class="col-lg-8">
        <div class="card p-4 border-light shadow-sm h-100">
            <h5 class="mb-3">Category Information</h5>

            <div class="mb-3">
                <label for="name" class="form-label-custom">Category Name</label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    class="form-control-custom @error('name') is-invalid-custom @enderror"
                    placeholder="e.g. Electronics"
                    value="{{ old('name', $category->name ?? '') }}"
                    required>
                @error('name')
                    <div class="form-feedback-custom text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="slug" class="form-label-custom">Slug</label>
                <input
                    type="text"
                    name="slug"
                    id="slug"
                    class="form-control-custom @error('slug') is-invalid-custom @enderror"
                    placeholder="Leave empty to auto-generate from the name"
                    value="{{ old('slug', $category->slug ?? '') }}">
                @error('slug')
                    <div class="form-feedback-custom text-danger">{{ $message }}</div>
                @enderror
                <small class="text-muted-green">Used in the category URL. Auto-generated from the name if left blank.</small>
            </div>
        </div>
    </div>

    {{-- Image & Status --}}
    <div class="col-lg-4">
        <div class="card p-4 border-light shadow-sm h-100">
            <h5 class="mb-3">Image &amp; Status</h5>

            <div class="mb-3">
                <label class="form-label-custom">Category Image</label>

                <div class="image-upload-box" id="image-upload-box">
                    <input
                        type="file"
                        name="image"
                        id="image"
                        accept="image/png, image/jpeg, image/webp"
                        class="image-upload-input"
                        hidden>

                    <input type="hidden" name="remove_image" id="remove_image" value="0">

                    <div
                        class="image-upload-preview"
                        id="image-upload-preview"
                        style="{{ empty($category?->image_url) ? 'display:none;' : '' }}">
                        <img src="{{ $category->image_url ?? '' }}" alt="Category image preview" id="image-preview-img">
                        <button type="button" class="image-upload-remove" id="image-upload-remove" title="Remove image">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <label
                        for="image"
                        class="image-upload-placeholder"
                        id="image-upload-placeholder"
                        style="{{ empty($category?->image_url) ? '' : 'display:none;' }}">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <span class="image-upload-title">Click to upload or drag &amp; drop</span>
                        <span class="image-upload-hint">PNG, JPG or WEBP &middot; up to 2MB</span>
                    </label>
                </div>

                @error('image')
                    <div class="form-feedback-custom text-danger mt-2">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="status" class="form-label-custom">Status</label>
                <select name="status" id="status" class="form-select-custom @error('status') is-invalid-custom @enderror">
                    <option value="active" @selected(old('status', $category->status ?? 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $category->status ?? 'active') === 'inactive')>Inactive</option>
                </select>
                @error('status')
                    <div class="form-feedback-custom text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="col-12 d-flex justify-content-end gap-2">
        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn-quick-action">
            {{ $category ? 'Update Category' : 'Save Category' }}
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

    .image-upload-preview {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .image-upload-preview img {
        width: 100%;
        max-height: 170px;
        object-fit: contain;
        border-radius: var(--radius-md);
    }

    .image-upload-remove {
        position: absolute;
        top: 0.6rem;
        right: 0.6rem;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: none;
        background-color: rgba(11, 19, 15, 0.75);
        color: #FFFFFF;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        cursor: pointer;
        transition: background-color 0.2s ease-in-out;
    }

    .image-upload-remove:hover {
        background-color: var(--sys-red);
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const box = document.getElementById('image-upload-box');
        const input = document.getElementById('image');
        const preview = document.getElementById('image-upload-preview');
        const previewImg = document.getElementById('image-preview-img');
        const placeholder = document.getElementById('image-upload-placeholder');
        const removeBtn = document.getElementById('image-upload-remove');
        const removeFlag = document.getElementById('remove_image');

        if (!box || !input) {
            return;
        }

        function showPreview(src) {
            previewImg.src = src;
            preview.style.display = 'flex';
            placeholder.style.display = 'none';
        }

        function resetPreview() {
            previewImg.src = '';
            preview.style.display = 'none';
            placeholder.style.display = 'flex';
        }

        input.addEventListener('change', function () {
            const file = input.files[0];

            if (!file) {
                return;
            }

            if (removeFlag) {
                removeFlag.value = '0';
            }

            const reader = new FileReader();
            reader.onload = (event) => showPreview(event.target.result);
            reader.readAsDataURL(file);
        });

        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                input.value = '';

                if (removeFlag) {
                    removeFlag.value = '1';
                }

                resetPreview();
            });
        }

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
            const file = event.dataTransfer.files[0];

            if (!file) {
                return;
            }

            input.files = event.dataTransfer.files;
            input.dispatchEvent(new Event('change'));
        });
    })();
</script>
@endpush
