@php
    $definitions = $postOptions ?? collect();
    [$translated, $shared] = $definitions->partition('is_translatable');
    $values = ($post?->optionValues ?? collect())->groupBy('post_option_id');
    $languages = config($laravelAdminPackage.'.allowed_languages', []);
    $languageCodes = array_column($languages, 'code');
    $errorLanguage = explode('.', $firstError ?? '')[2] ?? null;
    $activeLanguage = in_array($errorLanguage, $languageCodes, true) ? $errorLanguage : ($languageCodes[0] ?? null);
@endphp
<div class="tab-pane fade {{ $activeTab === $tab ? 'show active' : '' }}"
     id="nav-{{ $tab }}" role="tabpanel" aria-labelledby="nav-{{ $tab }}-tab" tabindex="0">
    <div class="pt-3">
        @error('options')
            <div class="alert alert-danger" role="alert">{{ $message }}</div>
        @enderror

        @if($shared->isNotEmpty())
            <div class="row g-3 mb-3">
                @foreach($shared as $definition)
                    @include($laravelAdminPackage.'::cms.posts.partials.option_field', ['locale' => null])
                @endforeach
            </div>
        @elseif($translated->isEmpty())
            <p class="text-muted">{{ __('No post options are enabled.') }}</p>
        @endif

        @if($translated->isNotEmpty() && $languages)
            <div class="d-flex align-items-start">
                <div class="nav flex-column nav-pills me-3" id="v-pills-options" role="tablist" aria-orientation="vertical">
                    @foreach($languages as $language)
                        @php($locale = $language['code'])
                        <button class="nav-link {{ $locale === $activeLanguage ? 'active' : '' }}"
                                id="v-pills-options-{{ $locale }}-tab" type="button" role="tab"
                                data-coreui-toggle="pill" data-coreui-target="#v-pills-options-{{ $locale }}"
                                aria-controls="v-pills-options-{{ $locale }}"
                                aria-selected="{{ $locale === $activeLanguage ? 'true' : 'false' }}">
                            {{ strtoupper($locale) }}
                        </button>
                    @endforeach
                </div>
                <div class="flex-grow-1 tab-content">
                    @foreach($languages as $language)
                        @php($locale = $language['code'])
                        <div class="tab-pane fade {{ $locale === $activeLanguage ? 'show active' : '' }}"
                             id="v-pills-options-{{ $locale }}" role="tabpanel"
                             aria-labelledby="v-pills-options-{{ $locale }}-tab" tabindex="0">
                            <div class="row g-3">
                                @foreach($translated as $definition)
                                    @include($laravelAdminPackage.'::cms.posts.partials.option_field', ['locale' => $locale])
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
