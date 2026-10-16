<!-- Styles professionnels pour formulaires et tableaux -->
<style>
    :root {
        --primary-color: #1d3f7f;
        --primary-hover: #152d5c;
        --danger-color: #dc3545;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --light-bg: #f8f9fa;
        --border-color: #dee2e6;
    }

    /* ============================================
       FORMULAIRES
       ============================================ */

    .form-label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.6rem;
        font-size: 0.95rem;
        letter-spacing: 0.3px;
    }

    .form-control,
    .form-select {
        border: 1.5px solid var(--border-color);
        border-radius: 0.5rem;
        padding: 0.65rem 0.875rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background-color: #fff;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(29, 63, 127, 0.15);
        background-color: #fff;
    }

    .form-control.is-invalid,
    .form-select.is-invalid {
        border-color: var(--danger-color);
    }

    .form-control.is-invalid:focus,
    .form-select.is-invalid:focus {
        border-color: var(--danger-color);
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.15);
    }

    .invalid-feedback {
        font-size: 0.85rem;
        color: var(--danger-color);
        margin-top: 0.35rem;
        display: block;
    }

    .form-check-input {
        width: 1.25rem;
        height: 1.25rem;
        border: 1.5px solid var(--border-color);
        border-radius: 0.25rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .form-check-input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(29, 63, 127, 0.15);
    }

    .form-check-label {
        margin-left: 0.5rem;
        cursor: pointer;
        font-weight: 500;
        color: #2c3e50;
    }

    /* ============================================
       CONTENEUR DE FORMULAIRE
       ============================================ */

    .form-container .card {
        border: none;
        border-radius: 0.75rem;
    }

    .form-container .card-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, #2a5bad 100%);
        color: white;
        border: none;
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
    }

    .form-container .card-header h5 {
        margin: 0;
        font-size: 1.1rem;
        letter-spacing: 0.5px;
    }

    .bg-gradient {
        background: linear-gradient(135deg, var(--primary-color) 0%, #2a5bad 100%);
        color: white;
    }

    /* ============================================
       BOUTONS
       ============================================ */

    .btn {
        font-weight: 600;
        border-radius: 0.5rem;
        padding: 0.6rem 1.5rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .btn-primary {
        background-color: var(--primary-color);
        color: white;
    }

    .btn-primary:hover {
        background-color: var(--primary-hover);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(29, 63, 127, 0.3);
    }

    .btn-primary:focus {
        box-shadow: 0 0 0 0.25rem rgba(29, 63, 127, 0.25);
    }

    .btn-light {
        background-color: var(--light-bg);
        color: #2c3e50;
        border: 1px solid var(--border-color);
    }

    .btn-light:hover {
        background-color: #e9ecef;
        border-color: #bbbec4;
    }

    .btn-sm {
        padding: 0.4rem 0.8rem;
        font-size: 0.85rem;
    }

    /* ============================================
       TABLEAUX
       ============================================ */

    .data-table .card {
        border: none;
        border-radius: 0.75rem;
        overflow: hidden;
    }

    .data-table .card-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, #2a5bad 100%);
        color: white;
        border: none;
        padding: 1.25rem;
    }

    .data-table .card-header h5 {
        margin: 0;
        font-size: 1.1rem;
        letter-spacing: 0.5px;
    }

    .table {
        font-size: 0.95rem;
    }

    .table thead th {
        background-color: var(--primary-color);
        color: white;
        font-weight: 600;
        border: none;
        padding: 1rem 0.875rem;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    .table tbody tr {
        transition: all 0.3s ease;
        border-bottom: 1px solid var(--border-color);
    }

    .table tbody tr:hover {
        background-color: var(--light-bg);
        box-shadow: inset 2px 0 0 var(--primary-color);
    }

    .table tbody td {
        padding: 1rem 0.875rem;
        vertical-align: middle;
        color: #2c3e50;
    }

    .table tbody td.text-muted {
        color: #6c757d;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(29, 63, 127, 0.02);
    }

    .table-bordered {
        border: 1px solid var(--border-color);
    }

    .table-bordered thead th {
        border: none;
    }

    .table-bordered tbody td {
        border-color: var(--border-color);
    }

    .table-responsive {
        border-radius: 0;
    }

    /* ============================================
       ACTIONS DANS LES TABLEAUX
       ============================================ */

    .action-buttons a,
    .action-buttons button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 0.25rem;
        transition: all 0.3s ease;
        border-radius: 0.35rem;
        width: 32px;
        height: 32px;
    }

    .action-buttons a:hover {
        transform: scale(1.1);
    }

    .action-buttons .btn-view {
        color: #007bff;
    }

    .action-buttons .btn-view:hover {
        background-color: rgba(0, 123, 255, 0.1);
    }

    .action-buttons .btn-edit {
        color: #17a2b8;
    }

    .action-buttons .btn-edit:hover {
        background-color: rgba(23, 162, 184, 0.1);
    }

    .action-buttons .btn-delete {
        color: var(--danger-color);
    }

    .action-buttons .btn-delete:hover {
        background-color: rgba(220, 53, 69, 0.1);
    }

    /* ============================================
       CARTES ET CONTENEURS
       ============================================ */

    .card {
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }

    .card-header {
        background-color: var(--light-bg);
        border-bottom: 2px solid var(--border-color);
        border-radius: 0.75rem 0.75rem 0 0;
        padding: 1rem 1.25rem;
    }

    .card-body {
        padding: 1.5rem;
    }

    .shadow-sm {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
    }

    /* ============================================
       MISE EN PAGE
       ============================================ */

    .gap-2 {
        gap: 0.875rem !important;
    }

    .border-top {
        border-top: 1px solid var(--border-color);
    }

    /* ============================================
       RÉSEAU RESPONSIF
       ============================================ */

    @media (max-width: 768px) {
        .form-label {
            font-size: 0.9rem;
        }

        .form-control,
        .form-select {
            font-size: 0.9rem;
            padding: 0.6rem 0.75rem;
        }

        .btn {
            padding: 0.55rem 1.25rem;
            font-size: 0.9rem;
        }

        .table {
            font-size: 0.875rem;
        }

        .table thead th,
        .table tbody td {
            padding: 0.75rem 0.6rem;
        }

        .table-responsive {
            font-size: 0.85rem;
        }
    }

    /* ============================================
       ANIMATIONS ET TRANSITIONS
       ============================================ */

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .form-container,
    .data-table {
        animation: slideIn 0.3s ease;
    }

    /* ============================================
       BADGES ET ÉTIQUETTES
       ============================================ */

    .badge {
        padding: 0.5rem 0.75rem;
        border-radius: 0.35rem;
        font-weight: 600;
        font-size: 0.8rem;
        letter-spacing: 0.3px;
    }

    .badge-success {
        background-color: var(--success-color);
        color: white;
    }

    .badge-danger {
        background-color: var(--danger-color);
        color: white;
    }

    .badge-warning {
        background-color: var(--warning-color);
        color: #000;
    }

    /* ============================================
       ÉTATS VIDES
       ============================================ */

    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .empty-state p {
        font-size: 1rem;
        margin: 0;
    }
</style>
