@extends($laravelAdminPackage.'::layouts.admin_layout')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <table id="posts" class="display nowrap table table-striped" style="width:100%">
                    <thead>
                    <tr>
                        <th>Id</th>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Excerpt</th>
                        <th>Status</th>
                        <th>Created at</th>
                        <th>Actions</th>
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
                            <td>{!! $post->translations->where('language',app()->getLocale())->first()->title !!}</td>
                            <td>{!! $post->translations->where('language',app()->getLocale())->first()->slug !!}</td>
                            <td>{!! $excerpt !!}</td>
                            @if(in_array($post->status,[$inactiveStatus, $activeStatus]))
                                <td><span class="badge text-bg-{{ $post->status_badge }}">&nbsp;</span></td>
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
                            <td>&nbsp;</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
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
