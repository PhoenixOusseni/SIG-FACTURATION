@extends('layouts.master')

@section('title')
    <title>SIG - CFE | TVA</title>
@endsection

@section('style')
    @include('partials.style')
@endsection

@section('content')
    <main>
        <x-page-header icon="percent" title="Taxes sur la Valeur Ajoutée (TVA)"></x-page-header>
        <!-- Main page content-->

        <div class="container-xl px-4 mt-n10">
            <!-- Liste des TVA -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Liste des TVA</span>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#createTvaModal">
                        <i class="fa fa-plus"></i>&nbsp;Ajouter
                    </button>
                </div>
                <div class="card-body">
                    <div style="margin-bottom: 1rem;">
                        <input type="text" placeholder="Rechercher une TVA..." class="form-control"
                            id="searchInput" onkeyup="searchTable()" />
                    </div>
                    @if ($collection->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Intitulé</th>
                                        <th>Compte TVA</th>
                                        <th>Taux (%)</th>
                                        <th style="width: 100px; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($collection as $item)
                                        <tr>
                                            <td><span
                                                    style="font-weight: 600; color: var(--primary-color);">{{ $item->code }}</span>
                                            </td>
                                            <td>{{ $item->intitule }}</td>
                                            <td>{{ $item->compte_tva }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ $item->taux }} %</span>
                                            </td>
                                            <td class="text-center d-flex justify-content-center" style="gap: 0.5rem;">
                                                <a href="{{ route('module_tva.show', [$item->id]) }}" class="btn btn-sm btn-success" style="border-radius: 50%; width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
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
                            <i data-feather="inbox" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>Aucune TVA trouvée</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Modal d'ajout -->
        <div class="modal fade" id="createTvaModal" tabindex="-1" aria-labelledby="createTvaModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <form action="{{ route('module_tva.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="createTvaModalLabel">Ajouter une nouvelle TVA</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
                        </div>
                        <div class="modal-body">
                            <x-form-errors />
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label">Code <span class="text-danger">*</span></label>
                                        <input class="form-control" name="code" type="text" placeholder="TVA20"
                                            value="{{ Request::old('code') }}" required />
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label class="form-label">Intitulé <span class="text-danger">*</span></label>
                                        <input class="form-control" name="intitule" type="text"
                                            placeholder="Intitulé de la TVA" value="{{ Request::old('intitule') }}"
                                            required />
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label">Compte TVA</label>
                                        <input class="form-control" name="compte_tva" type="number"
                                            placeholder="44566" value="{{ Request::old('compte_tva') }}" />
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label">Taux (%) <span class="text-danger">*</span></label>
                                        <input class="form-control" name="taux" type="number" step="0.01" min="0"
                                            max="100" placeholder="20" value="{{ Request::old('taux') }}" required />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i> Annuler
                            </button>
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
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('createTvaModal');
            if (el && window.bootstrap) new bootstrap.Modal(el).show();
        });
        @endif
    </script>
@endsection
