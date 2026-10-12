        <div class="card">
            <div class="card-body">
                @if($categories->count())
                    <div class="table-responsive">
                        <table id="trashed-categories" class="table table-striped align-middle mb-0 w-100">
                            <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Deleted at') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($categories as $category)
                                @php($translation = $category->translations->first())
                                <tr>
                                    <td>{{ $category->id }}</td>
                                    <td>{{ $translation?->title ?? __('Translation unavailable') }}</td>
                                    <td>{{ $category->deleted_at->format('d-m-Y H:i') }}</td>
                                    <td>
                                        <form data-crud-form data-loading-region="tr" data-loading-text="{{ __('Recovering…') }}" class="d-inline" method="POST" action="{{ route($adminRoutePrefix.'categories.recover', ['category' => $category]) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">{{ __('Recover') }}</button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger" data-coreui-toggle="modal" data-coreui-target="#destroy-category-{{ $category->id }}">{{ __('Destroy') }}</button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="mb-0">{{ __('No deleted categories of type :type found', ['type' => __($categoryType->type)]) }}</p>
                @endif
            </div>
            @if($categories->hasPages())
                <div class="card-footer">{{ $categories->onEachSide(2)->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    @foreach($categories as $category)
        @include($laravelAdminPackage.'::cms.categories.partials.destroy_confirmation', ['category' => $category])
    @endforeach
