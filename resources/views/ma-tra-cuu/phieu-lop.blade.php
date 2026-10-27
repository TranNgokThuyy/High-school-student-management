<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu mã tra cứu - Lớp {{ $lopHoc->ten_lop }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f0f2f5; color: #111; }
        .trang { width: 190mm; margin: 12px auto; background: #fff; padding: 8mm; box-sizing: border-box;
                 display: grid; grid-template-columns: 1fr 1fr; gap: 6mm; }
        .phieu { border: 1px dashed #555; padding: 5mm; break-inside: avoid; font-size: 13px; line-height: 1.55; }
        .phieu .truong { font-weight: bold; text-transform: uppercase; font-size: 11px; color: #1e3a5f; }
        .phieu .ten { font-size: 15px; font-weight: bold; margin: 4px 0; }
        .ma { font-family: "Courier New", monospace; font-size: 18px; font-weight: bold; letter-spacing: 2px; }
        .nho { font-size: 11px; color: #555; }
        .nut { text-align: center; margin: 14px; }
        @media print { body { background: #fff; } .trang { margin: 0; } .nut { display: none; } }
    </style>
</head>
<body>
<div class="nut">
    <button onclick="window.print()" style="font-size:15px;padding:8px 20px;cursor:pointer">🖨 In phiếu (cắt theo đường chấm)</button>
</div>
<div class="trang">
    @forelse($hocSinh as $hs)
    <div class="phieu">
        <div class="truong">{{ config('app.ten_truong') }}</div>
        <div>PHIẾU TRA CỨU KẾT QUẢ HỌC TẬP</div>
        <div class="ten">{{ $hs->ho_ten }} — Lớp {{ $lopHoc->ten_lop }}</div>
        <div>Mã học sinh: <span class="ma">{{ $hs->ma_hoc_sinh }}</span></div>
        <div>Mã tra cứu: <span class="ma">{{ $hs->ma_tra_cuu }}</span></div>
        <div class="nho">Tra cứu tại: {{ route('tra-cuu') }}<br>Vui lòng giữ bí mật mã tra cứu. Mất mã: liên hệ GVCN để cấp lại.</div>
    </div>
    @empty
    <p>Lớp chưa có học sinh đang học.</p>
    @endforelse
</div>
</body>
</html>
