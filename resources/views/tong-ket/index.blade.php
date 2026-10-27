@extends('layouts.app')
@section('title', 'Tổng kết học lực')

@push('styles')
<style>
@media print {
    .sidebar, .topbar, .no-print { display: none !important; }
    .main-content { margin-left: 0 !important; }
}
.diem-thap { color: #dc3545; font-weight: 600; }
</style>
@endpush

@section('content')
@php
    $mauHocTap = ['Tốt' => 'success', 'Khá' => 'primary', 'Đạt' => 'warning', 'Chưa đạt' => 'danger'];
    $mauLenLop = ['Lên lớp' => 'success', 'Hoàn thành chương trình' => 'success', 'Chưa được lên lớp' => 'warning', 'Ở lại lớp' => 'danger', 'Chưa đủ dữ liệu' => 'secondary'];
@endphp
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-award text-primary me-2"></i>Tổng kết học lực & Danh hiệu</h4>
    <div class="d-flex gap-2 no-print">
        @can('xet-len-lop')
        <a href="{{ route('tong-ket.len-lop', ['lop_hoc_id' => $lop?->id]) }}" class="btn btn-success">
            <i class="bi bi-arrow-up-circle me-2"></i>Xét lên lớp
        </a>
        @endcan
        @if($lop)
        @can('gui-tin-phu-huynh')
        <form method="POST" action="{{ route('tong-ket.gui-ket-qua') }}" class="d-flex align-items-center gap-2"
              onsubmit="return confirm('Gửi kết quả {{ $hocKy === 'Ca năm' ? 'cả năm' : 'học kỳ ' . $hocKy }} tới phụ huynh lớp {{ $lop->ten_lop }}?')">
            @csrf
            <input type="hidden" name="lop_hoc_id" value="{{ $lop->id }}">
            <input type="hidden" name="hoc_ky" value="{{ $hocKy }}">
            <input type="hidden" name="kenh[]" value="email">
            <input type="hidden" name="kenh[]" value="zalo">
            <button class="btn btn-outline-primary"><i class="bi bi-send me-2"></i>Gửi kết quả cho phụ huynh</button>
        </form>
        @endcan
        @endif
        @if($lop)
        <a href="{{ route('lop-hoc.so-diem', $lop) }}" class="btn btn-outline-success"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Sổ điểm</a>
        @endif
        <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer me-2"></i>In</button>
    </div>
</div>

<div class="card table-card mb-4 no-print">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Lớp</label>
                <select name="lop_hoc_id" class="form-select">
                    @foreach($lopHoc as $l)
                        <option value="{{ $l->id }}" {{ $lop?->id == $l->id ? 'selected' : '' }}>{{ $l->ten_lop }} ({{ $l->nam_hoc }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Kỳ tổng kết</label>
                <select name="hoc_ky" class="form-select">
                    @foreach(['1' => 'Học kỳ 1', '2' => 'Học kỳ 2', 'Ca năm' => 'Cả năm'] as $v => $l)
                        <option value="{{ $v }}" {{ $hocKy == $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="bi bi-calculator me-1"></i>Tổng kết</button>
            </div>
        </form>
    </div>
</div>

@if(!$lop)
    <div class="alert alert-warning">Chưa có lớp nào để tổng kết.</div>
@else
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card h-100"><div class="card-body">
            <div class="fw-semibold mb-2">Kết quả học tập</div>
            @foreach($thongKe['hoc_tap'] as $k => $v)
                <span class="badge bg-{{ $mauHocTap[$k] ?? 'secondary' }} me-1 mb-1">{{ $k }}: {{ $v }}</span>
            @endforeach
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card h-100"><div class="card-body">
            <div class="fw-semibold mb-2">Kết quả rèn luyện</div>
            @foreach($thongKe['ren_luyen'] as $k => $v)
                <span class="badge bg-{{ $mauHocTap[$k] ?? 'secondary' }} me-1 mb-1">{{ $k }}: {{ $v }}</span>
            @endforeach
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card h-100"><div class="card-body">
            <div class="fw-semibold mb-2">{{ $hocKy === 'Ca năm' ? 'Lên lớp' : 'Danh hiệu' }}</div>
            @foreach($hocKy === 'Ca năm' ? $thongKe['len_lop'] : $thongKe['danh_hieu'] as $k => $v)
                <span class="badge bg-{{ $mauLenLop[$k] ?? ($k === 'Không' ? 'secondary' : 'info') }} me-1 mb-1">{{ $k }}: {{ $v }}</span>
            @endforeach
        </div></div>
    </div>
</div>

<div class="card table-card">
    <div class="card-header bg-white py-3">
        <h6 class="fw-semibold mb-0">
            Bảng tổng kết lớp {{ $lop->ten_lop }} — {{ $hocKy === 'Ca năm' ? 'Cả năm' : 'Học kỳ ' . $hocKy }} — Năm học {{ $lop->nam_hoc }}
            @if($lop->giaoVienChuNhiem) <small class="text-muted fw-normal">(GVCN: {{ $lop->giaoVienChuNhiem->name }})</small> @endif
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover table-bordered mb-0 align-middle small">
            <thead>
                <tr>
                    <th class="text-center">STT</th>
                    <th style="min-width:160px">Họ và tên</th>
                    @foreach($monHoc as $mon)
                        <th class="text-center" title="{{ $mon->ten_mon }}">{{ $mon->ma_mon }}</th>
                    @endforeach
                    <th class="text-center">ĐTB</th>
                    <th class="text-center">Học tập</th>
                    <th class="text-center">Rèn luyện</th>
                    <th class="text-center">Nghỉ</th>
                    <th class="text-center">Danh hiệu</th>
                    @if($hocKy === 'Ca năm')<th class="text-center">Lên lớp</th>@endif
                </tr>
            </thead>
            <tbody>
                @forelse($ketQua as $i => $r)
                <tr class="{{ $r['dang_o_lop'] ? '' : 'text-muted' }}">
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>
                        <a href="{{ route('hoc-sinh.show', $r['hoc_sinh']->id) }}" class="text-decoration-none fw-semibold">{{ $r['hoc_sinh']->ho_ten }}</a>
                        @unless($r['dang_o_lop'])<span class="badge bg-light text-muted border ms-1">đã chuyển</span>@endunless
                    </td>
                    @foreach($monHoc as $mon)
                        @php $d = $r['diem_mon'][$mon->id]; @endphp
                        <td class="text-center {{ \App\Support\Diem::thap($d) ? 'diem-thap' : '' }}">{{ \App\Support\Diem::hienThi($d) }}</td>
                    @endforeach
                    <td class="text-center fw-semibold">{{ $r['dtb_chung'] ?? '—' }}</td>
                    <td class="text-center">
                        @if($r['hoc_tap'])
                            <span class="badge bg-{{ $mauHocTap[$r['hoc_tap']] }}">{{ $r['hoc_tap'] }}</span>
                        @else
                            <span class="text-muted" title="Còn môn chưa có điểm trung bình">Chưa đủ điểm</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($r['ren_luyen'])
                            <span class="badge bg-{{ $mauHocTap[$r['ren_luyen']] }}">{{ $r['ren_luyen'] }}</span>
                        @else — @endif
                    </td>
                    <td class="text-center {{ $r['so_buoi_nghi'] > 45 ? 'diem-thap' : '' }}">{{ $r['so_buoi_nghi'] }}</td>
                    <td class="text-center">
                        @if($r['danh_hieu'])
                            <span class="badge {{ $r['danh_hieu'] === 'Học sinh Xuất sắc' ? 'bg-danger' : 'bg-info' }}">{{ $r['danh_hieu'] }}</span>
                        @endif
                    </td>
                    @if($hocKy === 'Ca năm')
                    <td class="text-center">
                        <span class="badge bg-{{ $mauLenLop[$r['len_lop']['ket_qua']] ?? 'secondary' }}"
                              title="{{ implode('; ', $r['len_lop']['ly_do']) }}">{{ $r['len_lop']['ket_qua'] }}</span>
                    </td>
                    @endif
                </tr>
                @empty
                <tr><td colspan="{{ $monHoc->count() + 8 }}" class="text-center text-muted py-4">Lớp chưa có học sinh / điểm số</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white small text-muted">
        Xếp loại theo Thông tư 22/2021/TT-BGDĐT: <b>Tốt</b> (mọi môn ≥ 6.5, ít nhất 6 môn ≥ 8.0) ·
        <b>Khá</b> (mọi môn ≥ 5.0, ít nhất 6 môn ≥ 6.5) · <b>Đạt</b> (tối đa 1 môn &lt; 5.0, không môn nào &lt; 3.5) ·
        ĐTB môn cả năm = (HK1 + 2×HK2)/3. Rèn luyện quy đổi từ hạnh kiểm (Trung bình → Đạt, Yếu → Chưa đạt).
    </div>
</div>
@endif
@endsection
