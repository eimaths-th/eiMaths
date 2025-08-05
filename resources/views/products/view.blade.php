<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการสินค้า - eiMaths</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Sarabun', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: #fff;
            padding: 10px;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 15px;
        }

        @media (min-width: 768px) {
            .container {
                max-width: 210mm;
                padding: 10mm;
            }
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #f97316;
            padding-bottom: 15px;
        }

        .header h1 {
            color: #f97316;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header .subtitle {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .header .print-info {
            color: #888;
            font-size: 11px;
        }

        .centre-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .centre-header {
            background: linear-gradient(135deg, #f97316, #fb923c);
            color: white;
            padding: 10px 15px;
            border-radius: 8px 8px 0 0;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 0;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 0 0 8px 8px;
            overflow-x: auto;
            display: block;
            max-width: 100%;
        }

        .products-table table {
            width: 100%;
            min-width: 600px;
        }

        .products-table th {
            background: #f8f9fa;
            color: #495057;
            font-weight: bold;
            padding: 12px 8px;
            text-align: center;
            border: 1px solid #dee2e6;
            font-size: 11px;
        }

        .products-table td {
            padding: 10px 8px;
            border: 1px solid #dee2e6;
            vertical-align: middle;
            font-size: 11px;
        }

        .products-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .col-code {
            font-family: monospace;
            font-weight: bold;
        }

        .col-name {
            font-weight: 500;
        }

        .col-unit,
        .col-amount {
            text-align: center;
        }

        .col-amount {
            font-weight: bold;
            color: #f97316;
        }

        .col-detail {
            font-size: 10px;
            color: #666;
        }

        .col-barcode,
        .col-qrcode {
            text-align: center;
        }

        .barcode-container,
        .qrcode-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 40px;
        }

        /* Mobile styles */
        @media (max-width: 767px) {
            body {
                font-size: 14px;
                padding: 5px;
            }

            .header h1 {
                font-size: 20px;
                margin-top: 40px;
            }

            .header .subtitle {
                font-size: 13px;
            }

            .header .print-info {
                font-size: 11px;
            }

            .centre-header {
                font-size: 15px;
                padding: 8px 10px;
            }

            .products-table th,
            .products-table td {
                padding: 6px 4px;
                font-size: 10px;
            }

            .barcode-container svg {
                width: 100% !important;
                height: auto !important;
                max-height: 45mm !important;
            }

            .barcode-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 5px;
                padding: 0;
                margin: 0;
                width: 100%;
            }

            .barcode-card {
                background: white;
                border-radius: 8px;
                border: 1px solid #eee;
                page-break-inside: avoid;
                break-inside: avoid;
                padding: 8px;
                text-align: center;
                height: 100%;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }

            .barcode-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            }

            .barcode-card svg {
                max-width: 100%;
                height: auto !important;
                display: block;
                margin: 0 auto;
                transform: scale(1.5);
                transform-origin: center;
            }

            @media print {
                .barcode-card svg {
                    transform: scale(2) !important;
                    margin: 15px auto !important;
                }

                .barcode-card {
                    box-shadow: none !important;
                    border: 1px solid #eee !important;
                }

                .header {
                    margin-bottom: 2mm !important;
                    padding-bottom: 2mm !important;
                }

                .centre-header {
                    padding: 2mm 3mm !important;
                    margin-bottom: 2mm !important;
                }
            }

            .print-btn {
                top: 10px;
                right: 10px;
                padding: 8px 12px;
                font-size: 12px;
            }

            .print-btn i {
                margin-right: 3px;
            }
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #f97316;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        @media (min-width: 768px) {
            .print-btn {
                padding: 12px 20px;
                font-size: 14px;
            }
        }

        .print-btn:hover {
            background: #ea580c;
            transform: translateY(-2px);
        }

        /* QR Code specific styles */
        .qrcode-page {
            page-break-after: always;
            min-height: auto;
            padding: 0;
            margin: 0;
        }

        .qrcode-page:last-child {
            page-break-after: auto;
        }

        .qrcode-item {
            text-align: center;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .qrcode-code {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .qrcode-container {
            margin: 15px 0;
        }

        .qrcode-name {
            font-size: 14px;
            margin: 8px 0;
            font-weight: 500;
        }

        .qrcode-amount {
            font-size: 12px;
            color: #666;
        }

        @media print {
            @page {
                margin: 3mm 5mm 5mm 5mm;
                size: A4;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                padding: 0 !important;
                margin: 0 !important;
                line-height: 1.2;
            }

            .no-print {
                display: none !important;
            }

            .print-only {
                display: block !important;
            }

            .container {
                padding: 0;
                margin: 0;
                max-width: none;
            }

            .header {
                margin: 0 0 5px 0 !important;
                padding: 0 0 5px 0 !important;
            }

            /* Centre sections - only break for different centres, not QR pages */
            .centre-section {
                margin-bottom: 15px;
                page-break-inside: avoid;
            }

            .centre-section:not(:last-child) {
                page-break-after: always;
            }

            .centre-header {
                margin-bottom: 5px;
                padding: 8px 12px;
            }

            /* QR Code print styles - compact layout */
            .qrcode-page {
                page-break-after: always;
                padding: 5px 10px 10px 10px !important;
                /* top, right, bottom, left */
                margin: 0 !important;
            }

            .qrcode-page:last-child {
                page-break-after: auto;
            }

            .qrcode-item {
                margin-bottom: 5px;
                padding: 10px;
            }

            .products-table {
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>
    <button class="print-btn no-print" onclick="window.print()" aria-label="พิมพ์">
        <i class="fas fa-print" aria-hidden="true"></i> <span class="print-text">พิมพ์</span>
    </button>

    <div class="container">
        <div class="header">
            <h1>รายการสินค้าและอุปกรณ์</h1>
            <div class="subtitle">eiMaths Learning Center</div>
            <div class="print-info">
                วันที่พิมพ์: {{ date('d/m/Y H:i:s') }} |
                จำนวนรายการทั้งหมด: {{ $products->flatten()->count() }} รายการ
            </div>
        </div>

        @php
            $isFirstPage = true;
            $itemsPerFirstPage = 6;
            $itemsPerPage = 12;
        @endphp

        @foreach ($products as $centre => $productGroup)
            @php
                $totalItems = $productGroup->count();
                $firstPageItems = $productGroup->take($itemsPerFirstPage);
                $remainingItems = $productGroup->slice($itemsPerFirstPage);
                $remainingChunks = $remainingItems->chunk($itemsPerPage);
            @endphp

            <div class="centre-section">
                <div class="centre-header">
                    <i class="fas fa-building"></i> {{ $productGroup->first()->center->name }}
                    <span style="float: right; font-size: 14px;">
                        ({{ $totalItems }} รายการ)
                    </span>
                </div>

                @if ($code_type == 'barcode')
                    <!-- First Page (6 items) -->
                    <div class="barcode-grid first-page"
                    style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 5px; padding: 0; margin: 0; width: 100%; page-break-after: always;">
                        @foreach ($firstPageItems as $index => $product)
                            <div class="barcode-card">
                                <div style="padding: 15px; border-bottom: 1px solid #f5f5f5; background: #f9f9f9;">
                                    <div
                                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                        <span style="font-size: 13px; font-weight: 600; color: #444;">
                                            #{{ $index + 1 }} {{ $product->code }}
                                        </span>
                                        @if ($product->amount > 0)
                                            <span
                                                style="font-size: 12px; background: #f0f7ff; color: #0066cc; padding: 2px 8px; border-radius: 10px; font-weight: 500;">
                                                มีในสต็อก: {{ number_format($product->amount) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div style="font-size: 14px; color: #333; font-weight: 500;">
                                        {{ $product->name }}
                                    </div>
                                </div>
                                <div style="padding: 8px 10px 6px; text-align: center;">
                                    <div style="margin: 0 auto 5px; max-width: 100%;">
                                        <div
                                            style="margin-bottom: 8px; font-size: 11px; color: #666; letter-spacing: 0.5px;">
                                            สแกนบาร์โค้ดด้านล่าง
                                        </div>
                                        <div
                                            style="background: white; padding: 10px; border-radius: 6px; display: inline-block; border: 1px solid #f0f0f0; transform: scale(1.5); transform-origin: center; margin: 10px 0;">
                                            {!! $product->generateBarcode($product->code, ['scale' => 2]) !!}
                                        </div>
                                        <div class="product-info" style="margin-top: 3px; font-size: 11px; width: 100%; line-height: 1.2;">
                                            {{ $product->code }}
                                        </div>
                                    </div>
                                </div>
                                <div
                                    style="padding: 10px 15px; background: #fafafa; border-top: 1px solid #f0f0f0; font-size: 12px; color: #666;">
                                    <div style="font-weight: 500; margin-bottom: 3px; color: #555;">รายละเอียด:
                                    </div>
                                    {{ $product->detail ?? '-' }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Remaining Items (12 per page) -->
                    @foreach ($remainingChunks as $chunk)
                        <div class="barcode-grid"
                    style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 5px; padding: 0; margin: 0; width: 100%; @if (!$loop->last) page-break-after: always; @endif">
                            @foreach ($chunk as $index => $product)
                                <div class="barcode-card"
                                    style="background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden; transition: all 0.3s ease; border: 1px solid #eee;">
                                    <div style="padding: 15px; border-bottom: 1px solid #f5f5f5; background: #f9f9f9;">
                                        <div
                                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                            <span style="font-size: 13px; font-weight: 600; color: #444;">
                                                #{{ $index + 1 }} {{ $product->code }}
                                            </span>
                                            @if ($product->amount > 0)
                                                <span
                                                    style="font-size: 12px; background: #f0f7ff; color: #0066cc; padding: 2px 8px; border-radius: 10px; font-weight: 500;">
                                                    มีในสต็อก: {{ number_format($product->amount) }}
                                                </span>
                                            @endif
                                        </div>
                                        <div style="font-size: 14px; color: #333; font-weight: 500;">
                                            {{ $product->name }}
                                        </div>
                                    </div>
                                    <div style="padding: 8px 15px 12px; text-align: center;">
                                        <div style="margin: 0 auto 10px; max-width: 100%;">
                                            <div
                                                style="margin-bottom: 8px; font-size: 11px; color: #666; letter-spacing: 0.5px;">
                                                สแกนบาร์โค้ดด้านล่าง
                                            </div>
                                            <div
                                                style="background: white; padding: 10px; border-radius: 6px; display: inline-block; border: 1px solid #f0f0f0; transform: scale(1.5); transform-origin: center; margin: 10px 0;">
                                                {!! $product->generateBarcode($product->code, ['scale' => 2]) !!}
                                            </div>
                                            <div class="product-info" style="margin-top: 3px; font-size: 11px; width: 100%; line-height: 1.2;">
                                                {{ $product->code }}
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        style="padding: 10px 15px; background: #fafafa; border-top: 1px solid #f0f0f0; font-size: 12px; color: #666;">
                                        <div style="font-weight: 500; margin-bottom: 3px; color: #555;">รายละเอียด:
                                        </div>
                                        {{ $product->detail ?? '-' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @elseif($code_type == 'qrcode')
                    @php
                        $chunks = $productGroup->chunk(12);
                    @endphp
                    @foreach ($chunks as $pageIndex => $items)
                        <div class="qrcode-page">
                            <div
                                style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; height: 100%; align-items: center;">
                                @foreach ($items as $index => $product)
                                    <div class="qrcode-item"
                                        style="text-align: center; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                                        <div class="qrcode-code"
                                            style="font-size: 16px; font-weight: bold; margin-bottom: 10px;">
                                            {{ $product->code }}</div>
                                        <div class="barcode-container" style="margin: 2px 0; width: 100%;">
                                            {!! $product->generateQrcode($product->code) !!}
                                        </div>
                                        <div class="qrcode-name"
                                            style="font-size: 14px; margin: 8px 0; font-weight: 500;">
                                            {{ $product->name }}</div>
                                        <div class="product-price" style="font-weight: bold; font-size: 12px; margin-top: 2px;">จำนวน:
                                            {{ number_format($product->amount) }} {{ $product->unit }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        @endforeach

        @if ($products->isEmpty())
            <div style="text-align: center; padding: 50px; color: #666;">
                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 20px;"></i>
                <h3>ไม่พบข้อมูลสินค้า</h3>
                <p>ไม่มีสินค้าในระบบที่จะแสดงผล</p>
            </div>
        @endif
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script>
        // Add responsive behavior for mobile view
        document.addEventListener('DOMContentLoaded', function() {
            // Add touch support for better mobile interaction
            const printBtn = document.querySelector('.print-btn');
            if (printBtn) {
                printBtn.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.95)';
                });

                printBtn.addEventListener('touchend', function() {
                    this.style.transform = '';
                });
            }

            // Make tables horizontally scrollable on mobile
            const tables = document.querySelectorAll('.products-table');
            tables.forEach(table => {
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            });
        });
    </script>
</body>

</html>
