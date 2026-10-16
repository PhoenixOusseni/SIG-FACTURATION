<hr><h5 class="mb-3"><i class="fas fa-microchip"></i> Paramètres SFE Bridge</h5>
<div class="row">
    <div class="col-lg-8"><div class="mb-3"><label>URL du bridge</label><input class="form-control" type="url" name="sfe_bridge_url" value="{{ old('sfe_bridge_url',$entete->sfe_bridge_url ?? config('sfe.url')) }}"></div></div>
    <div class="col-lg-4"><div class="mb-3"><label>Timeout (secondes)</label><input class="form-control" type="number" min="1" max="120" name="sfe_bridge_timeout" value="{{ old('sfe_bridge_timeout',$entete->sfe_bridge_timeout ?? 10) }}"></div></div>
    <div class="col-lg-8"><div class="mb-3"><label>Adresse société pour le SFE</label><input class="form-control" name="sfe_company_address" value="{{ old('sfe_company_address',$entete->sfe_company_address ?? $entete->postale) }}"></div></div>
    <div class="col-lg-4"><div class="form-check mt-4"><input type="checkbox" class="form-check-input" name="sfe_automatic_health_check" value="1" @checked($entete->sfe_automatic_health_check ?? true)><label>Contrôle automatique</label></div><div class="form-check"><input type="checkbox" class="form-check-input" name="sfe_automatic_invoice_signing" value="1" @checked($entete->sfe_automatic_invoice_signing ?? false)><label>Signature automatique</label></div></div>
</div>
