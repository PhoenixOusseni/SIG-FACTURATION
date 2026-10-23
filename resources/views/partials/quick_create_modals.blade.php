{{--
  Shared quick-create modals for invoice forms.
  Expects optional: $categories, $tvas, $bics
--}}
@php
    $categories = $categories ?? collect();
    $tvas = $tvas ?? collect();
    $bics = $bics ?? collect();
@endphp

{{-- Quick create client --}}
<div class="modal fade modal-quick-create" id="quickCreateClientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="quickCreateClientForm" action="{{ route('module_contribuable.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nouveau client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer">X</button>
                </div>
                <div class="modal-body">
                    <div class="quick-create-errors d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="small fw-bold">Type client DGI <span class="text-danger">*</span></label>
                            <select name="type_client" id="qc_type_client" class="form-select">
                                <option value="CC" selected>CC — Client Comptant (Consommateur)</option>
                                <option value="PM">PM — Personne morale (Entreprise)</option>
                                <option value="PP">PP — Personne physique (Particulier)</option>
                                <option value="PC">PC — Personne physique commerçante</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold">Catégorie tarifaire <span class="text-danger">*</span></label>
                            <select name="categories_id" class="form-select" required>
                                <option value="">Sélectionner une catégorie</option>
                                @foreach ($categories as $categorie)
                                    <option value="{{ $categorie->id }}">{{ $categorie->code }} - {{ $categorie->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold">Nom / Raison sociale <span class="text-danger">*</span></label>
                            <input class="form-control" name="assujeti" id="qc_assujeti" type="text" placeholder="Ex: CLIENT COMPTANT" required>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold">N° Téléphone <span class="qc-req text-danger d-none">*</span></label>
                            <input class="form-control" name="telephone" id="qc_telephone" type="tel" placeholder="+226 XX XX XX XX">
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold">N° IFU <span class="qc-req text-danger d-none">*</span></label>
                            <input class="form-control" name="ifu" id="qc_ifu" type="text" placeholder="Ex: 00012345A">
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold">N° RCCM <span class="qc-req text-danger d-none">*</span></label>
                            <input class="form-control" name="rccm" id="qc_rccm" type="text" placeholder="Ex: BF OUA 2024 B 1234">
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold">Adresse géographique <span class="qc-req text-danger d-none">*</span></label>
                            <input class="form-control" name="adresse" id="qc_adresse" type="text" placeholder="Ville, Secteur">
                        </div>
                        <div class="col-md-6">
                            <label class="small">Email</label>
                            <input class="form-control" name="email" type="email" placeholder="email@exemple.com">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Quick create product --}}
<div class="modal fade modal-quick-create" id="quickCreateProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="quickCreateProductForm" action="{{ route('module_produit.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nouveau produit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer">X</button>
                </div>
                <div class="modal-body">
                    <div class="quick-create-errors d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="small">Code <span class="text-danger">*</span></label>
                            <input class="form-control" name="code" type="text" required>
                        </div>
                        <div class="col-md-5">
                            <label class="small">Désignation <span class="text-danger">*</span></label>
                            <input class="form-control" name="designation" type="text" required>
                        </div>
                        <div class="col-md-2">
                            <label class="small">Unité</label>
                            <input class="form-control" name="unite" type="text" placeholder="pièce">
                        </div>
                        <div class="col-md-2">
                            <label class="small">Prix unitaire</label>
                            <input class="form-control" name="prix_unitaire" type="number" min="0" step="0.01" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="small">Stock initial</label>
                            <input class="form-control" name="qte" type="number" min="0" step="0.01" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="small">TVA</label>
                            <select name="tva_id" class="form-select">
                                <option value="">—</option>
                                @foreach ($tvas as $tva)
                                    <option value="{{ $tva->id }}">{{ $tva->intitule }} ({{ $tva->taux }}%)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="small">Montant TS (FCFA)</label>
                            <input class="form-control" name="montant_ts" type="number" min="0" step="0.01" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="small">Type produit</label>
                            <select name="famille_produit" class="form-select">
                                <option value="">—</option>
                                <option value="LOCBIE">LOCBIE — Bien Local</option>
                                <option value="IMPBIE">IMPBIE — Bien importé</option>
                                <option value="LOCSER">LOCSER — Service Local</option>
                                <option value="IMPSER">IMPSER — Service Import</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Quick create mode paiement --}}
<div class="modal fade modal-quick-create" id="quickCreateModePaieModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="quickCreateModePaieForm" action="{{ route('module_modepaie.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nouveau mode de paiement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer">X</button>
                </div>
                <div class="modal-body">
                    <div class="quick-create-errors d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="small">Code <span class="text-danger">*</span></label>
                            <input class="form-control" name="code" type="text" required maxlength="50">
                        </div>
                        <div class="col-md-8">
                            <label class="small">Libellé <span class="text-danger">*</span></label>
                            <input class="form-control" name="libelle" type="text" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Quick create fournisseur --}}
<div class="modal fade modal-quick-create" id="quickCreateFournisseurModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="quickCreateFournisseurForm" action="{{ route('module_fournisseur.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nouveau fournisseur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer">X</button>
                </div>
                <div class="modal-body">
                    <div class="quick-create-errors d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="small">Désignation <span class="text-danger">*</span></label>
                            <input class="form-control" name="assujeti" type="text" required>
                        </div>
                        <div class="col-md-6">
                            <label class="small">Catégorie <span class="text-danger">*</span></label>
                            <select name="categories_id" class="form-select" required>
                                <option value="">Sélectionner</option>
                                @foreach ($categories as $categorie)
                                    <option value="{{ $categorie->id }}">{{ $categorie->code }} - {{ $categorie->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="small">Téléphone</label>
                            <input class="form-control" name="telephone" type="text">
                        </div>
                        <div class="col-md-4">
                            <label class="small">IFU</label>
                            <input class="form-control" name="ifu" type="text">
                        </div>
                        <div class="col-md-4">
                            <label class="small">RCCM</label>
                            <input class="form-control" name="rccm" type="text">
                        </div>
                        <div class="col-md-12">
                            <label class="small">Adresse</label>
                            <input class="form-control" name="adresse" type="text">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const qcType = document.getElementById('qc_type_client');
    if (!qcType) return;
    function updateQcRules() {
        const val = qcType.value;
        const reqStars = document.querySelectorAll('#quickCreateClientModal .qc-req');
        const phone = document.getElementById('qc_telephone');
        const ifu = document.getElementById('qc_ifu');
        const rccm = document.getElementById('qc_rccm');
        const addr = document.getElementById('qc_adresse');
        const name = document.getElementById('qc_assujeti');

        if (val === 'PM' || val === 'PC') {
            reqStars.forEach(s => s.classList.remove('d-none'));
            if (phone) phone.required = true;
            if (ifu) ifu.required = true;
            if (rccm) rccm.required = true;
            if (addr) addr.required = true;
            if (name && name.value === 'CLIENT COMPTANT') name.value = '';
        } else if (val === 'PP') {
            reqStars.forEach(s => s.classList.add('d-none'));
            const phoneStar = phone?.parentElement?.querySelector('.qc-req');
            const addrStar = addr?.parentElement?.querySelector('.qc-req');
            if (phoneStar) phoneStar.classList.remove('d-none');
            if (addrStar) addrStar.classList.remove('d-none');
            if (phone) phone.required = true;
            if (addr) addr.required = true;
            if (ifu) ifu.required = false;
            if (rccm) rccm.required = false;
            if (name && name.value === 'CLIENT COMPTANT') name.value = '';
        } else {
            // CC
            reqStars.forEach(s => s.classList.add('d-none'));
            if (phone) phone.required = false;
            if (ifu) ifu.required = false;
            if (rccm) rccm.required = false;
            if (addr) addr.required = false;
            if (name && !name.value.trim()) name.value = 'CLIENT COMPTANT';
        }
    }
    qcType.addEventListener('change', updateQcRules);
    updateQcRules();
});
</script>

