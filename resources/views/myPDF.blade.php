<!DOCTYPE html>
<html>

<head>
    <title>Invoice patunganYuk</title>
    <style>
        .container {
            width: 100%;
            margin: auto;
        }

        .heading {
            display: table;
            padding: 10px;
            width: 100%;
        }

        .heading div {
            display: table-cell;
            vertical-align: middle;
        }

        .heading div:first-child {
            width: 70%;
        }

        .heading div:last-child {
            width: 30%;
            text-align: right;
        }

        .heading-img {
            height: 60px;
        }

        .block {
            display: block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 1rem;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .no-border {
            border: none;
        }
    </style>
</head>

<body>
    <div class="heading container">
        <div>
            <span style="font-size: 1rem; font-weight: bold">Kepada Yth.</span>
            <span style="font-size: 1rem; font-weight: bold" class="block">{{ $user }}</span>
            <span style="font-size: 1rem; font-weight: bold" class="block">
                Status:
                {{
                    $transaction->status == 0
                    ? 'Pending'
                    : ($transaction->status == 1
                        ? 'Sukses/Lunas'
                        : 'Gagal')
                }}
            </span>

        </div>
        <div>
            <img class="heading-img" src="https://res.cloudinary.com/boxity-id/image/upload/v1720974567/4_ligzdg.png" alt="">
        </div>
    </div>
    <div class="container">
        <h1 style="text-align: center; font-size: 2rem">Invoice</h1>
        <table>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Total</th>
            </tr>
            {{-- @dd($transaction) --}}
                <tr>
                    <td>{{ $transaction->product->nama }}</td>
                    <td>{{$transaction->jumlah}}</td>
                    <td>Rp. {{ number_format($transaction->product->harga_jual, 0, ',', '.') }}</td>
                    <td>Rp. {{ number_format($transaction->product->harga_jual * $transaction->jumlah, 0, ',', '.') }}</td>
                </tr>
            <tr>
                <td colspan="2" class="no-border"></td>
                <td>Total</td>
                <td>Rp. {{number_format($transaction->harga)}}</td>
            </tr>
        </table>
        <div>
            <p>Catatan:
                {{
                    $transaction->status === 0
                    ? 'Harap segera melakukan pembayaran, anda dapat melakukan pembayaran melalui transafer bank'
                    : ($transaction->status === 1
                    ? 'Terimakasi atas pembayaran anda'
                    : 'Pembaran anda di batalkan')
                }}
            </p>
            <p>Bank {{ $rekening->bank }} a/n {{ $rekening->name }} {{ $rekening->no_rek }}</p>
        </div>
    </div>
</body>

</html>
