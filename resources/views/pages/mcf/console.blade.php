@php
    $level = $status['level'] ?? 'disconnected';
    $device = $status['device'] ?? [];
    $isOk = ($level === 'connected') || ($device['succes'] ?? false);
    $portCom = $config['port_com'] ?? $settings->port_com ?? 'AUTO';
    $nim = $device['nim'] ?? $settings->nim_mcf ?? 'EL02000012';
@endphp

<div class="container-xl px-4 pt-4 mb-5">
    {{-- ═══ 1. Bannière Statut Exécutive (Style Hub Facturation) ═══ --}}
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border-radius: 14px; border: 1px solid #e2e8f0;">
        <div class="card-body p-4 bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 52px; height: 52px; border-radius: 12px; background: {{ $isOk ? '#ecfdf5' : '#fffbeb' }}; color: {{ $isOk ? '#059669' : '#d97706' }}; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                        <i class="fas {{ $isOk ? 'fa-check-circle' : 'fa-info-circle' }}"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge {{ $isOk ? 'bg-success' : 'bg-warning text-dark' }} px-2 py-1">
                                {{ $isOk ? 'BOÎTIER MCF OPÉRATIONNEL' : 'CONNEXION EN COURS / CONFIGURATION' }}
                            </span>
                            <span class="text-muted small">&bull; Norme DGI SECeF</span>
                        </div>
                        <h4 class="fw-bold mb-1 text-dark">Dispositif Fiscal de Signature (MCF)</h4>
                        <div class="text-muted small">
                            Port de communication : <strong class="text-dark font-monospace">{{ $portCom }}</strong> &bull; 
                            NIM Boîtier : <strong class="text-dark font-monospace">{{ $nim }}</strong> &bull; 
                            IFU Vendeur : <strong class="text-dark font-monospace">{{ $settings->ifu ?: 'Non renseigné' }}</strong>
                        </div>
                    </div>
                </div>

                {{-- Actions rapides --}}
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <form method="POST" action="{{ route('mcf.tester') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary fw-bold px-3 shadow-sm">
                            <i class="fas fa-plug me-1"></i> Tester la connexion
                        </button>
                    </form>
                    <form method="POST" action="{{ route('mcf.auto-connect') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary fw-semibold px-3">
                            <i class="fas fa-magic me-1"></i> Détection USB auto
                        </button>
                    </form>
                </div>
            </div>

            @if (!empty($device['erreur']))
                <div class="alert alert-warning d-flex align-items-center gap-2 mt-3 mb-0 py-2">
                    <i class="fas fa-exclamation-triangle text-warning"></i>
                    <span class="small">{{ $device['erreur'] }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- ═══ 2. Configuration & Clôtures Fiscales ═══ --}}
    <div class="row g-4 mb-4">
        {{-- Configuration --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-sliders-h me-2"></i> Paramètres de Liaison du Boîtier</h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('mcf.configurer') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Port COM (Série / USB)</label>
                                <select name="port_com" class="form-select" required>
                                    <option value="AUTO" @selected(($config['port_com'] ?? '') === 'AUTO')>Détection automatique (USB / COM)</option>
                                    <option value="SIMULATION" @selected(($config['port_com'] ?? '') === 'SIMULATION')>Mode Émulateur (Sans boîtier physique)</option>
                                    @foreach ($ports as $p)
                                        <option value="{{ $p['device'] }}" @selected(($config['port_com'] ?? '') === $p['device'])>
                                            {{ $p['device'] }} — {{ $p['description'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Vitesse (Baudrate)</label>
                                <input name="baudrate" type="number" class="form-control" value="{{ $config['baudrate'] ?? 9600 }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">NIM du Boîtier Fiscal</label>
                                <input name="nim" class="form-control font-monospace" value="{{ $settings->nim_mcf ?? 'EL02000012' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Délai de réponse (secondes)</label>
                                <input name="sfe_bridge_timeout" type="number" min="1" max="120" class="form-control" value="{{ $settings->sfe_bridge_timeout ?? 5 }}">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary px-4 fw-bold">
                                <i class="fas fa-save me-1"></i> Enregistrer les paramètres
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Clôtures & Rapports --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                    <h6 class="m-0 fw-bold text-dark"><i class="fas fa-print me-2 text-primary"></i> Clôtures & Rapports Fiscaux</h6>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        {{-- Rapport X --}}
                        <div class="p-3 rounded border mb-3 bg-light">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <strong class="text-dark d-block">Rapport X (Lecture du jour)</strong>
                                    <small class="text-muted">Aperçu sans remise à zéro, imprimable à tout moment.</small>
                                </div>
                                <span class="badge bg-secondary">Lecture</span>
                            </div>
                            <form method="POST" action="{{ route('mcf.rapport') }}">
                                @csrf
                                <input type="hidden" name="type_rapport" value="X">
                                <button type="submit" class="btn btn-outline-primary btn-sm w-100 fw-bold">
                                    <i class="fas fa-file-invoice me-1"></i> Imprimer le Rapport X
                                </button>
                            </form>
                        </div>

                        {{-- Rapport Z --}}
                        <div class="p-3 rounded border bg-light" style="border-left: 4px solid #dc2626 !important;">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <strong class="text-danger d-block">Rapport Z (Clôture Journalière)</strong>
                                    <small class="text-muted">Clôture fiscale officielle, remise à zéro des totaux du jour.</small>
                                </div>
                                <span class="badge bg-danger">Clôture</span>
                            </div>
                            <form method="POST" action="{{ route('mcf.rapport') }}" onsubmit="return confirm('Confirmer la clôture journalière (Rapport Z) ? Cette opération est irréversible.')">
                                @csrf
                                <input type="hidden" name="type_rapport" value="Z">
                                <button type="submit" class="btn btn-danger btn-sm w-100 fw-bold">
                                    <i class="fas fa-lock me-1"></i> Effectuer la Clôture Z
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ 3. Dernières Certifications Sécurisées ═══ --}}
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-dark"><i class="fas fa-history me-2 text-primary"></i> Dernières Certifications Sécurisées</h6>
            <span class="badge bg-light text-dark border">{{ $certifications->total() }} certifications</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Date & Heure</th>
                            <th>Facture</th>
                            <th>Client Destinataire</th>
                            <th>NIM Boîtier</th>
                            <th class="text-center">Compteur</th>
                            <th class="text-center pe-3">Statut SFE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($certifications as $c)
                            <tr>
                                <td class="ps-3 text-muted small">{{ optional($c->date_certification)->format('d/m/Y H:i') ?: 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('module_ordre_facture.show', $c->facture_id) }}" class="fw-bold text-decoration-none">
                                        {{ optional($c->facture)->code ?: ('FAC-#' . $c->facture_id) }}
                                    </a>
                                </td>
                                <td class="fw-semibold">{{ optional(optional($c->facture)->Contribuable)->assujeti ?: 'Client' }}</td>
                                <td class="font-monospace small">{{ $c->nim ?: 'EL02000012' }}</td>
                                <td class="text-center font-monospace small">{{ $c->compteur_total ?: $c->compteur_facture ?: '1' }}</td>
                                <td class="text-center pe-3">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="fas fa-check-circle me-1"></i> Certifiée
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    Aucune certification enregistrée pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($certifications->hasPages())
            <div class="card-footer bg-white border-top py-2">
                {{ $certifications->links() }}
            </div>
        @endif
    </div>
</div>
