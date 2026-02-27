<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Stok Rendah</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 32px 16px;">
        <tr>
            <td align="center">
                <table width="640" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">

                    {{-- Header --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #dc2626, #b91c1c); padding: 32px 40px; text-align: center;">
                            <h1 style="color: #ffffff; font-size: 22px; margin: 0 0 8px 0;">⚠️ Laporan Stok Rendah</h1>
                            <p style="color: #fecaca; font-size: 14px; margin: 0;">{{ $reportDate }}</p>
                        </td>
                    </tr>

                    {{-- Summary --}}
                    <tr>
                        <td style="padding: 32px 40px 16px 40px;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 20px;">
                                <tr>
                                    <td style="padding: 16px 20px;">
                                        <p style="margin: 0; font-size: 14px; color: #991b1b;">
                                            Ditemukan <strong style="font-size: 20px; color: #dc2626;">{{ $totalItems }}</strong> item
                                            dengan stok di bawah Reorder Point (ROP) yang belum berstatus <em>On Order</em>.
                                        </p>
                                        <p style="margin: 8px 0 0 0; font-size: 13px; color: #b91c1c;">
                                            Tersebar di <strong>{{ $groupedItems->count() }}</strong> gudang.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Per-Warehouse Tables --}}
                    @foreach ($groupedItems as $warehouseName => $items)
                    <tr>
                        <td style="padding: 16px 40px;">
                            <h3 style="margin: 0 0 12px 0; font-size: 15px; color: #1f2937; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px;">
                                🏭 {{ $warehouseName }}
                                <span style="font-weight: normal; color: #9ca3af; font-size: 13px;">({{ count($items) }} item)</span>
                            </h3>
                            <table width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; font-size: 13px;">
                                <thead>
                                    <tr style="background-color: #f9fafb;">
                                        <th style="padding: 10px 12px; text-align: left; color: #6b7280; font-weight: 600; border-bottom: 1px solid #e5e7eb;">Produk</th>
                                        <th style="padding: 10px 12px; text-align: left; color: #6b7280; font-weight: 600; border-bottom: 1px solid #e5e7eb;">SKU</th>
                                        <th style="padding: 10px 12px; text-align: center; color: #6b7280; font-weight: 600; border-bottom: 1px solid #e5e7eb;">Stok</th>
                                        <th style="padding: 10px 12px; text-align: center; color: #6b7280; font-weight: 600; border-bottom: 1px solid #e5e7eb;">ROP</th>
                                        <th style="padding: 10px 12px; text-align: center; color: #6b7280; font-weight: 600; border-bottom: 1px solid #e5e7eb;">Defisit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $item)
                                    <tr style="border-bottom: 1px solid #f3f4f6;">
                                        <td style="padding: 10px 12px; color: #1f2937; font-weight: 500;">{{ $item['product_name'] }}</td>
                                        <td style="padding: 10px 12px; color: #6b7280; font-family: monospace; font-size: 12px;">{{ $item['product_sku'] }}</td>
                                        <td style="padding: 10px 12px; text-align: center; color: #dc2626; font-weight: 700;">{{ number_format($item['current_stock']) }}</td>
                                        <td style="padding: 10px 12px; text-align: center; color: #d97706; font-weight: 600;">{{ number_format($item['reorder_point']) }}</td>
                                        <td style="padding: 10px 12px; text-align: center;">
                                            <span style="background-color: #fef2f2; color: #dc2626; padding: 2px 8px; border-radius: 4px; font-weight: 700; font-size: 12px;">
                                                -{{ number_format($item['deficit']) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    @endforeach

                    {{-- Footer --}}
                    <tr>
                        <td style="padding: 24px 40px 32px 40px; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0; font-size: 12px; color: #9ca3af; text-align: center;">
                                Email ini dikirim otomatis oleh Sistem Manajemen Stok Produk pada pukul {{ config('stockcheck.check_time') }} WIB.
                                <br>Silakan login ke dashboard untuk tindakan lebih lanjut.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
