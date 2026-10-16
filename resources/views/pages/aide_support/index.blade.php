@extends('layouts.master')

@section('title')
    <title>SIG-FACTURATION | Aide & Support</title>
@endsection

@section('style')
    @include('partials.style')
    <style>
        /* ===== Hero Header ===== */
        .help-hero {
            background: linear-gradient(135deg, #0f2d6b 0%, #1d3f7f 40%, #2563eb 100%);
            padding: 3.5rem 0 6rem;
            position: relative;
            overflow: hidden;
        }
        .help-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(ellipse at 10% 80%, rgba(255,255,255,.06) 0%, transparent 60%),
                radial-gradient(ellipse at 90% 20%, rgba(255,255,255,.08) 0%, transparent 50%);
        }
        .help-hero-pattern {
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .help-hero-content { position: relative; z-index: 1; }
        .help-hero h1 { font-size: 2.25rem; font-weight: 800; color: #fff; letter-spacing: -.5px; }
        .help-hero p.lead { color: rgba(255,255,255,.8); font-size: 1.05rem; max-width: 580px; }

        /* Search bar hero */
        .help-search-wrap {
            background: rgba(255,255,255,.12);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 50px;
            padding: .35rem .35rem .35rem 1.25rem;
            display: flex;
            align-items: center;
            max-width: 520px;
            gap: .75rem;
        }
        .help-search-wrap input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            color: #fff;
            font-size: .95rem;
        }
        .help-search-wrap input::placeholder { color: rgba(255,255,255,.6); }
        .help-search-wrap button {
            background: #fff;
            color: #1d3f7f;
            border: none;
            border-radius: 50px;
            padding: .55rem 1.4rem;
            font-weight: 700;
            font-size: .88rem;
            cursor: pointer;
            white-space: nowrap;
            transition: all .2s;
        }
        .help-search-wrap button:hover { background: #e8efff; }

        /* Stats badges */
        .hero-stats {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-top: 2rem;
        }
        .hero-stat {
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 12px;
            padding: .65rem 1.2rem;
            display: flex;
            align-items: center;
            gap: .65rem;
            color: #fff;
        }
        .hero-stat svg { width: 18px; height: 18px; color: rgba(255,255,255,.7); }
        .hero-stat .stat-val { font-size: 1.1rem; font-weight: 700; }
        .hero-stat .stat-lbl { font-size: .75rem; opacity: .75; }

        /* ===== Main content pulled up ===== */
        .help-body { margin-top: -3.5rem; }

        /* ===== Quick action cards ===== */
        .quick-card {
            background: #fff;
            border: 1px solid #e8edf5;
            border-radius: 16px;
            padding: 1.75rem 1.5rem;
            text-align: center;
            transition: all .25s ease;
            cursor: pointer;
            height: 100%;
            text-decoration: none;
            display: block;
            color: inherit;
        }
        .quick-card:hover {
            border-color: #1d3f7f;
            box-shadow: 0 12px 32px rgba(29,63,127,.14);
            transform: translateY(-4px);
            color: inherit;
        }
        .quick-card .qc-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .quick-card .qc-icon svg { width: 28px; height: 28px; }
        .quick-card h5 { font-size: 1rem; font-weight: 700; color: #0f172a; margin-bottom: .4rem; }
        .quick-card p  { font-size: .83rem; color: #64748b; margin: 0; line-height: 1.5; }

        /* ===== Section titles ===== */
        .section-title {
            font-size: 1.35rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: .4rem;
        }
        .section-subtitle { font-size: .9rem; color: #64748b; margin-bottom: 2rem; }
        .section-divider {
            width: 48px;
            height: 4px;
            background: linear-gradient(90deg, #1d3f7f, #2563eb);
            border-radius: 2px;
            margin-bottom: .75rem;
        }

        /* ===== How it works – steps ===== */
        .step-card {
            background: #fff;
            border: 1px solid #e8edf5;
            border-radius: 16px;
            padding: 2rem 1.5rem;
            position: relative;
            height: 100%;
            transition: box-shadow .2s;
        }
        .step-card:hover { box-shadow: 0 8px 28px rgba(29,63,127,.1); }
        .step-number {
            position: absolute;
            top: -14px;
            left: 1.5rem;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1d3f7f, #2563eb);
            color: #fff;
            font-weight: 800;
            font-size: .8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(29,63,127,.35);
        }
        .step-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            background: #eff6ff;
            color: #1d3f7f;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        .step-icon svg { width: 24px; height: 24px; }
        .step-card h5 { font-size: .98rem; font-weight: 700; color: #0f172a; margin-bottom: .5rem; }
        .step-card p { font-size: .83rem; color: #64748b; margin: 0; line-height: 1.6; }

        /* ===== FAQ ===== */
        .faq-wrap .accordion-button {
            font-weight: 600;
            color: #0f172a;
            font-size: .93rem;
            background: #fff;
            box-shadow: none;
            padding: 1.15rem 1.25rem;
        }
        .faq-wrap .accordion-button:not(.collapsed) {
            color: #1d3f7f;
            background: #f0f5ff;
        }
        .faq-wrap .accordion-button::after {
            filter: hue-rotate(200deg);
        }
        .faq-wrap .accordion-item {
            border: 1px solid #e8edf5;
            border-radius: 12px !important;
            margin-bottom: .75rem;
            overflow: hidden;
        }
        .faq-wrap .accordion-body {
            font-size: .88rem;
            color: #475569;
            line-height: 1.7;
            padding: 0 1.25rem 1.25rem;
        }

        /* ===== Contact cards ===== */
        .contact-card {
            background: #fff;
            border: 1px solid #e8edf5;
            border-radius: 16px;
            padding: 1.75rem;
            height: 100%;
            transition: all .25s ease;
        }
        .contact-card:hover {
            border-color: #1d3f7f;
            box-shadow: 0 10px 28px rgba(29,63,127,.12);
            transform: translateY(-3px);
        }
        .contact-card .cc-icon {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
        }
        .contact-card .cc-icon svg { width: 26px; height: 26px; }
        .contact-card h5 { font-size: 1rem; font-weight: 700; color: #0f172a; margin-bottom: .35rem; }
        .contact-card .cc-desc { font-size: .82rem; color: #64748b; margin-bottom: 1rem; line-height: 1.5; }
        .contact-card .cc-value {
            font-size: 1rem;
            font-weight: 700;
            color: #1d3f7f;
            display: flex;
            align-items: center;
            gap: .5rem;
            text-decoration: none;
            word-break: break-all;
        }
        .contact-card .cc-value:hover { color: #2563eb; }
        .contact-card .cc-badge {
            display: inline-block;
            background: #dcfce7;
            color: #16a34a;
            font-size: .72rem;
            font-weight: 700;
            padding: .15rem .6rem;
            border-radius: 50px;
            margin-top: .35rem;
        }
        .contact-card .cc-badge.orange {
            background: #fff7ed;
            color: #ea580c;
        }

        /* ===== Modules grid ===== */
        .module-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            background: #fff;
            border: 1px solid #e8edf5;
            border-radius: 14px;
            padding: 1.25rem;
            transition: all .2s ease;
            margin-bottom: 1rem;
        }
        .module-item:hover {
            border-color: #bfdbfe;
            background: #f8fbff;
            box-shadow: 0 4px 16px rgba(29,63,127,.08);
        }
        .module-item .mi-icon {
            width: 44px;
            height: 44px;
            flex-shrink: 0;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .module-item .mi-icon svg { width: 22px; height: 22px; }
        .module-item h6 { font-size: .92rem; font-weight: 700; color: #0f172a; margin-bottom: .3rem; }
        .module-item p  { font-size: .8rem; color: #64748b; margin: 0; line-height: 1.5; }

        /* ===== Notice banner ===== */
        .notice-banner {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1px solid #bfdbfe;
            border-radius: 16px;
            padding: 2rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        .notice-banner .nb-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: #1d3f7f;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .notice-banner .nb-icon svg { width: 26px; height: 26px; }
        .notice-banner h5 { font-size: 1rem; font-weight: 700; color: #1e3a8a; margin-bottom: .35rem; }
        .notice-banner p  { font-size: .85rem; color: #1e40af; margin: 0; }

        @media (max-width: 767px) {
            .help-hero h1 { font-size: 1.6rem; }
            .hero-stats { gap: .75rem; }
            .notice-banner { flex-direction: column; text-align: center; }
        }
    </style>
@endsection

@section('content')
<main>

    {{-- ===== HERO ===== --}}
    <div class="help-hero">
        <div class="help-hero-pattern"></div>
        <div class="container-xl px-4 help-hero-content">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <div style="display:inline-flex;align-items:center;gap:.6rem;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.2);border-radius:50px;padding:.35rem 1rem .35rem .5rem;margin-bottom:1.25rem;">
                        <span style="background:#fff;color:#1d3f7f;border-radius:50px;padding:.15rem .7rem;font-size:.75rem;font-weight:700;">v1.0</span>
                        <span style="color:rgba(255,255,255,.85);font-size:.82rem;">SIG-FACTURATION — Système de gestion intégré</span>
                    </div>
                    <h1>Centre d'Aide &amp; Support</h1>
                    <p class="lead mt-2">Trouvez rapidement des réponses à vos questions, découvrez comment utiliser la plateforme et contactez notre équipe support.</p>

                    <div class="help-search-wrap mt-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color:rgba(255,255,255,.7);flex-shrink:0;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" placeholder="Rechercher dans la documentation…" id="helpSearch" />
                        <button type="button">Rechercher</button>
                    </div>

                    <div class="hero-stats">
                        <div class="hero-stat">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <div><div class="stat-val">Support dédié</div><div class="stat-lbl">Équipe disponible</div></div>
                        </div>
                        <div class="hero-stat">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <div><div class="stat-val">Lun – Ven</div><div class="stat-lbl">08h00 – 17h00</div></div>
                        </div>
                        <div class="hero-stat">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.58 3.44 2 2 0 0 1 3.55 1.25h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.73a16 16 0 0 0 6.29 6.29l.87-1.39a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21.73 16l.19.92z"/></svg>
                            <div><div class="stat-val">Réponse rapide</div><div class="stat-lbl">&lt; 24h ouvrées</div></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-flex justify-content-end">
                    <div style="width:260px;height:260px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <div style="width:200px;height:200px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                            <div style="width:130px;height:130px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== BODY ===== --}}
    <div class="container-xl px-4 help-body">

        {{-- Quick actions --}}
        <div class="row g-3 mb-5">
            <div class="col-sm-6 col-lg-3">
                <a href="#section-modules" class="quick-card">
                    <div class="qc-icon" style="background:#eff6ff;color:#1d4ed8;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    </div>
                    <h5>Modules</h5>
                    <p>Découvrez toutes les fonctionnalités disponibles sur la plateforme.</p>
                </a>
            </div>
            <div class="col-sm-6 col-lg-3">
                <a href="#section-how" class="quick-card">
                    <div class="qc-icon" style="background:#f0fdf4;color:#16a34a;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    </div>
                    <h5>Guide d'utilisation</h5>
                    <p>Apprenez à utiliser la plateforme étape par étape.</p>
                </a>
            </div>
            <div class="col-sm-6 col-lg-3">
                <a href="#section-faq" class="quick-card">
                    <div class="qc-icon" style="background:#fdf4ff;color:#9333ea;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                    <h5>FAQ</h5>
                    <p>Les réponses aux questions les plus fréquentes des utilisateurs.</p>
                </a>
            </div>
            <div class="col-sm-6 col-lg-3">
                <a href="#section-contact" class="quick-card">
                    <div class="qc-icon" style="background:#fff7ed;color:#ea580c;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.58 3.44 2 2 0 0 1 3.55 1.25h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.73a16 16 0 0 0 6.29 6.29l.87-1.39a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21.73 16l.19.92z"/></svg>
                    </div>
                    <h5>Nous contacter</h5>
                    <p>Joignez notre équipe technique pour toute demande d'assistance.</p>
                </a>
            </div>
        </div>

        {{-- ===== ABOUT THE PLATFORM ===== --}}
        <section id="section-about" class="mb-5">
            <div class="notice-banner">
                <div class="nb-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <div>
                    <h5>À propos de SIG-FACTURATION</h5>
                    <p>
                        SIG-FACTURATION est un système intégré de gestion conçu pour centraliser et simplifier l'ensemble du processus de facturation de votre organisation.
                        Il permet la gestion des factures clients et fournisseurs, le suivi des règlements, la gestion des dépenses, la génération de rapports et d'états financiers,
                        ainsi que la gestion des paramètres (clients, fournisseurs, produits, TVA, PSVBB, catégories, dépôts).
                        La plateforme est accessible aux utilisateurs autorisés selon leur rôle : <strong>Administrateur</strong>, <strong>Superviseur</strong> ou <strong>Opérateur</strong>.
                    </p>
                </div>
            </div>
        </section>

        {{-- ===== MODULES ===== --}}
        <section id="section-modules" class="mb-5">
            <div class="section-divider"></div>
            <div class="section-title">Modules de la plateforme</div>
            <div class="section-subtitle">Vue d'ensemble des fonctionnalités disponibles dans SIG-FACTURATION.</div>

            <div class="row g-0">
                <div class="col-lg-6 pe-lg-3">
                    <div class="module-item">
                        <div class="mi-icon" style="background:#eff6ff;color:#1d4ed8;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                        </div>
                        <div>
                            <h6>Tableau de bord</h6>
                            <p>Vue synthétique de l'activité : chiffre d'affaires, factures du jour, graphiques par dépôt et catégorie.</p>
                        </div>
                    </div>
                    <div class="module-item">
                        <div class="mi-icon" style="background:#f0fdf4;color:#16a34a;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        </div>
                        <div>
                            <h6>Factures clients</h6>
                            <p>Création et gestion des factures proforma, bons de livraison, saisies de factures et règlements clients.</p>
                        </div>
                    </div>
                    <div class="module-item">
                        <div class="mi-icon" style="background:#fff7ed;color:#ea580c;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        </div>
                        <div>
                            <h6>Factures fournisseurs</h6>
                            <p>Enregistrement et suivi des factures reçues des fournisseurs avec leur règlement associé.</p>
                        </div>
                    </div>
                    <div class="module-item">
                        <div class="mi-icon" style="background:#fdf4ff;color:#9333ea;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        </div>
                        <div>
                            <h6>Dépenses</h6>
                            <p>Saisie et suivi des dépenses internes avec gestion des règlements et historique complet.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 ps-lg-3">
                    <div class="module-item">
                        <div class="mi-icon" style="background:#f0fdf4;color:#16a34a;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                        </div>
                        <div>
                            <h6>États &amp; Rapports</h6>
                            <p>Solde client, état des taxes (TVA/PSVBB), états par catégorie et par dépôt. Exportation en PDF.</p>
                        </div>
                    </div>
                    <div class="module-item">
                        <div class="mi-icon" style="background:#eff6ff;color:#1d4ed8;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <div>
                            <h6>Gestion des utilisateurs</h6>
                            <p>Création de comptes, assignation des rôles (Admin, Superviseur, Opérateur) et gestion des accès.</p>
                        </div>
                    </div>
                    <div class="module-item">
                        <div class="mi-icon" style="background:#fff7ed;color:#ea580c;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
                        </div>
                        <div>
                            <h6>Structure &amp; Paramètres</h6>
                            <p>Configuration des clients, fournisseurs, produits, catégories, dépôts, taux TVA et PSVBB.</p>
                        </div>
                    </div>
                    <div class="module-item">
                        <div class="mi-icon" style="background:#fdf4ff;color:#9333ea;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        </div>
                        <div>
                            <h6>Administration</h6>
                            <p>Paramétrage de l'entête de facturation (logo, raison sociale, adresse, contacts) visible sur tous les documents imprimés.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===== HOW IT WORKS ===== --}}
        <section id="section-how" class="mb-5">
            <div class="section-divider"></div>
            <div class="section-title">Comment utiliser la plateforme ?</div>
            <div class="section-subtitle">Suivez ces étapes pour tirer le meilleur parti de SIG-FACTURATION.</div>

            <div class="row g-4">
                <div class="col-md-6 col-xl-3">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <div class="step-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <h5>Connexion sécurisée</h5>
                        <p>Connectez-vous avec vos identifiants fournis par l'administrateur. Votre rôle détermine les fonctionnalités accessibles.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <div class="step-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
                        </div>
                        <h5>Configurer les paramètres</h5>
                        <p>Avant toute facturation, renseignez les paramètres de base : clients, fournisseurs, produits, TVA, PSVBB et entête.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <div class="step-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        </div>
                        <h5>Créer et gérer les factures</h5>
                        <p>Saisissez vos factures clients ou fournisseurs, liez les produits et quantités, puis imprimez les documents en PDF.</p>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <div class="step-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                        </div>
                        <h5>Consulter les états</h5>
                        <p>Générez les rapports financiers : soldes clients, états de TVA/PSVBB, chiffres d'affaires par catégorie ou dépôt.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===== FAQ ===== --}}
        <section id="section-faq" class="mb-5">
            <div class="section-divider"></div>
            <div class="section-title">Questions fréquentes (FAQ)</div>
            <div class="section-subtitle">Des réponses rapides aux questions les plus posées par nos utilisateurs.</div>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="faq-wrap accordion" id="faqLeft">

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq1h">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    Comment créer une facture client ?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqLeft">
                                <div class="accordion-body">
                                    Rendez-vous dans <strong>Facturation → Factures clients → Saisie Facture</strong>. Cliquez sur <em>Nouvelle facture</em>, sélectionnez le client, la catégorie, le dépôt, ajoutez les lignes de produit avec leurs quantités et prix, puis validez. La facture peut ensuite être imprimée au format PDF.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq2h">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Comment enregistrer un règlement de facture ?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqLeft">
                                <div class="accordion-body">
                                    Allez dans <strong>Facturation → Factures clients → Règlements factures</strong>. Sélectionnez la facture concernée, saisissez le montant réglé, la date et le mode de paiement. Le statut de la facture sera mis à jour automatiquement.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq3h">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Comment transformer une proforma en facture ?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqLeft">
                                <div class="accordion-body">
                                    Depuis la liste des factures proforma, cliquez sur <em>Créer un bordereau</em> pour générer d'abord un bon de livraison. Une fois le bon livré, utilisez l'action <em>Transformer en facture</em> pour finaliser la facture client.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq4h">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Comment imprimer un document (facture, état…) ?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqLeft">
                                <div class="accordion-body">
                                    Chaque document dispose d'un bouton <strong>Imprimer</strong> ou <strong>PDF</strong> dans la liste ou la fiche détail. Cliquez dessus pour ouvrir la prévisualisation dans un nouvel onglet et lancez l'impression depuis votre navigateur (Ctrl+P).
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="faq-wrap accordion" id="faqRight">

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq5h">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    Quelle est la différence entre les rôles utilisateurs ?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqRight">
                                <div class="accordion-body">
                                    <strong>Administrateur</strong> : accès complet à toutes les fonctionnalités, y compris la gestion des utilisateurs.<br>
                                    <strong>Superviseur</strong> : accès à la facturation, aux états et à la gestion des utilisateurs, sans configuration système.<br>
                                    <strong>Opérateur</strong> : accès limité à la saisie des factures et des dépenses.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq6h">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                    Comment modifier l'entête des documents imprimés ?
                                </button>
                            </h2>
                            <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqRight">
                                <div class="accordion-body">
                                    Allez dans <strong>Administration → Entête</strong>. Vous pouvez y modifier la raison sociale, l'adresse, le numéro RCCM, la NIF, les contacts et le logo de votre organisation. Ces informations apparaîtront sur tous les documents générés.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq7h">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                                    Comment changer mon mot de passe ?
                                </button>
                            </h2>
                            <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqRight">
                                <div class="accordion-body">
                                    Cliquez sur votre nom en haut à droite, puis sur <em>Mon profil</em>. Une section dédiée au changement de mot de passe est disponible. Saisissez votre ancien mot de passe, le nouveau et confirmez, puis validez.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq8h">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq8">
                                    Que faire si je n'arrive pas à me connecter ?
                                </button>
                            </h2>
                            <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#faqRight">
                                <div class="accordion-body">
                                    Vérifiez vos identifiants (login et mot de passe). Si le problème persiste, contactez votre administrateur système pour réinitialiser votre compte. Vous pouvez aussi contacter notre support technique via les coordonnées ci-dessous.
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        {{-- ===== CONTACT ===== --}}
        <section id="section-contact" class="mb-5">
            <div class="section-divider"></div>
            <div class="section-title">Nous contacter</div>
            <div class="section-subtitle">Notre équipe est disponible pour vous accompagner dans l'utilisation de la plateforme.</div>

            <div class="row g-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="contact-card">
                        <div class="cc-icon" style="background:#eff6ff;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#1d4ed8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.58 3.44 2 2 0 0 1 3.55 1.25h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.73a16 16 0 0 0 6.29 6.29l.87-1.39a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21.73 16l.19.92z"/></svg>
                        </div>
                        <h5>Support Technique</h5>
                        <p class="cc-desc">Pour toute assistance technique ou dysfonctionnement de la plateforme.</p>
                        <a href="tel:+22671178598" class="cc-value">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.58 3.44 2 2 0 0 1 3.55 1.25h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.73a16 16 0 0 0 6.29 6.29l.87-1.39a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21.73 16l.19.92z"/></svg>
                            +226 71 17 85 98
                        </a>
                        <div class="cc-badge">Lun – Ven  08h–17h</div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="contact-card">
                        <div class="cc-icon" style="background:#f0fdf4;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.58 3.44 2 2 0 0 1 3.55 1.25h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.73a16 16 0 0 0 6.29 6.29l.87-1.39a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21.73 16l.19.92z"/></svg>
                        </div>
                        <h5>Assistance Fonctionnelle</h5>
                        <p class="cc-desc">Questions sur l'utilisation des modules de facturation et des états.</p>
                        <a href="tel:+22671178598" class="cc-value" style="color:#16a34a;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.58 3.44 2 2 0 0 1 3.55 1.25h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.73a16 16 0 0 0 6.29 6.29l.87-1.39a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21.73 16l.19.92z"/></svg>
                            +226 71 17 85 98
                        </a>
                        <div class="cc-badge">Lun – Ven  08h–17h</div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="contact-card">
                        <div class="cc-icon" style="background:#fff7ed;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </div>
                        <h5>E-mail Support</h5>
                        <p class="cc-desc">Envoyez-nous un message pour toute demande non urgente ou signalement de bug.</p>
                        <a href="mailto:support@safeco.net" class="cc-value" style="color:#ea580c;font-size:.88rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            support@safeco.net
                        </a>
                        <div class="cc-badge orange">Réponse &lt; 24h ouvrées</div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="contact-card">
                        <div class="cc-icon" style="background:#fdf4ff;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#9333ea" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <h5>Adresse</h5>
                        <p class="cc-desc">Vous pouvez également nous rendre visite ou nous envoyer un courrier.</p>
                        <span class="cc-value" style="color:#9333ea;font-size:.88rem;cursor:default;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            Ouagadougou, Burkina Faso
                        </span>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===== VERSION INFO ===== --}}
        <section class="mb-5">
            <div class="row g-3">
                <div class="col-md-4">
                    <div style="background:#fff;border:1px solid #e8edf5;border-radius:14px;padding:1.5rem;display:flex;align-items:center;gap:1rem;">
                        <div style="width:44px;height:44px;border-radius:12px;background:#eff6ff;color:#1d4ed8;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                        </div>
                        <div>
                            <div style="font-size:.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Version</div>
                            <div style="font-size:1rem;font-weight:700;color:#0f172a;">SIG-FACTURATION v1.0</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div style="background:#fff;border:1px solid #e8edf5;border-radius:14px;padding:1.5rem;display:flex;align-items:center;gap:1rem;">
                        <div style="width:44px;height:44px;border-radius:12px;background:#f0fdf4;color:#16a34a;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <div>
                            <div style="font-size:.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Environnement</div>
                            <div style="font-size:1rem;font-weight:700;color:#0f172a;">Laravel · PHP · XAMPP</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div style="background:#fff;border:1px solid #e8edf5;border-radius:14px;padding:1.5rem;display:flex;align-items:center;gap:1rem;">
                        <div style="width:44px;height:44px;border-radius:12px;background:#fff7ed;color:#ea580c;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <div>
                            <div style="font-size:.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Dernière mise à jour</div>
                            <div style="font-size:1rem;font-weight:700;color:#0f172a;">{{ \Carbon\Carbon::now()->format('d/m/Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>{{-- end container --}}
</main>
@endsection

@section('script')
    @include('partials.script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#section-"]').forEach(function (link) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    var target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });
        });
    </script>
@endsection
