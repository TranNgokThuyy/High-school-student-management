<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        body { margin: 10px 20px; }
        .trang-moi { page-break-before: always; }
        .dau { width: 100%; }
        .dau td { border: none; vertical-align: top; }
        .truong { font-weight: bold; text-transform: uppercase; }
        .bia { text-align: center; padding-top: 120px; }
        .bia h1 { font-size: 30px; letter-spacing: 4px; margin: 0 0 6px; }
        .bia .phu { font-size: 14px; margin-bottom: 60px; }
        .bia table { margin: 0 auto; text-align: left; }
        .bia td { padding: 5px 10px; font-size: 13px; border: none; }
        h2 { text-align: center; font-size: 15px; margin: 6px 0 2px; }
        .giua { text-align: center; margin-bottom: 8px; }
        table.bang { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.bang th, table.bang td { border: 1px solid #333; padding: 4px 6px; text-align: center; }
        table.bang th { background: #e8f0fe; }
        table.bang td.trai { text-align: left; }
        .thap { color: #c00; }
        .ky { width: 100%; margin-top: 20px; }
        .ky td { border: none; text-align: center; width: 50%; vertical-align: top; }
    </style>
</head>
<body>
@use('App\Support\Diem')

{{-- Trang bìa --}}
<table class="dau">
    <tr>
        <td><div class="truong">{{ config('app.ten_truong') }}</div></td>
        <td style="text-align:right"><b>CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</b><br>Độc lập - Tự do - Hạnh phúc</td>
    </tr>
</table>
<div class="bia">
    <h1>HỌC BẠ</h1>
    <div class="phu">TRUNG HỌC PHỔ THÔNG</div>
    <table>
        <tr><td>Họ và tên học sinh:</td><td><b>{{ $hocSinh->ho_ten }}</b></td></tr>
        <tr><td>Mã học sinh:</td><td>{{ $hocSinh->ma_hoc_sinh }}</td></tr>
        <tr><td>Ngày sinh:</td><td>{{ $hocSinh->ngay_sinh?->format('d/m/Y') }}</td></tr>
        <tr><td>Giới tính:</td><td>{{ $hocSinh->gioi_tinh }}</td></tr>
        <tr><td>Dân tộc:</td><td>{{ $hocSinh->dan_toc }}</td></tr>
        <tr><td>Nơi thường trú:</td><td>{{ $hocSinh->dia_chi_thuong_tru }}</td></tr>
        <tr><td>Họ tên cha:</td><td>{{ $hocSinh->ho_ten_cha }}</td></tr>
        <tr><td>Họ tên mẹ:</td><td>{{ $hocSinh->ho_ten_me }}</td></tr>
    </table>
</div>

{{-- Mỗi năm học một trang --}}
@foreach($cacNam as $nam)
@php $ky = $nam['theo_ky']; $kt = $khenThuong[$nam['nam_hoc']] ?? collect(); @endphp
<div class="trang-moi">
    <h2>KẾT QUẢ HỌC TẬP VÀ RÈN LUYỆN</h2>
    <div class="giua">Năm học {{ $nam['nam_hoc'] }} — Lớp {{ $nam['lop']->ten_lop }} — Học sinh: <b>{{ $hocSinh->ho_ten }}</b></div>

    <table class="bang">
        <thead><tr><th style="width:30px">STT</th><th>Môn học / Hoạt động giáo dục</th><th>Học kỳ I</th><th>Học kỳ II</th><th>Cả năm</th></tr></thead>
        <tbody>
            @foreach($nam['mon_hoc'] as $i => $mon)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="trai">{{ $mon->ten_mon }}</td>
                @foreach(['1', '2', 'Ca năm'] as $k)
                    @php $d = $ky[$k]['diem_mon'][$mon->id] ?? null; @endphp
                    <td class="{{ Diem::thap($d) ? 'thap' : '' }}">{{ Diem::hienThi($d) }}</td>
                @endforeach
            </tr>
            @endforeach
            <tr><td colspan="2" class="trai"><b>Kết quả học tập</b></td>@foreach(['1', '2', 'Ca năm'] as $k)<td><b>{{ $ky[$k]['hoc_tap'] ?? '—' }}</b></td>@endforeach</tr>
            <tr><td colspan="2" class="trai"><b>Kết quả rèn luyện</b></td>@foreach(['1', '2', 'Ca năm'] as $k)<td><b>{{ $ky[$k]['ren_luyen'] ?? '—' }}</b></td>@endforeach</tr>
            <tr><td colspan="2" class="trai">Tổng số buổi nghỉ</td>@foreach(['1', '2', 'Ca năm'] as $k)<td>{{ $ky[$k]['so_buoi_nghi'] ?? '—' }}</td>@endforeach</tr>
        </tbody>
    </table>

    <p><b>Danh hiệu cuối năm:</b> {{ $ky['Ca năm']['danh_hieu'] ?? 'Không' }}
       &nbsp;&nbsp; <b>Kết quả xét cuối năm:</b> {{ $ky['Ca năm']['len_lop']['ket_qua'] ?? '—' }}</p>
    @if($kt->isNotEmpty())
    <p><b>Khen thưởng / kỷ luật trong năm:</b></p>
    <ul style="margin-top:0">
        @foreach($kt as $k)<li>{{ $k->ngay_quyet_dinh->format('d/m/Y') }}: {{ $k->loai }} — {{ $k->hinh_thuc }}: {{ $k->noi_dung }}</li>@endforeach
    </ul>
    @endif
    <p><b>Nhận xét của giáo viên chủ nhiệm:</b><br>...........................................................................................................................................................<br>...........................................................................................................................................................</p>

    <table class="ky">
        <tr>
            <td><b>GIÁO VIÊN CHỦ NHIỆM</b><br><i>(Ký, ghi rõ họ tên)</i><br><br><br><br>{{ $nam['lop']->giaoVienChuNhiem?->name }}</td>
            <td><b>HIỆU TRƯỞNG</b><br><i>(Ký, ghi rõ họ tên, đóng dấu)</i></td>
        </tr>
    </table>
</div>
@endforeach
</body>
</html>
