@use('App\Support\Tien')
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu thu {{ $phieu->so_phieu }}</title>
    <style>
        body { font-family: "Times New Roman", serif; font-size: 15px; color: #000; background: #f0f2f5; margin: 0; }
        .phieu { background: #fff; width: 190mm; margin: 16px auto; padding: 12mm 14mm; box-sizing: border-box; position: relative; }
        .dau { display: flex; justify-content: space-between; }
        .dau .truong { font-weight: bold; text-transform: uppercase; }
        h1 { text-align: center; font-size: 22px; margin: 14px 0 2px; }
        .giua { text-align: center; font-style: italic; margin-bottom: 14px; }
        .dong { margin: 7px 0; }
        .dong b { font-weight: bold; }
        .ky { display: flex; justify-content: space-between; text-align: center; margin-top: 22px; }
        .ky > div { width: 33%; }
        .ky .chuc { font-weight: bold; }
        .ky .ghi { font-style: italic; font-size: 13px; }
        .ky .ten { margin-top: 60px; font-weight: bold; }
        .huy { position: absolute; top: 40%; left: 20%; font-size: 64px; color: rgba(220,53,69,.35); transform: rotate(-20deg); font-weight: bold; border: 6px solid rgba(220,53,69,.35); padding: 0 20px; }
        .nut { text-align: center; margin: 16px; }
        .nut button { font-size: 15px; padding: 8px 20px; cursor: pointer; }
        @media print { body { background: #fff; } .phieu { margin: 0; } .nut { display: none; } }
    </style>
</head>
<body>
<div class="nut"><button onclick="window.print()">🖨 In phiếu thu</button></div>
<div class="phieu">
    @if($phieu->da_huy)<div class="huy">ĐÃ HỦY</div>@endif
    <div class="dau">
        <div>
            <div class="truong">{{ config('app.ten_truong') }}</div>
            <div>Bộ phận: Kế toán</div>
        </div>
        <div style="text-align:right">
            <div>Số: <b>{{ $phieu->so_phieu }}</b></div>
            <div>Hình thức: {{ $phieu->hinh_thuc }}</div>
        </div>
    </div>

    <h1>PHIẾU THU</h1>
    <div class="giua">Ngày {{ $phieu->ngay_thu->format('d') }} tháng {{ $phieu->ngay_thu->format('m') }} năm {{ $phieu->ngay_thu->format('Y') }}</div>

    <div class="dong">Họ và tên người nộp tiền: <b>{{ $phieu->nguoi_nop ?: $phieu->hocSinh->ho_ten }}</b></div>
    <div class="dong">Học sinh: <b>{{ $phieu->hocSinh->ho_ten }}</b> — Mã HS: {{ $phieu->hocSinh->ma_hoc_sinh }} — Lớp: {{ $phieu->hocSinh->lopHoc?->ten_lop ?? '—' }}</div>
    <div class="dong">Lý do nộp: {{ $phieu->khoanThu->ten }} — năm học {{ $phieu->khoanThu->nam_hoc }}{{ $phieu->ghi_chu ? ' (' . $phieu->ghi_chu . ')' : '' }}</div>
    <div class="dong">Số tiền: <b>{{ Tien::dinhDang($phieu->so_tien) }}</b></div>
    <div class="dong">Viết bằng chữ: <i>{{ Tien::bangChu($phieu->so_tien) }}</i></div>
    @if($phieu->da_huy)
    <div class="dong" style="color:#dc3545">Phiếu đã hủy lúc {{ $phieu->huy_luc?->format('H:i d/m/Y') }}. Lý do: {{ $phieu->ly_do_huy }}</div>
    @endif

    <div class="ky">
        <div><div class="chuc">Người nộp tiền</div><div class="ghi">(Ký, họ tên)</div><div class="ten">{{ $phieu->nguoi_nop }}</div></div>
        <div><div class="chuc">Người thu tiền</div><div class="ghi">(Ký, họ tên)</div><div class="ten">{{ $phieu->nguoiThu?->name }}</div></div>
        <div><div class="chuc">Kế toán trưởng</div><div class="ghi">(Ký, họ tên)</div><div class="ten"></div></div>
    </div>
</div>
</body>
</html>
