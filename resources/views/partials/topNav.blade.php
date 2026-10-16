{{-- ====================== STYLES TOP NAV PRO ====================== --}}
<style>
    /* ===== Top Navigation - Design Professionnel ===== */
    .topnav.topnav-pro {
        background: #ffffff !important;
        height: 3.625rem;
        min-height: 3.625rem;
        padding: 0 1.25rem;
        margin: 0 !important;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.06);
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1039;
    }
    /* Le layout SB Admin gère déjà le décalage via top:3.625rem sur #layoutSidenav_content.
       On force uniquement margin/padding à 0 pour coller le page-header au topNav. */
    #layoutSidenav_content main,
    #layoutSidenav_content > main {
        margin-top: 0 !important;
        padding-top: 0 !important;
    }
    #layoutSidenav_content .page-header {
        margin-top: 0 !important;
    }

    /* Bouton toggle sidebar */
    .topnav-pro #sidebarToggle {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #475569;
        background: transparent;
        border: 1px solid transparent;
        transition: all .2s ease;
    }
    .topnav-pro #sidebarToggle:hover {
        background: #f1f5f9;
        color: #0d6efd;
        border-color: #e2e8f0;
        transform: translateY(-1px);
    }
    .topnav-pro #sidebarToggle svg { width: 20px; height: 20px; }

    /* Brand */
    .topnav-pro .navbar-brand {
        font-weight: 700;
        font-size: 1.05rem;
        letter-spacing: .3px;
        color: #0f172a !important;
        display: inline-flex;
        align-items: center;
        gap: .65rem;
        padding-left: .75rem;
    }
    .topnav-pro .brand-logo {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 6px 14px rgba(13, 110, 253, .35);
    }
    .topnav-pro .brand-logo svg { width: 20px; height: 20px; }
    .topnav-pro .brand-text { line-height: 1.1; }
    .topnav-pro .brand-text small {
        display: block;
        font-size: .68rem;
        font-weight: 500;
        color: #64748b;
        letter-spacing: 1.2px;
        text-transform: uppercase;
    }

    /* Recherche */
    .topnav-pro .topnav-search {
        position: relative;
        width: 320px;
        max-width: 100%;
    }
    .topnav-pro .topnav-search input {
        height: 38px;
        border-radius: 10px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding-left: 2.5rem;
        font-size: .9rem;
        transition: all .2s ease;
    }
    .topnav-pro .topnav-search input:focus {
        background: #fff;
        border-color: #0d6efd;
        box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .12);
    }
    .topnav-pro .topnav-search svg {
        position: absolute;
        left: .85rem;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        color: #94a3b8;
        pointer-events: none;
    }

    /* Boutons icônes */
    .topnav-pro .nav-icon-btn {
        position: relative;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: all .2s ease;
    }
    .topnav-pro .nav-icon-btn:hover {
        background: #fff;
        color: #0d6efd;
        border-color: #0d6efd;
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(13, 110, 253, .15);
    }
    .topnav-pro .nav-icon-btn svg { width: 18px; height: 18px; }
    .topnav-pro .nav-icon-btn .badge-dot {
        position: absolute;
        top: 8px;
        right: 9px;
        width: 9px;
        height: 9px;
        background: #ef4444;
        border-radius: 50%;
        border: 2px solid #fff;
    }

    /* Séparateur */
    .topnav-pro .nav-divider {
        width: 1px;
        height: 28px;
        background: #e2e8f0;
        margin: 0 .25rem;
    }

    /* User pill */
    .topnav-pro .user-pill {
        display: inline-flex;
        align-items: center;
        gap: .65rem;
        padding: .35rem .85rem .35rem .35rem;
        border-radius: 50px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #0f172a;
        text-decoration: none;
        transition: all .2s ease;
    }
    .topnav-pro .user-pill:hover {
        background: #fff;
        border-color: #0d6efd;
        box-shadow: 0 6px 14px rgba(13, 110, 253, .12);
        transform: translateY(-1px);
    }
    .topnav-pro .user-avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        color: #fff;
        font-weight: 700;
        font-size: .85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(13, 110, 253, .35);
    }
    .topnav-pro .user-meta { line-height: 1.15; text-align: left; }
    .topnav-pro .user-meta .user-name {
        font-size: .85rem;
        font-weight: 600;
        color: #0f172a;
        display: block;
    }
    .topnav-pro .user-meta .user-role {
        font-size: .7rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .5px;
    }
    .topnav-pro .user-pill svg.chevron { width: 16px; height: 16px; color: #94a3b8; }

    /* Dropdown menu */
    .topnav-pro .dropdown-menu {
        border: 1px solid #e2e8f0 !important;
        border-radius: 14px !important;
        padding: .5rem !important;
        box-shadow: 0 20px 50px rgba(15, 23, 42, .12) !important;
        min-width: 280px;
        margin-top: .6rem !important;
    }
    .topnav-pro .dropdown-menu .dropdown-header {
        padding: .75rem;
        background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
        border-radius: 10px;
        margin-bottom: 0;
    }
    .topnav-pro .dropdown-user-img {
        width: 44px !important;
        height: 44px !important;
        border-radius: 50% !important;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 4px 10px rgba(15, 23, 42, .1);
    }
    .topnav-pro .dropdown-user-details-name {
        font-weight: 700;
        color: #0f172a;
        font-size: .9rem;
    }
    .topnav-pro .dropdown-user-details-email a {
        color: #0d6efd;
        font-size: .75rem;
        text-decoration: none;
        text-transform: uppercase;
        letter-spacing: .5px;
        font-weight: 600;
    }
    .topnav-pro .dropdown-item {
        border-radius: 8px;
        padding: .55rem .75rem;
        font-size: .88rem;
        font-weight: 500;
        color: #334155;
        display: flex;
        align-items: center;
        gap: .65rem;
        transition: all .15s ease;
    }
    .topnav-pro .dropdown-item:hover {
        background: #eff6ff;
        color: #0d6efd;
    }
    .topnav-pro .dropdown-item .dropdown-item-icon {
        margin-right: 0;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #f1f5f9;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #475569;
    }
    .topnav-pro .dropdown-item:hover .dropdown-item-icon {
        background: #0d6efd;
        color: #fff;
    }
    .topnav-pro .dropdown-item.text-danger:hover {
        background: #fef2f2;
        color: #dc2626;
    }
    .topnav-pro .dropdown-item.text-danger:hover .dropdown-item-icon {
        background: #dc2626;
        color: #fff;
    }
    .topnav-pro .dropdown-divider {
        margin: .35rem .25rem;
        border-color: #e2e8f0;
    }

    /* Responsive */
    @media (max-width: 991.98px) {
        .topnav-pro .topnav-search { display: none !important; }
        .topnav-pro .user-meta { display: none; }
        .topnav-pro .user-pill { padding: .25rem; border-radius: 50%; }
    }
    @media (max-width: 575.98px) {
        .topnav-pro .brand-text span:first-child { font-size: .9rem; }
        .topnav-pro .brand-text small { display: none; }
        .topnav-pro .navbar-brand { padding-left: .25rem; }
    }

    /* ===== Dropdown utilisateur (CSS/JS pur, sans Bootstrap) ===== */
    #userDropdownWrapper {
        position: relative;
    }
    .user-dropdown-menu {
        display: none;
        position: absolute;
        right: 0;
        top: calc(100% + .6rem);
        z-index: 1050;
        min-width: 280px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: .5rem;
        box-shadow: 0 20px 50px rgba(15, 23, 42, .12);
    }
    .user-dropdown-menu.open {
        display: block;
        animation: fadeInUp .18s ease;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .user-dropdown-menu .dropdown-header {
        padding: .75rem;
        background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
        border-radius: 10px;
        margin-bottom: .25rem;
    }
    .user-dropdown-menu .dropdown-item {
        border-radius: 8px;
        padding: .55rem .75rem;
        font-size: .88rem;
        font-weight: 500;
        color: #334155;
        display: flex;
        align-items: center;
        gap: .65rem;
        text-decoration: none;
        transition: background .15s ease, color .15s ease;
    }
    .user-dropdown-menu .dropdown-item:hover {
        background: #eff6ff;
        color: #0d6efd;
    }
    .user-dropdown-menu .dropdown-item .dropdown-item-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #f1f5f9;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #475569;
        flex-shrink: 0;
    }
    .user-dropdown-menu .dropdown-item:hover .dropdown-item-icon {
        background: #0d6efd;
        color: #fff;
    }
    .user-dropdown-menu .dropdown-item.text-danger:hover {
        background: #fef2f2;
        color: #dc2626;
    }
    .user-dropdown-menu .dropdown-item.text-danger:hover .dropdown-item-icon {
        background: #dc2626;
        color: #fff;
    }
    .user-dropdown-menu .dropdown-divider {
        margin: .35rem .25rem;
        border-top: 1px solid #e2e8f0;
    }
    .user-dropdown-menu .dropdown-user-img {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 4px 10px rgba(15, 23, 42, .1);
    }
    .user-dropdown-menu .dropdown-user-details-name {
        font-weight: 700;
        color: #0f172a;
        font-size: .9rem;
    }
    .user-dropdown-menu .dropdown-user-details-email a {
        color: #0d6efd;
        font-size: .75rem;
        text-decoration: none;
        text-transform: uppercase;
        letter-spacing: .5px;
        font-weight: 600;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.getElementById('userDropdownToggle');
        var menu   = document.getElementById('userDropdownMenu');

        if (!toggle || !menu) return;

        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = menu.classList.contains('open');
            menu.classList.toggle('open', !isOpen);
            toggle.setAttribute('aria-expanded', String(!isOpen));
        });

        document.addEventListener('click', function () {
            menu.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
        });

        menu.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    });

    /* ===== Bascule thème clair / sombre ===== */
    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('themeToggle');
        if (!btn) return;

        btn.addEventListener('click', function () {
            var html    = document.documentElement;
            var current = html.getAttribute('data-theme');
            var next    = (current === 'dark') ? 'light' : 'dark';

            if (next === 'dark') {
                html.setAttribute('data-theme', 'dark');
            } else {
                html.removeAttribute('data-theme');
            }
            localStorage.setItem('sig-theme', next);
        });
    });
</script>

<nav class="topnav topnav-pro navbar navbar-expand navbar-light" id="sidenavAccordion">
    {{-- Bouton toggle sidebar --}}
    <button class="order-1 order-lg-0 me-2 ms-lg-0" id="sidebarToggle" type="button" aria-label="Menu">
        <i data-feather="menu"></i>
    </button>

    {{-- Brand --}}
    <a class="navbar-brand" href="{{ route('dashboard') }}">
        <span class="brand-logo">
            <i data-feather="layers"></i>
        </span>
        <span class="brand-text">
            <span>SIG - FACTURATION</span>
            <small>Système intégré de gestion</small>
        </span>
    </a>

    <ul class="navbar-nav align-items-center ms-auto">
        @include('partials.sfe-status')
        {{-- Notifications --}}
        <li class="nav-item me-2 d-none d-sm-block">
            <a class="nav-icon-btn" href="javascript:void(0);" aria-label="Notifications" title="Notifications">
                <i data-feather="bell"></i>
                <span class="badge-dot"></span>
            </a>
        </li>

        {{-- Messages --}}
        <li class="nav-item me-2 d-none d-sm-block">
            <a class="nav-icon-btn" href="javascript:void(0);" aria-label="Messages" title="Messages">
                <i data-feather="mail"></i>
            </a>
        </li>

        {{-- Bascule thème clair / sombre --}}
        <li class="nav-item me-2">
            <button class="theme-toggle-btn" id="themeToggle" type="button"
                    aria-label="Basculer mode sombre / clair" title="Mode sombre / clair">
                {{-- Lune : visible en mode clair --}}
                <svg class="icon-moon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
                {{-- Soleil : visible en mode sombre --}}
                <svg class="icon-sun" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="5"/>
                    <line x1="12" y1="1" x2="12" y2="3"/>
                    <line x1="12" y1="21" x2="12" y2="23"/>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                    <line x1="1" y1="12" x2="3" y2="12"/>
                    <line x1="21" y1="12" x2="23" y2="12"/>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                </svg>
            </button>
        </li>

        <li class="nav-divider d-none d-sm-block"></li>

        {{-- User dropdown --}}
        <li class="nav-item no-caret dropdown-user ms-2" id="userDropdownWrapper">
            <a class="user-pill" id="userDropdownToggle" href="javascript:void(0);" role="button"
                aria-haspopup="true" aria-expanded="false">
                <span class="user-avatar">
                    {{ strtoupper(substr(Auth::user()->prenom ?? Auth::user()->nom ?? 'U', 0, 1)) }}{{ strtoupper(substr(Auth::user()->nom ?? '', 0, 1)) }}
                </span>
                <span class="user-meta">
                    <span class="user-name">{{ Auth::user()->prenom }} {{ Auth::user()->nom }}</span>
                    <span class="user-role">{{ Auth::user()->role }}</span>
                </span>
                <i data-feather="chevron-down" class="chevron"></i>
            </a>
            <div class="user-dropdown-menu" id="userDropdownMenu" aria-labelledby="userDropdownToggle">
                <div class="dropdown-header d-flex align-items-center">
                    <img class="dropdown-user-img me-3" src="{{ asset('images/auth-bg.jpg') }}" alt="avatar" />
                    <div class="dropdown-user-details">
                        <div class="dropdown-user-details-name">
                            {{ Auth::user()->nom }} {{ Auth::user()->prenom }}
                        </div>
                        <div class="dropdown-user-details-email">
                            <a href="javascript:;">{{ Auth::user()->role }}</a>
                        </div>
                    </div>
                </div>
                <a class="dropdown-item" href="{{ route('module_utilisateur.show', [Auth::user()->id]) }}">
                    <span class="dropdown-item-icon"><i data-feather="user"></i></span>
                    Mon profil
                </a>
                <a class="dropdown-item" href="{{ route('parametres.index') }}">
                    <span class="dropdown-item-icon"><i data-feather="settings"></i></span>
                    Paramètres
                </a>
                <a class="dropdown-item" href="{{ route('aide_support') }}">
                    <span class="dropdown-item-icon"><i data-feather="help-circle"></i></span>
                    Aide &amp; Support
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <a class="dropdown-item text-danger" href="#"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        <span class="dropdown-item-icon"><i data-feather="log-out"></i></span>
                        {{ __('Se déconnecter') }}
                    </a>
                </form>
            </div>
        </li>
    </ul>
</nav>
