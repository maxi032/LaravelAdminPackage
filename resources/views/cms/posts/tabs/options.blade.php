<div class="tab-pane fade {{ $activeTab === $tab ? 'show active' : '' }}"
     id="nav-{{ $tab }}" role="tabpanel" aria-labelledby="nav-{{ $tab }}-tab" tabindex="0">
    <div class="pt-3" id="post-option-fields">
        @include($laravelAdminPackage.'::cms.posts.partials.option_fields')
    </div>
</div>
