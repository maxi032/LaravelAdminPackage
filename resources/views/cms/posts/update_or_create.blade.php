@extends($laravelAdminPackage.'::layouts.admin_layout')

@section('content')
    @php
        $allowedTabs = ['main','content','seo','options'];
        $activeTab = 'main';
        $firstError = array_key_first($errors->messages());
        if ($firstError && str_starts_with($firstError, 'translations.')) {
            $field = explode('.', $firstError)[1];
            $activeTab = in_array($field, ['slug', 'meta_title', 'meta_keywords', 'meta_description'], true)
                ? 'seo'
                : 'content';
        } elseif ($firstError === 'options' || str_starts_with($firstError ?? '', 'options.')) {
            $activeTab = 'options';
        }
    @endphp
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <form id="updateOrCreateForm" class="validationForm" enctype="multipart/form-data"
                      action="{{ Str::endsWith(Route::currentRouteName(), '.create') ? route($adminRoutePrefix.'posts.store') : route($adminRoutePrefix.'posts.update', $post) }}"
                      method="POST" novalidate
                      data-crud-form data-loading-region=".card"
                      data-loading-text="{{ $post ? __('Updating…') : __('Saving…') }}"
                >
                    @if(Str::endsWith(Route::currentRouteName(), '.edit'))
                        @method('PUT')
                    @endif
                    @csrf
                <div class="card mb-4">
                    <div class="card-header "><span class="card-header-text-lh">{{ __('Add post') }}</span>
                        <button type="submit" id="saveButton" class="btn btn-primary float-end">
                            {{ $post ? __('Save changes') : __('Save') }}
                        </button>
                    </div>

                    <div class="card-body">

                            @if(count($allowedTabs))
                                <nav>
                                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                        @foreach($allowedTabs as $tabk => $tab)
                                            <button class="custom-tab nav-link @if($tab === $activeTab) active @endif"
                                                    id="nav-{{$tab}}-tab"
                                                    data-coreui-toggle="tab" data-coreui-target="#nav-{{$tab}}"
                                                    type="button"
                                                    role="tab" aria-controls="nav-{{$tab}}"
                                                    aria-selected="{{ $tab === $activeTab ? 'true' : 'false' }}">{{ __(ucfirst($tab)) }}
                                            </button>
                                        @endforeach
                                    </div>
                                </nav>
                                <div class="tab-content" id="nav-tabContent">
                                    @foreach($allowedTabs as $tabk => $tab)
                                        @include($laravelAdminPackage.'::cms.posts.tabs.'.$tab,['tab'=>$tab, 'loop'=>$loop,'postTypes'=>$postTypes])
                                    @endforeach
                                </div>
                            @endif

                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>

@endsection
@push('footer-scripts')
    <script type="module">
        const type = document.getElementById('type_id');
        const category = document.getElementById('category_id');
        const options = document.getElementById('post-option-fields');
        const save = document.getElementById('saveButton');
        const error = document.getElementById('post-type-fields-error');
        const emptyOptions = @json(__('No post options are enabled.'));
        let loadedType = type.value;
        let request;

        type.addEventListener('change', async () => {
            request?.abort();
            request = new AbortController();
            const { signal } = request;
            const url = type.selectedOptions[0]?.dataset.fieldsUrl;
            error.hidden = true;
            save.disabled = true;

            try {
                if (url) {
                    const response = await fetch(url, { signal, headers: { Accept: 'application/json' } });
                    if (!response.ok) throw new Error();
                    const fields = await response.json();
                    if (signal.aborted) return;

                    const selectedCategory = category.value;
                    category.innerHTML = fields.categories;
                    if ([...category.options].some(option => option.value === selectedCategory)) {
                        category.value = selectedCategory;
                    }

                    const previous = new Map([...options.querySelectorAll('textarea, input:not([type=hidden])')]
                        .map(field => [field.name, field]));
                    options.innerHTML = fields.options;
                    options.querySelectorAll('textarea, input:not([type=hidden])').forEach(field => {
                        const old = previous.get(field.name);
                        if (old) {
                            if (field.type === 'checkbox') field.checked = old.checked;
                            else field.value = old.value;
                        }
                    });
                } else {
                    category.value = '';
                    options.textContent = emptyOptions;
                }
                loadedType = type.value;
            } catch (failure) {
                if (!signal.aborted) {
                    type.value = loadedType;
                    error.hidden = false;
                }
            } finally {
                if (!signal.aborted) save.disabled = false;
            }
        });
    </script>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script>
        var allEditors = document.querySelectorAll('.editor');
        for (var i = 0; i < allEditors.length; ++i) {
            ClassicEditor.create(allEditors[i], {
                removePlugins: ['CKFinderUploadAdapter', 'CKFinder', 'EasyImage', 'Image', 'ImageCaption', 'ImageStyle', 'ImageToolbar', 'ImageUpload', 'MediaEmbed'],
            }).then(editor => { editor.sourceElement.cmsEditor = editor; });
        }
    </script>
@endpush
