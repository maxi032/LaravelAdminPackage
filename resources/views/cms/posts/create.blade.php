@extends($laravelAdminPackage.'::layouts.admin_layout')

@section('content')
    <div class="container">
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
                        <form id="createOrEditForm" class="validationForm"
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
                                    @foreach(config($laravelAdminPackage.'.allowed_languages') as $languageKey => $language)
                                        <button class="@if($loop->first) active @endif nav-link"
                                                id="nav-{{$language['code']}}-tab" data-bs-toggle="tab"
                                                data-bs-target="#nav-{{$language['code']}}" type="button" role="tab"
                                                aria-controls="nav-{{$language['code']}}"
                                                aria-selected="false">{{$language['name']}}</button>
                                    @endforeach
                                </div>
                            </nav>
                            <div class="tab-content p-3 border border-top-0" id="nav-tabContent">
                                @foreach(config($laravelAdminPackage.'.allowed_languages') as $languageKey => $language)
                                    <div class="tab-pane fade @if($loop->first)show active @endif"
                                         id="nav-{{$language['code']}}" role="tabpanel"
                                         aria-labelledby="nav-{{$language['code']}}-tab">
                                        <div class="row mb-3">
                                            <label for="translations['title'][{{$language['code']}}]"
                                                   class="col-md-4 col-form-label text-md-end">{{ __('Title') }}</label>

                                            <div class="col-md-6">
                                                <input id="title_{{$language['code']}}" type="text"
                                                       class="form-control @error('translations.title.'.$language['code']) is-invalid @enderror"
                                                       name="translations[title][{{$language['code']}}]"
                                                       value="{{ old('translations.title.'.$language['code']) }}">

                                                @error('translations.title.' . $language['code'])
                                                <span class="invalid-feedback" role="alert">
                                        <strong>{!!  trimValidationMessage($message) !!}</strong>
                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="translations[slug][{{$language['code']}}]"
                                                   class="col-md-4 col-form-label text-md-end">{{ __('slug') }}</label>

                                            <div class="col-md-6">
                                                <input id="slug_{{$language['code']}}" type="text"
                                                       class="form-control @error('translations.slug.'.$language['code']) is-invalid @enderror"
                                                       name="translations[slug][{{$language['code']}}]"
                                                       value="{{ old('translations.slug.'.$language['code']) }}"
                                                       autocomplete="translations['slug'][{{$language['code']}}]"
                                                       autofocus>

                                                @error('translations.slug.' . $language['code'])
                                                <span class="invalid-feedback" role="alert">
                                        <strong>{!!  trimValidationMessage($message) !!}</strong>
                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="translations['excerpt'][{{$language['code']}}]"
                                                   class="col-md-4 col-form-label text-md-end">{{ __('Excerpt') }}</label>

                                            <div class="col-md-6">
                                                <textarea id="excerpt_{{$language['code']}}" class="form-control"
                                                          name="translations[excerpt][{{$language['code']}}]"
                                                          rows="3">{{ old('translations.excerpt.'.$language['code']) }}</textarea>

                                                @error('translations.excerpt.' . $language['code'])
                                                <span class="invalid-feedback" role="alert">
                                         <strong>{!!  trimValidationMessage($message) !!}</strong>
                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="translations['content'][{{$language['code']}}]"
                                                   class="col-md-4 col-form-label text-md-end">{{ __('Content') }}</label>

                                            <div class="col-md-6">
                                                <textarea id="content_{{$language['code']}}" class="form-control"
                                                          name="translations[content][{{$language['code']}}]"
                                                          rows="3">{{ old('translations.content.'.$language['code']) }}</textarea>

                                                @error('translations.content.' . $language['code'])
                                                <span class="invalid-feedback" role="alert">
                                         <strong>{!!  trimValidationMessage($message) !!}</strong>
                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                            </div>
                            <div class="row mb-0 mt-3">
                                <div class="col-md-6 offset-md-4">
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
