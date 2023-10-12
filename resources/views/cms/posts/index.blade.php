@extends($laravelAdminPackage.'::layouts.admin_layout')

@section('content')
    <div class="container-3xl">
        <div class="row">
           @if($posts->count())
            <div class="col-md-10">
                <h3 class="mb-4">{{ __($postType->type) }}</h3>
            </div>
            <div class="col-md-2">
                <a type="button" class="btn btn-primary float-end" href="{{ route('admin:posts.create') }}">
                    {{ __('Add') }}
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <table id="posts" class="display nowrap table table-striped w-100">
                    <thead>
                    <tr>
                        <th>{{ __('Id') }}</th>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Slug') }}</th>
                        <th>{{ __('Excerpt') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Created at') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($posts as $postk => $post)
                        @php
                            $excerpt = strlen($post->translations->where('language',app()->getLocale())->first()->excerpt) > 50 ?
                            substr($post->translations->where('language',app()->getLocale())->first()->excerpt, 0, 50).'...' :
                            $post->translations->where('language',app()->getLocale())->first()->excerpt;
                        @endphp
                        <tr>
                            <td>{{$post->id}}</td>
                            <td class="title">{!! $post->translations->where('language',app()->getLocale())->first()->title !!}</td>
                            <td class="slug">{!! $post->translations->where('language',app()->getLocale())->first()->slug !!}</td>
                            <td class="excerpt">{!! $excerpt !!}</td>
                            @if(in_array($post->status,[$inactiveStatus, $activeStatus]))
                                <td></td>
                            @else
                                <td>
                                    <div class="form-check form-switch form-switch-lg">
                                        <input class="form-check-input" type="checkbox" data-id="{{ $post->id }}" id="statusSwitch{{ $post->id }}"
                                               @if($post->status == 1) checked @endif>
                                        <label class="form-check-label" for="statusSwitch{{ $post->id }}"></label>
                                    </div>
                                </td>
                            @endif
                            <td>{{ \Carbon\Carbon::parse($post->created_at)->format('d-m-Y') }}</td>
                            <td>
                                <a role="button" class="btn btn-sm btn-primary" href="{{ route('admin:posts.edit',['post'=>$post]) }}"><span class="cil-notes"></span> Edit</a>
                                <a role="button" class="btn btn-sm btn-warning" href="#"><span class="cil-trash"></span> Delete</a>
                                <a role="button" class="btn btn-sm btn-light" href="{{ route('admin:posts.edit',['post'=>$post]) }}"><span class="cil-clone"></span> Duplicate</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <div class="col-12">
                    {{ __('No posts of type :type found',['type'=>$postType]) }}
                </div>
            @endif
        </div>
    </div>
    @push('footer-scripts')
        <script type="module">
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            window.jQuery(document).ready(function () {
                $("input[id^=statusSwitch]").on("change", function () {
                    let postId =  $(this).data('id');
                    let status = $(this).prop('checked') === true ? 1 : 0;

                    $.ajax({
                        type: 'POST',
                        url: "{{ route('admin:posts.ajax_change_status') }}",
                        data: {id: postId, status: status},
                        success: function (data) {
                            console.log(data)
                            if (data.success) {
                                let toastOptions = {
                                    html: data.success,
                                    className: 'bg-success text-light p-3',
                                    position: 'top-0 end-0',
                                    dismiss: {
                                        show: true,
                                        timeout: 3000,
                                    }
                                };

                                let toastContainer = $('<div>', {
                                    class: 'toast bg-success bg-gradient text-white ',
                                    role: 'alert',
                                    'aria-live': 'assertive',
                                    'aria-atomic': 'true'
                                }).append(
                                    $('<div>', {class: 'toast-header bg-success text-white'}).append(
                                        $('<strong>', {class: 'me-auto', text: 'Success'}),
                                        $('<button>', {
                                            type: 'button',
                                            class: 'btn-close',
                                            'data-bs-dismiss': 'toast',
                                            'aria-label': 'Close'
                                        })
                                    ),
                                    $('<div>', {class: 'toast-body', html: data.success})
                                );

                                $('header').append(toastContainer);
                                // Initialize the toast using CoreUI
                                let toast = new coreui.Toast(toastContainer, toastOptions);
                                toast.show();
                            }
                        },
                        error: function (jqXHR, status, err) {
                            alert("Error callback.");
                        },
                    });
                });
            });
        </script>
    @endpush
@endsection
