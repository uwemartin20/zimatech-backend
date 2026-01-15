<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>VorKalkulation - {{ $offer->customer_name }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 22px;
        }

        .section-title {
            font-size: 14px;
            margin-top: 20px;
            margin-bottom: 5px;
            font-weight: bold;
            text-decoration: underline;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            word-wrap: break-word; /* Wrap long text */
        }

        th, td {
            border: 1px solid #aaa;
            padding: 6px;
            font-size: 10px;
            vertical-align: top;
        }

        th {
            background: #f0f0f0;
            font-weight: bold;
        }

        tfoot td {
            font-weight: bold;
            background: #e8e8e8;
        }

        .text-right {
            text-align: right;
        }

        .small-text {
            font-size: 11px;
            color: #555;
        }
    </style>
</head>

<body>

<h1>VorKalkulation</h1>

{{-- Basic Offer Information --}}
<table>
    <tr>
        <td><strong>Anfragedatum:</strong></td>
        <td>{{ $offer->created_at->format('d.m.Y') }}</td>
    </tr>
    <tr>
        <td><strong>Bearbeiter:</strong></td>
        <td>{{ $offer->assignedUser->name ?? '—' }}</td>
    </tr>
    <tr>
        <td><strong>Anfrage per:</strong></td>
        <td>Email</td>
    </tr>
    <tr>
        <td><strong>Kunde:</strong></td>
        <td>{{ $offer->customer_name }}</td>
    </tr>
    <tr>
        <td><strong>Betreff:</strong></td>
        <td>{{ $offer->subject }}</td>
    </tr>
</table>

{{-- Calculation Table --}}
<h2 class="section-title">Kalkulation</h2>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Bezeichnung</th>
            <th>Stunden</th>
            <th>Betrag (€)</th>
            <th>Material (€)</th>
            <th>Fremd-Leistung (€)</th>
            <th>Stück</th>
            <th>Einzelpreis</th>
            <th>Gesamt</th>
            <th>Angebot je Stück</th>
            <th>Angebot Gesamt</th>
        </tr>
    </thead>

    <tbody>
    @foreach ($offer->calculations as $index => $calc)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $calc->designation }}</td>
            <td>{{ $calc->hours }}</td>
            <td class="text-right">{{ number_format($calc->cost, 2, ',', '.') }}</td>
            <td class="text-right">{{ number_format($calc->material_cost, 2, ',', '.') }}</td>
            <td class="text-right">{{ number_format($calc->external_cost, 2, ',', '.') }}</td>
            <td>{{ $calc->pieces }}</td>
            <td class="text-right">{{ number_format($calc->total_cost, 2, ',', '.') }}</td>
            <td class="text-right">{{ number_format($calc->gesamt_kosten, 2, ',', '.') }}</td>
            <td class="text-right">{{ number_format($calc->offer_cost, 2, ',', '.') }}</td>
            <td class="text-right">{{ number_format($calc->gesamt_angebot, 2, ',', '.') }}</td>
        </tr>
    @endforeach

    @if ($offer->calculations->count() == 0)
        <tr>
            <td colspan="12" class="text-center small-text">
                Keine Kalkulationen vorhanden.
            </td>
        </tr>
    @endif
    </tbody>

    @if($offer->calculations->count())
    <tfoot>
        <tr>
            <td colspan="7" class="text-right">SUMME:</td>
            <td class="text-right">
                {{ number_format($offer->calculations->sum('total_cost'), 2, ',', '.') }}
            </td>
            <td class="text-right">
                {{ number_format($offer->calculations->sum(fn($c)=>$c->gesamt_kosten), 2, ',', '.') }}
            </td>
            <td class="text-right">
                {{ number_format($offer->calculations->sum('offer_cost'), 2, ',', '.') }}
            </td>
            <td class="text-right">
                {{ number_format($offer->calculations->sum(fn($c)=>$c->gesamt_angebot), 2, ',', '.') }}
            </td>
        </tr>
    </tfoot>
    @endif
</table>

</body>
</html>
