@props([
    'label' => '',
    'name' => '',
    'options' => [],
    'value' => '',
    'placeholder' => 'Sélectionner...',
    'required' => false,
    'col' => 'col-lg-6 col-md-12',
    'help' => '',
    'multiple' => false
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
        <select 
            class="form-select @error($name) is-invalid @enderror" 
            id="{{ $name }}"
            name="{{ $name }}{{ $multiple ? '[]' : '' }}"
            {{ $required ? 'required' : '' }}
            {{ $multiple ? 'multiple' : '' }}
        >
            @if(!$multiple)
                <option value="">{{ $placeholder }}</option>
            @endif
            @foreach($options as $optValue => $optLabel)
                <option value="{{ $optValue }}" {{ (is_array(old($name, $value)) ? in_array($optValue, old($name, $value)) : old($name, $value) == $optValue) ? 'selected' : '' }}>
                    {{ $optLabel }}
                </option>
            @endforeach
        </select>
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
