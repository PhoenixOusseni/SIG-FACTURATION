@props([
    'label' => '',
    'name' => '',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'rows' => 3,
    'col' => 'col-lg-12 col-md-12',
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
        <textarea 
            class="form-control @error($name) is-invalid @enderror" 
            id="{{ $name }}"
            name="{{ $name }}" 
            rows="{{ $rows }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
        >{{ old($name, $value) }}</textarea>
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
