@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Liste des Factures d'Avoir</title>
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
                                <div class="page-header-icon"><i data-feather="list"></i></div>
                                Liste de toutes les factures d'avoir
                            </h1>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="container-xl px-4 mt-n10">
            <div class="card mb-4">
                <div class="card-body">
                    <div style="margin-bottom: 1rem;">
                        <input type="text" placeholder="Rechercher une facture d'avoir..." class="form-control"
                            id="searchInput" onkeyup="searchTable()" />
                    </div>
                    @if ($collection->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Référence</th>
                                        <th>Motif</th>
                                        <th>Client</th>
                                        <th>Facture d'origine</th>
                                        <th>Utilisateur</th>
                                        <th>Date</th>
                                        <th style="width: 80px; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($collection as $avoir)
                                        <tr>
                                            <td><span
                                                    style="font-weight: 600; color: var(--primary-color);">{{ $avoir->code }}</span>
                                            </td>
                                            <td>{{ $avoir->reference }}</td>
                                            <td>
                                                @switch($avoir->motif)
                                                    @case('retour')
                                                        <span class="badge bg-warning">Retour</span>
                                                    @break
                                                    @case('erreur')
                                                        <span class="badge bg-danger">Erreur</span>
                                                    @break
                                                    @case('annulation')
                                                        <span class="badge bg-dark">Annulation</span>
                                                    @break
                                                    @case('remise')
                                                        <span class="badge bg-info">Remise</span>
                                                    @break
                                                    @case('produit_defectueux')
                                                        <span class="badge bg-secondary">Défectueux</span>
                                                    @break
                                                    @default
                                                        <span class="badge bg-light">N/A</span>
                                                @endswitch
                                            </td>
                                            <td>{{ $avoir->Contribuable->assujeti ?? 'N/A' }}</td>
                                            <td>{{ $avoir->Facture->code ?? 'N/A' }}</td>
                                            <td>{{ $avoir->User->nom ?? 'N/A' }} {{ $avoir->User->prenom ?? '' }}</td>
                                            <td>{{ $avoir->date ?? 'N/A' }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('module_facture_avoir.show', [$avoir->id]) }}"
                                                    class="btn btn-sm btn-primary"
                                                    style="border-radius: 50%; width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="text-align: center; padding: 3rem 1rem; color: var(--text-light);">
                            <i data-feather="inbox"
                                style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>Aucune facture d'avoir trouvée</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
@endsection

@section('script')
    <script>
        function searchTable() {
            let input = document.getElementById("searchInput");
            let filter = input.value.toUpperCase();
            let table = document.querySelector("table");
            let tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName("td");
                let match = false;
                for (let j = 0; j < td.length; j++) {
                    if (td[j].innerHTML.toUpperCase().indexOf(filter) > -1) {
                        match = true;
                        break;
                    }
                }
                tr[i].style.display = match ? "" : "none";
            }
        }
    </script>
@endsection
