<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Ticket {{ $ticket->code }}</title>
    <style>
        body { font-family: ui-monospace, Menlo, Consolas, monospace; font-size: 12px; max-width: 320px; margin: 0 auto; padding: 12px; color: #111; }
        h1 { font-size: 14px; text-align: center; margin: 0 0 8px; }
        .muted { color: #555; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0; }
        td { padding: 2px 0; vertical-align: top; }
        .r { text-align: right; }
        .tot { font-weight: bold; font-size: 13px; border-top: 1px dashed #000; padding-top: 6px; margin-top: 6px; }
        .center { text-align: center; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
@php
    $ticket->loadMissing(['Elements', 'Reglements.ModePaie', 'Contribuable', 'User', 'Caisse']);
    $entete = \App\Models\Entete::first();
@endphp
    <div class="center">
        <strong>{{ optional($entete)->denomination ?? 'Établissement' }}</strong><br>
        <span class="muted">Ticket de caisse (non fiscal)</span>
    </div>
    <h1>{{ $ticket->code }}</h1>
    <div class="muted">
        {{ optional($ticket->date_metier)->format('d/m/Y') }}
        · {{ optional($ticket->Caisse)->libelle }}
        · {{ $ticket->statut }}
    </div>
    <div>
        Client : {{ optional($ticket->Contribuable)->assujeti ?: ($ticket->client_libre ?: 'Passage') }}
    </div>
    <table>
        @foreach ($ticket->Elements as $el)
            <tr>
                <td>
                    {{ $el->designation }}<br>
                    <span class="muted">{{ $el->quantite }} × {{ number_format((float)$el->prix_unitaire, 0, ',', ' ') }} HT</span>
                </td>
                <td class="r">{{ number_format((float)$el->montant_ttc_ligne, 0, ',', ' ') }}</td>
            </tr>
        @endforeach
    </table>
    <div class="r">Remise : {{ number_format((float)$ticket->remise, 0, ',', ' ') }}</div>
    <div class="r">HT : {{ number_format((float)$ticket->montant_ht, 0, ',', ' ') }}</div>
    <div class="tot r">TTC : {{ number_format((float)$ticket->montant_ttc, 0, ',', ' ') }} FCFA</div>
    @foreach ($ticket->Reglements as $r)
        <div class="muted">
            {{ $r->mode }} : {{ number_format((float)$r->montant, 0, ',', ' ') }}
            @if ($r->montant_recu !== null)
                · reçu {{ number_format((float)$r->montant_recu, 0, ',', ' ') }}
                · rendu {{ number_format((float)($r->rendu ?? 0), 0, ',', ' ') }}
            @endif
        </div>
    @endforeach
    <p class="center muted" style="margin-top:16px;">Merci de votre visite<br>Document hors MCF / SFE</p>
    <p class="center no-print"><button onclick="window.print()">Imprimer</button></p>
    <script>window.addEventListener('load', function () { setTimeout(function(){ window.print(); }, 250); });</script>
</body>
</html>
