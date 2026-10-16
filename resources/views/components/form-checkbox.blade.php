@props([
    'label' => '',
    'name' => '',
    'value' => '1',
    'checked' => false,
    'col' => 'col-lg-6 col-md-12',
    'help' => ''
])

<div class="{{ $col }}">
    <div class="mb-3">
        <div class="form-check">
            <input 
                type="checkbox" 
                class="form-check-input @error($name) is-invalid @enderror" 
                id="{{ $name }}"
                name="{{ $name }}" 
                value="{{ $value }}"
                {{ old($name, $checked) ? 'checked' : '' }}
            />
            @if($label)
                <label class="form-check-label" for="{{ $name }}">
                    {{ $label }}
                </label>
            @endif
        </div>
        @error($name)
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
        @if($help)
            <small class="text-muted d-block mt-1">{{ $help }}</small>
        @endif
    </div>
</div>
