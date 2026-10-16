<footer class="footer-admin footer-pro mt-auto">
    <div class="container-xl px-4">
        <div class="footer-pro-inner">
            <div class="footer-brand">
                <span class="footer-logo">
                    <i data-feather="layers"></i>
                </span>
                <div class="footer-brand-text">
                    <strong>SIG - FACTURATION</strong>
                    <small>Système intégré de gestion · v1.0</small>
                </div>
            </div>

            <div class="footer-meta">
                <span class="footer-copy">
                    © {{ date('Y') }} Gestion de stock — Tous droits réservés.
                </span>
                <span class="footer-sep">•</span>
                <a href="javascript:;" class="footer-link">
                    Développé par <strong>Safreco</strong>
                </a>
            </div>

            <div class="footer-links">
                <a href="javascript:;" class="footer-pill" title="Aide">
                    <i data-feather="help-circle"></i>
                </a>
                <a href="javascript:;" class="footer-pill" title="Confidentialité">
                    <i data-feather="shield"></i>
                </a>
                <a href="#" onclick="window.scrollTo({top:0,behavior:'smooth'});return false;"
                    class="footer-pill" title="Retour en haut">
                    <i data-feather="arrow-up"></i>
                </a>
            </div>
        </div>
    </div>
</footer>

<style>
    /* ===== Footer - Design Professionnel ===== */
    .footer-admin.footer-pro {
        background: #ffffff;
        border-top: 1px solid rgba(0, 0, 0, 0.06);
        box-shadow: 0 -2px 12px rgba(15, 23, 42, 0.04);
        padding: .9rem 0;
        font-size: .85rem;
        color: #475569;
    }
    .footer-pro-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    /* Brand */
    .footer-pro .footer-brand {
        display: inline-flex;
        align-items: center;
        gap: .65rem;
    }
    .footer-pro .footer-logo {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(13, 110, 253, .3);
    }
    .footer-pro .footer-logo svg { width: 16px; height: 16px; }
    .footer-pro .footer-brand-text { line-height: 1.15; }
    .footer-pro .footer-brand-text strong {
        display: block;
        font-size: .82rem;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: .3px;
    }
    .footer-pro .footer-brand-text small {
        font-size: .68rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 500;
    }

    /* Meta */
    .footer-pro .footer-meta {
        display: inline-flex;
        align-items: center;
        gap: .6rem;
        flex-wrap: wrap;
        justify-content: center;
        color: #64748b;
        font-size: .82rem;
    }
    .footer-pro .footer-sep { color: #cbd5e1; }
    .footer-pro .footer-link {
        color: #475569;
        text-decoration: none;
        transition: color .2s ease;
    }
    .footer-pro .footer-link:hover { color: #0d6efd; }
    .footer-pro .footer-link strong { color: #0d6efd; font-weight: 600; }

    /* Pills */
    .footer-pro .footer-links {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
    }
    .footer-pro .footer-pill {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all .2s ease;
    }
    .footer-pro .footer-pill:hover {
        background: #fff;
        color: #0d6efd;
        border-color: #0d6efd;
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(13, 110, 253, .15);
    }
    .footer-pro .footer-pill svg { width: 15px; height: 15px; }

    /* Responsive */
    @media (max-width: 767.98px) {
        .footer-pro-inner { justify-content: center; text-align: center; }
        .footer-pro .footer-brand { width: 100%; justify-content: center; }
        .footer-pro .footer-meta { width: 100%; justify-content: center; }
        .footer-pro .footer-links { width: 100%; justify-content: center; }
    }
</style>
