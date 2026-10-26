@extends('layouts.public')
@section('title', 'Kết quả - ' . $hocSinh->ho_ten)

@section('content')
@use('App\Support\Tien')
@php $ky = $ketQua['theo_ky'] ?? null; $dd = fn ($d) => is_null($d) ? '—' : number_format($d, 1); @endphp
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">{{ $hocSinh->ho_ten }}</h4>
        <div class="text-muted">{{ $hocSinh->ma_hoc_sinh }} · Lớp {{ $hocSinh->lopHoc?->ten_lop ?? '—' }} · Năm học {{ $namHoc }}</div>
    </div>
    <div class="d-flex gap-2 no-print">
        <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer me-1"></i>In</button>
        <a href="{{ route('tra-cuu') }}" class="btn btn-outline-primary">Tra cứu khác</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card the">
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-journal-text me-2 text-primary"></i>Điểm trung bình các môn</div>
            @if(!$ky)
                <div class="card-body text-muted">Chưa có dữ liệu điểm trong năm học này.</div>
            @else
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 text-center align-middle">
                    <thead class="table-light"><tr><th class="text-start">Môn học</th><th>HK I</th><th>HK II</th><th>Cả năm</th></tr></thead>
                    <tbody>
                        @foreach($ketQua['mon_hoc'] as $mon)
                        <tr>
                            <td class="text-start">{{ $mon->ten_mon }}</td>
                            @foreach(['1', '2', 'Ca năm'] as $k)
                                @php $d = $ky[$k]['diem_mon'][$mon->id] ?? null; @endphp
                                <td class="{{ !is_null($d) && $d < 5 ? 'text-danger fw-semibold' : '' }}">{{ $dd($d) }}</td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="fw-semibold">
                        <tr><td class="text-start">Kết quả học tập</td>@foreach(['1', '2', 'Ca năm'] as $k)<td>{{ $ky[$k]['hoc_tap'] ?? '—' }}</td>@endforeach</tr>
                        <tr><td class="text-start">Kết quả rèn luyện</td>@foreach(['1', '2', 'Ca năm'] as $k)<td>{{ $ky[$k]['ren_luyen'] ?? '—' }}</td>@endforeach</tr>
                        <tr><td class="text-start">Số buổi nghỉ</td>@foreach(['1', '2', 'Ca năm'] as $k)<td>{{ $ky[$k]['so_buoi_nghi'] ?? '—' }}</td>@endforeach</tr>
                        <tr><td class="text-start">Danh hiệu</td>@foreach(['1', '2', 'Ca năm'] as $k)<td class="text-success">{{ $ky[$k]['danh_hieu'] ?? '' }}</td>@endforeach</tr>
                    </tfoot>
                </table>
            </div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card the mb-4">
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-calendar-x me-2 text-warning"></i>Vắng / đi trễ 30 ngày gần đây</div>
            <ul class="list-group list-group-flush small">
                @forelse($vangGanDay as $v)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ $v->ngay->format('d/m/Y') }}</span>
                        <span class="{{ $v->trang_thai === 'Vắng không phép' ? 'text-danger fw-semibold' : '' }}">{{ $v->trang_thai }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-success">Đi học đầy đủ</li>
                @endforelse
            </ul>
        </div>

        <div class="card the mb-4">
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-cash-coin me-2 text-success"></i>Học phí & các khoản thu</div>
            <ul class="list-group list-group-flush small">
                @forelse($congNo as $c)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ $c['khoan_thu']->ten }}@if($c['khoan_thu']->han_nop)<br><span class="text-muted">Hạn {{ $c['khoan_thu']->han_nop->format('d/m/Y') }}</span>@endif</span>
                        @if($c['con_no'] > 0)
                            <span class="text-danger fw-semibold text-end">Còn {{ Tien::dinhDang($c['con_no']) }}</span>
                        @else
                            <span class="text-success"><i class="bi bi-check-circle"></i> Đã nộp</span>
                        @endif
                    </li>
                @empty
                    <li class="list-group-item text-muted">Không có khoản thu</li>
                @endforelse
            </ul>
        </div>

        @if($khenThuong->isNotEmpty())
        <div class="card the">
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-trophy me-2 text-warning"></i>Khen thưởng - Kỷ luật</div>
            <ul class="list-group list-group-flush small">
                @foreach($khenThuong as $kt)
                    <li class="list-group-item">
                        <span class="badge bg-{{ $kt->loai === 'Khen thưởng' ? 'success' : 'danger' }}">{{ $kt->hinh_thuc }}</span>
                        {{ $kt->noi_dung }} <span class="text-muted">({{ $kt->ngay_quyet_dinh->format('d/m/Y') }})</span>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</div>
@endsection
