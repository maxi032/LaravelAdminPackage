<div class="tab-pane fade show active" id="nav-{{$tab}}" role="tabpanel"
     aria-labelledby="nav-{{$tab}}-tab" tabindex="0">
    <div class="row g-3 pt-4">
        <div class="col-12">
            @php
                $checked = filter_var(old('status', $post?->status == 1), FILTER_VALIDATE_BOOLEAN)
            @endphp
            <div class="form-check form-switch form-switch-xl d-flex align-items-center gap-2 ps-0 mb-0">
                <label class="form-check-label pt-0" for="status">{{ __('Status') }}</label>
                <input class="form-check-input float-none ms-0 mt-0" type="checkbox" name="status" id="status" {{ $checked ? 'checked' : '' }}>
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label" for="type_id">{{ __('Type') }}</label>
            <select name="type_id" id="type_id" class="w-50 form-select @error('type_id') is-invalid @enderror" required
                    @error('type_id') aria-invalid="true" aria-describedby="type_id_error" @enderror>
                <option value="">{{ __('Please select') }}</option>
                @foreach ($postTypes as $postTypeId => $postType)
                    <option value="{{$postTypeId}}" {{ (old('type_id', $post?->type_id) == $postTypeId) ? 'selected' : '' }}>{{ $postType }}</option>
                @endforeach
            </select>
            @error('type_id')
                <span id="type_id_error" class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label" for="category_id">{{ __('Category') }}</label>
            <select name="category_id" id="category_id" class="w-50 form-select @error('category_id') is-invalid @enderror" required
                    @error('category_id') aria-invalid="true" aria-describedby="category_id_error" @enderror>
                <option value="">{{ __('Please select') }}</option>
                @foreach ($categories as $category)
                    <option value="{{$category->id}}" {{ (old('category_id', $post?->category_id) == $category->id) ? 'selected' : '' }}>{{ $category->translations->first()->title }}</option>
                @endforeach
            </select>
            @error('category_id')
                <span id="category_id_error" class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </div>
</div>