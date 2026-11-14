<option value="">{{ __('Please select') }}</option>
@foreach ($categories as $category)
    <option value="{{ $category->id }}" @selected(old('category_id', $post?->category_id) == $category->id)>
        {{ $category->translations->first()?->title ?? __('Category with id :id does not have a translation for :language', ['id' => $category->id, 'language' => app()->getLocale()]) }}
    </option>
@endforeach
