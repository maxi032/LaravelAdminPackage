@extends($laravelAdminPackage.'::layouts.admin_layout')

@section('meta_title', __('Post types trash'))

@section('content')
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between gap-2">
            <h1 class="h5 mb-0">{{ __('Post types trash') }}</h1>
            <a class="btn btn-outline-secondary" href="{{ route($adminRoutePrefix.'settings.post-types.index') }}">
                {{ __('View post types') }}
            </a>
        </div>
        @include($laravelAdminPackage.'::cms.settings.post_types.partials.overview', ['table' => 'trash_table'])
    </div>
    @include($laravelAdminPackage.'::cms.posts.partials.destroy_confirmation', [
        'destroyMessage' => __('Permanently delete this post type and its matching category type in Trash? This cannot be undone.'),
    ])
@endsection
