@extends($laravelAdminPackage.'::layouts.admin_layout')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <h1 class="h3 mb-0">{{ __(':type categories', ['type' => $postType->type === 'faq' ? 'FAQ' : \Illuminate\Support\Str::headline($postType->type)]) }}</h1>
            <a class="btn btn-outline-primary" href="{{ route($adminRoutePrefix.'posts.type.list', ['type' => $postType->type]) }}">
                {{ __('View posts') }}
            </a>
        </div>
        <div class="card">
            <div class="card-body">
                @if($categories->count())
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">{{ __('ID') }}</th>
                                    <th scope="col">{{ __('Title') }}</th>
                                    <th scope="col">{{ __('Slug') }}</th>
                                    <th scope="col">{{ __('Status') }}</th>
                                    <th scope="col">{{ __('Position') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $category)
                                    <tr>
                                        <td>{{ $category->id }}</td>
                                        <td>{{ $category->translations->first()?->title ?? __('Translation unavailable') }}</td>
                                        <td>{{ $category->translations->first()?->slug ?? '—' }}</td>
                                        <td><span class="badge {{ $category->status ? 'bg-success' : 'bg-secondary' }}">{{ $category->status ? __('Active') : __('Inactive') }}</span></td>
                                        <td>{{ $category->sort_order }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="mb-0">{{ __('No categories found for this post type.') }}</p>
                @endif
            </div>
            @if($categories->hasPages())
                <div class="card-footer">{{ $categories->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
@endsection
