@extends($laravelAdminPackage.'::layouts.admin_layout')

@section('content')
    @php
        $allowedTabs = ['main','content','seo'];
    @endphp
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header "><span class="card-header-text-lh">{{ __('Add post') }}</span>
                        <button type="button" id="saveButton" class="btn btn-primary float-end">
                            {{ __('Send') }}
                        </button>
                    </div>

                    <div class="card-body">
                        @if(session('message'))
                            <div class="alert alert-danger">
                                {{ session('message') }}
                            </div>
                        @endif
                        <form id="updateOrCreateForm" class="validationForm" enctype="multipart/form-data"
                              method="{{ Str::endsWith(Route::currentRouteName(), '.create') ? 'POST' : 'PUT' }}"
                              action="{{ Str::endsWith(Route::currentRouteName(), '.create') ? route('admin:posts.store') : route('admin:posts.update') }}">
                            @csrf

                            @if(count($allowedTabs))
                                <nav>
                                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                        @foreach($allowedTabs as $tabk => $tab)
                                            <button class="custom-tab nav-link @if($loop->index==0) active @endif"
                                                    id="nav-{{$tab}}-tab"
                                                    data-coreui-toggle="tab" data-coreui-target="#nav-{{$tab}}"
                                                    type="button"
                                                    role="tab" aria-controls="nav-{{$tab}}"
                                                    aria-selected="true">{{ ucfirst($tab) }}
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
    <script type="module">
        window.jQuery(document).ready(function () {
            $("#saveButton").on("click", function () {
                $('#updateOrCreateForm').submit();
            });

            @if ($errors->any())
            // toast code
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
            @endif
        });
    </script>
@endpush
