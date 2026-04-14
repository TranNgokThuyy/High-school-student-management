@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Dashboard</h4>
        <p class="text-muted mb-0">Xin chào, {{ auth()->user()->name }}! Năm học {{ $namHocHienTai }}</p>
    </div>
</div>

{{-- Thống kê tổng quan --}}
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-10">
                    <i class="bi bi-people-fill text-primary"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-primary">{{ number_format($stats['tong_hoc_sinh']) }}</div>
                    <div class="text-muted small">Học sinh đang học</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-10">
                    <i class="bi bi-building text-success"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-success">{{ $stats['tong_lop_hoc'] }}</div>
                    <div class="text-muted small">Lớp học đang hoạt động</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning bg-opacity-10">
                    <i class="bi bi-person-badge-fill text-warning"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-warning">{{ $stats['tong_giao_vien'] }}</div>
                    <div class="text-muted small">Giáo viên</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-info bg-opacity-10">
                    <i class="bi bi-person-check-fill text-info"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-info">{{ $stats['tong_tai_khoan'] }}</div>
                    <div class="text-muted small">Tài khoản hệ thống</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Phân bố học sinh theo lớp --}}
    <div class="col-lg-7">
        <div class="card table-card">
            <div class="card-header bg-white py-3">
                <h6 class="fw-semibold mb-0"><i class="bi bi-bar-chart me-2 text-primary"></i>Sĩ số theo lớp</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Lớp</th>
                                <th>Khối</th>
                                <th>Sĩ số</th>
                                <th>Tỷ lệ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($phanBoTheoLop as $lop)
                            <tr>
                                <td class="fw-semibold">{{ $lop->ten_lop }}</td>
                                <td><span class="badge bg-light text-dark">Khối {{ $lop->khoi }}</span></td>
                                <td>{{ $lop->hoc_sinh_count }} học sinh</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-primary" 
                                                 style="width: {{ $stats['tong_hoc_sinh'] > 0 ? ($lop->hoc_sinh_count / $stats['tong_hoc_sinh'] * 100) : 0 }}%">
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            {{ $stats['tong_hoc_sinh'] > 0 ? number_format($lop->hoc_sinh_count / $stats['tong_hoc_sinh'] * 100, 1) : 0 }}%
                                        </small>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Thời khóa biểu hôm nay --}}
<div class="col-lg-5">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-semibold mb-0">
                <i class="bi bi-calendar-week text-primary me-2"></i>
                Thời khóa biểu hôm nay
                <span class="badge bg-light text-muted fw-normal ms-1" style="font-size:0.7rem">
                    {{ ['Chủ nhật','Thứ 2','Thứ 3','Thứ 4','Thứ 5','Thứ 6','Thứ 7'][date('w')] }}
                </span>
            </h6>
            <a href="{{ route('thoi-khoa-bieu.index') }}"
               class="btn btn-sm btn-outline-primary">
                Xem đầy đủ
            </a>
        </div>
        <div class="card-body p-0">
            @php
                $thuHom  = (int)date('w'); // 0=CN,1=T2,...6=T7
                $thuMap  = [0=>8, 1=>2, 2=>3, 3=>4, 4=>5, 5=>6, 6=>7];
                $thuDB   = $thuMap[$thuHom] ?? 2;
                $namHoc  = $namHocHienTai ?? (date('Y').'-'.(date('Y')+1));

                $tkbHomNay = \App\Models\ThoiKhoaBieu::where('thu', $thuDB)
                    ->where('nam_hoc', $namHoc)
                    ->with(['monHoc', 'lopHoc', 'giaoVien'])
                    ->orderBy('tiet_bat_dau')
                    ->get();

                $tietGio = [
                    1=>'7:00', 2=>'7:45', 3=>'8:30', 4=>'9:15', 5=>'10:15',
                    6=>'11:00', 7=>'13:00', 8=>'13:45', 9=>'14:30', 10=>'15:15',
                ];
            @endphp

            @if($tkbHomNay->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($tkbHomNay as $tkb)
                    <div class="list-group-item border-0 px-3 py-2">
                        <div class="d-flex align-items-center gap-3">
                            {{-- Tiết --}}
                            <div class="text-center flex-shrink-0"
                                 style="width:44px">
                                <div class="fw-bold text-primary" style="font-size:0.9rem">
                                    T{{ $tkb->tiet_bat_dau }}
                                </div>
                                <div class="text-muted" style="font-size:0.65rem">
                                    {{ $tietGio[$tkb->tiet_bat_dau] ?? '' }}
                                </div>
                            </div>

                            {{-- Đường kẻ dọc màu --}}
                            @php
                                $monColors = [
                                    'TOAN'=>'#f59e0b','VAN'=>'#ec4899','ANH'=>'#10b981',
                                    'LY'=>'#8b5cf6','HOA'=>'#f97316','SINH'=>'#22c55e',
                                    'SU'=>'#ef4444','DIA'=>'#06b6d4','TIN'=>'#3b82f6',
                                ];
                                $mauMon = $monColors[$tkb->monHoc->ma_mon ?? ''] ?? '#6b7280';
                            @endphp
                            <div class="flex-shrink-0 rounded"
                                 style="width:4px;height:40px;background:{{ $mauMon }}">
                            </div>

                            {{-- Thông tin --}}
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-semibold text-truncate"
                                     style="font-size:0.85rem">
                                    {{ $tkb->monHoc->ten_mon }}
                                </div>
                                <div class="text-muted" style="font-size:0.72rem">
                                    <i class="bi bi-building me-1"></i>Lớp {{ $tkb->lopHoc->ten_lop }}
                                    @if($tkb->phong_hoc)
                                        &nbsp;·&nbsp;
                                        <i class="bi bi-door-open me-1"></i>{{ $tkb->phong_hoc }}
                                    @endif
                                </div>
                                <div class="text-muted" style="font-size:0.72rem">
                                    <i class="bi bi-person me-1"></i>{{ $tkb->giaoVien->name }}
                                </div>
                            </div>

                            {{-- Số tiết --}}
                            @if($tkb->so_tiet > 1)
                            <div class="flex-shrink-0">
                                <span class="badge bg-light text-muted border"
                                      style="font-size:0.65rem">
                                    {{ $tkb->so_tiet }} tiết
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center text-muted py-5">
                    <i class="bi bi-calendar-x d-block fs-2 mb-2 opacity-50"></i>
                    <div class="fw-semibold small">Không có lịch học hôm nay</div>
                    <div style="font-size:0.75rem">
                        {{ ['Chủ nhật','Thứ 2','Thứ 3','Thứ 4','Thứ 5','Thứ 6','Thứ 7'][date('w')] }}
                        — {{ date('d/m/Y') }}
                    </div>
                    <a href="{{ route('thoi-khoa-bieu.create') }}"
                       class="btn btn-sm btn-outline-primary mt-3">
                        <i class="bi bi-plus me-1"></i>Thêm lịch học
                    </a>
                </div>
            @endif
        </div>

        @if($tkbHomNay->count() > 0)
        <div class="card-footer bg-white border-0 text-center py-2">
            <small class="text-muted">
                <i class="bi bi-clock me-1"></i>
                {{ $tkbHomNay->count() }} tiết học hôm nay
            </small>
        </div>
        @endif
    </div>
</div>

{{-- Truy cập nhanh --}}
<div class="row g-3 mt-2">
    <div class="col-12">
        <h6 class="fw-semibold text-muted mb-3">TRUY CẬP NHANH</h6>
    </div>
    @can('them-hoc-sinh')
    <div class="col-auto">
        <a href="{{ route('hoc-sinh.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-2"></i>Thêm học sinh mới
        </a>
    </div>
    @endcan
    @can('xem-diem')
    <div class="col-auto">
        <a href="{{ route('diem-so.bang-diem') }}" class="btn btn-outline-primary">
            <i class="bi bi-journal-text me-2"></i>Nhập điểm
        </a>
    </div>
    @endcan
    @can('xem-bao-cao')
    <div class="col-auto">
        <a href="{{ route('bao-cao.index') }}" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-bar-graph me-2"></i>Xuất báo cáo
        </a>
    </div>
    @endcan

</div>
@endsection