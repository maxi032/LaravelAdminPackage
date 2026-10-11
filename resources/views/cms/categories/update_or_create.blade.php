@extends($laravelAdminPackage.'::layouts.admin_layout')

@section('content')
    @php
        $allowedTabs = ['main','content','seo'];
        $activeTab = 'main';
        $activeLanguage = config($laravelAdminPackage.'.allowed_languages')[0]['code'];
        $firstError = array_key_first($errors->messages());
        if ($firstError && str_starts_with($firstError, 'translations.')) {
            $field = explode('.', $firstError)[1];
            $activeTab = in_array($field, ['slug', 'meta_title', 'meta_keywords', 'meta_description'], true)
                ? 'seo'
                : 'content';
            $activeLanguage = explode('.', $firstError)[2] ?? $activeLanguage;
        }
    @endphp
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <form id="updateOrCreateForm" class="validationForm" enctype="multipart/form-data"
                      action="{{ !$category ? route($adminRoutePrefix.'categories.store') : route($adminRoutePrefix.'categories.update', $category) }}"
                      method="POST" novalidate
                      data-crud-form data-loading-region=".card"
                      data-loading-text="{{ $category ? __('Updating…') : __('Saving…') }}"
                >
                    @if($category)
                        @method('PUT')
                    @endif
                    @csrf
                <div class="card mb-4">
                    <div class="card-header "><span class="card-header-text-lh">{{ $category ? __('Edit category') : __('Add category') }}</span>
                        <button type="submit" id="saveButton" class="btn btn-primary float-end">
                            {{ $category ? __('Save changes') : __('Save') }}
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
                                        @include($laravelAdminPackage.'::cms.categories.tabs.'.$tab,['tab'=>$tab, 'loop'=>$loop,'categoryTypes'=>$categoryTypes])
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
