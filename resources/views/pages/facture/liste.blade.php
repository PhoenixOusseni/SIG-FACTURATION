@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Facturation</title>
@endsection

@section('style')
    @include('partials.style')
@endsection

@section('content')
    <main>
        <x-page-header icon="filter" title="Liste des factures"></x-page-header>
        <!-- Main page content-->

        <div class="container-xl px-4 mt-n10">
            <div class="row">
                <div class="col-lg-12">
                    <!-- Tabbed dashboard card example-->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- Tabbed dashboard card example-->
                                    <div class="card mb-4">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between mb-3">
                                                <h2 class="h4 mb-0">Liste des factures</h2>
                                                <div>
                                                    <input type="text" placeholder="Rechercher..." class="form-control"
                                                        id="searchInput" onkeyup="searchTable()">
                                                </div>
                                            </div>
                                            <div class="sbp-preview-content">
                                                <table class="table table-bordered table-hover table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Code</th>
                                                            <th>Réference</th>
                                                            <th>Client</th>
                                                            <th>Type</th>
                                                            <th>Mode de Paiement</th>
                                                            <th>PSVB</th>
                                                            <th>Ligne service</th>
                                                            <th>Date</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($collection as $facture)
                                                            <tr>
                                                                <td>{{ $facture->code }}</td>
                                                                <td>{{ $facture->reference }}</td>
                                                                <td>{{ $facture->Contribuable->assujeti }}</td>
                                                                <td>
                                                                    @if($facture->Typefacture)
                                                                        <span class="badge bg-primary">{{ $facture->Typefacture->code }}</span>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($facture->ModePaie)
                                                                        <span class="badge bg-info">{{ $facture->ModePaie->code }}</span>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($facture->Bic)
                                                                        <span class="badge bg-warning">{{ $facture->Bic->code }}</span>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>{{ $facture->Service->libelle ?? 'N/A' }}</td>
                                                                <td>{{ $facture->date }}</td>
                                                                <td class="d-flex justify-content-between">
                                                                    <a href="{{ route('module_ordre_facture.show', [$facture->id]) }}">
                                                                        <i class="fa fa-eye text-success" aria-hidden="true"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
