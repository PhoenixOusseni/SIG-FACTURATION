<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,600&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="{{ asset('cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css') }}" rel="stylesheet" />
<link href="{{ asset('cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css') }}" rel="stylesheet" />
<link href="{{ asset('asset/css/styles.css') }}" rel="stylesheet" />
<link href="{{ asset('asset/css/dataTable.min.css') }}" rel="stylesheet" />
<link href="{{ asset('css/page-header.css') }}" rel="stylesheet" />
<link href="{{ asset('css/dark-mode.css') }}" rel="stylesheet" />
<link href="{{ asset('css/ux-modals.css') }}" rel="stylesheet" />
<link href="{{ asset('css/layout-sidenav.css') }}" rel="stylesheet" />
<link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

<!-- Professional Design Styles -->
@include('components.professional-styles')

<style type="text/css">
	/* ── POLICE PROFESSIONNELLE & UNIFORMISATION GLOBALE (Style Hub Facturation) ── */
	body, .body, html, input, button, select, textarea {
		font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
		font-weight: 500;
		color: #1e293b;
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;
	}

	h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
		font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
		font-weight: 700 !important;
		color: #0f172a;
		letter-spacing: -0.02em;
	}

	.font-mono, .badge-mono, .code-ref {
		font-family: 'JetBrains Mono', monospace !important;
	}

	/* ── MODALS HARMONISÉS STYLE HUB FACTURATION ── */
	.modal-content {
		border: none !important;
		border-radius: 14px !important;
		box-shadow: 0 20px 60px rgba(15, 23, 42, 0.22) !important;
		overflow: hidden;
	}

	.modal-header {
		background: #1d3f7f !important;
		color: #ffffff !important;
		border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
		padding: 1.1rem 1.5rem !important;
	}

	.modal-header .modal-title,
	.modal-header h5,
	.modal-header h4 {
		color: #ffffff !important;
		font-weight: 700 !important;
		font-size: 1.15rem !important;
		letter-spacing: -0.01em;
		margin-bottom: 0;
		display: flex;
		align-items: center;
		gap: 0.5rem;
	}

	.modal-header .btn-close {
		filter: invert(1) grayscale(100%) brightness(200%) !important;
		opacity: 0.85;
		transition: opacity 0.2s;
	}

	.modal-header .btn-close:hover {
		opacity: 1;
	}

	.modal-body {
		padding: 1.5rem !important;
		background: #ffffff;
	}

	.modal-footer {
		background-color: #f8fafc !important;
		border-top: 1px solid #e2e8f0 !important;
		padding: 0.85rem 1.5rem !important;
	}

	/* ── CARTES ET LISTES HARMONISÉES STYLE HUB FACTURATION ── */
	.card {
		border-radius: 12px !important;
		border: 1px solid #e2e8f0 !important;
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04) !important;
	}

	.card-header:not(.bg-transparent):not(.bg-primary):not(.header-gradient):not(.bg-danger):not(.bg-success):not(.bg-warning):not(.bg-info):not(.bg-dark) {
		background: #ffffff !important;
		border-bottom: 1px solid #e2e8f0 !important;
		font-weight: 700 !important;
		color: #1e293b !important;
	}

	/* ── TABLEAUX - LISTES HARMONISÉES ── */
	table thead th,
	.table thead th,
	.dataTable-table thead th {
		background: #1d3f7f !important;
		color: #ffffff !important;
		font-size: 0.82rem !important;
		text-transform: uppercase !important;
		letter-spacing: 0.5px !important;
		font-weight: 700 !important;
		border-bottom: 2px solid #152d5c !important;
		padding: 9px 12px !important;
		vertical-align: middle !important;
	}

	table tbody td,
	.table tbody td,
	.dataTable-table tbody td {
		font-size: 0.875rem !important;
		font-weight: 500 !important;
		color: #334155 !important;
		padding: 9px 12px !important;
		vertical-align: middle !important;
		border-bottom: 1px solid #f1f5f9 !important;
	}

	table tbody tr:hover td,
	.table tbody tr:hover td {
		background-color: #f8fafc !important;
	}

	/* ── NEUTRALISATION DE L'ANCIEN MARGIN NÉGATIF mt-n10 ── */
	.mt-n10 {
		margin-top: 0 !important;
	}

	/* ── SUPPRESSION DE L'ANCIEN BANDEAU BLEU LOURD PAGE-HEADER ── */
	header.page-header:not(.executive-header) {
		background: transparent !important;
		box-shadow: none !important;
		padding: 0 !important;
		margin-bottom: 0 !important;
		border-radius: 0 !important;
	}
	header.page-header:not(.executive-header)::before,
	header.page-header:not(.executive-header)::after,
	header.page-header:not(.executive-header) .page-header-watermark,
	header.page-header:not(.executive-header) .page-header-pattern {
		display: none !important;
	}

	/* ── ÉCRAN LARGE : PLEINE LARGEUR ÉLÉGANTE (ÉVITE LE RÉTRÉCISSEMENT ET LE DÉBORDEMENT) ── */
	.container-xl, .container-xxl, .container-fluid {
		max-width: 100% !important;
		width: 100% !important;
		padding-left: 1.5rem !important;
		padding-right: 1.5rem !important;
	}

	@media (min-width: 1200px) {
		.container-xl, .container-xxl, .container-lg, .container {
			max-width: 100% !important;
			width: 100% !important;
		}
	}

	@media (min-width: 1500px) {
		.container-xl, .container-xxl, .container-lg, .container {
			max-width: 100% !important;
			width: 100% !important;
		}
	}

	#layoutSidenav_content {
		width: 100% !important;
		min-width: 0 !important;
		overflow-x: hidden !important;
	}

	.table-responsive {
		width: 100% !important;
	}

	.table-responsive > table {
		width: 100% !important;
	}

	#datatablesSimple_filter, #datatablesSimple_info, #datatablesSimple_paginate {
		display: none;
	}
</style>
<script data-search-pseudo-elements="" defer="" src="{{ asset('cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js') }}" crossorigin="anonymous"></script>
<script src="{{ asset('cdnjs.cloudflare.com/ajax/libs/feather-icons/4.28.0/feather.min.js') }}"></script>

<script src="{{ asset('js/jquery-3.5.1.js') }}"></script>

<link href="{{ asset('vendor/select2/select2.min.css') }}" rel="stylesheet" />
<script src="{{ asset('vendor/select2/select2.min.js') }}" defer></script>

{{-- <script>
	$(document).ready(function() {
    $('.js-example-basic-single').select2();
});
</script> --}}

<style>
    .header-gradient:not(header):not(.page-header) {
        background-color: #1d3f7f;
    }
    header.header-gradient,
    .page-header.header-gradient {
        background: transparent !important;
    }
    .btn-1 {
        background-color: #1d3f7f;
        color: white;
    }

    /* ====================================================================
       Tables — design compact et professionnel (appliqué globalement)
       ==================================================================== */
    table {
        font-size: 13px;
    }

    table thead th {
        background: #1d3f7f !important;
        color: #fff !important;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .5px;
        font-weight: 700;
        border-bottom: 2px solid #16305f !important;
        padding: 4px 8px !important;
        vertical-align: middle;
        white-space: nowrap;
    }

    table tbody td,
    table tbody th {
        padding: 3px 8px !important;
        vertical-align: middle;
        border-color: #eef1f5;
    }

    table.table-striped > tbody > tr:nth-of-type(odd) > * {
        background-color: #fafbfd;
    }

    table.table-hover > tbody > tr:hover > * {
        background-color: #f4f6fb;
    }

    .card .table:last-child {
        margin-bottom: 0;
    }

    /* --- Uniformiser la hauteur des Select2 avec les inputs Bootstrap (form-control) --- */
    .select2-container .select2-selection--single,
    .select2-container .select2-selection--multiple {
        height: calc(1.5em + 0.75rem + 2px) !important;
        min-height: calc(1.5em + 0.75rem + 2px);
        border: 1px solid #ced4da !important;
        border-radius: 0.25rem !important;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        background-color: #fff;
        display: flex;
        align-items: center;
        box-sizing: border-box;
    }

    .select2-container .select2-selection--single .select2-selection__rendered {
        line-height: 1.5 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        color: #495057;
    }

    .select2-container .select2-selection--single .select2-selection__arrow {
        height: 100% !important;
        top: 0 !important;
        right: 6px;
    }

    .select2-container .select2-selection--single .select2-selection__placeholder {
        color: #6c757d;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__rendered {
        padding: 0 !important;
        line-height: 1.5;
    }

    .select2-container--focus .select2-selection--single,
    .select2-container--focus .select2-selection--multiple,
    .select2-container--open .select2-selection--single,
    .select2-container--open .select2-selection--multiple {
        border-color: #80bdff !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
    }

    /* Variante form-control-sm */
    .form-control-sm + .select2-container .select2-selection--single,
    .input-group-sm .select2-container .select2-selection--single {
        height: calc(1.5em + 0.5rem + 2px) !important;
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    /* ====================================================================
       Tableau "Éléments de la facture" — design homogène (proforma,
       facture définitive, facture fournisseur)
       ==================================================================== */
    .elements-table-wrap {
        border: 1px solid #e3e7ee;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
    }

    .table-elements {
        margin-bottom: 0;
    }

    .table-elements thead th {
        background: #eef1f8 !important;
        color: #46546b !important;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .6px;
        font-weight: 700;
        border-bottom: 2px solid #dde2eb;
        padding: 10px 12px;
        vertical-align: middle;
        white-space: nowrap;
    }

    .table-elements tbody td {
        padding: 8px 10px;
        vertical-align: middle;
        border-bottom: 1px solid #eef1f5;
        background: #fff;
    }

    .table-elements tbody tr:last-child td {
        border-bottom: none;
    }

    .table-elements tbody tr:hover td {
        background: #f9fafc;
    }

    .table-elements .row-index {
        width: 26px;
        height: 26px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #1d3f7f;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
    }

    .table-elements .montant-cell {
        font-weight: 700;
        color: #1d3f7f;
        white-space: nowrap;
    }

    .table-elements tfoot td {
        background: #eef1f8;
        padding: 12px;
        border-top: 2px solid #dde2eb;
        font-size: 0.95rem;
    }

    .btn-remove-row {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: 1px solid #f1b3ba;
        background: #fff5f5;
        color: #dc3545;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background .15s, color .15s;
    }

    .btn-remove-row:hover {
        background: #dc3545;
        color: #fff;
    }

    .btn-add-line {
        border: 1.5px dashed #1d3f7f;
        background: #fff;
        color: #1d3f7f;
        font-weight: 600;
        font-size: 0.9rem;
        border-radius: 8px;
        padding: 8px 18px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all .15s;
        margin-top: 12px;
    }

    .btn-add-line:hover {
        background: #1d3f7f;
        color: #fff;
    }

    .elements-fieldset legend {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* ============================================================
       DESIGN EXÉCUTIF HARMONISÉ : PAGINATION (DataTables & Laravel)
       ============================================================ */
    .dataTables_wrapper .dataTables_paginate,
    .pagination {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        margin-top: 1rem !important;
        margin-bottom: 0.5rem !important;
        padding: 4px !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
    }

    /* Boutons de pagination généraux */
    .dataTables_wrapper .dataTables_paginate .paginate_button,
    .pagination .page-item .page-link {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 38px !important;
        height: 38px !important;
        padding: 0 12px !important;
        border-radius: 10px !important;
        border: 1px solid #e2e8f0 !important;
        background: #ffffff !important;
        color: #475569 !important;
        text-decoration: none !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        cursor: pointer !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
    }

    /* Hover standard */
    .dataTables_wrapper .dataTables_paginate .paginate_button:not(.disabled):not(.current):hover,
    .pagination .page-item:not(.active):not(.disabled) .page-link:hover {
        background: #f1f5f9 !important;
        color: #1d3f7f !important;
        border-color: #cbd5e1 !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 10px rgba(29, 63, 127, 0.1) !important;
    }

    /* Bouton Actif / Page courante */
    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #1d3f7f 0%, #152d5c 100%) !important;
        color: #ffffff !important;
        border-color: #1d3f7f !important;
        box-shadow: 0 4px 12px rgba(29, 63, 127, 0.28) !important;
        font-weight: 700 !important;
    }

    /* Boutons distinctifs PRÉCÉDENT et SUIVANT */
    .dataTables_wrapper .dataTables_paginate .paginate_button.previous,
    .dataTables_wrapper .dataTables_paginate .paginate_button.next,
    .pagination .page-item:first-child .page-link,
    .pagination .page-item:last-child .page-link {
        padding: 0 16px !important;
        font-weight: 700 !important;
        letter-spacing: 0.2px !important;
        background: #f8fafc !important;
        border: 1px solid #cbd5e1 !important;
        color: #1e293b !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.previous:hover,
    .dataTables_wrapper .dataTables_paginate .paginate_button.next:hover,
    .pagination .page-item:first-child:not(.disabled) .page-link:hover,
    .pagination .page-item:last-child:not(.disabled) .page-link:hover {
        background: #ffffff !important;
        color: #1d3f7f !important;
        border-color: #1d3f7f !important;
        box-shadow: 0 4px 12px rgba(29, 63, 127, 0.15) !important;
    }

    /* État désactivé (disabled) */
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover,
    .pagination .page-item.disabled .page-link {
        background: #f8fafc !important;
        color: #94a3b8 !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
        opacity: 0.65 !important;
        transform: none !important;
        box-shadow: none !important;
    }

    /* Info de pagination */
    .dataTables_wrapper .dataTables_info {
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        color: #64748b !important;
        padding-top: 1rem !important;
    }

    /* Sélecteur de longueur de page */
    .dataTables_wrapper .dataTables_length {
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        color: #475569 !important;
        margin-bottom: 0.75rem !important;
    }

    .dataTables_wrapper .dataTables_length select {
        border-radius: 8px !important;
        border: 1px solid #cbd5e1 !important;
        padding: 4px 10px !important;
        font-weight: 600 !important;
        color: #1e293b !important;
        background-color: #ffffff !important;
        margin: 0 6px !important;
        outline: none !important;
    }
</style>
