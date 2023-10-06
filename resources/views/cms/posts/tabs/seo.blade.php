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
                                $metaTitle = old('translations.meta_title.'.$language['code']) ?? $post->translations->where('language',$language['code'])->first()->meta_title
                            @endphp
                            <div class="form-floating mb-3">
                                <input type="text"
                                       value="{{ $metaTitle }}"
                                       autocomplete="translations['meta_title'][{{$language['code']}}]"
                                       autofocus
                                       name="translations[meta_title][{{$language['code']}}]"
                                       placeholder="Enter a meta title"
                                       class="form-control @error('translations.meta_title.'.$language['code']) is-invalid @enderror"
                                       id="meta_title_{{$language['code']}}">
                                <label for="meta_title_{{$language['code']}}">@if(!$errors->has('translations.meta_title.' . $language['code']))
                                        {{ __('Meta Title') }}
                                    @endif @error('translations.meta_title.' . $language['code']){!!  trimValidationMessage($message) !!} @enderror</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-11 offset-md-1">
                            @php
                                $slug = old('translations.slug.'.$language['code']) ?? $post->translations->where('language',$language['code'])->first()->slug
                            @endphp
                            <div class="form-floating mb-3">
                                <input type="text"
                                       value="{{ $slug }}"
                                       autocomplete="translations['slug'][{{$language['code']}}]"
                                       autofocus
                                       name="translations[slug][{{$language['code']}}]"
                                       placeholder="Enter a slug"
                                       class="form-control @error('translations.slug.'.$language['code']) is-invalid @enderror"
                                       id="slug_{{$language['code']}}">
                                <label for="slug_{{$language['code']}}">@if(!$errors->has('translations.slug.' . $language['code']))
                                        {{ __('Slug') }}
                                    @endif @error('translations.slug.' . $language['code']){!!  trimValidationMessage($message) !!} @enderror</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-11 offset-md-1">
                            @php
                                $metaKeywords = old('translations.meta_keywords.'.$language['code']) ?? $post->translations->where('language',$language['code'])->first()->meta_keywords
                            @endphp
                            <div class="form-floating mb-3">
                                                        <textarea name="translations[meta_keywords][{{$language['code']}}]"
                                                                  id="meta_keywords_{{$language['code']}}"
                                                                  class="form-control" cols="7"
                                                                  rows="9">{{ $metaKeywords }}</textarea>
                                <label for="meta_keywords_{{$language['code']}}">@if(!$errors->has('translations.meta_keywords.' . $language['code']))
                                        {{ __('Meta keywords') }}
                                    @endif @error('translations.meta_keywords.' . $language['code']){!!  trimValidationMessage($message) !!} @enderror</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-11 offset-md-1">
                            @php
                                $metaDescription = old('translations.meta_description.'.$language['code']) ?? $post->translations->where('language',$language['code'])->first()->meta_description
                            @endphp
                            <div class="form-floating mb-3">
                                                        <textarea name="translations[meta_description][{{$language['code']}}]"
                                                                  id="meta_descriprion_{{$language['code']}}"
                                                                  class="form-control" cols="7"
                                                                  rows="9">{{ $metaDescription }}</textarea>
                                <label for="meta_description_{{$language['code']}}">@if(!$errors->has('translations.meta_description.' . $language['code']))
                                        {{ __('Meta description') }}
                                    @endif @error('translations.meta_description.' . $language['code']){!!  trimValidationMessage($message) !!} @enderror</label>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    {{-- end content  --}}
</div>