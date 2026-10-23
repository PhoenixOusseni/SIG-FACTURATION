<li class="nav-item me-2" id="sfeStatus" data-url="{{ route('sfe.status') }}">
    <a class="nav-icon-btn" href="{{ route('mcf.index') }}" title="État du boîtier fiscal" style="width:auto;padding:0 .75rem;gap:.4rem">
        <span id="sfeStatusDot" style="width:9px;height:9px;border-radius:50%;background:#10b981"></span>
        <span id="sfeStatusText" class="d-none d-lg-inline">SFE</span>
    </a>
</li>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var root = document.getElementById('sfeStatus');
    if (!root) return;

    function applyStatus(s) {
        if (!s) return;
        var colors = {connected:'#10b981', warning:'#f59e0b', disconnected:'#ef4444'};
        var dot = document.getElementById('sfeStatusDot');
        var txt = document.getElementById('sfeStatusText');
        if (dot) dot.style.background = colors[s.level] || colors.warning;
        if (txt) txt.textContent = s.level === 'connected' ? 'SFE prêt' : (s.level === 'warning' ? 'SFE attention' : 'SFE hors ligne');
        root.querySelector('a').title = s.message || 'État SFE';
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
