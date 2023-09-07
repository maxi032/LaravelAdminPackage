@extends($laravelAdminPackage.'::layouts.admin_layout')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">{{ __('Add post') }}</div>

                    <div class="card-body">
                        @if(session('message'))
                            <div class="alert alert-danger">
                                {{ session('message') }}
                            </div>
                        @endif
                        <form id="createOrEditForm" class="validationForm" enctype="multipart/form-data"
                              method="{{ Str::endsWith(Route::currentRouteName(), '.create') ? 'POST' : 'PUT' }}"
                              action="{{ Str::endsWith(Route::currentRouteName(), '.create') ? route('admin:posts.store') : route('admin:posts.update') }}">
                            @csrf
                            <input id="type" type="hidden"
                                   name="type_id"
                                   value="2" required>
                            <input id="status" type="hidden"
                                   name="status"
                                   value="1">
                            @error('type_id')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror


                            <nav>
                                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                    <button class="nav-link active" id="nav-home-tab" data-coreui-toggle="tab" data-coreui-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true">Content</button>
                                    <button class="nav-link" id="nav-profile-tab" data-coreui-toggle="tab" data-coreui-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">Seo</button>
                                </div>
                            </nav>
                            <div class="tab-content" id="nav-tabContent">
                                <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab" tabindex="0">
                                    {{-- start content  --}}
                                    <div class="d-flex align-items-start mt-3">
                                        <div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist"
                                             aria-orientation="vertical">
                                            @foreach(config($laravelAdminPackage.'.allowed_languages') as $languageKey => $language)
                                                <button class="nav-link @if($loop->first) active @endif"
                                                        id="v-pills-{{ $language['code'] }}-tab" data-coreui-toggle="pill"
                                                        data-coreui-target="#v-pills-{{ $language['code'] }}" type="button"
                                                        role="tab" aria-controls="v-pills-{{ $language['code'] }}"
                                                        aria-selected="@if($loop->first) true @else false @endif">{{ strtoupper($language['code']) }}</button>
                                            @endforeach
                                        </div>
                                        <div class="flex-grow-1 tab-content" id="v-pills-tabContent">
                                            @foreach(config($laravelAdminPackage.'.allowed_languages') as $languageKey => $language)
                                                <div class="tab-pane fade @if($loop->first) show active @endif"
                                                     id="v-pills-{{ $language['code'] }}" role="tabpanel"
                                                     aria-labelledby="v-pills-{{ $language['code'] }}-tab" tabindex="0">
                                                    <div class="row g-2">
                                                        <div class="col-md-11 offset-md-1">
                                                            <div class="form-floating mb-3">
                                                                <input type="text"
                                                                       value="{{ old('translations.title.'.$language['code']) }}"
                                                                       autocomplete="translations['title'][{{$language['code']}}]"
                                                                       autofocus
                                                                       name="translations[title][{{$language['code']}}]"
                                                                       placeholder="Enter a title"
                                                                       class="form-control @error('translations.title.'.$language['code']) is-invalid @enderror"
                                                                       id="title_{{$language['code']}}">
                                                                <label for="title_{{$language['code']}}">@if(!$errors->has('translations.title.' . $language['code']))
                                                                        {{ __('Title') }}
                                                                    @endif @error('translations.title.' . $language['code']){!!  trimValidationMessage($message) !!} @enderror</label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row g-2">
                                                        <div class="col-md-11 offset-md-1">
                                                            <div class="form-floating mb-3">
                                                                <input type="text"
                                                                       value="{{ old('translations.slug.'.$language['code']) }}"
                                                                       autocomplete="translations['slug'][{{$language['code']}}]"
                                                                       autofocus
                                                                       name="translations[slug][{{$language['code']}}]"
                                                                       placeholder="Enter a slug"
                                                                       class="form-control @error('translations.slug.'.$language['code']) is-invalid @enderror"
                                                                       id="slug_{{$language['code']}}">
                                                                <label for="slug_{{$language['code']}}">@if(!$errors->has('translations.slug.' . $language['code']))
                                                                        {{ __('Slug') }}
                                                                    @endif @error('translations.slug.' . $language['code']){!!  trimValidationMessage($message) !!} @enderror</label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row g-2">
                                                        <div class="col-md-11 offset-md-1">
                                                            <div class="form-floating mb-3">
                                                        <textarea name="translations[excerpt][{{$language['code']}}]"
                                                                  id="excerpt_{{$language['code']}}"
                                                                  class="form-control" cols="7"
                                                                  rows="9">{{ old('translations.excerpt.'.$language['code']) }}</textarea>
                                                                <label for="excerpt_{{$language['code']}}">@if(!$errors->has('translations.excerpt.' . $language['code']))
                                                                        {{ __('Excerpt') }}
                                                                    @endif @error('translations.excerpt.' . $language['code']){!!  trimValidationMessage($message) !!} @enderror</label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row g-2">
                                                        <div class="col-md-11 offset-md-1">
                                                            <div class="form-floating mb-3">
                                                        <textarea name="translations[content][{{$language['code']}}]"
                                                                  id="content_{{$language['code']}}"
                                                                  class="form-control editor" cols="7"
                                                                  rows="9">{{ old('translations.content.'.$language['code']) }}</textarea>
                                                                <label for="content_{{$language['code']}}">@if(!$errors->has('translations.content.' . $language['code']))
                                                                        {{ __('Content') }}
                                                                    @endif @error('translations.content.' . $language['code']){!!  trimValidationMessage($message) !!} @enderror</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    {{-- end content  --}}
                                </div>
                                <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab" tabindex="0">
                                    seo content
                                </div>
                            </div>

                            <div class="row mb-0 mt-3">
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Send') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
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
            });
        }
    </script>
    @if ($errors->any())
        <script type="module">
            window.jQuery(document).ready(function () {
                let allErrors = @json($errors->all());
                let errorMessage = '<ul>';
                $.each(allErrors, function (index, err) {
                    errorMessage += '<li>' + err + '</li>';
                });
                errorMessage += '</ul>';

                let toastOptions = {
                    html: errorMessage,
                    className: 'bg-danger text-light p-3',
                    position: 'top-0 end-0',
                    dismiss: {
                        show: true,
                        timeout: 3000,
                    }
                };

                let toastContainer = $('<div>', {
                    class: 'toast bg-danger bg-gradient text-white ',
                    role: 'alert',
                    'aria-live': 'assertive',
                    'aria-atomic': 'true'
                }).append(
                    $('<div>', {class: 'toast-header bg-danger text-white'}).append(
                        $('<strong>', {class: 'me-auto', text: 'Error'}),
                        $('<button>', {
                            type: 'button',
                            class: 'btn-close',
                            'data-bs-dismiss': 'toast',
                            'aria-label': 'Close'
                        })
                    ),
                    $('<div>', {class: 'toast-body', html: errorMessage})
                );

                $('header').append(toastContainer);
                // Initialize the toast using CoreUI
                let toast = new coreui.Toast(toastContainer, toastOptions);
                toast.show();

            });
        </script>
    @endif
@endpush
