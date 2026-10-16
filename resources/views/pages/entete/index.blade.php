@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Entête</title>
@endsection

@section('style')
    @include('partials.style')
@endsection

@section('content')
    <main>
        <x-page-header icon="filter" title="Entête de la société"></x-page-header>

        <div class="container-xl px-4 mt-n10" style="margin-bottom: 8rem;">
            <div class="card mb-4">
                <div class="card-header">Informations de l'entête</div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div><br />
                    @endif

                    <form action="{{ route('module_entete.update', [$entete->id]) }}" method="POST" enctype="multipart/form-data">
                        @method('PATCH')
                        @csrf

                        @if ($entete->logo)
                            <div class="mb-3">
                                <img src="{{ asset('storage') . '/' . $entete->logo }}" class="w-25" alt="logo entête">
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-lg-4 col-md-12">
                                <div class="mb-3">
                                    <label class="small mb-1">Dénomination</label>
                                    <input class="form-control" name="denomination" value="{{ old('denomination', $entete->denomination) }}" type="text" />
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-12">
                                <div class="mb-3">
                                    <label class="small mb-1">Activité</label>
                                    <input class="form-control" name="activite" value="{{ old('activite', $entete->activite) }}" type="text" />
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-12">
                                <div class="mb-3">
                                    <label class="small mb-1">Boite postale</label>
                                    <input class="form-control" name="postale" value="{{ old('postale', $entete->postale) }}" type="text" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4 col-md-12">
                                <div class="mb-3">
                                    <label class="small mb-1">Téléphone</label>
                                    <input class="form-control" name="telephone" value="{{ old('telephone', $entete->telephone) }}" type="text" />
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-12">
                                <div class="mb-3">
                                    <label class="small mb-1">Fax</label>
                                    <input class="form-control" name="fax" value="{{ old('fax', $entete->fax) }}" type="text" />
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-12">
                                <div class="mb-3">
                                    <label class="small mb-1">E-mail</label>
                                    <input class="form-control" name="email" value="{{ old('email', $entete->email) }}" type="email" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-8 col-md-12">
                                <div class="mb-3">
                                    <label class="small mb-1">Pied de page</label>
                                    <input class="form-control" name="pied_page" value="{{ old('pied_page', $entete->pied_page) }}" type="text" />
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-12">
                                <div class="mb-3">
                                    <label class="small mb-1">Logo</label>
                                    <input class="form-control" name="logo" type="file" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-3 col-md-12">
                                <div class="mb-3">
                                    <label class="small mb-1">IFU</label>
                                    <input class="form-control" name="ifu" value="{{ old('ifu', $entete->ifu) }}" type="text" placeholder="Identifiant Fiscal Unique" />
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-12">
                                <div class="mb-3">
                                    <label class="small mb-1">RCCM</label>
                                    <input class="form-control" name="rccm" value="{{ old('rccm', $entete->rccm) }}" type="text" />
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-12">
                                <div class="mb-3">
                                    <label class="small mb-1">Régime fiscal</label>
                                    <input class="form-control" name="regime_fiscal" value="{{ old('regime_fiscal', $entete->regime_fiscal) }}" type="text" placeholder="Ex: RNI" />
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-12">
                                <div class="mb-3">
                                    <label class="small mb-1">Division fiscale (service d'impôts)</label>
                                    <input class="form-control" name="division_fiscal" value="{{ old('division_fiscal', $entete->division_fiscal) }}" type="text" placeholder="Ex: DME4" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6 col-md-12">
                                <div class="mb-3">
                                    <label class="small mb-1">Identifiant du SFE (ISF)</label>
                                    <input class="form-control" name="identifiant_sfe" value="{{ old('identifiant_sfe', $entete->identifiant_sfe) }}" type="text" placeholder="Ex: AB01710090" />
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <div class="mb-3">
                                    <label class="small mb-1">Compte(s) bancaire(s)</label>
                                    <input class="form-control" name="comptes_bancaires" value="{{ old('comptes_bancaires', $entete->comptes_bancaires) }}" type="text" placeholder="Ex: BF00-1234-5678-90" />
                                </div>
                            </div>
                        </div>

                        @include('pages.entete.sfe-settings')
                        <button class="btn btn-1" type="submit">
                            <i class="fas fa-save"></i>
                            &nbsp; &nbsp;Enregistrer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection
