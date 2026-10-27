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

    {{-- ═══ 1.BIS Indicateurs Diagnostics Matériel & Télétransmission DGI SECeF ═══ --}}
    <div class="row g-3 mb-4">
        {{-- 1. Horloge Fiscale Inviolable --}}
        @php
            $rawRtc = trim((string) ($device['date_heure'] ?? ''));
            $rtcFormatted = $rawRtc;
            if (strlen($rawRtc) === 14 && ctype_digit($rawRtc)) {
                try {
                    $rtcFormatted = \Carbon\Carbon::createFromFormat('YmdHis', $rawRtc)->format('d/m/Y à H:i:s');
                } catch (\Throwable $e) {}
            }
        @endphp
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #10b981 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-bold text-uppercase" style="font-size: 11px;">Horloge Fiscale Inviolable</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5" style="font-size: 10px;">
                            <i class="fas fa-shield-alt me-1"></i> Puce RTC
                        </span>
                    </div>
                    <div class="h5 fw-bold text-dark font-monospace mb-1">
                        {{ $rtcFormatted ?: now()->format('d/m/Y à H:i:s') }}
                    </div>
                    <small class="text-muted d-block" style="font-size: 11px;">
                        <i class="far fa-clock text-primary me-1"></i> Synchronisée depuis la puce interne du boîtier (Quartz scellé DGI).
                    </small>
                </div>
            </div>
        </div>

        {{-- 2. Télétransmission DGI (Serveur Central) --}}
        @php
            $daysSince = (int) ($status['dgi_days_since'] ?? 0);
            $warn7 = (bool) ($status['warn_7_days'] ?? false);
            $isDgiInSync = ($daysSince === 0) && ($status['level'] === 'connected');
            
            $rawDgi = trim((string) ($status['dgi_last_sync'] ?? $connexion['derniere_connexion_dgi'] ?? ''));
            $derniereDgiFormatted = $rawDgi;
            if (strlen($rawDgi) === 14 && ctype_digit($rawDgi)) {
                try {
                    $derniereDgiFormatted = \Carbon\Carbon::createFromFormat('YmdHis', $rawDgi)->format('d/m/Y à H:i:s');
                } catch (\Throwable $e) {}
            }
            $pendingTx = (int) ($connexion['transactions_disponibles'] ?? 0);
        @endphp
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid {{ $warn7 ? '#ef4444' : ($isDgiInSync ? '#3b82f6' : '#f59e0b') }} !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-bold text-uppercase" style="font-size: 11px;">Télétransmission DGI</span>
                        <span class="badge {{ $warn7 ? 'bg-danger text-white' : ($isDgiInSync ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle') }} px-2 py-0.5" style="font-size: 10px;">
                            <i class="fas fa-satellite-dish me-1"></i> {{ $warn7 ? 'Hors-Ligne > 7j' : ($isDgiInSync ? 'En direct' : 'En attente') }}
                        </span>
                    </div>
                    <div class="h5 fw-bold {{ $warn7 ? 'text-danger' : ($isDgiInSync ? 'text-primary' : 'text-warning') }} mb-1">
                        {{ $warn7 ? 'Non synchronisé DGI' : ($isDgiInSync ? 'Connecté au Serveur' : 'Liaison en attente') }}
                    </div>
                    <small class="text-muted d-block" style="font-size: 11px;">
                        <i class="fas {{ $warn7 ? 'fa-exclamation-triangle text-danger' : 'fa-check-circle text-success' }} me-1"></i> Dernière liaison : <span class="font-monospace text-dark fw-bold">{{ $derniereDgiFormatted ?: 'Non disponible' }}</span>
                    </small>
                </div>
            </div>
        </div>

        {{-- 3. Règle Hors-Ligne (> 7 jours) --}}
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid {{ $warn7 ? '#ef4444' : '#10b981' }} !important; {{ $warn7 ? 'background-color: #fef2f2;' : '' }}">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-bold text-uppercase" style="font-size: 11px;">Règle Hors-Ligne (7J)</span>
                        <span class="badge {{ $warn7 ? 'bg-danger text-white' : 'bg-success-subtle text-success border border-success-subtle' }} px-2 py-0.5" style="font-size: 10px;">
                            {{ $warn7 ? 'Dépassement > 7j' : 'Conforme SECeF' }}
                        </span>
                    </div>
                    <div class="h5 fw-bold {{ $warn7 ? 'text-danger' : 'text-success' }} mb-1">
                        {{ $daysSince }} jour(s) écoulé(s)
                    </div>
                    <small class="text-muted d-block" style="font-size: 11px;">
                        Tolérance légale maximale : <strong>7 jours consécutifs</strong>. Statut : <strong class="{{ $warn7 ? 'text-danger' : 'text-success' }}">{{ $warn7 ? 'Non conforme' : 'Conforme' }}</strong>.
                    </small>
                </div>
            </div>
        </div>

        {{-- 4. Compteurs & Transactions DGI --}}
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #6366f1 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-bold text-uppercase" style="font-size: 11px;">Actes Fiscaux Certifiés</span>
                        <span class="badge bg-indigo-subtle text-indigo border px-2 py-0.5" style="font-size: 10px; background:#e0e7ff; color:#4338ca;">
                            {{ $pendingTx > 0 ? $pendingTx . ' en attente' : '100% Télétransmis' }}
                        </span>
                    </div>
                    <div class="h5 fw-bold text-dark font-monospace mb-1">
                        {{ $device['compteur_total'] ?? 0 }} actes
                    </div>
                    <small class="text-muted d-block" style="font-size: 11px;">
                        Ventes (FV) : <strong>{{ $device['compteur_fv'] ?? 0 }}</strong> &bull; Avoirs (FA) : <strong>{{ $device['compteur_fa'] ?? 0 }}</strong>
                    </small>
                </div>
            </div>
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
            <h6 class="m-0 fw-bold text-dark"><i class="fas fa-history me-2 text-primary"></i> Journal des Certifications Sécurisées SFE</h6>
            <span class="badge bg-light text-dark border">{{ count($certifications) }} certification(s)</span>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="mcfCertificationsTable" style="width: 100%;">
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
                                <td class="ps-3 text-muted small" data-order="{{ $c->date_certification ? $c->date_certification->timestamp : 0 }}">
                                    <i class="far fa-clock me-1 text-primary"></i>
                                    {{ optional($c->date_certification)->format('d/m/Y H:i') ?: 'N/A' }}
                                </td>
                                <td>
                                    <a href="{{ route('module_ordre_facture.show', $c->facture_id) }}" class="fw-bold font-monospace text-decoration-none text-primary">
                                        {{ optional($c->facture)->code ?: ('FAC-#' . $c->facture_id) }}
                                    </a>
                                </td>
                                <td class="fw-semibold">{{ optional(optional($c->facture)->Contribuable)->assujeti ?: 'Client' }}</td>
                                <td class="font-monospace small">{{ $c->nim ?: 'EL02000012' }}</td>
                                <td class="text-center font-monospace small"><span class="badge bg-light text-dark border">{{ $c->compteur_total ?: $c->compteur_facture ?: '1' }}</span></td>
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
    </div>
</div>
