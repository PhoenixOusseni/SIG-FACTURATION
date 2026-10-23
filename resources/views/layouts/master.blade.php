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

        /* ============================================================
           ===== EN-TÊTE HARMONISÉ EXECUTIVE (STYLE HUB FACTURATION) =====
           ============================================================ */
        header.page-header,
        .page-header,
        .page-header.page-header-pro,
        .page-header.page-header-dark,
        .page-header.header-gradient {
            position: relative !important;
            background: transparent !important;
            background-image: none !important;
            box-shadow: none !important;
            border: none !important;
            border-radius: 0 !important;
            padding: 1.25rem 0 0.5rem !important;
            margin-bottom: 0.75rem !important;
            color: #1e293b !important;
            overflow: visible !important;
        }

        /* Supprime tous les filigranes géants et motifs d'arrière-plan */
        .page-header::before,
        .page-header::after,
        .page-header .page-header-watermark,
        .page-header .page-header-pattern,
        .page-header .container-xl::before {
            display: none !important;
        }

        /* Cadre Executive Card identique au Hub Facturation */
        .page-header .page-header-content {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%) !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 14px !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04) !important;
            padding: 1.25rem 1.75rem !important;
            position: relative !important;
            z-index: 2 !important;
        }

        /* Icône principale style Hub */
        .page-header .page-header-icon {
            width: 48px !important;
            height: 48px !important;
            min-width: 48px !important;
            border-radius: 12px !important;
            background: rgba(29, 63, 127, 0.08) !important;
            border: 1px solid rgba(29, 63, 127, 0.15) !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #1d3f7f !important;
            box-shadow: none !important;
            backdrop-filter: none !important;
            margin-right: 0.75rem !important;
        }
        .page-header .page-header-icon svg,
        .page-header .page-header-icon i {
            width: 24px !important;
            height: 24px !important;
            color: #1d3f7f !important;
            stroke-width: 2 !important;
        }

        /* Titre professionnel */
        .page-header .page-header-title {
            color: #0f172a !important;
            font-size: 1.5rem !important;
            font-weight: 700 !important;
            margin: 0 !important;
            line-height: 1.2 !important;
            letter-spacing: -0.5px !important;
            text-shadow: none !important;
            display: flex !important;
            align-items: center !important;
        }

        /* Sous-titre */
        .page-header .page-header-subtitle {
            color: #64748b !important;
            font-size: 0.875rem !important;
            font-weight: 400 !important;
            margin: 0.25rem 0 0 !important;
            letter-spacing: normal !important;
        }

        /* Fil d'Ariane */
        .page-header .page-header-breadcrumb {
            font-size: 0.72rem !important;
            text-transform: uppercase !important;
            letter-spacing: 1px !important;
            color: #64748b !important;
            margin-bottom: 0.25rem !important;
            font-weight: 600 !important;
        }
        .page-header .page-header-breadcrumb a {
            color: #1d3f7f !important;
            text-decoration: none !important;
        }
        .page-header .page-header-breadcrumb a:hover {
            color: #0f172a !important;
            text-decoration: underline !important;
        }

        /* Boutons d'actions */
        .page-header .btn {
            border-radius: 8px !important;
            font-weight: 600 !important;
            padding: 0.5rem 1rem !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08) !important;
        }
        .page-header .btn-primary {
            background-color: #1d3f7f !important;
            border-color: #1d3f7f !important;
            color: #ffffff !important;
        }
        .page-header .btn-primary:hover {
            background-color: #152d5c !important;
            border-color: #152d5c !important;
            color: #ffffff !important;
        }

        /* Annuler le padding pb-10 et le décalage mt-n10 sur toutes les pages */
        .page-header.pb-10 { padding-bottom: 0.5rem !important; }
        .mt-n10 { margin-top: 0 !important; }

        @media (max-width: 767.98px) {
            .page-header .page-header-icon { width: 40px !important; height: 40px !important; min-width: 40px !important; }
            .page-header .page-header-title { font-size: 1.2rem !important; }
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
    <script src="{{ asset('cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js') }}"></script>
    @notifyJs
    @stack('scripts')
    @yield('script')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Nettoyage automatique des préfixes d'écrans non professionnels (ex: Écran 2.4 :)
            document.querySelectorAll('.page-header-title, h1, h2, .card-header').forEach(function(el) {
                if (el.childNodes && el.childNodes.length) {
                    el.childNodes.forEach(function(node) {
                        if (node.nodeType === Node.TEXT_NODE && node.nodeValue) {
                            node.nodeValue = node.nodeValue.replace(/(?:Écran|Ecran)\s+[\d\.]+\s*:\s*/gi, '');
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>
