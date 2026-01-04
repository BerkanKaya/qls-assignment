<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            margin: 10px;
        }

        .slip {
            font-size: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table {
            border: none;
            margin-bottom: 0.7em;
        }

        .header-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        .title {
            font-size: 2.6em;
            line-height: 1.02;
            margin: 0;
            font-weight: 900;
        }

        .order-number {
            margin-top: 0.25em;
            font-size: 1.6em;
            font-weight: 800;
            color: #0f172a;
        }

        .generated-at {
            font-size: 1.1em;
            font-weight: 700;
            color: #475569;
            margin-top: 0.45em;
            text-align: right;
        }

        .muted {
            color: #64748b;
        }

        .address-table {
            border: none;
            margin-top: 0.6em;
            margin-bottom: 0.7em;
        }

        .address-table td {
            width: 50%;
            padding: 0 0.4em;
            vertical-align: top;
            border: none;
        }

        .box {
            border: 1px solid #e2e8f0;
            border-radius: 0.35em;
            padding: 0.8em;
            background: #ffffff;
        }

        .box h2 {
            font-size: 1.1em;
            margin: 0 0 0.6em 0;
            font-weight: 800;
        }

        .box div {
            margin-bottom: 0.35em;
            line-height: 1.25;
            font-size: 0.95em;
        }

        .items-table {
            margin-top: 0.5em;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #e2e8f0;
            padding: 0.7em;
            font-size: 1em;
        }

        .items-table th {
            background: #f8fafc;
            text-align: left;
            font-weight: 800;
        }
    </style>
</head>

<body>
    <section class="slip">
        <table class="header-table">
            <tr>
                <td style="width: 70%;">
                    <h1 class="title">Packing Slip</h1>
                    <div class="order-number">Order #{{ $order->number }}</div>
                </td>
                <td style="width: 30%; text-align: right;">
                    <div class="generated-at">Generated {{ now()->format('Y-m-d H:i') }}</div>
                </td>
            </tr>
        </table>

        <table class="address-table">
            <tr>
                <td>
                    <div class="box">
                        <h2>Sender</h2>
                        @if($order->billingAddress->companyName)
                        <div class="muted">{{ $order->billingAddress->companyName }}</div>
                        @endif
                        <div>{{ $order->billingAddress->name }}</div>
                        <div>{{ $order->billingAddress->street }} {{ $order->billingAddress->houseNumber }}</div>
                        @if($order->billingAddress->addressLine2)
                        <div>{{ $order->billingAddress->addressLine2 }}</div>
                        @endif
                        <div>{{ $order->billingAddress->postalCode }} {{ $order->billingAddress->city }}</div>
                        <div>{{ $order->billingAddress->country }}</div>
                    </div>
                </td>
                <td>
                    <div class="box">
                        <h2>Receiver</h2>
                        @if($order->deliveryAddress->companyName)
                        <div class="muted">{{ $order->deliveryAddress->companyName }}</div>
                        @endif
                        <div>{{ $order->deliveryAddress->name }}</div>
                        <div>{{ $order->deliveryAddress->street }} {{ $order->deliveryAddress->houseNumber }}</div>
                        @if($order->deliveryAddress->addressLine2)
                        <div>{{ $order->deliveryAddress->addressLine2 }}</div>
                        @endif
                        <div>{{ $order->deliveryAddress->postalCode }} {{ $order->deliveryAddress->city }}</div>
                        <div>{{ $order->deliveryAddress->country }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Product</th>
                    <th style="width: 15%;">SKU</th>
                    <th style="width: 10%;">Qty</th>
                    <th style="width: 25%;">EAN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderLines as $line)
                <tr>
                    <td>{{ $line->name }}</td>
                    <td>{{ $line->sku }}</td>
                    <td>{{ $line->amountOrdered }}</td>
                    <td>{{ $line->ean ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </section>
</body>

</html>