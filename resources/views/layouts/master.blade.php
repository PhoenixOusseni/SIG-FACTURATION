<!DOCTYPE html>
<html lang="fr">

<head>
    {{-- Anti-FOUC : applique le thème sauvegardé avant tout rendu --}}
    <script>
        (function(){
            var t = localStorage.getItem('sig-theme');
            if (t === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
        })();
    </script>
    @include('partials.meta')

    @yield('title')

    <title>SIG-FORIVISMAZARS</title>

    @yield('style')

    @include('partials.style')

    @notifyCss
    <style>
        .inset-0 {
            z-index: 999999999 !important;
        }

        .btn-1 {
            background-color: #133e9a;
            color: white;
        }

        .form-control {
            padding: 9px 12px;
            border: 2px solid #e9ecef;
            border-radius: 5px;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #1d3f7f;
            box-shadow: 0 0 0 0.2rem rgba(29, 63, 127, 0.1);
        }

        .form-select {
            padding: 9px 12px;
            border: 2px solid #e9ecef;
            border-radius: 5px;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .form-select:focus {
            border-color: #1d3f7f;
            box-shadow: 0 0 0 0.2rem rgba(29, 63, 127, 0.1);
        }

        .page-header{
            background: #133e9a
        }

        /* ============================================================
           ===== PAGE HEADER - DESIGN PROFESSIONNEL AVEC FILIGRANE =====
           ============================================================ */
        .page-header,
        .page-header.page-header-pro,
        .page-header.page-header-dark,
        .page-header.header-gradient {
            position: relative;
            overflow: visible; /* avoid clipping titles / action buttons under topnav */
            background: linear-gradient(135deg, #133e9a 0%, #1d4ed8 50%, #2563eb 100%) !important;
            border-radius: 0 0 24px 24px;
            padding: 2.25rem 0 2.75rem !important;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 30px rgba(19, 62, 154, 0.25);
            color: #fff;
        }

        /* Filigrane - icône géante en fond (clipped to header only) */
        .page-header .page-header-watermark {
            position: absolute;
            right: -30px;
            top: 50%;
            transform: translateY(-50%) rotate(-12deg);
            opacity: 0.08;
            pointer-events: none;
            z-index: 0;
            line-height: 0;
            overflow: hidden;
            max-height: 100%;
        }
        .page-header .page-header-watermark svg {
            width: 240px;
            height: 240px;
            stroke-width: 1;
            color: #fff;
        }

        /* Motif décoratif (cercles diagonaux) */
        .page-header .page-header-pattern {
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 15% 20%, rgba(255, 255, 255, 0.08) 0, transparent 35%),
                radial-gradient(circle at 85% 80%, rgba(255, 255, 255, 0.06) 0, transparent 40%);
            pointer-events: none;
            z-index: 0;
        }

        /* Liseré supérieur lumineux */
        .page-header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
            z-index: 1;
        }

        /* Filigrane SVG générique (motif hexagonal) appliqué à TOUS les page-header */
        .page-header::after {
            content: "";
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='160' height='160' viewBox='0 0 160 160'><g fill='none' stroke='%23ffffff' stroke-width='1' opacity='0.08'><circle cx='80' cy='80' r='40'/><circle cx='80' cy='80' r='60'/><circle cx='80' cy='80' r='20'/><path d='M20 80 L140 80 M80 20 L80 140'/></g></svg>");
            background-repeat: repeat;
            background-size: 160px 160px;
            opacity: .6;
            pointer-events: none;
            z-index: 0;
        }

        .page-header .page-header-content,
        .page-header .container-xl { position: relative; z-index: 2; }

        /* Icône principale */
        .page-header .page-header-icon {
            width: 64px;
            height: 64px;
            min-width: 64px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.18), inset 0 1px 0 rgba(255, 255, 255, 0.25);
            margin-right: 0;
        }
        .page-header .page-header-icon svg {
            width: 28px;
            height: 28px;
            stroke-width: 2.2;
        }

        /* Titre */
        .page-header .page-header-title {
            color: #ffffff !important;
            font-size: 1.65rem;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
            letter-spacing: .2px;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 0;
        }

        /* Sous-titre */
        .page-header .page-header-subtitle {
            color: rgba(255, 255, 255, 0.85) !important;
            font-size: .88rem;
            font-weight: 400;
            margin: .25rem 0 0;
            letter-spacing: .3px;
        }

        /* Breadcrumb dans header */
        .page-header .page-header-breadcrumb {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: .35rem;
            font-weight: 600;
        }
        .page-header .page-header-breadcrumb a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
        }
        .page-header .page-header-breadcrumb a:hover { color: #fff; }

        /* Boutons d'action à droite dans le header (style glass) */
        .page-header .btn {
            border-radius: 10px;
            font-weight: 600;
            padding: .55rem 1.1rem;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
        }
        .page-header .btn-primary,
        .page-header .btn-light {
            background: rgba(255, 255, 255, 0.95);
            border-color: rgba(255, 255, 255, 0.95);
            color: #133e9a;
        }
        .page-header .btn-primary:hover,
        .page-header .btn-light:hover {
            background: #fff;
            color: #0d3074;
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.18);
        }
        .page-header .btn-outline-light {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-color: rgba(255, 255, 255, 0.4);
            color: #fff;
        }
        .page-header .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: #fff;
            color: #fff;
        }

        /* Annule les paddings hérités */
        .page-header.pb-10 { padding-bottom: 2.75rem !important; }

        /* Responsive */
        @media (max-width: 767.98px) {
            .page-header { padding: 1.5rem 0 2rem !important; border-radius: 0 0 18px 18px; }
            .page-header .page-header-icon { width: 52px; height: 52px; min-width: 52px; border-radius: 12px; }
            .page-header .page-header-icon svg { width: 22px; height: 22px; }
            .page-header .page-header-title { font-size: 1.25rem; }
            .page-header .page-header-watermark svg { width: 160px; height: 160px; }
        }
    </style>

<body class="nav-fixed">
    @include('notify::components.notify')
    @include('partials.header')
    <div id="layoutSidenav_content">

        @yield('content')

        @include('partials.footer')
    </div>
    </div>
    @include('partials.script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
    @notifyJs
    @stack('scripts')
    @yield('script')
</body>

</html>
