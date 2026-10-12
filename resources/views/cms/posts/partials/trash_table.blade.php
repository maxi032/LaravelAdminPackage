        <div class="card">
            <div class="card-body">
                @if($posts->count())
                    <div class="table-responsive">
                        <table id="trashed-posts" class="table table-striped align-middle mb-0 w-100">
                            <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Title') }}</th>
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
                                    <td>{{ $post->deleted_at->format('d-m-Y H:i') }}</td>
                                    <td>
                                        <form data-crud-form data-loading-region="tr" data-loading-text="{{ __('Recovering…') }}" class="d-inline" method="POST" action="{{ route($adminRoutePrefix.'posts.recover', ['post' => $post]) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">{{ __('Recover') }}</button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger" data-coreui-toggle="modal" data-coreui-target="#destroy-post" data-post-delete
                                                data-delete-url="{{ route($adminRoutePrefix.'posts.force_delete', ['post' => $post]) }}"
                                                data-post-title="{{ $translation?->title ?? __('Translation unavailable') }}">{{ __('Destroy') }}</button>
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
            @if($posts->hasPages())
                <div class="card-footer">{{ $posts->onEachSide(2)->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
