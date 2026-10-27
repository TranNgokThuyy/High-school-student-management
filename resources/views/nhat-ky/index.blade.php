@extends('layouts.app')
@section('title', 'Nhật ký thao tác')

@section('content')
@use('App\Models\NhatKy')
@php
    $mauHanhDong = ['Thêm' => 'success', 'Sửa' => 'warning', 'Xóa' => 'danger'];
    $hienThi = fn ($v) => is_null($v) ? '∅' : (is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : (is_bool($v) ? ($v ? 'Có' : 'Không') : (string) $v));
@endphp
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-clock-history text-primary me-2"></i>Nhật ký thao tác
        @if($hocSinh)<small class="text-muted fs-6">— {{ $hocSinh->ho_ten }}</small>@endif
    </h4>
</div>

<div class="card table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            @if($hocSinh)<input type="hidden" name="hoc_sinh_id" value="{{ $hocSinh->id }}">@endif
            <div class="col-md-2">
                <label class="form-label small">Người thao tác</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">Tất cả</option>
                    @foreach($nguoiDung as $u)<option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Dữ liệu</label>
                <select name="doi_tuong" class="form-select form-select-sm">
                    <option value="">Tất cả</option>
                    @foreach(NhatKy::TEN_DOI_TUONG as $ma => $ten)<option value="{{ $ma }}" {{ request('doi_tuong') == $ma ? 'selected' : '' }}>{{ $ten }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label small">Hành động</label>
                <select name="hanh_dong" class="form-select form-select-sm">
                    <option value="">Tất cả</option>
                    @foreach(['Thêm', 'Sửa', 'Xóa'] as $hd)<option {{ request('hanh_dong') == $hd ? 'selected' : '' }}>{{ $hd }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2"><label class="form-label small">Từ ngày</label><input type="date" name="tu_ngay" class="form-control form-control-sm" value="{{ request('tu_ngay') }}"></div>
            <div class="col-md-2"><label class="form-label small">Đến ngày</label><input type="date" name="den_ngay" class="form-control form-control-sm" value="{{ request('den_ngay') }}"></div>
            <div class="col-md-2"><label class="form-label small">Tìm theo mô tả</label><input type="text" name="tim" class="form-control form-control-sm" value="{{ request('tim') }}" placeholder="Tên HS, môn..."></div>
            <div class="col-md-1"><button class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel"></i></button></div>
        </form>
    </div>
</div>

<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0 align-middle small">
            <thead><tr><th>Thời gian</th><th>Người thao tác</th><th>Hành động</th><th>Dữ liệu</th><th>Thay đổi</th><th>IP</th></tr></thead>
            <tbody>
                @forelse($nhatKy as $nk)
                <tr>
                    <td class="text-nowrap">{{ $nk->created_at->format('H:i:s d/m/Y') }}</td>
                    <td>{{ $nk->nguoiThaoTac?->name ?? 'Hệ thống' }}</td>
                    <td><span class="badge bg-{{ $mauHanhDong[$nk->hanh_dong] ?? 'secondary' }}">{{ $nk->hanh_dong }}</span></td>
                    <td>
                        <div class="text-muted">{{ NhatKy::TEN_DOI_TUONG[$nk->doi_tuong_loai] ?? $nk->doi_tuong_loai }}</div>
                        {{ $nk->mo_ta }}
                    </td>
                    <td>
                        @foreach($nk->thay_doi ?? [] as $truong => [$cu, $moi])
                            <div>
                                <span class="text-muted">{{ NhatKy::TEN_TRUONG[$truong] ?? $truong }}:</span>
                                @if($nk->hanh_dong === 'Sửa')
                                    <span class="text-decoration-line-through text-danger">{{ $hienThi($cu) }}</span> → <span class="text-success fw-semibold">{{ $hienThi($moi) }}</span>
                                @else
                                    {{ $hienThi($moi ?? $cu) }}
                                @endif
                            </div>
                        @endforeach
                    </td>
                    <td class="text-muted">{{ $nk->ip }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Không có nhật ký phù hợp</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($nhatKy->hasPages())<div class="card-footer bg-white">{{ $nhatKy->links() }}</div>@endif
</div>
@endsection
