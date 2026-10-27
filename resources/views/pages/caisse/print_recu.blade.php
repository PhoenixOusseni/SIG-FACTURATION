<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bon de Caisse {{ $mouvement->code }}</title>
    <style>
        @page {
            size: A5 landscape;
            margin: 12mm;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 13px;
            color: #1e293b;
            background: #fff;
            margin: 0;
            padding: 20px;
        }
        .receipt-card {
            border: 2px solid #0f172a;
            border-radius: 8px;
            padding: 24px;
            background: #ffffff;
            max-width: 800px;
            margin: 0 auto;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .company-name {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
        }
        .company-meta {
            font-size: 11px;
            color: #475569;
            line-height: 1.4;
        }
        .receipt-title-box {
            text-align: right;
        }
        .receipt-title {
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            color: {{ $mouvement->isDepot() ? '#15803d' : '#b91c1c' }};
        }
        .receipt-code {
            font-family: monospace;
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .details-table td {
            padding: 8px 10px;
            border-bottom: 1px dashed #cbd5e1;
        }
        .details-table td.label {
            width: 25%;
            font-weight: 700;
            color: #334155;
            background: #f8fafc;
        }
        .amount-highlight {
            font-size: 18px;
            font-weight: 800;
            font-family: monospace;
            color: {{ $mouvement->isDepot() ? '#15803d' : '#b91c1c' }};
        }
        .signatures-table {
            width: 100%;
            margin-top: 30px;
        }
        .signatures-table td {
            width: 50%;
            vertical-align: top;
            text-align: center;
        }
        .sig-box {
            border-top: 1px solid #94a3b8;
            margin-top: 50px;
            padding-top: 6px;
            font-weight: 600;
            color: #475569;
            font-size: 11px;
        }
        .fec-mention {
            margin-top: 24px;
            font-size: 9px;
            color: #64748b;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .receipt-card { border: 1px solid #000; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right; max-width: 800px; margin: 0 auto 12px;">
        <button onclick="window.print()" style="background:#1d3f7f;color:#fff;border:none;padding:8px 18px;border-radius:6px;font-weight:700;cursor:pointer;">
            Imprimer le Bon de Caisse
        </button>
        <button onclick="window.close()" style="background:#64748b;color:#fff;border:none;padding:8px 14px;border-radius:6px;cursor:pointer;margin-left:6px;">
            Fermer
        </button>
    </div>

    <div class="receipt-card">
        <table class="header-table">
            <tr>
                <td>
                    <div class="company-name">{{ $entete->denomination ?? 'SIG-FACTURATION' }}</div>
                    <div class="company-meta">
                        @if($entete->ifu) IFU : <strong>{{ $entete->ifu }}</strong> · @endif
                        @if($entete->rccm) RCCM : {{ $entete->rccm }} · @endif
                        @if($entete->telephone) Tél : {{ $entete->telephone }} @endif
                    </div>
                </td>
                <td class="receipt-title-box">
                    <div class="receipt-title">
                        {{ $mouvement->isDepot() ? "Reçu d'Entrée de Caisse" : "Bon de Sortie de Caisse" }}
                    </div>
                    <div class="receipt-code">N° {{ $mouvement->code }}</div>
                    <div style="font-size: 11px; color: #64748b;">
                        Date : {{ optional($mouvement->date_mouvement)->format('d/m/Y à H:i') }}
                    </div>
                </td>
            </tr>
        </table>

        <table class="details-table">
            <tr>
                <td class="label">Montant de l'opération</td>
                <td class="amount-highlight">
                    {{ number_format($mouvement->montant, 0, ',', ' ') }} FCFA
                </td>
            </tr>
            <tr>
                <td class="label">Motif de l'opération</td>
                <td>
                    <strong>{{ $mouvement->motif }}</strong>
                    <span style="color: #64748b; font-size: 11px;">({{ $mouvement->type_motif }})</span>
                </td>
            </tr>
            <tr>
                <td class="label">{{ $mouvement->isDepot() ? "Déposant / Provenance" : "Bénéficiaire" }}</td>
                <td>
                    <strong>{{ $mouvement->beneficiaire ?: 'Non spécifié' }}</strong>
                </td>
            </tr>
            @if ($mouvement->piece_justificative)
            <tr>
                <td class="label">Pièce justificative</td>
                <td style="font-family: monospace;">{{ $mouvement->piece_justificative }}</td>
            </tr>
            @endif
            <tr>
                <td class="label">Caisse concernée</td>
                <td>{{ optional($mouvement->Caisse)->libelle ?? 'Caisse Principale' }}</td>
            </tr>
            @if ($mouvement->observation)
            <tr>
                <td class="label">Observations</td>
                <td>{{ $mouvement->observation }}</td>
            </tr>
            @endif
        </table>

        <table class="signatures-table">
            <tr>
                <td>
                    <div style="font-weight: 700; color: #0f172a; margin-bottom: 4px;">Le Caissier / Opérateur</div>
                    <div style="font-size: 11px; color: #64748b;">{{ optional($mouvement->User)->prenom }} {{ optional($mouvement->User)->nom }}</div>
                    <div class="sig-box">Signature & Date</div>
                </td>
                <td>
                    <div style="font-weight: 700; color: #0f172a; margin-bottom: 4px;">{{ $mouvement->isDepot() ? "Le Déposant" : "Le Bénéficiaire" }}</div>
                    <div style="font-size: 11px; color: #64748b;">{{ $mouvement->beneficiaire ?: 'Signature pour acquit' }}</div>
                    <div class="sig-box">Signature pour acquit</div>
                </td>
            </tr>
        </table>

        <div class="fec-mention">
            Pièce de trésorerie conforme aux spécifications FEC / SFE (DGI Burkina Faso) · Archive probante
        </div>
    </div>
</body>
</html>
