<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; color: #222; line-height: 1.6">
    <div style="max-width: 600px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden">
        <div style="background: #1e3a5f; color: #fff; padding: 14px 20px; font-weight: bold">{{ config('app.ten_truong') }}</div>
        <div style="padding: 20px">
            <p>Kính gửi Quý phụ huynh em <b>{{ $nguoiNhan->hocSinh->ho_ten }}</b>
                ({{ $nguoiNhan->hocSinh->ma_hoc_sinh }}{{ $nguoiNhan->hocSinh->lopHoc ? ' - lớp ' . $nguoiNhan->hocSinh->lopHoc->ten_lop : '' }}),</p>
            <h3 style="margin: 12px 0">{{ $nguoiNhan->tinNhan->tieu_de }}</h3>
            <div style="white-space: pre-line">{{ $nguoiNhan->noi_dung }}</div>
            <p style="margin-top: 20px; font-size: 13px; color: #666">
                Quý phụ huynh có thể xem kết quả học tập, chuyên cần và học phí tại
                <a href="{{ route('tra-cuu') }}">{{ route('tra-cuu') }}</a>.
            </p>
        </div>
    </div>
</body>
</html>
