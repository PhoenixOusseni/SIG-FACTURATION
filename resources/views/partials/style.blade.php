<link href="{{ asset('cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css') }}" rel="stylesheet" />
<link href="{{ asset('cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css') }}" rel="stylesheet" />
<link href="{{ asset('asset/css/styles.css') }}" rel="stylesheet" />
<link href="{{ asset('asset/css/dataTable.min.css') }}" rel="stylesheet" />
<link href="{{ asset('css/professional-design.css') }}" rel="stylesheet" />
<link href="{{ asset('css/page-header.css') }}" rel="stylesheet" />
<link href="{{ asset('css/bootstrap-overrides.css') }}" rel="stylesheet" />
<link href="{{ asset('css/page-header.css') }}" rel="stylesheet" />
<link href="{{ asset('css/dark-mode.css') }}" rel="stylesheet" />
<link href="{{ asset('css/ux-modals.css') }}" rel="stylesheet" />
<link href="{{ asset('css/layout-sidenav.css') }}" rel="stylesheet" />
<link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

<!-- Professional Design Styles -->
@include('components.professional-styles')

<style type="text/css">
	#datatablesSimple_filter, #datatablesSimple_info, #datatablesSimple_paginate {
		display: none;
	}
</style>
<script data-search-pseudo-elements="" defer="" src="{{ asset('cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js') }}" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.28.0/feather.min.js" crossorigin="anonymous"></script>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>

{{-- <script>
	$(document).ready(function() {
    $('.js-example-basic-single').select2();
});
</script> --}}

<style>
    .header-gradient {
        background-color: #1d3f7f;
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
</style>
