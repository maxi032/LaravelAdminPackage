@extends($laravelAdminPackage.'::layouts.admin_layout')

@section('meta_title', __('Post options'))

@section('content')
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h1 class="h5 mb-0">{{ __('Post options') }}</h1>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary" href="{{ route($adminRoutePrefix.'settings.post-options.trash') }}">
                    {{ __('Trash') }}
                </a>
                <a class="btn btn-primary" href="{{ route($adminRoutePrefix.'settings.post-options.create') }}">
                    {{ __('Add') }}
                </a>
            </div>
        </div>
        @include($laravelAdminPackage.'::cms.settings.post_options.partials.overview', ['table' => 'index_table'])
    </div>
    @include($laravelAdminPackage.'::cms.posts.partials.delete_confirmation')
@endsection
