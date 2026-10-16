@php
    $level = $status['level'] ?? 'disconnected';
    $device = $status['device'] ?? [];

    $levelMeta = [
        'connected' => ['icon' => 'check-circle', 'title' => 'Opérationnelle'],
        'warning' => ['icon' => 'alert-triangle', 'title' => 'Action requise'],
        'disconnected' => ['icon' => 'x-circle', 'title' => 'Indisponible'],
    ];
    $lm = $levelMeta[$level] ?? $levelMeta['disconnected'];

    $metrics = [
        ['icon' => 'link-2', 'label' => 'Bridge', 'value' => !empty($status['bridge']) ? 'En ligne' : 'Hors ligne', 'ok' => !empty($status['bridge'])],
        ['icon' => 'hard-drive', 'label' => 'Port actif', 'value' => $config['port_com'] ?? 'Non configuré', 'ok' => !empty($config['port_com'])],
        ['icon' => 'hash', 'label' => 'NIM', 'value' => $device['nim'] ?? 'Non détecté', 'ok' => !empty($device['nim'])],
        ['icon' => 'shield', 'label' => 'IFU', 'value' => $device['ifu'] ?? ($config['ifu_vendeur'] ?? 'Non configuré'), 'ok' => !empty($device['ifu']) || !empty($config['ifu_vendeur'])],
    ];

    $checks = [
        ['icon' => 'wifi', 'label' => 'Bridge joignable', 'ok' => !empty($status['bridge'])],
        ['icon' => 'sliders', 'label' => 'Configuration valide', 'ok' => $status['configuration_valid'] ?? false],
        ['icon' => 'cpu', 'label' => 'Boîtier connecté', 'ok' => $device['succes'] ?? false],
        ['icon' => 'globe', 'label' => 'Connexion DGI', 'ok' => $connexion['succes'] ?? false],
    ];
@endphp

<style>
    .mcf-console {
        --mcf-primary: var(--primary-color, #1d3f7f);
        --mcf-primary-dark: var(--primary-hover, #152d5c);
    }

    /* ── Hero status banner ─────────────────────────── */
    .mcf-hero {
        border: 0;
        border-radius: 20px;
        color: #fff;
        overflow: hidden;
        position: relative;
        box-shadow: 0 20px 45px -12px rgba(15, 23, 42, 0.35);
    }

    .mcf-hero::after {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 85% -10%, rgba(255, 255, 255, 0.18), transparent 55%);
        pointer-events: none;
    }

    .mcf-hero.is-connected { background: linear-gradient(135deg, #0d9488 0%, #059669 100%); }
    .mcf-hero.is-warning { background: linear-gradient(135deg, #d97706 0%, #b45309 100%); }
    .mcf-hero.is-disconnected { background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); }

    .mcf-hero-body {
        position: relative;
        z-index: 1;
        padding: 1.75rem 2rem;
    }

    .mcf-hero-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.18);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .mcf-hero-icon i { width: 28px; height: 28px; }

    .mcf-hero-eyebrow {
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.75rem;
        font-weight: 700;
        opacity: 0.85;
    }

    .mcf-hero-title {
        font-size: 1.6rem;
        font-weight: 700;
        margin: 0.15rem 0 0.35rem;
    }

    .mcf-hero-msg { opacity: 0.95; }

    .mcf-hero-error {
        display: inline-block;
        margin-top: 0.6rem;
        padding: 0.4rem 0.85rem;
        border-radius: 8px;
        background: rgba(0, 0, 0, 0.18);
        font-weight: 600;
        font-size: 0.9rem;
    }

    .mcf-hero .btn-light {
        border: 0;
        font-weight: 700;
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.18);
    }

    /* ── Metric tiles ────────────────────────────────── */
    .mcf-metric {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        background: #fff;
        border: 1px solid var(--border-color, #e2e8f0);
        border-radius: 14px;
        padding: 1rem 1.1rem;
        height: 100%;
        transition: box-shadow .2s ease, transform .2s ease;
    }

    .mcf-metric:hover {
        box-shadow: 0 10px 25px -10px rgba(15, 23, 42, 0.18);
        transform: translateY(-2px);
    }

    .mcf-metric-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: rgba(29, 63, 127, 0.1);
        color: var(--mcf-primary);
    }

    .mcf-metric.is-ok .mcf-metric-icon { background: rgba(5, 150, 105, 0.12); color: #047857; }
    .mcf-metric.is-ko .mcf-metric-icon { background: rgba(220, 38, 38, 0.1); color: #b91c1c; }
    .mcf-metric-icon i { width: 20px; height: 20px; }

    .mcf-metric-label {
        display: block;
        text-transform: uppercase;
        letter-spacing: .4px;
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 700;
        margin-bottom: 0.15rem;
    }

    .mcf-metric-value {
        display: block;
        font-weight: 700;
        color: #1e293b;
        font-size: 0.98rem;
        word-break: break-word;
    }

    /* ── Section cards ───────────────────────────────── */
    .mcf-card {
        border: 1px solid var(--border-color, #e2e8f0);
        border-radius: 16px;
        overflow: hidden;
    }

    .mcf-card .card-header {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        background: #f8fafc;
        font-weight: 700;
        color: #1e293b;
        border-bottom: 1px solid var(--border-color, #e2e8f0);
    }

    .mcf-card .card-header i { width: 18px; height: 18px; color: var(--mcf-primary); }

    .mcf-card .card-header .badge-count {
        margin-left: auto;
        background: rgba(29, 63, 127, 0.1);
        color: var(--mcf-primary);
        font-weight: 700;
        border-radius: 999px;
        padding: 0.25rem 0.7rem;
        font-size: 0.78rem;
    }

    /* ── Config form ─────────────────────────────────── */
    .mcf-card form label {
        font-weight: 600;
        color: #334155;
        font-size: 0.88rem;
        margin-bottom: 0.35rem;
        display: inline-block;
    }

    /* ── Diagnostic checklist ────────────────────────── */
    .mcf-check {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.7rem 0.85rem;
        background: #f8fafc;
        border-radius: 12px;
        margin-bottom: 0.6rem;
        border: 1px solid transparent;
    }

    .mcf-check:last-of-type { margin-bottom: 0; }

    .mcf-check-icon {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #fff;
    }

    .mcf-check-icon i { width: 16px; height: 16px; }

    .mcf-check.ok { border-color: rgba(5, 150, 105, 0.25); }
    .mcf-check.ok .mcf-check-icon { background: #059669; }
    .mcf-check.ok .mcf-check-title { color: #047857; }

    .mcf-check.ko { border-color: rgba(220, 38, 38, 0.2); }
    .mcf-check.ko .mcf-check-icon { background: #dc2626; }
    .mcf-check.ko .mcf-check-title { color: #b91c1c; }

    .mcf-check-title { font-weight: 700; font-size: 0.92rem; }
    .mcf-check-sub { font-size: 0.78rem; color: #64748b; margin-left: auto; font-weight: 600; }

    /* ── Reports ─────────────────────────────────────── */
    .mcf-report-btn {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
        border-radius: 12px;
        border: 1.5px solid var(--border-color, #e2e8f0);
        background: #fff;
        text-align: left;
        font-weight: 700;
        color: #1e293b;
        transition: all .18s ease;
    }

    .mcf-report-btn .mcf-report-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .mcf-report-btn .mcf-report-icon i { width: 18px; height: 18px; }

    .mcf-report-btn small { display: block; font-weight: 500; color: #64748b; font-size: 0.78rem; }

    .mcf-report-btn.mcf-report-x .mcf-report-icon { background: rgba(29, 63, 127, 0.1); color: var(--mcf-primary); }
    .mcf-report-btn.mcf-report-x:hover { border-color: var(--mcf-primary); box-shadow: 0 8px 18px -8px rgba(29, 63, 127, 0.35); }

    .mcf-report-btn.mcf-report-z .mcf-report-icon { background: rgba(220, 38, 38, 0.1); color: #dc2626; }
    .mcf-report-btn.mcf-report-z:hover { border-color: #dc2626; box-shadow: 0 8px 18px -8px rgba(220, 38, 38, 0.35); }

    /* ── History table ───────────────────────────────── */
    .mcf-sig {
        background: #f1f5f9;
        border-radius: 6px;
        padding: 0.2rem 0.5rem;
        font-size: 0.8rem;
    }
</style>

<div class="mcf-console container-xl px-4 mt-n10 mb-5">

    {{-- ═══ Statut ═══ --}}
    <div class="card mcf-hero {{ $lm ? 'is-' . $level : '' }} mb-4">
        <div class="mcf-hero-body d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <span class="mcf-hero-icon"><i data-feather="{{ $lm['icon'] }}"></i></span>
                <div>
                    <div class="mcf-hero-eyebrow">État de la facturation fiscale</div>
                    <h2 class="mcf-hero-title">{{ $lm['title'] }}</h2>
                    <div class="mcf-hero-msg">{{ $status['message'] ?? 'Statut inconnu' }}</div>
                    @if (!empty($device['erreur']))
                        <div class="mcf-hero-error"><i data-feather="alert-circle" style="width:14px;height:14px"></i>
                            {{ $device['erreur'] }}</div>
                    @endif
                </div>
            </div>
            <form method="POST" action="{{ route('mcf.tester') }}">
                @csrf
                <button class="btn btn-light btn-lg"><i data-feather="refresh-cw"></i> Tester le boîtier</button>
            </form>
        </div>
    </div>

    {{-- ═══ Indicateurs ═══ --}}
    <div class="row g-3 mb-4">
        @foreach ($metrics as $m)
            <div class="col-6 col-md-3">
                <div class="mcf-metric {{ $m['ok'] ? 'is-ok' : 'is-ko' }}">
                    <span class="mcf-metric-icon"><i data-feather="{{ $m['icon'] }}"></i></span>
                    <div>
                        <small class="mcf-metric-label">{{ $m['label'] }}</small>
                        <b class="mcf-metric-value">{{ $m['value'] }}</b>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        {{-- ═══ Configuration ═══ --}}
        <div class="col-xl-7">
            <div class="card mcf-card h-100">
                <div class="card-header"><i data-feather="settings"></i> Configuration physique</div>
                <div class="card-body">
                    <div class="alert alert-info d-flex align-items-center gap-2">
                        <i data-feather="info" style="width:18px;height:18px;flex-shrink:0"></i>
                        <span>Choisissez le port du boîtier, enregistrez, puis testez la connexion.</span>
                    </div>
                    <form method="POST" action="{{ route('mcf.configurer') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>Port COM</label>
                                <select name="port_com" class="form-select" required>
                                    <option value="">Choisir...</option>
                                    @foreach ($ports as $p)
                                        <option value="{{ $p['device'] }}" @selected(($config['port_com'] ?? '') === $p['device'])>
                                            {{ $p['device'] }} — {{ $p['description'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Vitesse</label>
                                <input name="baudrate" type="number" class="form-control"
                                    value="{{ $config['baudrate'] ?? 9600 }}">
                            </div>
                            <div class="col-md-6">
                                <label>Identifiant SFE</label>
                                <input name="sfeid" class="form-control" value="{{ $config['sfeid'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label>IFU vendeur</label>
                                <input name="ifu_vendeur" class="form-control"
                                    value="{{ $config['ifu_vendeur'] ?? $settings->ifu }}" required>
                            </div>
                            <div class="col-md-6">
                                <label>Clé de licence</label>
                                <input type="password" name="license_key" class="form-control" value=""
                                    placeholder="Laisser vide pour conserver la clé">
                            </div>
                            <div class="col-md-6">
                                <label>Timeout (s)</label>
                                <input name="sfe_bridge_timeout" type="number" min="1" max="120"
                                    class="form-control" value="{{ $settings->sfe_bridge_timeout ?? 10 }}">
                            </div>
                            <div class="col-12">
                                <label>URL du bridge</label>
                                <input type="url" name="sfe_bridge_url" class="form-control"
                                    value="{{ $settings->sfe_bridge_url ?? config('sfe.url') }}" required>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-lg mt-4"><i data-feather="save"></i> Enregistrer et
                            appliquer</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            {{-- ═══ Diagnostic ═══ --}}
            <div class="card mcf-card mb-4">
                <div class="card-header"><i data-feather="activity"></i> Diagnostic guidé</div>
                <div class="card-body">
                    @foreach ($checks as $c)
                        <div class="mcf-check {{ $c['ok'] ? 'ok' : 'ko' }}">
                            <span class="mcf-check-icon"><i data-feather="{{ $c['ok'] ? 'check' : 'x' }}"></i></span>
                            <span class="mcf-check-title">{{ $c['label'] }}</span>
                            <span class="mcf-check-sub">{{ $c['ok'] ? 'Validé' : 'À corriger' }}</span>
                        </div>
                    @endforeach
                    @if (!empty($connexion['erreur']))
                        <div class="alert alert-danger d-flex align-items-center gap-2 mt-3 mb-0">
                            <i data-feather="alert-triangle" style="width:18px;height:18px;flex-shrink:0"></i>
                            <span>{{ $connexion['erreur'] }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ═══ Rapports ═══ --}}
            <div class="card mcf-card">
                <div class="card-header"><i data-feather="printer"></i> Rapports fiscaux</div>
                <div class="card-body">
                    <p class="text-muted small mb-3">X = lecture (consultable à tout moment). Z = clôture
                        journalière, action irréversible.</p>

                    <form method="POST" action="{{ route('mcf.rapport') }}" class="mb-2">
                        @csrf
                        <input type="hidden" name="type_rapport" value="X">
                        <button class="mcf-report-btn mcf-report-x">
                            <span class="mcf-report-icon"><i data-feather="file-text"></i></span>
                            <span>Générer le rapport X<small>Lecture, non destructive</small></span>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('mcf.rapport') }}"
                        onsubmit="return confirm('Confirmer la clôture Z ?')">
                        @csrf
                        <input type="hidden" name="type_rapport" value="Z">
                        <button class="mcf-report-btn mcf-report-z">
                            <span class="mcf-report-icon"><i data-feather="lock"></i></span>
                            <span>Générer le rapport Z<small>Clôture journalière, irréversible</small></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ Historique ═══ --}}
    <div class="card mcf-card mt-4">
        <div class="card-header">
            <i data-feather="clock"></i> Historique des certifications physiques
            <span class="badge-count">{{ $certifications->total() }}</span>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Facture</th>
                        <th>Client</th>
                        <th>NIM</th>
                        <th>Signature</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($certifications as $c)
                        <tr>
                            <td>{{ optional($c->date_certification)->format('d/m/Y H:i') }}</td>
                            <td><a href="{{ route('module_ordre_facture.show', $c->facture_id) }}"
                                    class="fw-bold text-decoration-none">{{ optional($c->facture)->code }}</a></td>
                            <td>{{ optional(optional($c->facture)->Contribuable)->assujeti }}</td>
                            <td>{{ $c->nim }}</td>
                            <td><code class="mcf-sig">{{ $c->sig }}</code></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>Aucune certification physique.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($certifications->hasPages())
            <div class="card-footer bg-white border-top-0">{{ $certifications->links() }}</div>
        @endif
    </div>
</div>
