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
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <form id="quickCreateClientForm" action="{{ route('module_contribuable.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold text-white mb-0">
                        <i class="fas fa-user-plus me-2"></i> Nouveau Client
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="quick-create-errors alert alert-danger d-none py-2 px-3 small mb-3"></div>

                    <div class="alert alert-info py-2 px-3 small mb-3 d-flex align-items-center gap-2" id="qc_client_help">
                        <i class="fas fa-info-circle fa-lg"></i>
                        <span id="qc_client_help_text">Sélectionnez la catégorie du client pour ajuster les obligations fiscales DGI.</span>
                    </div>

                    <div class="row g-3">
                        {{-- UN SEUL SELECT UNIQUE POUR LA CATÉGORIE DU CLIENT --}}
                        <div class="col-md-12">
                            <label class="small fw-bold">Type / Catégorie du Client <span class="text-danger">*</span></label>
                            <select name="categories_id" id="qc_categories_id" class="form-select form-select-lg" required>
                                @foreach ($categories as $categorie)
                                    <option value="{{ $categorie->id }}" data-code="{{ $categorie->code }}" {{ $loop->first ? 'selected' : '' }}>
                                        {{ $categorie->code }} — {{ $categorie->libelle }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="type_client" id="qc_type_client" value="CC">
                        </div>

                        <div class="col-md-6">
                            <label class="small fw-bold">Nom / Raison Sociale <span class="text-danger">*</span></label>
                            <input class="form-control" name="assujeti" id="qc_assujeti" type="text" placeholder="Ex: ETS OUEDRAOGO & FRERES ou Nom Prénom" value="CLIENT COMPTANT" required>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold">N° IFU <span class="qc-req text-danger d-none">*</span></label>
                            <input class="form-control font-monospace" name="ifu" id="qc_ifu" type="text" placeholder="Ex: 00012345A">
                            <small class="text-muted d-block" id="qc_help_ifu" style="font-size: 11px;">Obligatoire pour Personne Morale et Commerçant</small>
                        </div>

                        <div class="col-md-6">
                            <label class="small fw-bold">N° RCCM <span class="qc-req text-danger d-none">*</span></label>
                            <input class="form-control font-monospace" name="rccm" id="qc_rccm" type="text" placeholder="Ex: BF OUA 2024 B 1234">
                            <small class="text-muted d-block" id="qc_help_rccm" style="font-size: 11px;">Obligatoire pour Personne Morale et Commerçant</small>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold">N° Téléphone <span class="qc-req text-danger d-none">*</span></label>
                            <input class="form-control" name="telephone" id="qc_telephone" type="tel" placeholder="+226 XX XX XX XX">
                        </div>

                        <div class="col-md-6">
                            <label class="small fw-bold">Adresse géographique <span class="qc-req text-danger d-none">*</span></label>
                            <input class="form-control" name="adresse" id="qc_adresse" type="text" placeholder="Ville, Secteur, Rue">
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold">Email</label>
                            <input class="form-control" name="email" type="email" placeholder="contact@exemple.bf">
                        </div>

                        <div class="col-md-6">
                            <label class="small fw-bold">Division Fiscale / CDI</label>
                            <input class="form-control" name="division_fiscal" type="text" placeholder="Ex: DME4, DGE, Kadiogo">
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold">Régime Fiscal</label>
                            <select name="regime_fiscal" class="form-select">
                                <option value="">-- Non spécifié --</option>
                                <option value="RNI">RNI — Réel Normal</option>
                                <option value="RSI">RSI — Réel Simplifié</option>
                                <option value="CME">CME — Micro-Entreprise</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" id="qc_submit_btn">
                        <i class="fas fa-save me-1"></i> Enregistrer le client
                    </button>
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
    const qcCatSelect = document.getElementById('qc_categories_id');
    const qcHiddenType = document.getElementById('qc_type_client');
    const reqStars = document.querySelectorAll('#quickCreateClientModal .qc-req');
    const phone = document.getElementById('qc_telephone');
    const ifu = document.getElementById('qc_ifu');
    const rccm = document.getElementById('qc_rccm');
    const addr = document.getElementById('qc_adresse');
    const name = document.getElementById('qc_assujeti');
    const helpText = document.getElementById('qc_client_help_text');
    const helpIfu = document.getElementById('qc_help_ifu');
    const helpRccm = document.getElementById('qc_help_rccm');

    function updateQcRules() {
        if (!qcCatSelect) return;
        const opt = qcCatSelect.options[qcCatSelect.selectedIndex];
        const type = opt ? (opt.getAttribute('data-code') || 'CC') : 'CC';
        if (qcHiddenType) qcHiddenType.value = type;

        if (type === 'PM' || type === 'PC') {
            reqStars.forEach(s => s.classList.remove('d-none'));
            if (phone) phone.required = true;
            if (ifu) ifu.required = true;
            if (rccm) rccm.required = true;
            if (addr) addr.required = true;
            if (helpIfu) helpIfu.textContent = 'Obligatoire (Personne Morale / Commerçant)';
            if (helpRccm) helpRccm.textContent = 'Obligatoire (Personne Morale / Commerçant)';
            if (helpText) helpText.innerHTML = '<strong>' + (type === 'PM' ? 'Personne Morale' : 'Commerçant') + '</strong> : Nom, IFU, RCCM, Téléphone et Adresse sont <strong>obligatoires</strong> selon la réglementation fiscale DGI.';
            if (name && name.value === 'CLIENT COMPTANT') name.value = '';
        } else if (type === 'PP') {
            reqStars.forEach(s => s.classList.add('d-none'));
            if (phone) {
                phone.required = true;
                const star = phone.parentElement.querySelector('.qc-req');
                if (star) star.classList.remove('d-none');
            }
            if (addr) {
                addr.required = true;
                const star = addr.parentElement.querySelector('.qc-req');
                if (star) star.classList.remove('d-none');
            }
            if (ifu) ifu.required = false;
            if (rccm) rccm.required = false;
            if (helpIfu) helpIfu.textContent = 'Optionnel (Particulier)';
            if (helpRccm) helpRccm.textContent = 'Optionnel (Particulier)';
            if (helpText) helpText.innerHTML = '<strong>Personne Physique (Particulier)</strong> : Nom, Téléphone et Adresse sont obligatoires. IFU et RCCM optionnels.';
            if (name && name.value === 'CLIENT COMPTANT') name.value = '';
        } else {
            // CC
            reqStars.forEach(s => s.classList.add('d-none'));
            if (phone) phone.required = false;
            if (ifu) ifu.required = false;
            if (rccm) rccm.required = false;
            if (addr) addr.required = false;
            if (helpIfu) helpIfu.textContent = 'Optionnel (Client Comptant)';
            if (helpRccm) helpRccm.textContent = 'Optionnel (Client Comptant)';
            if (helpText) helpText.innerHTML = '<strong>Client Comptant (Vente grand public)</strong> : Aucun identifiant fiscal requis pour les ventes au comptant.';
            if (name && !name.value.trim()) name.value = 'CLIENT COMPTANT';
        }
    }

    if (qcCatSelect) {
        qcCatSelect.addEventListener('change', updateQcRules);
        updateQcRules();
    }

    // Gestion de la soumission AJAX du formulaire Client
    const formClient = document.getElementById('quickCreateClientForm');
    if (formClient) {
        formClient.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = document.getElementById('qc_submit_btn');
            const errBox = formClient.querySelector('.quick-create-errors');
            if (errBox) { errBox.classList.add('d-none'); errBox.innerHTML = ''; }
            if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...'; }

            const formData = new FormData(formClient);
            fetch(formClient.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, data: data })))
            .then(result => {
                if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="fas fa-save me-1"></i> Enregistrer le client'; }
                if (!result.ok) {
                    let errMsg = result.data.message || 'Une erreur est survenue lors de la création.';
                    if (result.data.errors) {
                        errMsg = Object.values(result.data.errors).flat().join('<br>');
                    }
                    if (errBox) {
                        errBox.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> ' + errMsg;
                        errBox.classList.remove('d-none');
                    }
                    return;
                }

                const client = result.data.item || {};
                const clientId = client.id;
                const clientText = client.text || client.assujeti;

                // 1. Ajouter l'option dans le select #contribuable_id s'il est présent
                const $select = $('#contribuable_id');
                if ($select.length) {
                    const newOption = new Option(clientText, clientId, true, true);
                    $select.append(newOption).trigger('change');
                }

                // 2. Fermer la modale
                const modalEl = document.getElementById('quickCreateClientModal');
                if (modalEl) {
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) modalInstance.hide();
                    else $(modalEl).modal('hide');
                }

                // 3. Réinitialiser le formulaire
                formClient.reset();
                updateQcRules();

                // 4. Notification
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Client ajouté !',
                        text: `${clientText} a été créé et sélectionné pour cette facture.`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            })
            .catch(err => {
                if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="fas fa-save me-1"></i> Enregistrer le client'; }
                if (errBox) {
                    errBox.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> Erreur de communication réseau.';
                    errBox.classList.remove('d-none');
                }
            });
        });
    }
});
</script>

