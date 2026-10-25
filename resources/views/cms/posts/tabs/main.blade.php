<div class="tab-pane fade {{ ($activeTab ?? 'main') === $tab ? 'show active' : '' }}" id="nav-{{$tab}}" role="tabpanel"
     aria-labelledby="nav-{{$tab}}-tab" tabindex="0">
    <div class="row g-3 pt-4">
        <div class="col-6">
            <label class="form-label" for="status">{{ __('Status') }}</label>
            <select name="status" id="status" class="w-50 form-select @error('status') is-invalid @enderror" required
                    @error('status') aria-invalid="true" aria-describedby="status_error" @enderror>
                <option value="" @selected((string) old('status', $post?->status) === '')>{{ __('Please select') }}</option>
                @foreach (\Maxi032\LaravelAdminPackage\Enums\PostStatusEnum::cases() as $status)
                    <option value="{{ $status->value }}" @selected((string) old('status', $post?->status) === (string) $status->value)>{{ __(ucfirst(strtolower($status->name))) }}</option>
                @endforeach
            </select>
            @error('status')
                <span id="status_error" class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label" for="type_id">{{ __('Type') }}</label>
            <select name="type_id" id="type_id" class="w-50 form-select @error('type_id') is-invalid @enderror" required
                    @error('type_id') aria-invalid="true" aria-describedby="type_id_error" @enderror>
                <option value="">{{ __('Please select') }}</option>
                @foreach ($postTypes as $postTypeId => $postType)
                    <option value="{{$postTypeId}}" {{ (old('type_id', $post?->type_id ?? ($selectedTypeId ?? null)) == $postTypeId) ? 'selected' : '' }}>{{ $postType }}</option>
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
                    <option value="{{$category->id}}" {{ (old('category_id', $post?->category_id) == $category->id) ? 'selected' : '' }}>{{ $category->translations->first()?->title ?? __('Category with id :id does not have a translation for :language', ['id' => $category->id, 'language' => app()->getLocale()]) }}</option>
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