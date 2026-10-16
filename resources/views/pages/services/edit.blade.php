@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Modifier un dépôt</title>
@endsection

@section('style')
    @include('partials.style')
@endsection

@section('content')
    <header class="page-header page-header-dark header-gradient pb-10">
        <div class="container-xl px-4">
            <div class="page-header-content pt-4">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto mt-4">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="edit"></i></div>
                            Modifier un dépôt
                        </h1>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- Main page content-->
    <div class="container-xl px-4 mt-n10">
        <!-- Example Charts for Dashboard Demo-->
        <div class="row">
            <div class="col-lg-12">
                <!-- Tabbed dashboard card example-->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="col-sm-12 mb-3">
                            <a href="{{ route('gestion_service.index') }}" class="btn btn-light"><i
                                    data-feather="arrow-left"></i>&thinsp;&thinsp;
                                Retour à la liste</a>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <h2 class="mb-4">Modifier un dépôt</h2>
                                <form method="POST" action="{{ route('gestion_service.update', $finds->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="mb-3">
                                        <label for="code" class="form-label">Code</label>
                                        <input type="text" class="form-control" id="code" name="code"
                                            value="{{ $finds->code }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="libelle" class="form-label">Libellé</label>
                                        <input type="text" class="form-control" id="libelle" name="libelle"
                                            value="{{ $finds->libelle }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3">{{ $finds->description }}</textarea>
                                    </div>

                                    <button type="submit" class="btn btn-dark"><i data-feather="edit"></i>&thinsp;&thinsp;
                                        Modifier</button>
                                    <button type="reset" class="btn btn-danger"><i data-feather="x"></i>&thinsp;&thinsp;
                                        Annuler</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
