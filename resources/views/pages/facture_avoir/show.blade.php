@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Détails Facture d'Avoir</title>
@endsection

@section('style')
    @include('partials.style')
@endsection

@section('content')
    <main>
        <header class="page-header pb-10">
            <div class="container-xl px-4">
                <div class="page-header-content pt-4">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mt-4">
                            <h1 class="page-header-title">
                                <div class="page-header-icon"><i data-feather="credit-card"></i></div>
                                Détails de la facture d'avoir
                            </h1>
                        </div>
                        <div class="col-12 col-xl-auto mt-4">
                            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                                @if (!$facture_avoir->is_fiscal_locked)
                                    <form action="{{ route('module_facture_avoir.certifier', $facture_avoir->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Fiscaliser cet avoir via le bridge SFE (FA/EA) ?');">
                                        @csrf
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-stamp me-1"></i> Fiscaliser (SFE)
                                        </button>
                                    </form>
                                    <a href="{{ route('module_facture_avoir.edit', $facture_avoir->id) }}" class="btn btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @else
                                    <span class="badge bg-success align-self-center px-3 py-2">Fiscalisé / verrouillé</span>
                                @endif
                                <a href="{{ route('print_facture_avoir', $facture_avoir->id) }}" class="btn btn-success" target="_blank">
                                    <i class="fas fa-print"></i>
                                </a>
                                <a href="{{ route('module_facture_avoir.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="container-xl px-4 mt-n10">
            <div class="card mb-4">
                <div class="card-header">
                    Informations générales
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Code:</strong> <span style="color: var(--primary-color); font-weight: 600;">{{ $facture_avoir->code }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Référence:</strong> {{ $facture_avoir->reference }}
                        </div>
                        <div class="col-md-3">
                            <strong>Date:</strong> {{ $facture_avoir->date }}
                        </div>
                        <div class="col-md-3">
                            <strong>Statut:</strong> 
                            <span class="badge bg-{{ $facture_avoir->statut === 'en attente' ? 'warning' : 'success' }}">
                                {{ ucfirst($facture_avoir->statut) }}
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Client:</strong> {{ $facture_avoir->Contribuable->assujeti ?? 'N/A' }}
                        </div>
                        <div class="col-md-4">
                            <strong>Facture d'origine:</strong> {{ $facture_avoir->Facture->code ?? 'N/A' }}
                        </div>
                        <div class="col-md-4">
                            <strong>Motif:</strong>
                            @switch($facture_avoir->motif)
                                @case('retour')
                                    <span class="badge bg-warning">Retour de marchandise</span>
                                @break
                                @case('erreur')
                                    <span class="badge bg-danger">Erreur sur la facture</span>
                                @break
                                @case('annulation')
                                    <span class="badge bg-dark">Annulation de vente</span>
                                @break
                                @case('remise')
                                    <span class="badge bg-info">Remise/Rabais accordé</span>
                                @break
                                @case('produit_defectueux')
                                    <span class="badge bg-secondary">Produit défectueux</span>
                                @break
                                @default
                                    <span class="badge bg-light">N/A</span>
                            @endswitch
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Nature DGI:</strong>
                            <span class="badge bg-primary">{{ $facture_avoir->nature_avoir ?: '—' }}</span>
                        </div>
                        <div class="col-md-4">
                            <strong>Signature DGI:</strong>
                            <code style="font-size:.85rem;">{{ $facture_avoir->dgi_signature ?: '—' }}</code>
                        </div>
                        <div class="col-md-4">
                            <strong>NIM / Compteur:</strong>
                            {{ $facture_avoir->dgi_nim_machine ?: '—' }}
                            @if ($facture_avoir->dgi_compteur)
                                / {{ $facture_avoir->dgi_compteur }}
                            @endif
                        </div>
                    </div>
                    @if($facture_avoir->objet)
                    <div class="row">
                        <div class="col-12">
                            <strong>Objet:</strong> {{ $facture_avoir->objet }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    Éléments de l'avoir
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Produit</th>
                                    <th class="text-center">Quantité</th>
                                    <th class="text-end">Prix unitaire</th>
                                    <th class="text-end">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($elements as $index => $element)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @if($element->Produit)
                                                <strong>{{ $element->Produit->code }}</strong> - {{ $element->Produit->designation }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $element->quantite }}</td>
                                        <td class="text-end">{{ number_format($element->prix_unitaire, 2, ',', ' ') }} XOF</td>
                                        <td class="text-end fw-bold">{{ number_format($element->montant_total ?? ($element->quantite * $element->prix_unitaire), 2, ',', ' ') }} XOF</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Aucun élément</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr style="border-top: 2px solid var(--border-color);">
                                    <td colspan="4" class="text-end fw-bold">Total:</td>
                                    <td class="text-end fw-bold fs-5" style="color: var(--primary-color);">
                                        {{ number_format($montant_total, 2, ',', ' ') }} XOF
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('script')
    <script>
        function refreshFeatherIcons() {
            if (typeof feather !== 'undefined') feather.replace();
        }

        $(document).ready(function() {
            refreshFeatherIcons();
        });
    </script>
@endsection
