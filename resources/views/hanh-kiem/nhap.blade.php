@extends('layouts.app')
@section('title', 'Nhập hạnh kiểm')

@push('styles')
<style>
.hanh-kiem-select option[value="Tốt"]        { color: #198754; font-weight:600; }
.hanh-kiem-select option[value="Khá"]        { color: #0d6efd; font-weight:600; }
.hanh-kiem-select option[value="Trung bình"] { color: #fd7e14; font-weight:600; }
.hanh-kiem-select option[value="Yếu"]        { color: #dc3545; font-weight:600; }
.vang-highlight { border-color: #ffc107 !important; background-color: #fff8e1 !important; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Nhập hạnh kiểm & Chuyên cần</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('hanh-kiem.index') }}">Hạnh kiểm</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('hanh-kiem.nhap') }}">Chọn lớp</a>
                </li>
                <li class="breadcrumb-item active">{{ $lop->ten_lop }}</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('hanh-kiem.nhap') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Chọn lớp khác
    </a>
</div>

{{-- Thông tin đang nhập --}}
<div class="alert alert-info d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-info-circle fs-5 flex-shrink-0"></i>
    <div>
        Lớp <strong>{{ $lop->ten_lop }}</strong> —
        Học kỳ <strong>{{ $request->hoc_ky }}</strong> —
        Năm học <strong>{{ $request->nam_hoc }}</strong>
        ({{ $lop->hocSinh->count() }} học sinh)
        @if($lop->giaoVienChuNhiem)
            — GVCN: <strong>{{ $lop->giaoVienChuNhiem->name }}</strong>
        @endif
    </div>
</div>

<form method="POST" action="{{ route('hanh-kiem.luu') }}">
    @csrf
    <input type="hidden" name="lop_hoc_id" value="{{ $lop->id }}">
    <input type="hidden" name="hoc_ky"     value="{{ $request->hoc_ky }}">
    <input type="hidden" name="nam_hoc"    value="{{ $request->nam_hoc }}">

    <div class="card table-card">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="fw-semibold mb-0">
                    Danh sách — {{ $lop->ten_lop }} ({{ $lop->hocSinh->count() }} HS)
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <small class="text-muted">Đặt tất cả hạnh kiểm:</small>
                    <select id="setAllHK" class="form-select form-select-sm" style="width:130px">
                        <option value="">-- Chọn --</option>
                        <option value="Tốt">Tốt</option>
                        <option value="Khá">Khá</option>
                        <option value="Trung bình">Trung bình</option>
                        <option value="Yếu">Yếu</option>
                    </select>
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                            onclick="setAllHanhKiem()">
                        Áp dụng
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
    <tr>
        <th style="width:40px" class="text-center">STT</th>
        <th style="min-width:160px">Họ và tên</th>
        <th style="width:140px" class="text-center">Hạnh kiểm</th>
        <th style="width:85px" class="text-center">Tổng buổi</th>
        <th style="width:85px" class="text-center text-warning">Vắng CP</th>
        <th style="width:85px" class="text-center text-danger">Vắng KP</th>
        <th style="width:80px" class="text-center">Đi trễ</th>

        {{-- Cột tham khảo kỳ kia --}}
        <th style="width:100px" class="text-center table-secondary">
            HK{{ $hocKyKia }}
            <div class="text-muted fw-normal" style="font-size:0.65rem">Tham khảo</div>
        </th>

        {{-- Cột cả năm (tự động) --}}
        <th style="width:100px" class="text-center table-success">
            Cả năm
            <div class="text-muted fw-normal" style="font-size:0.65rem">Tự động</div>
        </th>

        <th style="min-width:130px">Ghi chú</th>
    </tr>
</thead>
                <tbody>
                    @foreach($lop->hocSinh as $index => $hs)
                        @php $hk = $hanhKiemHienCo[$hs->id] ?? null; @endphp
                        <tr>
                            <td class="text-center text-muted">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-semibold">{{ $hs->ho_ten }}</div>
                                <small class="text-muted">{{ $hs->ma_hoc_sinh }}</small>
                            </td>
                            <td>
                                <select name="hanh_kiem[{{ $hs->id }}][xep_loai]"
                                        class="form-select form-select-sm hanh-kiem-select">
                                    <option value="">-- Chọn --</option>
                                    @foreach(['Tốt','Khá','Trung bình','Yếu'] as $loai)
                                        <option value="{{ $loai }}"
                                            {{ ($hk?->xep_loai_hanh_kiem === $loai) ? 'selected' : '' }}>
                                            {{ $loai }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number"
                                       name="hanh_kiem[{{ $hs->id }}][so_buoi_hoc]"
                                       class="form-control form-control-sm text-center"
                                       value="{{ $hk?->so_buoi_hoc ?? 0 }}"
                                       min="0" max="300">
                            </td>
                            <td>
                                <input type="number"
                                       name="hanh_kiem[{{ $hs->id }}][vang_co_phep]"
                                       class="form-control form-control-sm text-center vang-input"
                                       value="{{ $hk?->so_buoi_vang_co_phep ?? 0 }}"
                                       min="0" max="300">
                            </td>
                            <td>
                                <input type="number"
                                       name="hanh_kiem[{{ $hs->id }}][vang_khong_phep]"
                                       class="form-control form-control-sm text-center vang-input"
                                       value="{{ $hk?->so_buoi_vang_khong_phep ?? 0 }}"
                                       min="0" max="300">
                            </td>
                            <td>
                                <input type="number"
                                       name="hanh_kiem[{{ $hs->id }}][di_tre]"
                                       class="form-control form-control-sm text-center"
                                       value="{{ $hk?->so_buoi_di_tre ?? 0 }}"
                                       min="0" max="300">
                            </td>
                            <td>
                                <input type="text"
                                       name="hanh_kiem[{{ $hs->id }}][ghi_chu]"
                                       class="form-control form-control-sm"
                                       value="{{ $hk?->ghi_chu }}"
                                       placeholder="Ghi chú...">
                            </td>
                            {{-- Tham khảo kỳ kia --}}
<td class="text-center table-secondary bg-opacity-25">
    @php $kyKia = $hanhKiemKyKia[$hs->id] ?? null; @endphp
    @if($kyKia?->xep_loai_hanh_kiem)
        @php
            $hkColors = ['Tốt'=>'success','Khá'=>'primary','Trung bình'=>'warning','Yếu'=>'danger'];
            $c = $hkColors[$kyKia->xep_loai_hanh_kiem] ?? 'secondary';
        @endphp
        <span class="badge bg-{{ $c }}">{{ $kyKia->xep_loai_hanh_kiem }}</span>
    @else
        <span class="text-muted small">—</span>
    @endif
</td>

{{-- Cả năm (tự động) --}}
<td class="text-center table-success bg-opacity-25"
    id="ca-nam-{{ $hs->id }}">
    @php $caNam = $hanhKiemCaNam[$hs->id] ?? null; @endphp
    @if($caNam?->xep_loai_hanh_kiem)
        @php
            $hkColors = ['Tốt'=>'success','Khá'=>'primary','Trung bình'=>'warning','Yếu'=>'danger'];
            $c = $hkColors[$caNam->xep_loai_hanh_kiem] ?? 'secondary';
        @endphp
        <span class="badge bg-{{ $c }}">{{ $caNam->xep_loai_hanh_kiem }}</span>
        <div style="font-size:0.6rem" class="text-muted">Đã tính</div>
    @else
        <span class="text-muted small">
            @if($hanhKiemKyKia[$hs->id] ?? null)
                Lưu để tính
            @else
                Chưa đủ 2 HK
            @endif
        </span>
    @endif
</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <small class="text-muted">
                <i class="bi bi-info-circle me-1"></i>
                CP = Có phép &nbsp;|&nbsp; KP = Không phép
            </small>
            <div class="d-flex gap-2">
                <a href="{{ route('hanh-kiem.nhap') }}" class="btn btn-outline-secondary">
                    Hủy bỏ
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-floppy me-2"></i>Lưu hạnh kiểm
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function setAllHanhKiem() {
    const val = document.getElementById('setAllHK').value;
    if (!val) return;
    document.querySelectorAll('.hanh-kiem-select').forEach(sel => sel.value = val);
}

// Tô màu ô vắng khi > 0
function applyVangColor(input) {
    if (parseInt(input.value) > 0) {
        input.classList.add('vang-highlight');
    } else {
        input.classList.remove('vang-highlight');
    }
}

document.querySelectorAll('.vang-input').forEach(input => {
    applyVangColor(input); // Áp dụng ngay khi load
    input.addEventListener('input', () => applyVangColor(input));
});
</script>
@endpush