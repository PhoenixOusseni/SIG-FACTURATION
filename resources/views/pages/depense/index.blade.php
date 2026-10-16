@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Dépenses</title>
@endsection

@section('style')
    @include('partials.style')
@endsection

@section('content')
    <main>
        <x-page-header icon="credit-card" title="Gestion des dépenses">
            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#createDepenseModal">
                <i class="fas fa-plus me-1"></i> Nouvelle dépense
            </button>
        </x-page-header>

        <div class="container-xl px-4 mt-n10">
            <x-form-errors class="mb-3" />

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Liste des dépenses</span>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary" style="font-size: 0.95rem;">
                            Total : {{ number_format($total, 2, ',', ' ') }} FCFA
                        </span>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#createDepenseModal">
                            <i class="fas fa-plus me-1"></i> Nouvelle dépense
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div style="margin-bottom: 1rem;">
                        <input type="text" placeholder="Rechercher une dépense..." class="form-control"
                            id="searchInput" onkeyup="searchTable()" />
                    </div>
                    @if ($collection->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Motif</th>
                                        <th>Fournisseur</th>
                                        <th class="text-end">Montant (FCFA)</th>
                                        <th style="width: 100px; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($collection as $item)
                                        <tr>
                                            <td>
                                                <span style="font-weight: 600; color: var(--primary-color);">
                                                    {{ $item->date ? $item->date->format('d/m/Y') : '—' }}
                                                </span>
                                            </td>
                                            <td>{{ $item->motif }}</td>
                                            <td>{{ $item->fournisseur->assujeti ?? '—' }}</td>
                                            <td class="text-end">
                                                <span class="badge bg-info" style="font-size: 0.9rem;">
                                                    {{ number_format($item->montant, 2, ',', ' ') }}
                                                </span>
                                            </td>
                                            <td class="text-center d-flex justify-content-center" style="gap: 0.5rem;">
                                                <a href="{{ route('module_depense.show', [$item->id]) }}"
                                                    class="btn btn-sm btn-success"
                                                    style="border-radius: 50%; width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            {{ $collection->links() }}
                        </div>
                    @else
                        <div style="text-align: center; padding: 3rem 1rem; color: var(--text-light);">
                            <i data-feather="inbox"
                                style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>Aucune dépense trouvée</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Modal création dépense --}}
        <div class="modal fade" id="createDepenseModal" tabindex="-1" aria-labelledby="createDepenseModalLabel"
            aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('module_depense.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="createDepenseModalLabel">Ajouter une nouvelle dépense</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer">X</button>
                        </div>
                        <div class="modal-body">
                            <x-form-errors />
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Motif <span class="text-danger">*</span></label>
                                    <input class="form-control" name="motif" type="text"
                                        placeholder="Motif de la dépense" value="{{ old('motif') }}" required />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date <span class="text-danger">*</span></label>
                                    <input class="form-control" name="date" type="date"
                                        value="{{ old('date', date('Y-m-d')) }}" required />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Montant <span class="text-danger">*</span></label>
                                    <input class="form-control" name="montant" type="number" step="0.01" min="0"
                                        placeholder="0.00" value="{{ old('montant') }}" required />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Fournisseur</label>
                                    <select class="form-select" name="fournisseurs_id">
                                        <option value="">-- Selectionner un fournisseur --</option>
                                        @foreach ($fournisseurs as $fournisseur)
                                            <option value="{{ $fournisseur->id }}"
                                                {{ old('fournisseurs_id') == $fournisseur->id ? 'selected' : '' }}>
                                                {{ $fournisseur->assujeti }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-save me-2"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('script')
    <script>
        @if ($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            var el = document.getElementById('createDepenseModal');
            if (el && window.bootstrap) new bootstrap.Modal(el).show();
        });
        @endif
    </script>
@endsection
