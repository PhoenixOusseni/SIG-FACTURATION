@extends('layouts.master')

@section('title')
    <title>
        SIG-FACTURATION | Paramètres — Import / Export</title>
@endsection

@section('style')
    @include('partials.style')
    <style>
        /* ===== Page header ===== */
        .param-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #1d4ed8 100%);
            padding: 3rem 0 5.5rem;
            position: relative;
            overflow: hidden;
        }

        .param-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(ellipse at 5% 90%, rgba(255, 255, 255, .05) 0%, transparent 55%),
                radial-gradient(ellipse at 95% 10%, rgba(255, 255, 255, .07) 0%, transparent 50%);
        }

        .param-header-grid {
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg stroke='%23ffffff' stroke-opacity='0.03' stroke-width='1'%3E%3Cpath d='M0 0h40v40H0z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .param-header-content {
            position: relative;
            z-index: 1;
        }

        .param-header h1 {
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.4px;
        }

        .param-header .lead {
            color: rgba(255, 255, 255, .75);
            font-size: .95rem;
        }

        .param-badge {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .2);
            border-radius: 50px;
            padding: .3rem .9rem;
            font-size: .78rem;
            color: rgba(255, 255, 255, .9);
            font-weight: 600;
            margin-bottom: 1rem;
        }

        /* ===== Body pulled up ===== */
        .param-body {
            margin-top: -3rem;
        }

        /* ===== Stat mini cards ===== */
        .stat-mini {
            background: #fff;
            border: 1px solid #e8edf5;
            border-radius: 14px;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: .9rem;
            transition: box-shadow .2s;
        }

        .stat-mini:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, .07);
        }

        .stat-mini .sm-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-mini .sm-icon svg {
            width: 20px;
            height: 20px;
        }

        .stat-mini .sm-val {
            font-size: 1.4rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
        }

        .stat-mini .sm-lbl {
            font-size: .75rem;
            color: #64748b;
            font-weight: 500;
            margin-top: .15rem;
        }

        /* ===== Section headings ===== */
        .sec-heading {
            font-size: 1.1rem;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: .65rem;
            padding-bottom: .75rem;
            border-bottom: 2px solid #e8edf5;
            margin-bottom: 1.5rem;
        }

        .sec-heading .sh-line {
            width: 4px;
            height: 20px;
            border-radius: 2px;
            background: linear-gradient(180deg, #1d4ed8, #60a5fa);
        }

        /* ===== Data cards ===== */
        .data-card {
            background: #fff;
            border: 1px solid #e8edf5;
            border-radius: 16px;
            overflow: hidden;
            height: 100%;
            transition: box-shadow .2s, border-color .2s;
        }

        .data-card:hover {
            box-shadow: 0 6px 24px rgba(0, 0, 0, .08);
            border-color: #cbd5e1;
        }

        .data-card .dc-head {
            padding: 1.25rem 1.25rem .9rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: flex-start;
            gap: .9rem;
        }

        .data-card .dc-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .data-card .dc-icon svg {
            width: 22px;
            height: 22px;
        }

        .data-card .dc-name {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: .2rem;
        }

        .data-card .dc-desc {
            font-size: .78rem;
            color: #64748b;
            line-height: 1.45;
        }

        .data-card .dc-count {
            margin-left: auto;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .data-card .dc-count .count-val {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
        }

        .data-card .dc-count .count-lbl {
            font-size: .7rem;
            color: #94a3b8;
        }

        .data-card .dc-actions {
            padding: .9rem 1.25rem;
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .btn-export {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            background: #1d4ed8;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: .42rem .9rem;
            font-size: .82rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background .15s, transform .15s;
            white-space: nowrap;
        }

        .btn-export:hover {
            background: #1e40af;
            color: #fff;
            transform: translateY(-1px);
        }

        .btn-export svg {
            width: 15px;
            height: 15px;
        }

        .btn-template {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: .42rem .9rem;
            font-size: .82rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all .15s;
            white-space: nowrap;
        }

        .btn-template:hover {
            background: #e2e8f0;
            color: #1e293b;
            border-color: #cbd5e1;
        }

        .btn-template svg {
            width: 15px;
            height: 15px;
        }

        .btn-import-toggle {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: .42rem .9rem;
            font-size: .82rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all .15s;
            white-space: nowrap;
        }

        .btn-import-toggle:hover {
            background: #dcfce7;
            border-color: #86efac;
        }

        .btn-import-toggle svg {
            width: 15px;
            height: 15px;
        }

        .btn-import-toggle.active {
            background: #dcfce7;
            border-color: #4ade80;
            color: #166534;
        }

        /* Import panel */
        .dc-import-panel {
            background: #f8fafc;
            border-top: 1px solid #e8edf5;
            padding: 1rem 1.25rem;
            display: none;
        }

        .dc-import-panel.open {
            display: block;
        }

        .import-dropzone {
            border: 2px dashed #cbd5e1;
            border-radius: 10px;
            padding: 1.25rem 1rem;
            text-align: center;
            background: #fff;
            cursor: pointer;
            transition: all .2s;
            position: relative;
        }

        .import-dropzone:hover,
        .import-dropzone.dragover {
            border-color: #1d4ed8;
            background: #eff6ff;
        }

        .import-dropzone input[type=file] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .import-dropzone svg {
            width: 28px;
            height: 28px;
            color: #94a3b8;
            margin-bottom: .5rem;
        }

        .import-dropzone p {
            font-size: .82rem;
            color: #64748b;
            margin: 0;
        }

        .import-dropzone .file-name {
            font-size: .8rem;
            color: #1d4ed8;
            font-weight: 600;
            margin-top: .3rem;
            display: none;
        }

        .btn-do-import {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: #15803d;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: .5rem 1.1rem;
            font-size: .85rem;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            justify-content: center;
            margin-top: .75rem;
            transition: background .15s;
        }

        .btn-do-import:hover {
            background: #166534;
        }

        .btn-do-import svg {
            width: 16px;
            height: 16px;
        }

        .import-cols-hint {
            font-size: .75rem;
            color: #64748b;
            background: #f1f5f9;
            border-radius: 8px;
            padding: .6rem .85rem;
            margin-bottom: .75rem;
            line-height: 1.5;
        }

        .import-cols-hint code {
            background: #e2e8f0;
            border-radius: 4px;
            padding: .05rem .3rem;
            font-size: .72rem;
            color: #334155;
        }

        /* Result alerts */
        .alert-import-ok {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 10px;
            padding: .75rem 1rem;
            margin-top: .75rem;
            font-size: .83rem;
        }

        .alert-import-ok strong {
            color: #15803d;
        }

        .alert-import-err {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            border-radius: 10px;
            padding: .75rem 1rem;
            margin-top: .75rem;
            font-size: .83rem;
            color: #991b1b;
        }

        .alert-import-err ul {
            margin: .35rem 0 0;
            padding-left: 1.2rem;
        }

        /* ===== Tool cards ===== */
        .tool-card {
            background: #fff;
            border: 1px solid #e8edf5;
            border-radius: 16px;
            padding: 1.5rem;
            height: 100%;
            transition: all .2s;
        }

        .tool-card:hover {
            box-shadow: 0 6px 24px rgba(0, 0, 0, .07);
        }

        .tool-card .tc-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .tool-card .tc-icon svg {
            width: 24px;
            height: 24px;
        }

        .tool-card h5 {
            font-size: .97rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: .4rem;
        }

        .tool-card p {
            font-size: .82rem;
            color: #64748b;
            line-height: 1.55;
            margin-bottom: 1rem;
        }

        .btn-tool {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            border-radius: 8px;
            padding: .5rem 1.1rem;
            font-size: .83rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all .15s;
            border: none;
            white-space: nowrap;
        }

        .btn-tool svg {
            width: 16px;
            height: 16px;
        }

        .btn-tool-blue {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .btn-tool-blue:hover {
            background: #dbeafe;
            color: #1e40af;
        }

        /* ===== Danger zone ===== */
        .danger-zone {
            background: #fff;
            border: 1px solid #fecaca;
            border-radius: 16px;
            overflow: hidden;
        }

        .danger-zone .dz-head {
            background: linear-gradient(135deg, #fef2f2, #fff5f5);
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: .85rem;
            border-bottom: 1px solid #fecaca;
        }

        .danger-zone .dz-head svg {
            width: 22px;
            height: 22px;
            color: #dc2626;
        }

        .danger-zone .dz-head h5 {
            font-size: 1rem;
            font-weight: 800;
            color: #7f1d1d;
            margin: 0;
        }

        .danger-zone .dz-head p {
            font-size: .8rem;
            color: #991b1b;
            margin: .2rem 0 0;
        }

        .danger-zone .dz-body {
            padding: 1.25rem 1.5rem;
        }

        .purge-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .75rem 0;
            border-bottom: 1px solid #fef2f2;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .purge-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .purge-row .pr-info {
            font-size: .88rem;
            color: #374151;
            font-weight: 600;
        }

        .purge-row .pr-count {
            font-size: .78rem;
            color: #6b7280;
            font-weight: 400;
        }

        .btn-purge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: #fff;
            color: #dc2626;
            border: 1px solid #fca5a5;
            border-radius: 8px;
            padding: .35rem .8rem;
            font-size: .8rem;
            font-weight: 700;
            cursor: pointer;
            transition: all .15s;
            white-space: nowrap;
        }

        .btn-purge:hover {
            background: #dc2626;
            color: #fff;
            border-color: #dc2626;
        }

        .btn-purge svg {
            width: 14px;
            height: 14px;
        }

        /* Purge modal */
        .purge-modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .5);
            backdrop-filter: blur(4px);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .purge-modal-backdrop.open {
            display: flex;
        }

        .purge-modal {
            background: #fff;
            border-radius: 20px;
            width: 100%;
            max-width: 420px;
            margin: 1rem;
            box-shadow: 0 25px 60px rgba(15, 23, 42, .25);
            overflow: hidden;
        }

        .purge-modal .pm-head {
            padding: 1.5rem 1.5rem 1rem;
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .purge-modal .pm-head .pm-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: #fef2f2;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .purge-modal .pm-head .pm-icon svg {
            width: 24px;
            height: 24px;
            color: #dc2626;
        }

        .purge-modal .pm-head h5 {
            font-size: 1.05rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
        }

        .purge-modal .pm-head p {
            font-size: .82rem;
            color: #64748b;
            margin: .2rem 0 0;
        }

        .purge-modal .pm-body {
            padding: 0 1.5rem 1.5rem;
        }

        .purge-modal .pm-warning {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 10px;
            padding: .75rem 1rem;
            font-size: .82rem;
            color: #92400e;
            margin-bottom: 1rem;
        }

        .purge-modal .pm-body label {
            font-size: .83rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: .4rem;
            display: block;
        }

        .purge-modal .pm-body input {
            width: 100%;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            padding: .55rem .85rem;
            font-size: .9rem;
            font-weight: 700;
            color: #dc2626;
            letter-spacing: 1px;
        }

        .purge-modal .pm-body input:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, .1);
        }

        .purge-modal .pm-foot {
            display: flex;
            gap: .75rem;
            margin-top: 1.25rem;
        }

        .pm-btn-cancel {
            flex: 1;
            background: #f1f5f9;
            color: #475569;
            border: none;
            border-radius: 8px;
            padding: .6rem;
            font-size: .88rem;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s;
        }

        .pm-btn-cancel:hover {
            background: #e2e8f0;
        }

        .pm-btn-confirm {
            flex: 1;
            background: #dc2626;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: .6rem;
            font-size: .88rem;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s;
        }

        .pm-btn-confirm:hover {
            background: #b91c1c;
        }

        @media (max-width: 767px) {
            .param-header h1 {
                font-size: 1.5rem;
            }

            .data-card .dc-count {
                display: none;
            }
        }
    </style>
@endsection

@section('content')
    <main>

        {{-- ===== HEADER ===== --}}
        <div class="param-header">
            <div class="param-header-grid"></div>
            <div class="container-xl px-4 param-header-content">
                <div class="param-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3" />
                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14" />
                    </svg>
                    Administration système
                </div>
                <h1>Paramètres — Import &amp; Export</h1>
                <p class="lead mt-1 mb-0">Importez des données en masse, exportez vos référentiels en CSV et sauvegardez
                    l'intégralité de vos données.</p>
            </div>
        </div>

        <div class="container-xl px-4 param-body mt-4">

            {{-- ===== STATS MINI ===== --}}
            <div class="row g-2 mb-4">
                @foreach ($schemas as $key => $schema)
                    <div class="col-6 col-sm-4 col-lg-3 col-xl">
                        <div class="stat-mini">
                            <div class="sm-icon" style="background: {{ $schema['bg'] }}; color: {{ $schema['color'] }};">
                                <i data-feather="{{ $schema['icon'] }}"
                                    style="width:20px;height:20px;color:{{ $schema['color'] }};"></i>
                            </div>
                            <div>
                                <div class="sm-val">{{ number_format($stats[$key]) }}</div>
                                <div class="sm-lbl">{{ $schema['label'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ===== DATA CARDS ===== --}}
            <div class="sec-heading mb-3">
                <span class="sh-line"></span>
                Données de référence — Import / Export CSV
            </div>

            <div class="row g-3 mb-4">
                @foreach ($schemas as $type => $schema)
                    <div class="col-md-6 col-xl-4">
                        <div class="data-card" id="card-{{ $type }}">

                            {{-- Head --}}
                            <div class="dc-head">
                                <div class="dc-icon"
                                    style="background: {{ $schema['bg'] }}; color: {{ $schema['color'] }};">
                                    <i data-feather="{{ $schema['icon'] }}"
                                        style="width:22px;height:22px;color:{{ $schema['color'] }};"></i>
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div class="dc-name">{{ $schema['label'] }}</div>
                                    <div class="dc-desc">{{ $schema['description'] }}</div>
                                </div>
                                <div class="dc-count">
                                    <div class="count-val" style="color: {{ $schema['color'] }};">
                                        {{ number_format($stats[$type]) }}</div>
                                    <div class="count-lbl">enreg.</div>
                                </div>
                            </div>

                            {{-- Alerts résultat import --}}
                            @if (session('import_result_' . $type))
                                @php $r = session('import_result_' . $type); @endphp
                                <div style="padding: 0 1.25rem;">
                                    <div class="alert-import-ok">
                                        <strong>Import terminé :</strong>
                                        @if ($type === 'factures')
                                            {{ $r['imported'] }} facture(s) créée(s),
                                            {{ $r['updated'] ?? 0 }} ligne(s) ajoutée(s),
                                            {{ $r['skipped'] }} ligne(s) ignorée(s).
                                        @else
                                            {{ $r['imported'] }} ajouté(s),
                                            {{ $r['updated'] ?? 0 }} mis à jour,
                                            {{ $r['skipped'] }} ignoré(s).
                                        @endif
                                        @if (!empty($r['errors']))
                                            <ul>
                                                @foreach (array_slice($r['errors'], 0, 5) as $err)
                                                    <li>{{ $err }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @if (session('import_error_' . $type))
                                <div style="padding: 0 1.25rem;">
                                    <div class="alert-import-err">{{ session('import_error_' . $type) }}</div>
                                </div>
                            @endif
                            @if (session('purge_result_' . $type))
                                @php $pr = session('purge_result_' . $type); @endphp
                                <div style="padding: 0 1.25rem;">
                                    <div class="alert-import-err">
                                        <strong>{{ $pr['deleted'] }} enregistrement(s) supprimé(s)</strong> de «
                                        {{ $pr['label'] }} ».
                                    </div>
                                </div>
                            @endif

                            {{-- Actions --}}
                            <div class="dc-actions">
                                <a href="{{ route('parametres.export', $type) }}" class="btn-export"
                                    title="Exporter en CSV">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                        <polyline points="7 10 12 15 17 10" />
                                        <line x1="12" y1="15" x2="12" y2="3" />
                                    </svg>
                                    Exporter
                                </a>
                                <a href="{{ route('parametres.template', $type) }}" class="btn-template"
                                    title="Télécharger le modèle CSV">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                        <polyline points="14 2 14 8 20 8" />
                                    </svg>
                                    Modèle
                                </a>
                                @if ($schema['import_ok'])
                                    <button type="button" class="btn-import-toggle"
                                        onclick="toggleImport('{{ $type }}')" id="btn-import-{{ $type }}"
                                        title="Importer un fichier CSV">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                            <polyline points="17 8 12 3 7 8" />
                                            <line x1="12" y1="3" x2="12" y2="15" />
                                        </svg>
                                        Importer
                                    </button>
                                @else
                                    <span class="btn-template" style="opacity:.5;cursor:not-allowed;"
                                        title="Import désactivé pour ce type">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <rect x="3" y="11" width="18" height="11" rx="2"
                                                ry="2" />
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                        </svg>
                                        Export seul.
                                    </span>
                                @endif
                            </div>

                            {{-- Import panel --}}
                            @if ($schema['import_ok'])
                                <div class="dc-import-panel @if (session('import_error_' . $type) || session('import_result_' . $type)) open @endif"
                                    id="import-panel-{{ $type }}">
                                    <div class="import-cols-hint">
                                        <strong>Colonnes attendues :</strong>
                                        @foreach ($schema['cols'] as $col)
                                            <code>{{ $col }}</code>{{ !$loop->last ? ' · ' : '' }}
                                        @endforeach
                                        @if ($schema['unique_key'])
                                            <br><strong>Clé unique :</strong> <code>{{ $schema['unique_key'] }}</code> —
                                            les enregistrements existants seront mis à jour.
                                        @endif
                                        @if ($type === 'factures')
                                            <br><strong>Format ligne par article :</strong> répétez le même
                                            <code>facture_code</code> sur toutes les lignes d'une même facture (voir le
                                            modèle). Laissez <code>facture_code</code> vide pour générer un nouveau code
                                            automatique (1 ligne = 1 facture dans ce cas).
                                            <br><strong>Facture déjà existante :</strong> si <code>facture_code</code>
                                            correspond à une facture déjà en base, ses lignes du fichier sont ignorées
                                            (aucun doublon, aucune mise à jour).
                                            <br><strong>Stock :</strong> non décrémenté automatiquement (reprise de
                                            données historiques).
                                        @endif
                                    </div>

                                    <form action="{{ route('parametres.import', $type) }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <div class="import-dropzone" id="dropzone-{{ $type }}">
                                            <input type="file" name="fichier_{{ $type }}" accept=".csv,.txt"
                                                onchange="showFileName('{{ $type }}', this)">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                <polyline points="17 8 12 3 7 8" />
                                                <line x1="12" y1="3" x2="12" y2="15" />
                                            </svg>
                                            <p>Glissez votre fichier CSV ici<br>ou <strong style="color:#1d4ed8;">cliquez
                                                    pour parcourir</strong></p>
                                            <div class="file-name" id="fname-{{ $type }}"></div>
                                        </div>
                                        @error('fichier_' . $type)
                                            <div class="alert-import-err mt-2">{{ $message }}</div>
                                        @enderror
                                        <button type="submit" class="btn-do-import">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <polyline points="9 11 12 14 22 4" />
                                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                                            </svg>
                                            Lancer l'import
                                        </button>
                                    </form>
                                </div>
                            @endif

                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ===== OUTILS ===== --}}
            <div class="sec-heading">
                <span class="sh-line" style="background: linear-gradient(180deg,#7c3aed,#a78bfa);"></span>
                Outils de gestion
            </div>

            <div class="row g-3 mb-4">

                {{-- Sauvegarde complète --}}
                <div class="col-md-6 col-lg-4">
                    <div class="tool-card">
                        <div class="tc-icon" style="background:#eff6ff;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#1d4ed8"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                <polyline points="7 10 12 15 17 10" />
                                <line x1="12" y1="15" x2="12" y2="3" />
                            </svg>
                        </div>
                        <h5>Sauvegarde complète (ZIP)</h5>
                        <p>Téléchargez l'ensemble des données de référence en un seul fichier ZIP contenant tous les CSV :
                            clients, fournisseurs, produits, catégories, dépôts, TVA, PSVBB et utilisateurs.</p>
                        @if (in_array(Auth::user()->role, ['administrateur', 'superviseur']))
                            <a href="{{ route('parametres.backup') }}" class="btn-tool btn-tool-blue">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <polyline points="7 10 12 15 17 10" />
                                    <line x1="12" y1="15" x2="12" y2="3" />
                                </svg>
                                Télécharger la sauvegarde
                            </a>
                        @else
                            <span class="btn-tool btn-tool-blue" style="opacity:.5;cursor:not-allowed;">Réservé Admin /
                                Superviseur</span>
                        @endif
                    </div>
                </div>

                {{-- Sauvegarde SQL --}}
                <div class="col-md-6 col-lg-4">
                    <div class="tool-card">
                        <div class="tc-icon" style="background:#f5f3ff;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#7c3aed"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <ellipse cx="12" cy="5" rx="9" ry="3" />
                                <path d="M3 5v14c0 1.66 4.03 3 9 3s9-1.34 9-3V5" />
                                <path d="M3 12c0 1.66 4.03 3 9 3s9-1.34 9-3" />
                            </svg>
                        </div>
                        <h5>Sauvegarde complète SQL</h5>
                        <p>Exportez l'intégralité de la base de données en un fichier <code>.sql</code> prêt à être restauré
                            via phpMyAdmin ou MySQL. Inclut la structure et toutes les données.</p>
                        @if (in_array(Auth::user()->role, ['administrateur', 'superviseur']))
                            <a href="{{ route('parametres.backup-sql') }}" class="btn-tool"
                                style="background:#f5f3ff;color:#7c3aed;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <ellipse cx="12" cy="5" rx="9" ry="3" />
                                    <path d="M3 5v14c0 1.66 4.03 3 9 3s9-1.34 9-3V5" />
                                    <path d="M3 12c0 1.66 4.03 3 9 3s9-1.34 9-3" />
                                </svg>
                                Télécharger le fichier .sql
                            </a>
                        @else
                            <span class="btn-tool"
                                style="background:#f5f3ff;color:#7c3aed;opacity:.5;cursor:not-allowed;">Réservé Admin /
                                Superviseur</span>
                        @endif
                    </div>
                </div>

                {{-- Informations système --}}
                <div class="col-md-6 col-lg-4">
                    <div class="tool-card">
                        <div class="tc-icon" style="background:#f0fdf4;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#15803d"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" y1="8" x2="12" y2="12" />
                                <line x1="12" y1="16" x2="12.01" y2="16" />
                            </svg>
                        </div>
                        <h5>Informations système</h5>
                        <p>Vue synthétique de l'état de la base de données : nombre total d'enregistrements par entité,
                            volumes de facturation et état général du système.</p>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.4rem;">
                            @foreach ($schemas as $key => $schema)
                                <div
                                    style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:.4rem .7rem;display:flex;align-items:center;justify-content:space-between;">
                                    <span style="font-size:.75rem;color:#64748b;">{{ $schema['label'] }}</span>
                                    <span
                                        style="font-size:.8rem;font-weight:700;color:{{ $schema['color'] }};">{{ number_format($stats[$key]) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Guide d'import --}}
                <div class="col-md-6 col-lg-4">
                    <div class="tool-card">
                        <div class="tc-icon" style="background:#fff7ed;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#ea580c"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                                <line x1="12" y1="17" x2="12.01" y2="17" />
                            </svg>
                        </div>
                        <h5>Guide d'utilisation des imports</h5>
                        <p style="margin-bottom:.75rem;">Pour importer des données correctement :</p>
                        <ol style="font-size:.8rem;color:#64748b;padding-left:1.25rem;line-height:1.75;margin:0;">
                            <li>Téléchargez le <strong>modèle CSV</strong> de l'entité souhaitée.</li>
                            <li>Remplissez le fichier en respectant les colonnes (ne pas modifier les en-têtes).</li>
                            <li>Utilisez le séparateur <strong>point-virgule (;)</strong>.</li>
                            <li>Les IDs de référence (categories_id, tva_id, bic_id) doivent correspondre aux
                                enregistrements existants.</li>
                            <li>Cliquez sur <strong>Importer</strong> et sélectionnez votre fichier.</li>
                        </ol>
                    </div>
                </div>

            </div>

            {{-- ===== DANGER ZONE (admin only) ===== --}}
            @if (in_array(Auth::user()->role, ['administrateur']))
                <div class="sec-heading">
                    <span class="sh-line" style="background: linear-gradient(180deg,#dc2626,#f87171);"></span>
                    Zone de danger — Réinitialisation des données
                </div>

                <div class="danger-zone mb-5">
                    <div class="dz-head">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                            <line x1="12" y1="9" x2="12" y2="13" />
                            <line x1="12" y1="17" x2="12.01" y2="17" />
                        </svg>
                        <div>
                            <h5>Actions irréversibles — Réservé Administrateur</h5>
                            <p>La suppression d'une table est définitive et ne peut pas être annulée. Effectuez une
                                sauvegarde avant de continuer.</p>
                        </div>
                    </div>
                    <div class="dz-body">
                        @foreach ($schemas as $type => $schema)
                            @if ($schema['purge_ok'] ?? true)
                                <div class="purge-row">
                                    <div>
                                        <div class="pr-info">{{ $schema['label'] }}</div>
                                        <div class="pr-count">{{ number_format($stats[$type]) }} enregistrement(s)
                                            actuellement</div>
                                    </div>
                                    <button type="button" class="btn-purge"
                                        onclick="openPurgeModal('{{ $type }}', '{{ $schema['label'] }}', {{ $stats[$type] }})">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6" />
                                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                            <path d="M10 11v6" />
                                            <path d="M14 11v6" />
                                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                                        </svg>
                                        Vider
                                    </button>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

        </div>{{-- /container --}}
    </main>

    {{-- ===== PURGE MODAL ===== --}}
    <div class="purge-modal-backdrop" id="purgeBackdrop" onclick="closePurgeModal()">
        <div class="purge-modal" onclick="event.stopPropagation()">
            <div class="pm-head">
                <div class="pm-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6" />
                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                    </svg>
                </div>
                <div>
                    <h5 id="pm-title">Vider la table</h5>
                    <p id="pm-subtitle">Cette action est irréversible.</p>
                </div>
            </div>
            <div class="pm-body">
                <div class="pm-warning">
                    <strong>Attention :</strong> Tous les enregistrements de cette table seront supprimés définitivement.
                    Assurez-vous d'avoir effectué une sauvegarde au préalable.
                </div>
                <form id="purgeForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <label for="purge-confirm-input">Tapez <strong>CONFIRMER</strong> pour valider :</label>
                    <input type="text" id="purge-confirm-input" name="confirmation" placeholder="CONFIRMER"
                        autocomplete="off" oninput="checkPurgeConfirm()">
                    <div class="pm-foot">
                        <button type="button" class="pm-btn-cancel" onclick="closePurgeModal()">Annuler</button>
                        <button type="submit" class="pm-btn-confirm" id="pm-confirm-btn" disabled>Supprimer
                            tout</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @include('partials.script')
    <script>
        // Toggle import panel
        function toggleImport(type) {
            const panel = document.getElementById('import-panel-' + type);
            const btn = document.getElementById('btn-import-' + type);
            const isOpen = panel.classList.contains('open');
            panel.classList.toggle('open', !isOpen);
            btn.classList.toggle('active', !isOpen);
        }

        // Show selected file name
        function showFileName(type, input) {
            const nameEl = document.getElementById('fname-' + type);
            if (input.files.length > 0) {
                nameEl.textContent = input.files[0].name;
                nameEl.style.display = 'block';
            }
        }

        // Drag & drop visual feedback
        document.querySelectorAll('.import-dropzone').forEach(function(zone) {
            zone.addEventListener('dragover', function(e) {
                e.preventDefault();
                zone.classList.add('dragover');
            });
            zone.addEventListener('dragleave', function() {
                zone.classList.remove('dragover');
            });
            zone.addEventListener('drop', function() {
                zone.classList.remove('dragover');
            });
        });

        // Auto-open import panel if result/error in session
        document.querySelectorAll('[id^="import-panel-"]').forEach(function(panel) {
            if (panel.classList.contains('open')) {
                const type = panel.id.replace('import-panel-', '');
                const btn = document.getElementById('btn-import-' + type);
                if (btn) btn.classList.add('active');
                panel.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        });

        var purgeBaseUrl = '{{ url('parametres/purge') }}';

        // Purge modal
        function openPurgeModal(type, label, count) {
            document.getElementById('pm-title').textContent = 'Vider : ' + label;
            document.getElementById('pm-subtitle').textContent = count + ' enregistrement(s) seront supprimés.';
            document.getElementById('purgeForm').action = purgeBaseUrl + '/' + type;
            document.getElementById('purge-confirm-input').value = '';
            document.getElementById('pm-confirm-btn').disabled = true;
            document.getElementById('purgeBackdrop').classList.add('open');
        }

        function closePurgeModal() {
            document.getElementById('purgeBackdrop').classList.remove('open');
        }

        function checkPurgeConfirm() {
            const val = document.getElementById('purge-confirm-input').value.trim();
            document.getElementById('pm-confirm-btn').disabled = (val !== 'CONFIRMER');
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closePurgeModal();
        });
    </script>
@endsection
