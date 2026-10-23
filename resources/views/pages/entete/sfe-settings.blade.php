<hr>
<h5 class="mb-3 text-primary"><i class="fas fa-microchip me-2"></i> Configuration Matérielle MCF & Certification Fiscale (SECeF ST 1.0)</h5>

<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="mb-3">
            <label class="small mb-1 fw-bold">Port COM (Boîtier MCF)</label>
            <input class="form-control" type="text" name="port_com" placeholder="Ex: COM3 (ou vide = Auto)" value="{{ old('port_com', $entete->port_com) }}">
            <small class="text-muted">Port série USB du boîtier Eltrade CC300</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="mb-3">
            <label class="small mb-1 fw-bold">Vitesse (Baudrate)</label>
            <select name="mcf_baudrate" class="form-select">
                <option value="9600" {{ old('mcf_baudrate', $entete->mcf_baudrate ?? 9600) == 9600 ? 'selected' : '' }}>9600 bauds (Standard Eltrade)</option>
                <option value="115200" {{ old('mcf_baudrate', $entete->mcf_baudrate) == 115200 ? 'selected' : '' }}>115200 bauds</option>
                <option value="19200" {{ old('mcf_baudrate', $entete->mcf_baudrate) == 19200 ? 'selected' : '' }}>19200 bauds</option>
            </select>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="mb-3">
            <label class="small mb-1 fw-bold">NIM de la Machine (Boîtier)</label>
            <input class="form-control" type="text" name="nim_mcf" placeholder="Ex: EL02000012-1" value="{{ old('nim_mcf', $entete->nim_mcf) }}">
            <small class="text-muted">N° d'identification machine certifiée</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="mb-3">
            <label class="small mb-1 fw-bold">Catégorie PSVB Entreprise</label>
            <select name="categorie_psvb" class="form-select">
                <option value="A" {{ old('categorie_psvb', $entete->categorie_psvb ?? 'A') == 'A' ? 'selected' : '' }}>Catégorie A — 2% (Droit commun)</option>
                <option value="B" {{ old('categorie_psvb', $entete->categorie_psvb) == 'B' ? 'selected' : '' }}>Catégorie B — 1% (Dérogation)</option>
                <option value="C" {{ old('categorie_psvb', $entete->categorie_psvb) == 'C' ? 'selected' : '' }}>Catégorie C — 0.2% (Dérogation)</option>
                <option value="D" {{ old('categorie_psvb', $entete->categorie_psvb) == 'D' ? 'selected' : '' }}>Catégorie D — 0% (Exonéré / Non assujetti)</option>
            </select>
        </div>
    </div>
</div>

<div class="row mt-2">
    <div class="col-lg-8">
        <div class="mb-3">
            <label class="small mb-1">Adresse complète pour quittance fiscale SFE</label>
            <input class="form-control" name="sfe_company_address" value="{{ old('sfe_company_address', $entete->sfe_company_address ?? ($entete->postale ?: $entete->infos_cadastrales)) }}" placeholder="Ex: Ouagadougou, Secteur 15, Rue 15.102">
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-2 bg-light border-0 mt-1">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="check_auto_health" name="sfe_automatic_health_check" value="1" @checked($entete->sfe_automatic_health_check ?? true)>
                <label class="form-check-label fw-bold small" for="check_auto_health">
                    <i class="fas fa-heartbeat text-success me-1"></i> Contrôle automatique (Ping MCF)
                </label>
            </div>
            <div class="form-check mt-1">
                <input type="checkbox" class="form-check-input" id="check_auto_sign" name="sfe_automatic_invoice_signing" value="1" @checked($entete->sfe_automatic_invoice_signing ?? false)>
                <label class="form-check-label fw-bold small" for="check_auto_sign">
                    <i class="fas fa-file-signature text-primary me-1"></i> Signature automatique lors de la validation
                </label>
            </div>
        </div>
    </div>
</div>
