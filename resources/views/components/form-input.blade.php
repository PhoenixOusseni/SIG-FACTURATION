@props([
    'label' => '',
    'name' => '',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'readonly' => false,
    'required' => false,
    'col' => 'col-lg-6 col-md-12',
    'help' => ''
])

<div class="{{ $col }}">
    <div class="mb-3">
        @if($label)
            <label for="{{ $name }}" class="form-label">
                {{ $label }}
                @if($required)
                    <span class="text-danger">*</span>
                @endif
            </label>
        @endif
        <input 
            type="{{ $type }}" 
            class="form-control @error($name) is-invalid @enderror" 
            id="{{ $name }}"
            name="{{ $name }}" 
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $readonly ? 'readonly' : '' }}
            {{ $required ? 'required' : '' }}
        />
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
