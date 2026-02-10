<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        h1 { font-size: 18px; margin: 0; }
        h2 { font-size: 14px; margin: 10px 0 5px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 5px 8px;
            text-align: left;
        }
        th {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 10px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 { margin-bottom: 3px; }
        .header p { margin: 2px 0; color: #666; font-size: 10px; }
        .footer {
            text-align: center;
            font-size: 9px;
            color: #999;
            margin-top: 20px;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
        .summary {
            margin: 10px 0;
            font-size: 11px;
        }
        .summary strong { font-size: 12px; }
        .badge {
            padding: 2px 6px;
            font-size: 9px;
            border-radius: 3px;
            color: #fff;
        }
        .badge-success { background: #198754; }
        .badge-danger { background: #dc3545; }
        .badge-warning { background: #ffc107; color: #333; }
    </style>
</head>
<body>
    <div class="header">
        <h1>@yield('site-name', config('app.name'))</h1>
        <p>@yield('report-title')</p>
        <p>@yield('report-subtitle')</p>
    </div>

    @yield('content')

    <div class="footer">
        Oluşturulma tarihi: {{ now()->format('d.m.Y H:i') }} | {{ config('app.name') }}
    </div>
</body>
</html>
