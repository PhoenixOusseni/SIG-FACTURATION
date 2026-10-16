@props([
    'title' => 'Tableau de données',
    'id' => 'dataTable',
    'striped' => true,
    'hover' => true,
    'bordered' => true,
    'responsive' => true
])

<div class="card shadow-sm">
    <div class="card-header bg-gradient py-3">
        <h5 class="mb-0 text-dark fw-bold">{{ $title }}</h5>
    </div>
    
    <div class="card-body p-0">
        @if($responsive)
            <div class="table-responsive">
        @endif
        
        <table 
            id="{{ $id }}" 
            class="table mb-0 
                @if($striped) table-striped @endif
                @if($hover) table-hover @endif
                @if($bordered) table-bordered @endif
            "
        >
            {{ $slot }}
        </table>
        
        @if($responsive)
            </div>
        @endif
    </div>
</div>
