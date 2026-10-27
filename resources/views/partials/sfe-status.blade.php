<li class="nav-item me-2" id="sfeStatus" data-url="{{ route('sfe.status') }}">
    <a class="nav-icon-btn position-relative" href="{{ route('mcf.index') }}" id="sfeStatusBtn" title="État du boîtier fiscal" style="width:auto;padding:0 .75rem;gap:.4rem">
        <span id="sfeStatusDot" style="width:9px;height:9px;border-radius:50%;background:#10b981"></span>
        <span id="sfeStatusText" class="d-none d-lg-inline">SFE</span>
        <span id="sfe7DaysBadge" class="badge bg-danger rounded-pill px-2 py-1 ms-1 d-none shadow-sm" style="font-size: 10px; cursor: pointer;" title="Alerte réglementaire : Boîtier non synchronisé DGI depuis plus de 7 jours (cliquer pour voir le détail)">
            <i class="fas fa-exclamation-triangle me-1"></i><span id="sfe7DaysText">>7j</span>
        </span>
    </a>
</li>

<!-- Modal d'Alerte Réglementaire DGI : MCF > 7 Jours Déconnecté (Compact & Épuré) -->
<div class="modal fade" id="mcf7DaysAlertModal" tabindex="-1" aria-labelledby="mcf7DaysAlertModalLabel" aria-hidden="true" style="z-index: 1065;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-body p-4 text-center">
                <!-- Icône ronde compacte -->
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 52px; height: 52px; background-color: #fee2e2;">
                    <i class="fas fa-satellite-dish text-danger fa-lg"></i>
                </div>

                <h5 class="fw-bold text-dark mb-1" id="mcf7DaysAlertModalLabel">
                    Synchronisation DGI Requise
                </h5>
                <p class="text-muted small mb-3">
                    Délai légal hors-ligne dépassé (> 7 jours)
                </p>

                <!-- Encadré compact avec jours et date -->
                <div class="p-3 rounded-3 mb-3 text-start" style="background-color: #fef2f2; border: 1px solid #fecaca;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-danger fw-bold small">Délai sans transmission :</span>
                        <span class="badge bg-danger fs-6 px-2 py-0.5 font-monospace" id="mcfAlertDaysBadge">> 7 jours</span>
                    </div>
                    <div class="text-muted small">
                        <i class="fas fa-clock me-1"></i> Dernière liaison : 
                        <strong class="text-dark font-monospace" id="mcfAlertLastSyncDate">Non disponible</strong>
                    </div>
                </div>

                <p class="text-muted small mb-0" style="font-size: 12.5px; line-height: 1.5;">
                    Veuillez vérifier la connexion Internet du boîtier MCF (câble Ethernet ou carte SIM) et relancer la télétransmission.
                </p>
            </div>

            <div class="modal-footer bg-light border-0 justify-content-center gap-2 py-3 px-4">
                <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-medium" data-bs-dismiss="modal">
                    Fermer
                </button>
                <a href="{{ route('mcf.index') }}" class="btn btn-danger btn-sm px-3 fw-bold shadow-sm">
                    <i class="fas fa-sync-alt me-1"></i> Ouvrir Console MCF
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var root = document.getElementById('sfeStatus');
    if (!root) return;

    var sfeModalElement = document.getElementById('mcf7DaysAlertModal');
    // Déplacer le modal directement à la racine de <body> pour éliminer l'effet de superposition grisée du backdrop
    if (sfeModalElement && sfeModalElement.parentNode !== document.body) {
        document.body.appendChild(sfeModalElement);
    }

    function show7DaysModal(s) {
        if (!sfeModalElement) return;
        var daysBadge = document.getElementById('mcfAlertDaysBadge');
        var syncDate = document.getElementById('mcfAlertLastSyncDate');
        if (daysBadge) {
            daysBadge.textContent = (s && s.dgi_days_since !== null && s.dgi_days_since !== undefined) 
                ? (s.dgi_days_since + ' jours') 
                : '> 7 jours';
        }
        if (syncDate && s && s.dgi_last_sync) {
            syncDate.textContent = s.dgi_last_sync;
        }
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var inst = bootstrap.Modal.getOrCreateInstance(sfeModalElement);
            inst.show();
        } else if (typeof $ !== 'undefined' && $('#mcf7DaysAlertModal').modal) {
            $('#mcf7DaysAlertModal').modal('show');
        }
    }

    function applyStatus(s) {
        if (!s) return;
        var colors = {connected:'#10b981', warning:'#f59e0b', disconnected:'#ef4444'};
        var dot = document.getElementById('sfeStatusDot');
        var txt = document.getElementById('sfeStatusText');
        var badge7d = document.getElementById('sfe7DaysBadge');
        var text7d = document.getElementById('sfe7DaysText');
        var btn = document.getElementById('sfeStatusBtn');

        if (dot) dot.style.background = colors[s.level] || colors.warning;
        if (txt) txt.textContent = s.level === 'connected' ? 'SFE prêt' : (s.level === 'warning' ? 'SFE attention' : 'SFE hors ligne');

        if (badge7d) {
            if (s.warn_7_days) {
                badge7d.classList.remove('d-none');
                if (text7d) text7d.textContent = s.dgi_days_since ? (s.dgi_days_since + 'j') : '>7j';

                // Intercepter le clic sur le badge pour ouvrir le modal
                badge7d.onclick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    show7DaysModal(s);
                };

                // Pop-up automatique de l'alerte à chaque connexion
                setTimeout(function() {
                    show7DaysModal(s);
                }, 700);
            } else {
                badge7d.classList.add('d-none');
            }
        }
        if (btn) btn.title = s.message || 'État SFE';
    }

    // Client-side cache for 30s to avoid redundant requests during fast page browsing
    var cached = null;
    try {
        var raw = sessionStorage.getItem('sig_sfe_status');
        if (raw) {
            var parsed = JSON.parse(raw);
            if (Date.now() - parsed.ts < 30000) {
                cached = parsed.data;
                applyStatus(cached);
                return;
            }
        }
    } catch(e) {}

    // Strict 1.5s timeout abort controller so offline bridge never blocks UI
    var controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
    var signal = controller ? controller.signal : undefined;
    var timer = setTimeout(function() { if (controller) controller.abort(); }, 1500);

    fetch(root.dataset.url, {
        headers: {'Accept': 'application/json'},
        signal: signal
    })
    .then(function(r) { return r.json(); })
    .then(function(s) {
        clearTimeout(timer);
        applyStatus(s);
        try {
            sessionStorage.setItem('sig_sfe_status', JSON.stringify({ts: Date.now(), data: s}));
        } catch(e) {}
    })
    .catch(function() {
        clearTimeout(timer);
        if (!cached) {
            var dot = document.getElementById('sfeStatusDot');
            var txt = document.getElementById('sfeStatusText');
            if (dot) dot.style.background = '#9ca3af';
            if (txt) txt.textContent = 'SFE';
        }
    });
});
</script>
