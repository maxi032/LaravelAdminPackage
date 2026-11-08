@php
    $optionPath = 'options.'.$definition->key;
    $path = $optionPath.($locale === null ? '' : '.'.$locale);
    $name = 'options['.$definition->key.']'.($locale === null ? '' : '['.$locale.']');
    $id = 'option-'.$definition->key.($locale === null ? '' : '-'.$locale);
    $saved = $values->get($definition->id)?->firstWhere('locale', $locale ?? '');
    $value = old($path, $saved?->getAttribute($definition->valueColumn()) ?? $definition->defaultValue());
    $invalid = $errors->has($path) ? 'is-invalid' : '';
@endphp
<div class="col-md-6">
    @if($locale !== null)
        @error($optionPath)
            <div class="text-danger mb-2">{{ $message }}</div>
        @enderror
    @endif
    @if($definition->type === 'boolean')
        <div class="form-check form-switch">
            <input type="hidden" name="{{ $name }}" value="0">
            <input class="form-check-input {{ $invalid }}" type="checkbox"
                   id="{{ $id }}" name="{{ $name }}" value="1" @checked(in_array($value, [true, 1, '1'], true))
                   @if($invalid) aria-describedby="{{ $id }}-error" @endif>
            <label class="form-check-label" for="{{ $id }}">{{ $definition->label }}</label>
        </div>
    @else
        <label class="form-label" for="{{ $id }}">{{ $definition->label }}</label>
        @if($definition->type === 'integer')
            <input type="number" step="1" min="{{ $definition->key === 'reading_time' ? 1 : -2147483648 }}"
                   max="2147483647" class="form-control w-25 {{ $invalid }}" id="{{ $id }}" name="{{ $name }}"
                   value="{{ is_scalar($value) ? $value : '' }}"
                   @if($invalid) aria-describedby="{{ $id }}-error" @endif>
        @else
            <textarea class="form-control w-75 {{ $invalid }}" id="{{ $id }}" name="{{ $name }}" rows="2"
                      maxlength="10000" @if($invalid) aria-describedby="{{ $id }}-error" @endif>{{ is_scalar($value) ? $value : '' }}</textarea>
        @endif
    @endif
    @error($path)
        <div class="invalid-feedback d-block" id="{{ $id }}-error">{{ $message }}</div>
    @enderror
</div>
