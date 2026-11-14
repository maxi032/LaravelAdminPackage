@if($posts->isNotEmpty())
    <input type="search" class="form-control form-control-sm mb-2" data-post-search
           placeholder="{{ __('Search posts') }}" aria-label="{{ __('Search posts') }}">
    <div class="text-muted" role="status" data-post-search-empty hidden>{{ __('No matching posts found.') }}</div>
@endif
<ul class="list-unstyled mb-0 overflow-auto">
    @forelse($posts as $post)
        @php
            $title = $post->translations->firstWhere('language', app()->getLocale())?->title
                ?? $post->translations->firstWhere('language', config('app.fallback_locale'))?->title;
            $url = $post->trashed()
                ? route($adminRoutePrefix.'posts.trash', ['type' => $post->type, 'post_id' => $post->id])
                : route($adminRoutePrefix.'posts.edit', $post);
        @endphp
        <li class="py-1" data-post-search-item>
            <strong>{{ $post->type->displayName() }}</strong> · {{ $title ?? __('Translation unavailable') }} ·
            {{ __('Post ID') }}: <a href="{{ $url }}">{{ $post->id }}</a>
        </li>
    @empty
        <li>{{ __('No associated posts found.') }}</li>
    @endforelse
</ul>
