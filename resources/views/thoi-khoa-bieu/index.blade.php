@extends('layouts.app')
@section('title', 'Thời khóa biểu')

@push('styles')
<style>
.tkb-table { border-collapse: collapse; width: 100%; }
.tkb-table th, .tkb-table td {
    border: 1px solid #e5e7eb;
    padding: 0;
    vertical-align: top;
    min-width: 110px;
}
.tkb-table th {
    background: #f8fafc;
    text-align: center;
    padding: 10px 6px;
    font-size: 0.82rem;
    font-weight: 600;
}
.tiet-label {
    background: #f1f5f9;
    text-align: center;
    padding: 8px 4px;
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 600;
    border-right: 2px solid #e2e8f0;
    min-width: 70px;
}
.tkb-cell {
    padding: 4px;
    height: 70px;
    position: relative;
}
.tkb-item {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    border: 1px solid #93c5fd;
    border-left: 4px solid #3b82f6;
    border-radius: 6px;
    padding: 5px 7px;
    font-size: 0.75rem;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
}
.tkb-item .mon { font-weight: 700; color: #1e40af; font-size: 0.78rem; }
.tkb-item .gv  { color: #3b82f6; font-size: 0.68rem; }
.tkb-item .phong { color: #6b7280; font-size: 0.68rem; }
.tkb-item .btn-delete {
    position: absolute; top: 3px; right: 3px;
    width: 18px; height: 18px;
    background: #fee2e2; border: none; border-radius: 50%;
    color: #dc2626; font-size: 10px; line-height: 1;
    display: none; align-items: center; justify-content: center;
    cursor: pointer; padding: 0;
}
.tkb-item:hover .btn-delete { display: flex; }
.tkb-item:hover { background: linear-gradient(135deg, #dbeafe, #bfdbfe); }

/* Màu theo môn */
.tkb-item.mon-toan  { background:linear-gradient(135deg,#fef3c7,#fde68a); border-left-color:#f59e0b; }
.tkb-item.mon-toan .mon { color:#92400e; }
.tkb-item.mon-van   { background:linear-gradient(135deg,#fce7f3,#fbcfe8); border-left-color:#ec4899; }
.tkb-item.mon-van .mon  { color:#9d174d; }
.tkb-item.mon-anh   { background:linear-gradient(135deg,#d1fae5,#a7f3d0); border-left-color:#10b981; }
.tkb-item.mon-anh .mon  { color:#065f46; }
.tkb-item.mon-ly    { background:linear-gradient(135deg,#ede9fe,#ddd6fe); border-left-color:#8b5cf6; }
.tkb-item.mon-ly .mon   { color:#4c1d95; }
.tkb-item.mon-hoa   { background:linear-gradient(135deg,#ffedd5,#fed7aa); border-left-color:#f97316; }
.tkb-item.mon-hoa .mon  { color:#7c2d12; }

.empty-cell { background: #fafafa; }
.today-col  { background: #fefce8 !important; }

.gv-tkb-item {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border-left-color: #22c55e;
}
.gv-tkb-item .mon { color: #166534; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-calendar-week text-primary me-2"></i>Thời khóa biểu
        </h4>
        <p class="text-muted mb-0">Xem và quản lý lịch học theo lớp</p>
    </div>
    @can('them-lop-hoc')
    <a href="{{ route('thoi-khoa-bieu.create', ['lop_hoc_id' => $lopId]) }}"
       class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Thêm lịch học
    </a>
    @endcan
</div>

{{-- Bộ lọc --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold small">Lớp học</label>
                <select name="lop_hoc_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Chọn lớp --</option>
                    @foreach($lopHoc as $lop)
                        <option value="{{ $lop->id }}"
                            {{ $lopId == $lop->id ? 'selected' : '' }}>
                            {{ $lop->ten_lop }} ({{ $lop->nam_hoc }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Học kỳ</label>
                <select name="hoc_ky" class="form-select" onchange="this.form.submit()">
                    <option value="1" {{ $hocKy == '1' ? 'selected' : '' }}>Học kỳ 1</option>
                    <option value="2" {{ $hocKy == '2' ? 'selected' : '' }}>Học kỳ 2</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Năm học</label>
                <input type="text" name="nam_hoc" class="form-control"
                       value="{{ $namHoc }}" placeholder="2024-2025">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel me-2"></i>Xem TKB
                </button>
            </div>
        </form>
    </div>
</div>

@if($selectedLop)

{{-- Thông tin lớp --}}
<div class="alert alert-info d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-info-circle-fill flex-shrink-0"></i>
    <div>
        TKB lớp <strong>{{ $selectedLop->ten_lop }}</strong> —
        Học kỳ <strong>{{ $hocKy }}</strong> —
        Năm học <strong>{{ $namHoc }}</strong>
        @if($selectedLop->giaoVienChuNhiem)
            — GVCN: <strong>{{ $selectedLop->giaoVienChuNhiem->name }}</strong>
        @endif
    </div>
</div>

{{-- Bảng TKB dạng lưới --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-semibold mb-0">
            <i class="bi bi-grid-3x3 text-primary me-2"></i>
            Lịch học theo tuần — {{ $selectedLop->ten_lop }}
        </h6>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary"
                    onclick="window.print()">
                <i class="bi bi-printer me-1"></i>In TKB
            </button>
            <a href="{{ route('thoi-khoa-bieu.create', [
                    'lop_hoc_id' => $lopId,
                    'hoc_ky'     => $hocKy,
                    'nam_hoc'    => $namHoc,
                ]) }}"
               class="btn btn-sm btn-primary">
                <i class="bi bi-plus me-1"></i>Thêm lịch
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="tkb-table">
                <thead>
                    <tr>
                        <th style="width:70px">Tiết</th>
                        @foreach($thuLabels as $thu => $tenThu)
                            <th>{{ $tenThu }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($tietLabels as $tiet => $tenTiet)
                    <tr>
                        <td class="tiet-label">
                            <div>Tiết {{ $tiet }}</div>
                            <div style="font-size:0.65rem;color:#94a3b8">
                                {{ substr($tenTiet, strpos($tenTiet,'(')+1, -1) }}
                            </div>
                        </td>
                        @foreach($thuLabels as $thu => $tenThu)
                            @php
                                $item = $tkbData->get($thu)?->first(function($tkb) use ($tiet) {
                                    return $tkb->tiet_bat_dau <= $tiet
                                        && $tiet <= ($tkb->tiet_bat_dau + $tkb->so_tiet - 1);
                                });

                                // Màu theo môn
                                $monColors = [
                                    'TOAN'=>'mon-toan','VAN'=>'mon-van',
                                    'ANH'=>'mon-anh','LY'=>'mon-ly','HOA'=>'mon-hoa',
                                ];
                                $monClass = $item
                                    ? ($monColors[$item->monHoc->ma_mon] ?? '')
                                    : '';
                            @endphp
                            <td class="tkb-cell {{ !$item ? 'empty-cell' : '' }}">
                                @if($item && $item->tiet_bat_dau == $tiet)
                                <div class="tkb-item {{ $monClass }}"
                                     style="{{ $item->so_tiet > 1 ? 'height:'.($item->so_tiet * 70 - 8).'px' : '' }}">
                                    <div>
                                        <div class="mon">{{ $item->monHoc->ten_mon }}</div>
                                        <div class="gv">
                                            <i class="bi bi-person me-1"></i>
                                            {{ $item->giaoVien->name }}
                                        </div>
                                        @if($item->phong_hoc)
                                        <div class="phong">
                                            <i class="bi bi-door-open me-1"></i>
                                            {{ $item->phong_hoc }}
                                        </div>
                                        @endif
                                        @if($item->so_tiet > 1)
                                        <div class="phong">
                                            {{ $item->so_tiet }} tiết
                                        </div>
                                        @endif
                                    </div>
                                    @can('sua-lop-hoc')
                                    <form method="POST"
                                          action="{{ route('thoi-khoa-bieu.destroy', $item) }}"
                                          class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-delete"
                                                onclick="return confirm('Xóa lịch này?')"
                                                title="Xóa lịch">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@else
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-calendar-week d-block fs-1 mb-3 text-primary opacity-50"></i>
        <h5 class="fw-semibold">Chọn lớp để xem thời khóa biểu</h5>
        <p>Sử dụng bộ lọc phía trên để chọn lớp và học kỳ</p>
    </div>
</div>
@endif

{{-- TKB của giáo viên (nếu là GV/GVCN) --}}
@if($tkbGiaoVien->isNotEmpty())
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white py-3">
        <h6 class="fw-semibold mb-0">
            <i class="bi bi-person-workspace text-success me-2"></i>
            Lịch dạy của tôi — HK{{ $hocKy }}/{{ $namHoc }}
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="tkb-table">
                <thead>
                    <tr>
                        <th style="width:70px">Tiết</th>
                        @foreach($thuLabels as $thu => $tenThu)
                            <th>{{ $tenThu }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($tietLabels as $tiet => $tenTiet)
                    <tr>
                        <td class="tiet-label">
                            <div>Tiết {{ $tiet }}</div>
                        </td>
                        @foreach($thuLabels as $thu => $tenThu)
                            @php
                                $item = $tkbGiaoVien->get($thu)?->first(function($tkb) use ($tiet) {
                                    return $tkb->tiet_bat_dau <= $tiet
                                        && $tiet <= ($tkb->tiet_bat_dau + $tkb->so_tiet - 1);
                                });
                            @endphp
                            <td class="tkb-cell {{ !$item ? 'empty-cell' : '' }}">
                                @if($item && $item->tiet_bat_dau == $tiet)
                                <div class="tkb-item gv-tkb-item">
                                    <div>
                                        <div class="mon">{{ $item->monHoc->ten_mon }}</div>
                                        <div class="gv">
                                            <i class="bi bi-building me-1"></i>
                                            Lớp {{ $item->lopHoc->ten_lop }}
                                        </div>
                                        @if($item->phong_hoc)
                                        <div class="phong">
                                            <i class="bi bi-door-open me-1"></i>
                                            {{ $item->phong_hoc }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@endsection