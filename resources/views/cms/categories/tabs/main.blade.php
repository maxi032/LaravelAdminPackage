<div class="tab-pane fade {{ ($activeTab ?? 'main') === $tab ? 'show active' : '' }}" id="nav-{{ $tab }}" role="tabpanel" aria-labelledby="nav-{{ $tab }}-tab" tabindex="0">
    <div class="row g-3 pt-4">
        <div class="col-12">
            <div class="form-check form-switch form-switch-xl d-flex align-items-center gap-2 ps-0 mb-0">
                <label class="form-check-label pt-0" for="status">{{ __('Status') }}</label>
                <input class="form-check-input float-none ms-0 mt-0" type="checkbox" name="status" id="status" {{ filter_var(old('status', $category?->status == 1), FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
            </div>
            @error('status')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label" for="type_id">{{ __('Type') }}</label>
            <select name="type_id" id="type_id" class="w-50 form-select @error('type_id') is-invalid @enderror" required>
                <option value="">{{ __('Please select') }}</option>
                @foreach($categoryTypes as $id => $name)
                    <option value="{{ $id }}" {{ old('type_id', $category?->type_id ?? ($selectedTypeId ?? null)) == $id ? 'selected' : '' }}>{{ __($name) }}</option>
                @endforeach
            </select>
            @error('type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label" for="sort_order">{{ __('Position') }}</label>
            <input type="number" name="sort_order" id="sort_order" min="0" max="4294967295" value="{{ old('sort_order', $category?->sort_order) }}" class="w-50 form-control @error('sort_order') is-invalid @enderror">
            @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>
