<li class="nav-item me-2" id="sfeStatus" data-url="{{ route('sfe.status') }}">
    <a class="nav-icon-btn" href="{{ route('mcf.index') }}" title="État du boîtier fiscal" style="width:auto;padding:0 .75rem;gap:.4rem">
        <span id="sfeStatusDot" style="width:9px;height:9px;border-radius:50%;background:#f59e0b"></span>
        <span id="sfeStatusText" class="d-none d-lg-inline">SFE...</span>
    </a>
</li>
<script>
document.addEventListener('DOMContentLoaded',function(){var root=document.getElementById('sfeStatus');if(!root)return;fetch(root.dataset.url,{headers:{'Accept':'application/json'}}).then(r=>r.json()).then(s=>{var colors={connected:'#10b981',warning:'#f59e0b',disconnected:'#ef4444'};document.getElementById('sfeStatusDot').style.background=colors[s.level]||colors.warning;document.getElementById('sfeStatusText').textContent=s.level==='connected'?'SFE prêt':(s.level==='warning'?'SFE attention':'SFE hors ligne');root.querySelector('a').title=s.message||'État SFE';}).catch(()=>{document.getElementById('sfeStatusDot').style.background='#ef4444';document.getElementById('sfeStatusText').textContent='SFE hors ligne';});});
</script>
