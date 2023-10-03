<div class="tab-pane fade show active" id="nav-{{$tab}}" role="tabpanel"
     aria-labelledby="nav-{{$tab}}-tab" tabindex="0">
    {{-- start content  --}}
    <div class="row g-2">
        <div class="col-md-6">
            <div class="form-check form-switch form-switch-xl mt-4">
                <input class="form-check-input" type="checkbox" name="status" id="status" {{ in_array(old('status'),['on',1])   ? 'checked' : '' }}>
                <label class="form-check-label" for="status">Status</label>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-check form-switch mt-4">
                <label class="form-check-label mb-2 d-block" for="type_id">{{ __('Type') }}</label>
                <select name="type_id" class="form-select" aria-label="Type" required>
                    <option value="">{{ __('Please select') }}</option>
                    @foreach ($postTypes as $postTypeId => $postType)
                        <option value="{{$postTypeId}}" {{ old('type_id') == $postTypeId  ? 'selected' : '' }}>{{ $postType }}</option>
                    @endforeach
                </select>
                @error('type_id')
                <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                @enderror
            </div>
        </div>
    </div>

    {{-- end content  --}}
</div>