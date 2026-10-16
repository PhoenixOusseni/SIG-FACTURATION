@props([
    'dismissible' => true,
    'title' => 'Veuillez corriger les erreurs suivantes',
])

@php
    $formError = session('form_error');
    $hasBag = $errors->any() || !empty($formError);
@endphp

@if ($hasBag)
    <div {{ $attributes->merge(['class' => 'alert alert-danger alert-validation' . ($dismissible ? ' alert-dismissible fade show' : ''), 'role' => 'alert']) }}>
        <strong>
            <i class="fas fa-exclamation-circle me-1"></i>
            {{ $title }}
        </strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
            @if ($formError)
                <li>{{ $formError }}</li>
            @endif
        </ul>
        @if ($dismissible)
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        @endif
    </div>
@endif
