@extends('layouts.master')
@section('title')
    <title>Console SFE physique</title>
@endsection
@section('style')
    @include('partials.style')
    <style>
        .sfe-status {
            border: 0;
            border-radius: 18px;
            color: #fff;
            box-shadow: 0 18px 45px #0f172a2e
        }

        .sfe-status.connected {
            background: #059669
        }

        .sfe-status.warning {
            background: #d97706
        }

        .sfe-status.disconnected {
            background: #dc2626
        }

        .sfe-card {
            border: 1px solid #e2e8f0;
            border-radius: 16px
        }

        .metric {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1rem;
            height: 100%
        }

        .metric small {
            color: #64748b;
            text-transform: uppercase
        }

        .metric b {
            display: block
        }

        .check {
            padding: 12px;
            background: #f8fafc;
            border-radius: 10px;
            margin-bottom: 8px
        }

        .check.ok {
            color: #047857
        }

        .check.ko {
            color: #b91c1c
        }
    </style>
@endsection
@section('content')
    <main>
        <header class="page-header page-header-dark header-gradient pb-10">
            <div class="container-xl px-4 pt-4">
                <h1 class="page-header-title"><i data-feather="cpu"></i> Console du boîtier fiscal</h1>
                <p>Pilotage sécurisé via le bridge SFE local</p>
            </div>
        </header>
        @include('pages.mcf.console')
    </main>
@endsection
