@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Caisses</title>
@endsection

@section('style')
    @include('partials.style')
@endsection

@section('content')
<main>
    <header class="page-header page-header-dark pb-10">
        <div class="container-xl px-4">
            <div class="page-header-content pt-4">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto mt-4">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="box"></i></div>
                            Caisses (Comptoir)
                        </h1>
                    </div>
                    <div class="col-12 col-xl-auto mt-4">
                        <a href="{{ route('module_comptoir.pos') }}" class="btn btn-light btn-sm">Point de vente</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container-xl px-4 mt-n10">
        <x-form-errors class="mb-3" />
        <div class="row">
            <div class="col-lg-7">
                <div class="card mb-3 shadow-sm">
                    <div class="card-header">Liste des caisses</div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Libellé</th>
                                    <th>Dépôt</th>
                                    <th>Active</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($caisses as $c)
                                    <tr>
                                        <td><code>{{ $c->code }}</code></td>
                                        <td>{{ $c->libelle }}</td>
                                        <td>{{ optional($c->Service)->libelle }}</td>
                                        <td>{{ $c->active ? 'Oui' : 'Non' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-muted text-center py-4">Aucune caisse</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-header">Nouvelle caisse</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('module_caisse.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Code (court, ex. C01)</label>
                                <input type="text" name="code" class="form-control" required maxlength="20" placeholder="C01">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Libellé</label>
                                <input type="text" name="libelle" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Dépôt (service)</label>
                                <select name="service_id" class="form-select" required>
                                    <option value="">—</option>
                                    @foreach ($services as $s)
                                        <option value="{{ $s->id }}">{{ $s->libelle }} ({{ $s->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="active" value="1" id="activeCaisse" checked>
                                <label class="form-check-label" for="activeCaisse">Active</label>
                            </div>
                            <button type="submit" class="btn btn-primary">Créer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@section('script')
    @include('partials.script')
@endsection
