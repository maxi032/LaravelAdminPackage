<div class="tab-pane fade " id="nav-{{$tab}}" role="tabpanel"
     aria-labelledby="nav-{{$tab}}-tab" tabindex="0">
    {{-- start content  --}}
    <div class="d-flex align-items-start mt-3">
        <div class="nav flex-column nav-pills me-3" id="v-pills-{{$tab}}" role="tablist"
             aria-orientation="vertical">
            @foreach(config($laravelAdminPackage.'.allowed_languages') as $languageKey => $language)
                <button class="nav-link @if($loop->first) active @endif"
                        id="v-pills-{{$tab}}-{{ $language['code'] }}-tab"
                        data-coreui-toggle="pill"
                        data-coreui-target="#v-pills-{{$tab}}-{{ $language['code'] }}"
                        type="button"
                        role="tab" aria-controls="v-pills-{{$tab}}-{{ $language['code'] }}"
                        aria-selected="@if($loop->first) true @else false @endif">{{ strtoupper($language['code']) }}</button>
            @endforeach
        </div>
        <div class="flex-grow-1 tab-content" id="v-pills-tab{{$tab}}">
            @foreach(config($laravelAdminPackage.'.allowed_languages') as $languageKey => $language)
                <div class="tab-pane fade @if($loop->first) show active @endif"
                     id="v-pills-{{$tab}}-{{ $language['code'] }}" role="tabpanel"
                     aria-labelledby="v-pills-{{$tab}}-{{ $language['code'] }}-tab"
                     tabindex="0">
                    <div class="row g-2">
                        <div class="col-md-11 offset-md-1">
                            @php
                                $title = old('translations.title.'.$language['code']) ?? $post?->translations->where('language', $language['code'])->first()->title
                            @endphp
                            <div class="form-floating mb-3">
                                <input type="text"
                                       value="{{ $title }}"
                                       autocomplete="translations['title'][{{$language['code']}}]"
                                       autofocus
                                       name="translations[title][{{$language['code']}}]"
                                       placeholder="Enter a title"
                                       class="form-control @error('translations.title.'.$language['code']) is-invalid @enderror"
                                       id="title_{{$language['code']}}">
                                <label for="title_{{$language['code']}}">@if(!$errors->has('translations.title.' . $language['code']))
                                        {{ __('Title') }}
                                    @endif @error('translations.title.' . $language['code']){!!  trimValidationMessage($message) !!} @enderror</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-11 offset-md-1">
                            @php
                                $excerpt = old('translations.excerpt.'.$language['code']) ?? $post?->translations->where('language', $language['code'])->first()->excerpt
                            @endphp
                            <div class="form-floating mb-3">
                                                        <textarea name="translations[excerpt][{{$language['code']}}]"
                                                                  id="excerpt_{{$language['code']}}"
                                                                  class="form-control" cols="7"
                                                                  rows="9">{{ $excerpt }}</textarea>
                                <label for="excerpt_{{$language['code']}}">@if(!$errors->has('translations.excerpt.' . $language['code']))
                                        {{ __('Excerpt') }}
                                    @endif @error('translations.excerpt.' . $language['code']){!!  trimValidationMessage($message) !!} @enderror</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-11 offset-md-1">
                            @php
                                $content = old('translations.content.'.$language['code']) ?? $post?->translations->where('language', $language['code'])->first()->content
                            @endphp
                            <div class="form-floating mb-3">
                                                        <textarea name="translations[content][{{$language['code']}}]"
                                                                  id="content_{{$language['code']}}"
                                                                  class="form-control editor" cols="7"
                                                                  rows="9">{{ $content }}</textarea>
                                <label for="content_{{$language['code']}}">@if(!$errors->has('translations.content.' . $language['code']))
                                        {{ __('Content') }}
                                    @endif @error('translations.content.' . $language['code']){!!  trimValidationMessage($message) !!} @enderror</label>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    {{-- end content  --}}
</div>