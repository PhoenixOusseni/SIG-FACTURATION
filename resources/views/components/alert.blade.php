@props([
    'type' => 'info', // info, success, warning, danger
    'title' => '',
    'dismissible' => true,
    'icon' => true
])

@php
    $colors = [
        'success' => ['bg' => 'alert-success', 'icon' => 'fa-check-circle'],
        'danger' => ['bg' => 'alert-danger', 'icon' => 'fa-exclamation-circle'],
        'warning' => ['bg' => 'alert-warning', 'icon' => 'fa-exclamation-triangle'],
        'info' => ['bg' => 'alert-info', 'icon' => 'fa-info-circle'],
    ];
    $color = $colors[$type] ?? $colors['info'];
@endphp

<div class="alert {{ $color['bg'] }} alert-{{ $type }}{{ $dismissible ? ' alert-dismissible fade show' : '' }}" role="alert">
    @if($title)
        <strong>
            @if($icon)
                <i class="fas {{ $color['icon'] }}"></i>
            @endif
            {{ $title }}
        </strong>
    @elseif($icon)
        <i class="fas {{ $color['icon'] }}"></i>
    @endif
    
    {{ $slot }}
    
    @if($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>
