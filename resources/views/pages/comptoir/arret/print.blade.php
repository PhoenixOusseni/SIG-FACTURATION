<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Arrêt de caisse #{{ $arret->id }}</title>
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
    <h1>Arrêt de caisse (shift)</h1>
    <p>
        Caisse : <strong>{{ optional(optional($arret->SessionCaisse)->Caisse)->libelle }}</strong><br>
        Date métier : {{ optional(optional($arret->SessionCaisse)->date_metier)->format('d/m/Y') }}<br>
        Arrêté le : {{ optional($arret->arrete_at)->format('d/m/Y H:i') }}
        par {{ optional($arret->User)->prenom }} {{ optional($arret->User)->nom }}
    </p>
    <p>
        Ventes TTC (période) : <strong>{{ number_format((float)$arret->montant_ventes_ttc, 0, ',', ' ') }}</strong>
        · Tickets : {{ $arret->nombre_tickets }}<br>
        Fond théorique : {{ number_format((float)$arret->fond_theorique, 0, ',', ' ') }}
        @if ($arret->fond_declare !== null)
            · Déclaré : {{ number_format((float)$arret->fond_declare, 0, ',', ' ') }}
            · Écart : {{ number_format((float)$arret->ecart, 0, ',', ' ') }}
        @endif
    </p>
    @if (is_array($arret->detail_modes))
        <h2 style="font-size:1rem;">Par mode de paiement</h2>
        <table>
            <thead><tr><th>Mode</th><th class="r">Montant</th></tr></thead>
            <tbody>
                @foreach ($arret->detail_modes as $m)
                    <tr>
                        <td>{{ $m['libelle'] ?? $m['code'] ?? '—' }}</td>
                        <td class="r">{{ number_format((float)($m['montant'] ?? 0), 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    @if ($arret->notes)
        <p>Notes : {{ $arret->notes }}</p>
    @endif
    <p class="muted" style="color:#666; font-size:0.85rem;">Rapport commercial — hors MCF.</p>
    <p class="no-print"><button onclick="window.print()">Imprimer</button></p>
</body>
</html>
