@extends($laravelAdminPackage.'::layouts.admin_layout')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <h1 class="h3 mb-0">{{ __(':type categories', ['type' => $categoryType->type === 'faq' ? 'FAQ' : \Illuminate\Support\Str::headline($categoryType->type)]) }}</h1>
            <div class="d-flex gap-2">
                @if($hasPostType)
                <a class="btn btn-outline-primary" href="{{ route($adminRoutePrefix.'posts.type.list', ['type' => $categoryType->type]) }}">{{ __('View posts') }}</a>
                @endif
                <a class="btn btn-outline-secondary" href="{{ route($adminRoutePrefix.'categories.trash', ['type' => $categoryType->type]) }}">{{ __('Trash') }}</a>
                <a class="btn btn-primary" href="{{ route($adminRoutePrefix.'categories.create', ['type' => $categoryType->type]) }}">{{ __('Add') }}</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                @if($categories->count())
                    <div class="table-responsive">
                        <table id="categories" class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">{{ __('ID') }}</th>
                                    <th scope="col">{{ __('Title') }}</th>
                                    <th scope="col">{{ __('Slug') }}</th>
                                    <th scope="col">{{ __('Status') }}</th>
                                    <th scope="col">{{ __('Position') }}</th>
                                    <th scope="col">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $category)
                                    <tr>
                                        <td>{{ $category->id }}</td>
                                        <td>{{ $category->translations->first()?->title ?? __('Translation unavailable') }}</td>
                                        <td>{{ $category->translations->first()?->slug ?? '—' }}</td>
                                        <td>
                                            <form method="POST" action="{{ route($adminRoutePrefix.'categories.change_status', $category) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="0">
                                                <div class="form-check form-switch form-switch-lg">
                                                    <input class="form-check-input" type="checkbox" name="status" value="1"
                                                           id="statusSwitch{{ $category->id }}" @checked($category->status)
                                                           onchange="this.form.requestSubmit()">
                                                    <label class="form-check-label visually-hidden" for="statusSwitch{{ $category->id }}">{{ __('Status') }}</label>
                                                </div>
                                            </form>
                                        </td>
                                        <td>{{ $category->sort_order }}</td>
                                        <td>
                                            <a class="btn btn-sm btn-primary" href="{{ route($adminRoutePrefix.'categories.edit', $category) }}">{{ __('Edit') }}</a>
                                            <button type="button" class="btn btn-sm btn-warning" data-coreui-toggle="modal" data-coreui-target="#delete-category-{{ $category->id }}">{{ __('Delete') }}</button>
                                        </td>
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
    @foreach($categories as $category)
        @include($laravelAdminPackage.'::cms.categories.partials.delete_confirmation', ['category' => $category])
    @endforeach
@endsection
