<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Inventory Report - {{ $generatedAt }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1e293b;
            background: #ffffff;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .title h1 {
            margin: 0;
            font-size: 22px;
            color: #1e1b4b;
        }
        .title p {
            margin: 4px 0 0;
            color: #64748b;
            font-size: 11px;
        }
        .meta-info {
            text-align: right;
            font-size: 11px;
            color: #64748b;
        }
        .kpi-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .kpi-card {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
        }
        .kpi-card .kpi-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
        }
        .kpi-card .kpi-value {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        th {
            background-color: #3730a3;
            color: #ffffff;
            text-align: left;
            padding: 8px 10px;
            font-weight: 600;
            border: 1px solid #3730a3;
        }
        td {
            padding: 7px 10px;
            border: 1px solid #e2e8f0;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9.5px;
            font-weight: 600;
        }
        .badge-active { background: #d1fae5; color: #065f46; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .print-btn-bar {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(30, 27, 75, 0.9);
            padding: 10px 18px;
            border-radius: 9999px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .print-btn {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 12px;
        }
        @media print {
            .print-btn-bar { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="title">
            <h1>📦 Product Inventory & Export Report</h1>
            <p>Generated via Alpine Product Manager</p>
        </div>
        <div class="meta-info">
            <div><strong>Generated At:</strong> {{ $generatedAt }}</div>
            <div><strong>Total Records:</strong> {{ $totalCount }} items</div>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-label">Total Products</div>
            <div class="kpi-value">{{ number_format($totalCount) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Total Units in Stock</div>
            <div class="kpi-value">{{ number_format($totalStock) }} units</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Total Inventory Value</div>
            <div class="kpi-value">${{ number_format($totalValue, 2) }}</div>
        </div>
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                @foreach ($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr>
                    @foreach ($columns as $col)
                        @if ($col === 'id')
                            <td class="text-center">{{ $product->id }}</td>
                        @elseif ($col === 'name')
                            <td><strong>{{ $product->name }}</strong></td>
                        @elseif ($col === 'price')
                            <td class="text-right">${{ number_format((float) $product->price, 2) }}</td>
                        @elseif ($col === 'stock')
                            <td class="text-right">{{ number_format((int) $product->stock) }}</td>
                        @elseif ($col === 'status')
                            <td class="text-center">
                                <span class="badge {{ $product->status === 'active' ? 'badge-active' : 'badge-inactive' }}">
                                    {{ ucfirst($product->status) }}
                                </span>
                            </td>
                        @elseif ($col === 'inventory_value')
                            <td class="text-right font-bold">${{ number_format((float) $product->price * (int) $product->stock, 2) }}</td>
                        @elseif ($col === 'created_at')
                            <td>{{ $product->created_at?->format('Y-m-d H:i') }}</td>
                        @elseif ($col === 'updated_at')
                            <td>{{ $product->updated_at?->format('Y-m-d H:i') }}</td>
                        @endif
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) }}" class="text-center" style="padding: 20px; color: #94a3b8;">
                        No products found for the selected export criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="print-btn-bar">
        <span style="color: white; font-size: 11px;">📄 Ready to Print / Save as PDF</span>
        <button class="print-btn" onclick="window.print()">🖨️ Print / Save as PDF</button>
    </div>

</body>
</html>
