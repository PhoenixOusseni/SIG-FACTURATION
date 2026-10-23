@extends('layouts.master')

@section('title')
    <title>SIG-FACTURATION | Paramètres Entreprise</title>
@endsection

@section('style')
    @include('partials.style')
@endsection

@section('content')
    <main>
        <x-page-header icon="briefcase" title="Paramètres Entreprise" subtitle="Identité juridique de l'entreprise, coordonnées légales, régime d'imposition et cadastre">
            <form action="{{ route('entete.recuperer-mcf') }}" method="POST" class="d-inline" onsubmit="return confirm('Interroger le boîtier MCF physique pour extraire automatiquement l\'IFU et le NIM ?')">
                @csrf
                <button type="submit" class="btn btn-primary shadow-sm fw-bold">
                    <i class="fas fa-sync-alt me-1"></i> Récupérer IFU & NIM MCF
                </button>
            </form>
            <a href="{{ route('mcf.index') }}" class="btn btn-outline-secondary fw-semibold">
                <i class="fas fa-microchip me-1"></i> Console Boîtier MCF →
            </a>
        </x-page-header>

        <div class="container-xl px-4" style="margin-bottom: 5rem;">
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="m-0 fw-bold text-dark"><i class="fas fa-building text-primary me-2"></i> Identité de l'Entreprise</h5>
                    <small class="text-muted">Renseignez les données légales et fiscales imprimées sur vos factures normalisées</small>
                </div>

                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('module_entete.update', [$entete->id]) }}" method="POST" enctype="multipart/form-data">
                        @method('PATCH')
                        @csrf

                        <!-- Section 1 : Coordonnées Générales -->
                        <div class="row">
                            <div class="col-lg-4 col-md-12">
                                <div class="mb-3">
                                    <label class="small mb-1 fw-bold">Dénomination Sociale <span class="text-danger">*</span></label>
                                    <input class="form-control" name="denomination" value="{{ old('denomination', $entete->denomination) }}" type="text" required placeholder="Ex: ENTREPRISE COMMERCIALE DU FASO" />
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-12">
                                <div class="mb-3">
                                    <label class="small mb-1">Activité Principale</label>
                                    <input class="form-control" name="activite" value="{{ old('activite', $entete->activite) }}" type="text" placeholder="Ex: Commerce général, import-export" />
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-12">
                                <div class="mb-3">
                                    <label class="small mb-1">Boîte Postale / Ville</label>
                                    <input class="form-control" name="postale" value="{{ old('postale', $entete->postale) }}" type="text" placeholder="Ex: 01 BP 1234 Ouagadougou 01" />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4 col-md-12">
                                <div class="mb-3">
                                    <label class="small mb-1">Téléphone</label>
                                    <input class="form-control" name="telephone" value="{{ old('telephone', $entete->telephone) }}" type="text" placeholder="Ex: +226 25 30 00 00" />
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-12">
                                <div class="mb-3">
                                    <label class="small mb-1">Fax</label>
                                    <input class="form-control" name="fax" value="{{ old('fax', $entete->fax) }}" type="text" placeholder="Optionnel" />
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-12">
                                <div class="mb-3">
                                    <label class="small mb-1">E-mail Officiel</label>
                                    <input class="form-control" name="email" value="{{ old('email', $entete->email) }}" type="email" placeholder="contact@entreprise.bf" />
                                </div>
                            </div>
                        </div>

                        <!-- Section 2 : Fiscalité DGI & Adresse Cadastrale -->
                        <div class="border-top pt-3 mt-2 mb-3">
                            <h6 class="fw-bold text-primary mb-3"><i class="fas fa-file-invoice-dollar me-2"></i> Référentiels Fiscaux DGI Obligatoires</h6>
                            <div class="row">
                                <div class="col-lg-3 col-md-6">
                                    <div class="mb-3">
                                        <label class="small mb-1 fw-bold">IFU (Numéro Fiscal Unique) <span class="text-danger">*</span></label>
                                        <input class="form-control font-monospace fw-bold" name="ifu" value="{{ old('ifu', $entete->ifu) }}" type="text" required placeholder="Ex: 00012345X" />
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="mb-3">
                                        <label class="small mb-1">RCCM</label>
                                        <input class="form-control font-monospace" name="rccm" value="{{ old('rccm', $entete->rccm) }}" type="text" placeholder="Ex: BF OUA 2020 B 1234" />
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="mb-3">
                                        <label class="small mb-1">Régime Fiscal</label>
                                        <select name="regime_fiscal" class="form-select">
                                            <option value="RNI" {{ old('regime_fiscal', $entete->regime_fiscal ?? 'RNI') == 'RNI' ? 'selected' : '' }}>RNI — Régime Normal d'Imposition</option>
                                            <option value="RSI" {{ old('regime_fiscal', $entete->regime_fiscal) == 'RSI' ? 'selected' : '' }}>RSI — Régime Simplifié d'Imposition</option>
                                            <option value="CME" {{ old('regime_fiscal', $entete->regime_fiscal) == 'CME' ? 'selected' : '' }}>CME — Contribution des Micro-Entreprises</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="mb-3">
                                        <label class="small mb-1">Division Fiscale / CDI</label>
                                        <input class="form-control" name="division_fiscal" value="{{ old('division_fiscal', $entete->division_fiscal) }}" type="text" placeholder="Ex: DME / CDI OUAGA II" />
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6 col-md-12">
                                    <div class="mb-3">
                                        <label class="small mb-1 fw-bold text-dark">
                                            Adresse Cadastrale (DGI) <span class="text-danger">*</span>
                                            <span class="badge bg-secondary ms-1">Format imposé : XXXX-YYY-ZZZZ</span>
                                        </label>
                                        <input 
                                            class="form-control font-monospace fw-bold" 
                                            name="infos_cadastrales" 
                                            id="infos_cadastrales_input"
                                            value="{{ old('infos_cadastrales', $entete->infos_cadastrales) }}" 
                                            type="text" 
                                            maxlength="13"
                                            placeholder="Ex: 0012-004-0018"
                                            title="Saisie simplifiée : tapez les 11 caractères, les tirets sont ajoutés automatiquement."
                                        />
                                        <small class="text-muted d-block mt-1">
                                            Saisie libre sans tirets : tapez simplement les 11 caractères (Section: 4, Lot: 3, Parcelle: 4). Les tirets s'insèrent automatiquement.
                                        </small>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-12">
                                    <div class="mb-3">
                                        <label class="small mb-1">Adresse Complète (Quittance SFE)</label>
                                        <input class="form-control" name="sfe_company_address" value="{{ old('sfe_company_address', $entete->sfe_company_address ?? ($entete->postale ?: $entete->infos_cadastrales)) }}" placeholder="Ex: Ouagadougou, Secteur 15, Rue 15.102" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3 : Identifiants Dispositif SFE & Boîtier MCF -->
                        <div class="border-top pt-3 mt-2 mb-3">
                            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-link me-2"></i> Liaison Dispositif Fiscal (SFE / MCF)</h6>
                            <div class="row">
                                <div class="col-lg-4 col-md-6">
                                    <div class="mb-3">
                                        <label class="small mb-1">Identifiant du SFE (ISF)</label>
                                        <input class="form-control font-monospace" name="identifiant_sfe" value="{{ old('identifiant_sfe', $entete->identifiant_sfe) }}" type="text" placeholder="Ex: AB01710090" />
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="mb-3">
                                        <label class="small mb-1">NIM Actuel du Boîtier MCF (Synchronisé)</label>
                                        <input class="form-control font-monospace bg-light" name="nim_mcf" value="{{ old('nim_mcf', $entete->nim_mcf) }}" type="text" readonly placeholder="Récupéré depuis le MCF" />
                                        <small class="text-muted">Pour modifier le port ou reconnecter le boîtier, utilisez la <a href="{{ route('mcf.index') }}">Console Boîtier MCF</a></small>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-12">
                                    <div class="mb-3">
                                        <label class="small mb-1">Compte(s) Bancaire(s)</label>
                                        <input class="form-control" name="comptes_bancaires" value="{{ old('comptes_bancaires', $entete->comptes_bancaires) }}" type="text" placeholder="Ex: CORIS: BF00-1234-5678 / BOA: BF00-..." />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4 : Personnalisation Document Facture -->
                        <div class="border-top pt-3 mt-2 mb-4">
                            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-image me-2"></i> Logo & Mention de Bas de Page</h6>
                            <div class="row align-items-center">
                                <div class="col-lg-8 col-md-12">
                                    <div class="mb-3">
                                        <label class="small mb-1">Pied de Page (Mentions légales imprimées en bas de facture)</label>
                                        <input class="form-control" name="pied_page" value="{{ old('pied_page', $entete->pied_page) }}" type="text" placeholder="Ex: Arrêté N° ... — Facture établie conformément à la législation fiscale en vigueur." />
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-12">
                                    <div class="mb-3">
                                        <label class="small mb-1">Logo de l'Entreprise</label>
                                        <input class="form-control" name="logo" type="file" accept="image/*" />
                                        @if ($entete->logo)
                                            <div class="mt-2 p-1 bg-light border rounded text-center">
                                                <img src="{{ asset('storage') . '/' . $entete->logo }}" class="img-fluid" style="max-height: 50px;" alt="Logo actuel">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                            <button class="btn btn-primary px-4 py-2 fw-bold" type="submit">
                                <i class="fas fa-save me-1"></i> Enregistrer les Paramètres
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
    document.addEventListener('DOMContentLoaded', function() {
        const cadastreInput = document.getElementById('infos_cadastrales_input');
        if (cadastreInput) {
            cadastreInput.addEventListener('input', function() {
                let raw = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
                if (raw.length > 11) raw = raw.substring(0, 11);
                
                let formatted = '';
                if (raw.length > 0) {
                    formatted += raw.substring(0, Math.min(raw.length, 4));
                }
                if (raw.length > 4) {
                    formatted += '-' + raw.substring(4, Math.min(raw.length, 7));
                }
                if (raw.length > 7) {
                    formatted += '-' + raw.substring(7, 11);
                }
                this.value = formatted;
            });
        }
    });
</script>
@endsection
