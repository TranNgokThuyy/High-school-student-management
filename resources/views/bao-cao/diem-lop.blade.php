@extends('layouts.app')
@section('title', 'Báo cáo điểm lớp ' . $lop->ten_lop)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-file-earmark-bar-graph text-primary me-2"></i>
            Báo cáo điểm — {{ $lop->ten_lop }}
        </h4>
        <p class="text-muted mb-0">
            Học kỳ {{ $request->hoc_ky }} &nbsp;|&nbsp;
            Năm học {{ $request->nam_hoc }} &nbsp;|&nbsp;
            {{ $lop->hocSinh->count() }} học sinh
            @if($lop->giaoVienChuNhiem)
                &nbsp;|&nbsp; GVCN: {{ $lop->giaoVienChuNhiem->name }}
            @endif
        </p>
    </div>
    <div class="d-flex gap-2">
        {{-- Nút xuất --}}
        <a href="{{ route('bao-cao.xuat-pdf', request()->all()) }}"
           class="btn btn-danger" target="_blank">
            <i class="bi bi-file-pdf me-2"></i>Xuất PDF
        </a>
        <a href="{{ route('bao-cao.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Quay lại
        </a>
    </div>
</div>

{{-- Thống kê tổng hợp --}}
@php
    $tongHocSinh  = count($bangDiem);
    $soGioi       = collect($bangDiem)->filter(fn($r) => isset($r['diem_tb_tong_hop']) && $r['diem_tb_tong_hop'] >= 8.0)->count();
    $soKha        = collect($bangDiem)->filter(fn($r) => isset($r['diem_tb_tong_hop']) && $r['diem_tb_tong_hop'] >= 6.5 && $r['diem_tb_tong_hop'] < 8.0)->count();
    $soTrungBinh  = collect($bangDiem)->filter(fn($r) => isset($r['diem_tb_tong_hop']) && $r['diem_tb_tong_hop'] >= 5.0 && $r['diem_tb_tong_hop'] < 6.5)->count();
    $soYeuKem     = collect($bangDiem)->filter(fn($r) => isset($r['diem_tb_tong_hop']) && $r['diem_tb_tong_hop'] < 5.0)->count();
    $diemTBLop    = collect($bangDiem)->filter(fn($r) => isset($r['diem_tb_tong_hop']))->avg('diem_tb_tong_hop');
@endphp

<div class="row g-3 mb-4">
    <div class="col-md-2 col-sm-4">
        <div class="card border-0 bg-light text-center py-3">
            <div class="fs-2 fw-bold text-dark">{{ $tongHocSinh }}</div>
            <div class="small text-muted">Tổng HS</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4">
        <div class="card border-0 bg-success bg-opacity-10 text-center py-3">
            <div class="fs-2 fw-bold text-success">{{ $soGioi }}</div>
            <div class="small text-muted">Giỏi (≥8.0)</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4">
        <div class="card border-0 bg-primary bg-opacity-10 text-center py-3">
            <div class="fs-2 fw-bold text-primary">{{ $soKha }}</div>
            <div class="small text-muted">Khá (6.5–7.9)</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4">
        <div class="card border-0 bg-warning bg-opacity-10 text-center py-3">
            <div class="fs-2 fw-bold text-warning">{{ $soTrungBinh }}</div>
            <div class="small text-muted">Trung bình (5.0–6.4)</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4">
        <div class="card border-0 bg-danger bg-opacity-10 text-center py-3">
            <div class="fs-2 fw-bold text-danger">{{ $soYeuKem }}</div>
            <div class="small text-muted">Yếu/Kém (&lt;5.0)</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4">
        <div class="card border-0 bg-info bg-opacity-10 text-center py-3">
            <div class="fs-2 fw-bold text-info">
                {{ $diemTBLop ? number_format($diemTBLop, 2) : '—' }}
            </div>
            <div class="small text-muted">ĐTB lớp</div>
        </div>
    </div>
</div>

{{-- Bảng điểm chi tiết --}}
<div class="card table-card">
    <div class="card-header bg-white py-3">
        <h6 class="fw-semibold mb-0">
            <i class="bi bi-table me-2 text-primary"></i>
            Bảng điểm chi tiết — Lớp {{ $lop->ten_lop }} — HK{{ $request->hoc_ky }}/{{ $request->nam_hoc }}
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0 small">
            <thead class="table-light">
                <tr>
                    <th class="text-center" rowspan="2" style="width:40px;vertical-align:middle">STT</th>
                    <th rowspan="2" style="min-width:160px;vertical-align:middle">Họ và tên</th>
                    @foreach($monHoc as $mon)
                        <th class="text-center" style="min-width:80px">
                            {{ $mon->ten_mon }}
                        </th>
                    @endforeach
                    <th class="text-center bg-warning bg-opacity-25" style="min-width:80px;vertical-align:middle" rowspan="2">
                        ĐTB<br>Tổng hợp
                    </th>
                    <th class="text-center bg-info bg-opacity-25" style="min-width:90px;vertical-align:middle" rowspan="2">
                        Xếp loại
                    </th>
                </tr>
                <tr>
                    @foreach($monHoc as $mon)
                        <th class="text-center text-muted fw-normal" style="font-size:0.7rem">
                            TB
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($bangDiem as $i => $item)
                    @php
                        $dtbTH = $item['diem_tb_tong_hop'] ?? null;
                        $xepLoai = match(true) {
                            is_null($dtbTH)     => null,
                            $dtbTH >= 8.0       => 'Giỏi',
                            $dtbTH >= 6.5       => 'Khá',
                            $dtbTH >= 5.0       => 'Trung bình',
                            $dtbTH >= 3.5       => 'Yếu',
                            default             => 'Kém',
                        };
                        $xepLoaiColors = [
                            'Giỏi'       => 'success',
                            'Khá'        => 'primary',
                            'Trung bình' => 'warning',
                            'Yếu'        => 'danger',
                            'Kém'        => 'dark',
                        ];
                    @endphp
                    <tr>
                        <td class="text-center text-muted">{{ $i + 1 }}</td>
                        <td>
                            <a href="{{ route('hoc-sinh.show', $item['hoc_sinh']) }}"
                               class="text-decoration-none text-dark fw-semibold">
                                {{ $item['hoc_sinh']->ho_ten }}
                            </a>
                        </td>
                        @foreach($monHoc as $mon)
                            @php
                                $diem = $item['diem'][$mon->id] ?? null;
                                $dtb  = $diem?->diem_trung_binh;
                            @endphp
                            <td class="text-center
                                {{ !is_null($dtb) && $dtb < 5.0 ? 'bg-danger bg-opacity-10 text-danger fw-bold' : '' }}
                                {{ !is_null($dtb) && $dtb >= 8.0 ? 'text-success fw-semibold' : '' }}">
                                {{ !is_null($dtb) ? number_format($dtb, 1) : '—' }}
                            </td>
                        @endforeach
                        <td class="text-center fw-bold bg-warning bg-opacity-10
                            {{ !is_null($dtbTH) && $dtbTH < 5.0 ? 'text-danger' : '' }}
                            {{ !is_null($dtbTH) && $dtbTH >= 8.0 ? 'text-success' : '' }}">
                            {{ !is_null($dtbTH) ? number_format($dtbTH, 2) : '—' }}
                        </td>
                        <td class="text-center bg-info bg-opacity-10">
                            @if($xepLoai)
                                <span class="badge bg-{{ $xepLoaiColors[$xepLoai] ?? 'secondary' }}">
                                    {{ $xepLoai }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($monHoc) + 4 }}" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox d-block fs-2 mb-2"></i>
                            Chưa có dữ liệu điểm cho lớp này
                        </td>
                    </tr>
                @endforelse
            </tbody>

            {{-- Hàng thống kê cuối bảng --}}
            @if(count($bangDiem) > 0)
            <tfoot class="table-light">
                <tr>
                    <td colspan="2" class="fw-bold text-end">Điểm TB môn:</td>
                    @foreach($monHoc as $mon)
                        @php
                            $diemMon = collect($bangDiem)
                                ->map(fn($r) => $r['diem'][$mon->id]?->diem_trung_binh ?? null)
                                ->filter(fn($d) => !is_null($d));
                            $avgMon = $diemMon->count() > 0 ? $diemMon->avg() : null;
                        @endphp
                        <td class="text-center fw-semibold text-primary">
                            {{ $avgMon ? number_format($avgMon, 1) : '—' }}
                        </td>
                    @endforeach
                    <td class="text-center fw-bold text-primary bg-warning bg-opacity-10">
                        {{ $diemTBLop ? number_format($diemTBLop, 2) : '—' }}
                    </td>
                    <td class="text-center bg-info bg-opacity-10">
                        <small class="text-muted">TB lớp</small>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="fw-bold text-end">Số HS đạt:</td>
                    @foreach($monHoc as $mon)
                        @php
                            $soHsDat = collect($bangDiem)
                                ->filter(fn($r) => !is_null($r['diem'][$mon->id]?->diem_trung_binh)
                                    && $r['diem'][$mon->id]->diem_trung_binh >= 5.0)
                                ->count();
                            $tongHsCoDiem = collect($bangDiem)
                                ->filter(fn($r) => !is_null($r['diem'][$mon->id]?->diem_trung_binh))
                                ->count();
                        @endphp
                        <td class="text-center small text-muted">
                            {{ $tongHsCoDiem > 0 ? $soHsDat . '/' . $tongHsCoDiem : '—' }}
                        </td>
                    @endforeach
                    <td colspan="2" class="text-center text-muted small">
                        Đạt TB: {{ $tongHocSinh > 0 ? number_format(($soGioi + $soKha + $soTrungBinh) / $tongHocSinh * 100, 1) : 0 }}%
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- Chú thích --}}
    <div class="card-footer bg-white">
        <div class="row g-2 align-items-center">
            <div class="col-md-6">
                <small class="text-muted">
                    <span class="badge bg-success me-1">Giỏi</span> ≥ 8.0
                    <span class="badge bg-primary me-1 ms-2">Khá</span> 6.5 – 7.9
                    <span class="badge bg-warning me-1 ms-2">TB</span> 5.0 – 6.4
                    <span class="badge bg-danger ms-2">Yếu/Kém</span> &lt; 5.0
                </small>
            </div>
            <div class="col-md-6 text-end">
                <small class="text-muted">
                    <i class="bi bi-clock me-1"></i>
                    Xuất lúc: {{ now()->format('H:i d/m/Y') }}
                </small>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table-bordered th, .table-bordered td {
    border-color: #dee2e6 !important;
}
thead th {
    white-space: nowrap;
    font-size: 0.78rem;
}
</style>
@endpush