<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Journal de Piste d'Audit DGI</title>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 9px; color: #000; background: #fff; padding: 10px; }
        .toolbar { background: #f8fafc; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; }
        .btn { padding: 4px 10px; font-size: 11px; font-weight: bold; border-radius: 4px; cursor: pointer; text-decoration: none; }
        .btn-print { background: #0f172a; color: #fff; border: 1px solid #0f172a; }
        .btn-back { background: #fff; color: #334155; border: 1px solid #cbd5e1; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #0f172a; padding-bottom: 6px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; font-size: 8.5px; }
        th, td { border: 1px solid #cbd5e1; padding: 4px 5px; }
        th { background: #f1f5f9; font-weight: bold; text-transform: uppercase; }
        @media print {
            .toolbar { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <a href="{{ route('audit.index') }}" class="btn btn-back">← Retour au Journal</a>
        <button onclick="window.print()" class="btn btn-print">🖨 Imprimer la Piste d'Audit</button>
    </div>

    <div class="header">
        <div>
            <h2 style="font-size: 14px; text-transform: uppercase;">{{ $enteteSoc->denomination ?? 'ENTREPRISE' }}</h2>
            <p>IFU : {{ $enteteSoc->ifu ?? '—' }} | RCCM : {{ $enteteSoc->rccm ?? '—' }} | ISF : {{ $enteteSoc->identifiant_sfe ?? '—' }}</p>
        </div>
        <div style="text-align: right;">
            <h3 style="font-size: 12px; text-transform: uppercase;">Registre de Piste d'Audit Interne (DGI)</h3>
            <p>Édité le {{ now()->format('d/m/Y H:i:s') }} {{ $dateDebut ? " | Du {$dateDebut}" : "" }} {{ $dateFin ? " au {$dateFin}" : "" }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%;">Date & Heure</th>
                <th style="width: 15%;">Opérateur</th>
                <th style="width: 10%;">Action</th>
                <th style="width: 10%;">Entité</th>
                <th style="width: 8%;">ID</th>
                <th style="width: 35%;">Détails de l'opération</th>
                <th style="width: 10%;">IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                <td><strong>{{ $log->nom_utilisateur }}</strong></td>
                <td>{{ $log->action }}</td>
                <td>{{ $log->entite }}</td>
                <td>{{ $log->entite_id ?: '—' }}</td>
                <td>{{ $log->details }}</td>
                <td>{{ $log->ip_address }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px;">Aucun événement d'audit enregistré.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 8px; text-align: center; color: #64748b;">
        Registre légal inaltérable conformément aux spécifications techniques SECeF ST 1.0 de la Direction Générale des Impôts du Burkina Faso.
    </div>

</body>
</html>
