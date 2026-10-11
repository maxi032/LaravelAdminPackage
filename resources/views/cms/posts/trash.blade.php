@extends($laravelAdminPackage.'::layouts.admin_layout')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <h1 class="h3 mb-0">{{ __(':type trash', ['type' => __($postType->type)]) }}</h1>
            <a class="btn btn-outline-secondary" href="{{ route($adminRoutePrefix.'posts.type.list', ['type' => $postType->type]) }}">{{ __('View posts') }}</a>
        </div>
        <div class="card">
            <div class="card-body">
                @if($posts->count())
                    <div class="table-responsive">
                        <table id="trashed-posts" class="table table-striped align-middle mb-0 w-100">
                            <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Slug') }}</th>
                                <th>{{ __('Deleted at') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($posts as $post)
                                @php($translation = $post->translations->firstWhere('language', app()->getLocale()))
                                <tr>
                                    <td>{{ $post->id }}</td>
                                    <td>{{ $translation?->title ?? __('Translation unavailable') }}</td>
                                    <td>{{ $translation?->slug ?? __('Translation unavailable') }}</td>
                                    <td>{{ $post->deleted_at->format('d-m-Y H:i') }}</td>
                                    <td>
                                        <form data-crud-form data-loading-region="tr" data-loading-text="{{ __('Recovering…') }}" class="d-inline" method="POST" action="{{ route($adminRoutePrefix.'posts.recover', ['post' => $post]) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">{{ __('Recover') }}</button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger" data-coreui-toggle="modal" data-coreui-target="#destroy-post-{{ $post->id }}">{{ __('Destroy') }}</button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="mb-0">{{ __('No deleted posts of type :type found', ['type' => __($postType->type)]) }}</p>
                @endif
            </div>
        </div>
    </div>
    @foreach($posts as $post)
        @include($laravelAdminPackage.'::cms.posts.partials.destroy_confirmation', ['post' => $post])
    @endforeach
@endsection
