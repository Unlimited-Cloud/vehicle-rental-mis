<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Coupon</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 20px;
        }

        .wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .coupon {
            width: 90%;
            border: 2px dashed #444;
            padding: 20px;
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
        }

        .logo {
            width: 70px;
        }

        .title {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
        }

        .divider {
            border-top: 1px solid #ccc;
            margin: 10px 0 15px;
        }

        .label {
            font-weight: bold;
            width: 40%;
        }

        .value {
            width: 60%;
        }

        .info-table td {
            padding: 6px 0;
            font-size: 14px;
        }

        .amount-box {
            margin-top: 20px;
            text-align: center;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 6px;
            background: #f8f8f8;
        }

        .amount {
            font-size: 26px;
            font-weight: bold;
            color: #1b8a3a;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 11px;
            color: #777;
        }
    </style>
</head>
<body>

@php
    use App\Helpers\MenuHelper;
    $basic = MenuHelper::showBasicSetup();
@endphp

<div class="wrapper">
<div class="coupon">

    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td>
                @if($basic && $basic->login_logo)
                    <img src="{{ public_path($basic->login_logo) }}" class="logo">
                @else
                    <img src="{{ public_path('adminlte/logo4.png') }}" class="logo">
                @endif
            </td>

            <td class="title">
                FUEL COUPON
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- DETAILS -->
    <table class="info-table">
        <tr>
            <td class="label">Coupon No:</td>
            <td class="value">{{ $coupon->coupon_number }}</td>
        </tr>

        <tr>
            <td class="label">Petrol Pump:</td>
            <td class="value">{{ $coupon->petrolPump->name ?? 'N/A' }}</td>
        </tr>

        <tr>
            <td class="label">Booking ID:</td>
            <td class="value">{{ $coupon->booking_id ?? 'N/A' }}</td>
        </tr>
    </table>

    <!-- AMOUNT -->
    <div class="amount-box">
        <div>Coupon Value</div>
        <div class="amount">Rs. {{ number_format($coupon->amount, 2) }}</div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        Valid for fuel usage only • Non-transferable
    </div>

</div>
</div>

</body>
</html>