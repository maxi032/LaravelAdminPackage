        <div class="card">
            <div class="card-body">
                @if($posts->count())
                    <div class="table-responsive">
                        <table id="posts" class="table table-striped align-middle mb-0 w-100">
                            <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Title') }}</th>
                                @if($postType->type === 'products')
                                    <th>{{ __('Category') }}</th>
                                @endif
                                <th>{{ __('Excerpt') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Created at') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($posts as $postk => $post)
                                @php
                                    $translation = $post->translations->first();
                                    $excerpt = \Illuminate\Support\Str::limit($translation?->excerpt ?? '', 50);
                                @endphp
                                <tr>
                                    <td>{{$post->id}}</td>
                                    <td class="title">{{ $translation?->title ?? __('Translation unavailable') }}</td>
                                    @if($postType->type === 'products')
                                        <td class="category">{{ $post->category ? ($post->category->translations->first()?->title ?? __('Translation unavailable')) : '—' }}</td>
                                    @endif
                                    <td class="excerpt">{{ $excerpt }}</td>
                                    @if(in_array($post->status,[$inactiveStatus, $activeStatus]))
                                        <td></td>
                                    @else
                                        <td>
                                            <div class="form-check form-switch form-switch-lg">
                                                <input class="form-check-input" type="checkbox" data-status-toggle
                                                       data-status-url="{{ route($adminRoutePrefix.'posts.ajax_change_status') }}"
                                                       data-loading-text="{{ __('Updating…') }}"
                                                       data-error-text="{{ __('The status could not be updated. Please try again.') }}"
                                                       aria-label="{{ __('Status') }}" data-id="{{ $post->id }}" id="statusSwitch{{ $post->id }}"
                                                       @if($post->status == 1) checked @endif>
                                                <label class="form-check-label" for="statusSwitch{{ $post->id }}"></label>
                                            </div>
                                        </td>
                                    @endif
                                    <td>{{ \Carbon\Carbon::parse($post->created_at)->format('d-m-Y') }}</td>
                                    <td>
                                        <a role="button" class="btn btn-sm btn-primary" href="{{ route($adminRoutePrefix.'posts.edit',['post'=>$post]) }}"><span class="cil-notes"></span> {{ __('Edit') }}</a>
                                        <button type="button" class="btn btn-sm btn-warning" data-coreui-toggle="modal" data-coreui-target="#delete-post" data-post-delete
                                                data-delete-url="{{ route($adminRoutePrefix.'posts.destroy', ['post' => $post]) }}"
                                                data-post-title="{{ $translation?->title ?? __('Translation unavailable') }}"><span class="cil-trash"></span> {{ __('Delete') }}</button>
                                        <a role="button" class="btn btn-sm btn-light" href="{{ route($adminRoutePrefix.'posts.edit',['post'=>$post]) }}"><span class="cil-clone"></span> {{ __('Duplicate') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="mb-0">{{ __('No posts of type :type found',['type'=>__($postType->type)]) }}</p>
                @endif
            </div>
            @if($posts->hasPages())
                <div class="card-footer">{{ $posts->onEachSide(2)->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
