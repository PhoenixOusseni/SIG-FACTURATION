<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Fermeture de caisse #{{ $session->id }}</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 720px; margin: 24px auto; padding: 0 16px; color: #111; }
        h1 { font-size: 1.25rem; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0; }
        th, td { border-bottom: 1px solid #ddd; padding: 6px 4px; text-align: left; font-size: 0.9rem; }
        .r { text-align: right; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <h1>Fermeture de caisse</h1>
    <p>
        Caisse : <strong>{{ optional($session->Caisse)->libelle }}</strong><br>
        Date métier : {{ optional($session->date_metier)->format('d/m/Y') }}<br>
        Fermée le : {{ optional($session->closed_at)->format('d/m/Y H:i') }}
        par {{ optional($session->ClosedBy)->prenom }} {{ optional($session->ClosedBy)->nom }}
    </p>
    <p>
        Ventes TTC (session) : <strong>{{ number_format((float)$session->fermeture_montant_ventes_ttc, 0, ',', ' ') }}</strong>
        · Tickets : {{ $session->fermeture_nombre_tickets }}<br>
        Fond initial : {{ number_format((float)$session->fond_initial, 0, ',', ' ') }}
        · Fond théorique : {{ number_format((float)$session->fermeture_fond_theorique, 0, ',', ' ') }}
        @if ($session->montant_fermeture_declare !== null)
            · Déclaré : {{ number_format((float)$session->montant_fermeture_declare, 0, ',', ' ') }}
            · Écart : {{ number_format((float)$session->fermeture_ecart, 0, ',', ' ') }}
        @endif
    </p>
    @if (is_array($session->fermeture_detail_modes))
        <h2 style="font-size:1rem;">Par mode de paiement</h2>
        <table>
            <thead><tr><th>Mode</th><th class="r">Montant</th></tr></thead>
            <tbody>
                @foreach ($session->fermeture_detail_modes as $m)
                    <tr>
                        <td>{{ $m['libelle'] ?? $m['code'] ?? '—' }}</td>
                        <td class="r">{{ number_format((float)($m['montant'] ?? 0), 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    @if ($session->notes_fermeture)
        <p>Notes : {{ $session->notes_fermeture }}</p>
    @endif
    <p style="color:#666; font-size:0.85rem;">Rapport commercial — hors MCF. Snapshot immuable.</p>
    <p class="no-print"><button onclick="window.print()">Imprimer</button></p>
</body>
</html>
