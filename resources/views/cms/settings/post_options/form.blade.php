@extends($laravelAdminPackage.'::layouts.admin_layout')

@section('meta_title', $option->exists ? __('Edit post option') : __('Add post option'))

@section('content')
    @php
        $selectedType = $schemaLocked ? $option->type : old('type', $option->type);
        $showBooleanDefault = $selectedType === 'boolean';
        $oldText = static function (string $key, mixed $fallback = null): string {
            $value = old($key, $fallback);

            return is_scalar($value) ? (string) $value : '';
        };
    @endphp
    <form class="card mb-4" method="POST"
          action="{{ $option->exists
              ? route($adminRoutePrefix.'settings.post-options.update', $option)
              : route($adminRoutePrefix.'settings.post-options.store') }}">
        @csrf
        @if($option->exists)
            @method('PUT')
        @endif
        <div class="card-header">
            <h1 class="h5 mb-0 d-inline-block card-header-text-lh">{{ $option->exists ? __('Edit post option') : __('Add post option') }}</h1>
            <button type="submit" class="btn btn-primary float-end">{{ __('Save') }}</button>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="post-option-key" class="form-label">{{ __('Key') }}</label>
                        <input id="post-option-key" class="w-50 form-control @error('key') is-invalid @enderror"
                               value="{{ $option->exists ? $option->key : $oldText('key') }}" maxlength="64"
                               @if($option->exists) readonly @else name="key" pattern="[a-z][a-z0-9_]*" required @endif>
                        <div class="form-text w-50">{{ __('Use lowercase letters, numbers and underscores. The key starts with a letter and is fixed after creation.') }}</div>
                        @error('key') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="post-option-label" class="form-label">{{ __('Label') }}</label>
                        <input id="post-option-label" name="label" class="w-50 form-control @error('label') is-invalid @enderror"
                               value="{{ $oldText('label', $option->label) }}" maxlength="150" required>
                        @error('label') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="post-option-order" class="form-label">{{ __('Sort order') }}</label>
                        <input id="post-option-order" name="sort_order" type="number" min="0" max="4294967295" step="1"
                               class="w-25 form-control @error('sort_order') is-invalid @enderror"
                               value="{{ $oldText('sort_order', $option->sort_order) }}" required>
                        @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="post-option-type" class="form-label">{{ __('Type') }}</label>
                        <select id="post-option-type" class="w-50 form-select @error('type') is-invalid @enderror"
                                @if($schemaLocked) disabled @else name="type" required @endif>
                            @foreach(\Maxi032\LaravelAdminPackage\Models\PostOption::TYPES as $type)
                                <option value="{{ $type }}" @selected($selectedType === $type)>
                                    {{ __(ucfirst($type)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div id="post-option-boolean-default" class="mb-3" @if(!$showBooleanDefault) hidden @endif>
                        <fieldset @disabled(!$showBooleanDefault)>
                            <div class="form-check form-switch">
                                <input type="hidden" name="boolean_default" value="0">
                                <input id="post-option-default-value" type="checkbox" name="boolean_default" value="1"
                                       class="form-check-input @error('boolean_default') is-invalid @enderror"
                                       @checked(in_array(old('boolean_default', $option->defaultValue()), [true, 1, '1'], true))>
                                <label class="form-check-label" for="post-option-default-value">{{ __('Default value') }}</label>
                                <div class="w-50 form-text">{{ __('Turn on for true. Leave off for false.') }}</div>
                                @error('boolean_default') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </fieldset>
                    </div>

                    <div class="form-check mb-3">
                        @unless($schemaLocked)
                            <input type="hidden" name="is_translatable" value="0">
                        @endunless
                        <input id="post-option-translatable" type="checkbox" class="form-check-input @error('is_translatable') is-invalid @enderror"
                               value="1" @checked($schemaLocked ? $option->is_translatable : old('is_translatable', $option->is_translatable))
                               @if($schemaLocked) disabled @else name="is_translatable" @endif>
                        <label for="post-option-translatable" class="form-check-label">{{ __('Store a value per language') }}</label>
                        @error('is_translatable') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @if($schemaLocked)
                            <div class="form-text">{{ __('The type and translation setting are locked while this option has saved values.') }}</div>
                        @endif
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="visible_in_post" value="0">
                        <input id="post-option-visible-in-post" name="visible_in_post" type="checkbox" value="1"
                               class="form-check-input @error('visible_in_post') is-invalid @enderror"
                               @checked(in_array(old('visible_in_post', $option->visible_in_post), [true, 1, '1'], true))>
                        <label for="post-option-visible-in-post" class="form-check-label">{{ __('Visible in post') }}</label>
                        <div class="form-text w-50">{{ __('Allow editors to set a value on individual posts. When off, posts use the option’s default.') }}</div>
                        @error('visible_in_post') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="is_eager_loaded" value="0">
                        <input id="post-option-eager-loading" name="is_eager_loaded" type="checkbox" value="1"
                               class="form-check-input @error('is_eager_loaded') is-invalid @enderror"
                               @checked(in_array(old('is_eager_loaded', $option->is_eager_loaded), [true, 1, '1'], true))>
                        <label for="post-option-eager-loading" class="form-check-label">{{ __('Eager loaded') }}</label>
                        @error('is_eager_loaded') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="enabled" value="0">
                        <input id="post-option-enabled" name="enabled" type="checkbox" value="1"
                               class="form-check-input @error('enabled') is-invalid @enderror" @checked(old('enabled', $option->enabled))>
                        <label for="post-option-enabled" class="form-check-label">{{ __('Enabled') }}</label>
                        <div class="form-text w-50">{{ __('Disabling an option hides it from post editors and preserves its saved values.') }}</div>
                        @error('enabled') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('footer-scripts')
    <script type="module">
        const type = document.getElementById('post-option-type');
        const booleanDefault = document.getElementById('post-option-boolean-default');
        const inputs = booleanDefault.querySelector('fieldset');

        function updateDefaultVisibility() {
            const showDefault = type.value === 'boolean';
            booleanDefault.hidden = !showDefault;
            inputs.disabled = !showDefault;
        }

        type.addEventListener('change', updateDefaultVisibility);
        updateDefaultVisibility();
    </script>
@endpush
