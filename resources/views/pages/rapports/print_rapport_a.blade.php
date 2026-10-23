<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Articles (A) - {{ $rapport ? $rapport->numero_rapport : 'Bilan Périodique' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
            background: #fff;
            padding: 15px;
            max-width: 850px;
            margin: 0 auto;
        }
        .toolbar {
            background: #f8f9fa;
            padding: 10px 15px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: Arial, sans-serif;
        }
        .btn {
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-print { background: #0f172a; color: #fff; border: 1px solid #0f172a; }
        .btn-back { background: #fff; color: #334155; border: 1px solid #cbd5e1; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-start { text-align: left; }
        .fw-bold { font-weight: bold; }
        .dashed-line { border-bottom: 1px dashed #000; margin: 8px 0; }
        .double-line { border-bottom: 2px solid #000; margin: 8px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { padding: 4px 6px; }
        th { border-bottom: 1px solid #000; border-top: 1px solid #000; text-transform: uppercase; font-size: 10px; }
        .fs-title { font-size: 15px; font-weight: bold; }
        .fs-sub { font-size: 12px; }
        .badge-qty { font-weight: bold; }
        @media print {
            .toolbar { display: none !important; }
            body { padding: 0; max-width: 100%; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <div>
            <a href="{{ route('rapports.index') }}" class="btn btn-back">← Retour aux rapports</a>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-print">🖨 Imprimer le Rapport A</button>
        </div>
    </div>

    <!-- En-tête officiel DGI -->
    <div class="text-center">
        <p class="fs-title">BURKINA FASO</p>
        <p class="fs-sub">DIRECTION GÉNÉRALE DES IMPÔTS (DGI)</p>
        <p>DISPOSITIF FISCAL ÉLECTRONIQUE SFE / MCF</p>
        <p class="double-line"></p>
        <h2 style="font-size: 16px; margin: 5px 0; letter-spacing: 1px;">
            RAPPORT ARTICLES (A)
        </h2>
        <p class="fw-bold">MOUVEMENTS DES ARTICLES & ÉTAT DES STOCKS</p>
        <p class="double-line"></p>
    </div>

    <!-- Informations Entreprise & MCF -->
    <table>
        <tr>
            <td class="text-start" style="width: 55%; vertical-align: top;">
                <p class="fw-bold fs-sub">{{ $entete->denomination }}</p>
                <p>IFU : <span class="fw-bold">{{ $entete->ifu ?? 'N/A' }}</span></p>
                <p>RCCM : {{ $entete->rccm ?? 'N/A' }}</p>
                <p>Régime : {{ $entete->regime_fiscal ?? 'RNI' }} | Division : {{ $entete->division_fiscal ?? 'DME' }}</p>
                <p>Cadastre : {{ $entete->infos_cadastrales ?? 'N/A' }}</p>
            </td>
            <td class="text-end" style="width: 45%; vertical-align: top;">
                @if($rapport)
                    <p>N° Rapport : <span class="fw-bold fs-sub">{{ $rapport->numero_rapport }}</span></p>
                @endif
                <p>NIM Machine : <span class="fw-bold">{{ $entete->nim_mcf ?? 'N/A' }}</span></p>
                <p>ISF SFE : {{ $entete->identifiant_sfe ?? 'N/A' }}</p>
                <p>Date d'édition : {{ date('d/m/Y H:i:s') }}</p>
                <p>Période : {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}</p>
            </td>
        </tr>
    </table>

    <div class="dashed-line"></div>

    <!-- Table des articles -->
    <table>
        <thead>
            <tr>
                <th class="text-start" style="width: 15%;">Code</th>
                <th class="text-start" style="width: 35%;">Désignation</th>
                <th class="text-end" style="width: 12%;">PU (FCFA)</th>
                <th class="text-center" style="width: 8%;">TVA</th>
                <th class="text-end" style="width: 10%;">Vendue</th>
                <th class="text-end" style="width: 10%;">Retournée</th>
                <th class="text-end" style="width: 10%;">Stock</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totVendue = 0;
                $totRetournee = 0;
                $totStock = 0;
                $totCaVendu = 0;
                $totCaRetourne = 0;
            @endphp
            @forelse($articlesData as $item)
                @php
                    $totVendue += $item['quantite_vendue'];
                    $totRetournee += $item['quantite_retournee'];
                    $totStock += $item['quantite_stock'];
                    $totCaVendu += $item['montant_vendu'];
                    $totCaRetourne += $item['montant_retourne'];
                @endphp
                <tr>
                    <td class="text-start fw-bold">{{ $item['code'] }}</td>
                    <td class="text-start">{{ $item['nom'] }}</td>
                    <td class="text-end">{{ number_format($item['prix_unitaire'], 0, ',', ' ') }}</td>
                    <td class="text-center">{{ (float)$item['taux_impot'] }}%</td>
                    <td class="text-end badge-qty" style="color: #047857;">{{ number_format($item['quantite_vendue'], 2, ',', ' ') }}</td>
                    <td class="text-end badge-qty" style="color: #b91c1c;">{{ number_format($item['quantite_retournee'], 2, ',', ' ') }}</td>
                    <td class="text-end fw-bold">{{ number_format($item['quantite_stock'], 2, ',', ' ') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px;">Aucun mouvement d'article sur la période sélectionnée.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="double-line"></div>

    <!-- Synthèse & Récapitulatif -->
    <table style="width: 100%;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <p class="fw-bold fs-sub mb-1">RÉCAPITULATIF QUANTITATIF</p>
                <table style="width: 90%;">
                    <tr>
                        <td>Nombre d'articles listés :</td>
                        <td class="text-end fw-bold">{{ count($articlesData) }}</td>
                    </tr>
                    <tr>
                        <td>Total Quantités Vendues :</td>
                        <td class="text-end fw-bold" style="color: #047857;">{{ number_format($totVendue, 2, ',', ' ') }}</td>
                    </tr>
                    <tr>
                        <td>Total Quantités Retournées (Avoirs) :</td>
                        <td class="text-end fw-bold" style="color: #b91c1c;">{{ number_format($totRetournee, 2, ',', ' ') }}</td>
                    </tr>
                    <tr>
                        <td>Quantité Nette Vendue :</td>
                        <td class="text-end fw-bold">{{ number_format($totVendue - $totRetournee, 2, ',', ' ') }}</td>
                    </tr>
                    <tr>
                        <td>Total Unités en Stock Actuel :</td>
                        <td class="text-end fw-bold">{{ number_format($totStock, 2, ',', ' ') }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <p class="fw-bold fs-sub mb-1">RÉCAPITULATIF FINANCIER (FCFA)</p>
                <table style="width: 100%;">
                    <tr>
                        <td>Chiffre d'Affaires Brut Ventes :</td>
                        <td class="text-end fw-bold">{{ number_format($totCaVendu, 0, ',', ' ') }} F</td>
                    </tr>
                    <tr>
                        <td>Total Avoirs / Retours Marchandises :</td>
                        <td class="text-end fw-bold" style="color: #b91c1c;">- {{ number_format($totCaRetourne, 0, ',', ' ') }} F</td>
                    </tr>
                    <tr style="border-top: 1px solid #000; border-bottom: 2px solid #000;">
                        <td class="fw-bold fs-sub">C.A. NET RÉALISÉ SUR ARTICLES :</td>
                        <td class="text-end fw-bold fs-sub">{{ number_format($totCaVendu - $totCaRetourne, 0, ',', ' ') }} F</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="dashed-line" style="margin-top: 15px;"></div>

    <div class="text-center" style="font-size: 9px; margin-top: 10px;">
        <p>Document officiel DGI généré par SIG-FACTURATION sous contrôle SFE/MCF.</p>
        <p>Logiciel certifié conforme à la norme SECeF ST 1.0 — République du Burkina Faso.</p>
    </div>

</body>
</html>
