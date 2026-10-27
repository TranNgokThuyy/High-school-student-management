<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ config('app.ten_truong') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Inter', system-ui, sans-serif; }
        .dau-trang { background: linear-gradient(90deg, #1e3a5f, #2d5a8e); color: #fff; }
        .the { border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        @media print { .no-print { display: none !important; } body { background: #fff; } }
    </style>
</head>
<body>
    <div class="dau-trang py-3 mb-4 no-print">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="fw-bold"><i class="bi bi-mortarboard-fill text-warning me-2"></i>{{ config('app.ten_truong') }}</div>
            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-light">Đăng nhập cán bộ</a>
        </div>
    </div>
    <div class="container pb-5">@yield('content')</div>
</body>
</html>
