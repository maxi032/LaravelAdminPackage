@extends($laravelAdminPackage.'::layouts.admin_layout')

@section('meta_title', __('Post options trash'))

@section('content')
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between gap-2">
            <h1 class="h5 mb-0">{{ __('Post options trash') }}</h1>
            <a class="btn btn-outline-secondary" href="{{ route($adminRoutePrefix.'settings.post-options.index') }}">
                {{ __('View post options') }}
            </a>
        </div>
        @include($laravelAdminPackage.'::cms.settings.post_options.partials.overview', ['table' => 'trash_table'])
    </div>
    @include($laravelAdminPackage.'::cms.posts.partials.destroy_confirmation', [
        'destroyMessage' => __('Permanently delete this post option and its saved values? This cannot be undone.'),
    ])
@endsection
