@extends('layouts.app')
@section('title', 'Thêm khen thưởng - kỷ luật')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-trophy text-warning me-2"></i>Thêm khen thưởng - kỷ luật</h4>
    <a href="{{ route('khen-thuong.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Quay lại</a>
</div>

@if($errors->any())
<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

<form method="POST" action="{{ route('khen-thuong.store') }}">
    @csrf
    <div class="card table-card">
        <div class="card-body row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Học sinh <span class="text-danger">*</span></label>
                <select name="hoc_sinh_id" class="form-select" required>
                    <option value="">-- Chọn học sinh --</option>
                    @foreach($lopHoc as $lop)
                        <optgroup label="Lớp {{ $lop->ten_lop }} ({{ $lop->nam_hoc }})">
                            @foreach($lop->hocSinh as $hs)
                                <option value="{{ $hs->id }}" {{ old('hoc_sinh_id', $hocSinhChon) == $hs->id ? 'selected' : '' }}>{{ $hs->ho_ten }} — {{ $hs->ma_hoc_sinh }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Loại <span class="text-danger">*</span></label>
                <select name="loai" id="loai" class="form-select" required onchange="doiHinhThuc()">
                    @foreach(array_keys($hinhThuc) as $l)
                        <option value="{{ $l }}" {{ old('loai') == $l ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Hình thức <span class="text-danger">*</span></label>
                <select name="hinh_thuc" id="hinhThuc" class="form-select" required></select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Nội dung <span class="text-danger">*</span></label>
                <textarea name="noi_dung" rows="3" class="form-control" required placeholder="VD: Đạt giải Nhì HSG cấp tỉnh môn Toán / Vi phạm nội quy...">{{ old('noi_dung') }}</textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Ngày quyết định <span class="text-danger">*</span></label>
                <input type="date" name="ngay_quyet_dinh" class="form-control" required
                       value="{{ old('ngay_quyet_dinh', today()->toDateString()) }}" max="{{ today()->toDateString() }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Số quyết định</label>
                <input type="text" name="so_quyet_dinh" class="form-control" value="{{ old('so_quyet_dinh') }}" maxlength="50">
            </div>
        </div>
        <div class="card-footer bg-white text-end">
            <button class="btn btn-primary"><i class="bi bi-save me-2"></i>Lưu</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
const HINH_THUC = @json($hinhThuc);
const HINH_THUC_CU = @json(old('hinh_thuc'));
function doiHinhThuc() {
    const sel = document.getElementById('hinhThuc');
    sel.innerHTML = '';
    HINH_THUC[document.getElementById('loai').value].forEach(ht => {
        const o = new Option(ht, ht, false, ht === HINH_THUC_CU);
        sel.add(o);
    });
}
doiHinhThuc();
</script>
@endpush
