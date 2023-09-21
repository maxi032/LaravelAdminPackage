<div class="tab-pane fade " id="nav-{{$tab}}" role="tabpanel"
     aria-labelledby="nav-{{$tab}}-tab" tabindex="0">
    {{-- start content  --}}
    <div class="d-flex align-items-start mt-3">
        <div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist"
             aria-orientation="vertical">
            @foreach(config($laravelAdminPackage.'.allowed_languages') as $languageKey => $language)
                <button class="nav-link @if($loop->first) active @endif"
                        id="v-pills-{{ $language['code'] }}-tab"
                        data-coreui-toggle="pill"
                        data-coreui-target="#v-pills-{{ $language['code'] }}"
                        type="button"
                        role="tab" aria-controls="v-pills-{{ $language['code'] }}"
                        aria-selected="@if($loop->first) true @else false @endif">{{ strtoupper($language['code']) }}</button>
            @endforeach
        </div>
        <div class="flex-grow-1 tab-content" id="v-pills-tabContent">
            @foreach(config($laravelAdminPackage.'.allowed_languages') as $languageKey => $language)
                <div class="tab-pane fade @if($loop->first) show active @endif"
                     id="v-pills-{{ $language['code'] }}" role="tabpanel"
                     aria-labelledby="v-pills-{{ $language['code'] }}-tab"
                     tabindex="0">
                    <div class="row g-2">
                        <div class="col-md-11 offset-md-1">
                            <div class="form-floating mb-3">
                                <input type="text"
                                       value="{{ old('translations.meta_title.'.$language['code']) }}"
                                       autocomplete="translations['meta_title'][{{$language['code']}}]"
                                       autofocus
                                       name="translations[meta_title][{{$language['code']}}]"
                                       placeholder="Enter a meta title"
                                       class="form-control @error('translations.meta_title.'.$language['code']) is-invalid @enderror"
                                       id="meta_title_{{$language['code']}}">
                                <label for="meta_title_{{$language['code']}}">@if(!$errors->has('translations.meta_title.' . $language['code']))
                                        {{ __('Meta title') }}
                                    @endif @error('translations.meta_title.' . $language['code']){!!  trimValidationMessage($message) !!} @enderror</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-11 offset-md-1">
                            <div class="form-floating mb-3">
                                <input type="text"
                                       value="{{ old('translations.slug.'.$language['code']) }}"
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
                            <div class="form-floating mb-3">
                                                        <textarea name="translations[meta_keywords][{{$language['code']}}]"
                                                                  id="meta_keywords_{{$language['code']}}"
                                                                  class="form-control" cols="7"
                                                                  rows="9">{{ old('translations.meta_keywords.'.$language['code']) }}</textarea>
                                <label for="meta_keywords_{{$language['code']}}">@if(!$errors->has('translations.meta_keywords.' . $language['code']))
                                        {{ __('Meta keywords') }}
                                    @endif @error('translations.meta_keywords.' . $language['code']){!!  trimValidationMessage($message) !!} @enderror</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-11 offset-md-1">
                            <div class="form-floating mb-3">
                                                        <textarea name="translations[meta_description][{{$language['code']}}]"
                                                                  id="meta_description_{{$language['code']}}"
                                                                  class="form-control " cols="7"
                                                                  rows="12">{{ old('translations.meta_description.'.$language['code']) }}</textarea>
                                <label for="content_{{$language['code']}}">@if(!$errors->has('translations.meta_description.' . $language['code']))
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