@props([
    'title' => 'Formulaire',
    'submitText' => 'Enregistrer',
    'submitClass' => 'btn-primary',
    'action' => '',
    'method' => 'POST',
    'enctype' => 'application/x-www-form-urlencoded',
    'id' => '',
    'backButton' => false,
    'backUrl' => '#'
])

<div class="card shadow-sm">
    <div class="card-header bg-gradient py-3">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0 text-dark fw-bold">{{ $title }}</h5>
            </div>
            @if($backButton)
                <div class="col-auto">
                    <a href="{{ $backUrl }}" class="btn btn-sm btn-light">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            @endif
        </div>
    </div>
    
    <div class="card-body p-4">
        <form 
            @if($id) id="{{ $id }}" @endif
            action="{{ $action }}" 
            method="{{ $method === 'GET' ? 'GET' : 'POST' }}"
            @if($enctype !== 'application/x-www-form-urlencoded') enctype="{{ $enctype }}" @endif
            novalidate
        >
            @if($method !== 'GET')
                @csrf
                @method($method)
            @endif
            
            {{ $slot }}
            
            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                <button type="submit" class="btn {{ $submitClass }}">
                    <i class="fas fa-save"></i>
                    {{ $submitText }}
                </button>
                @if($backButton)
                    <a href="{{ $backUrl }}" class="btn btn-light">
                        Annuler
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>
