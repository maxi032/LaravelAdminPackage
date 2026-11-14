@extends($laravelAdminPackage.'::layouts.admin_layout')

@php
    $title = $postType->exists ? __('Edit post type') : __('Add post type');
    $type = old('type', $postType->type);
    $defaultIcon = $postType->icon ?? \Maxi032\LaravelAdminPackage\Models\PostType::DEFAULT_ICON;
    $icon = old('icon', $defaultIcon);
    if (!is_string($icon) || !isset($icons[$icon])) {
        $icon = $defaultIcon;
    }
@endphp

@section('meta_title', $title)

@section('content')
    <form class="card mb-4" method="POST" data-crud-form data-loading-region=".card"
          data-loading-text="{{ __('Saving…') }}"
          action="{{ $postType->exists
              ? route($adminRoutePrefix.'settings.post-types.update', $postType)
              : route($adminRoutePrefix.'settings.post-types.store') }}">
        @csrf
        @if($postType->exists)
            @method('PUT')
        @endif
        <div class="card-header">
            <h1 class="h5 mb-0 d-inline-block card-header-text-lh">{{ $title }}</h1>
            <button type="submit" class="btn btn-primary float-end">{{ __('Save') }}</button>
        </div>
        <div class="card-body">
            <div class="col-md-6 mb-3">
                <label for="post-type-key" class="form-label">{{ __('Type') }}</label>
                <input id="post-type-key" name="type" class="form-control @error('type') is-invalid @enderror"
                       value="{{ is_scalar($type) ? $type : '' }}" maxlength="50" pattern="[a-z][a-z0-9_\-]*"
                       aria-describedby="post-type-key-help @error('type') post-type-key-error @enderror" required>
                <div id="post-type-key-help" class="form-text">
                    {{ __('Start with a lowercase letter. Use lowercase letters, numbers, underscores or hyphens.') }}
                    {{ __('Hyphens are saved as underscores.') }}
                </div>
                @error('type') <div id="post-type-key-error" class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label id="post-type-icon-label" for="post-type-icon" class="form-label">{{ __('Icon') }}</label>
                <select id="post-type-icon" name="icon" required class="form-select @error('icon') is-invalid @enderror"
                        @error('icon') aria-invalid="true" aria-describedby="post-type-icon-error" @enderror>
                    @foreach($icons as $value => $label)
                        <option value="{{ $value }}" @selected($icon === $value)>{{ __($label) }}</option>
                    @endforeach
                </select>
                <div class="dropdown" id="post-type-icon-picker" hidden>
                    <button id="post-type-icon-toggle" type="button" class="form-select text-start @error('icon') is-invalid @enderror"
                            data-coreui-toggle="dropdown" aria-expanded="false"
                            aria-labelledby="post-type-icon-label post-type-icon-selection"
                            @error('icon') aria-invalid="true" aria-describedby="post-type-icon-error" @enderror>
                        <span id="post-type-icon-selection"></span>
                    </button>
                    <div class="dropdown-menu w-100" aria-labelledby="post-type-icon-label">
                        <div class="px-2 pb-2">
                            <input id="post-type-icon-search" type="search" class="form-control form-control-sm"
                                   placeholder="{{ __('Search icons') }}" aria-label="{{ __('Search icons') }}">
                        </div>
                        <div class="overflow-auto" style="max-height: 14rem">
                            @foreach($icons as $value => $label)
                                <button type="button" class="dropdown-item" data-icon="{{ $value }}">
                                    <span class="{{ $value }} me-2" aria-hidden="true"></span>{{ __($label) }}
                                </button>
                            @endforeach
                            <p id="post-type-icon-empty" class="px-3 mb-0 text-muted" role="status" hidden>{{ __('No icons found.') }}</p>
                        </div>
                    </div>
                </div>
                @error('icon') <div id="post-type-icon-error" class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="form-check form-switch">
                <input type="hidden" name="status" value="{{ $statusLocked ? '1' : '0' }}">
                <input id="post-type-status" name="status" type="checkbox" value="1" role="switch"
                       class="form-check-input @error('status') is-invalid @enderror"
                       @disabled($statusLocked)
                       @checked($statusLocked || in_array(old('status', $postType->status), [true, 1, '1'], true))>
                <label for="post-type-status" class="form-check-label">{{ __('Enabled') }}</label>
                @if($statusLocked)
                    <div class="form-text">{{ __('A post type with posts cannot be disabled.') }}</div>
                @endif
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </form>
@endsection

@push('footer-scripts')
    <script type="module">
        const select = document.getElementById('post-type-icon');
        const picker = document.getElementById('post-type-icon-picker');
        const toggle = document.getElementById('post-type-icon-toggle');
        const selection = document.getElementById('post-type-icon-selection');
        const search = document.getElementById('post-type-icon-search');
        const empty = document.getElementById('post-type-icon-empty');
        const choices = [...picker.querySelectorAll('[data-icon]')];

        function selectIcon(choice) {
            select.value = choice.dataset.icon;
            selection.innerHTML = choice.innerHTML;
            choices.forEach(item => {
                item.classList.toggle('active', item === choice);
                item.setAttribute('aria-pressed', String(item === choice));
            });
        }

        if (window.coreui?.Dropdown) {
            selectIcon(choices.find(choice => choice.dataset.icon === select.value));
            search.addEventListener('input', () => {
                const query = search.value.trim().toLocaleLowerCase();
                choices.forEach(choice => {
                    choice.hidden = !choice.textContent.toLocaleLowerCase().includes(query) && !choice.dataset.icon.includes(query);
                });
                empty.hidden = choices.some(choice => !choice.hidden);
            });
            search.addEventListener('keydown', event => {
                if (event.key === 'Enter') event.preventDefault();
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    choices.find(choice => !choice.hidden)?.focus();
                }
            });
            picker.addEventListener('click', event => {
                const choice = event.target.closest('[data-icon]');
                if (choice) {
                    selectIcon(choice);
                    toggle.focus();
                }
            });
            document.getElementById('post-type-icon-label').htmlFor = toggle.id;
            select.hidden = true;
            picker.hidden = false;
        }
    </script>
@endpush
