@extends('layouts.app')
@section('title', 'Hồ sơ giáo viên')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-person-badge text-primary me-2"></i>Hồ sơ giáo viên — {{ $giaoVien->name }}</h4>
    <a href="{{ route('giao-vien.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Quay lại</a>
</div>

@if($errors->any())
<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

<form method="POST" action="{{ route('giao-vien.update', $giaoVien) }}" class="card table-card">
    @csrf @method('PUT')
    <div class="card-body row g-3">
        <div class="col-md-3"><label class="form-label">Ngày sinh</label><input type="date" name="ngay_sinh" class="form-control" value="{{ old('ngay_sinh', $hoSo->ngay_sinh?->toDateString()) }}"></div>
        <div class="col-md-3"><label class="form-label">Giới tính</label>
            <select name="gioi_tinh" class="form-select"><option value="">—</option>@foreach(['Nam', 'Nữ'] as $g)<option {{ old('gioi_tinh', $hoSo->gioi_tinh) == $g ? 'selected' : '' }}>{{ $g }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">Trình độ</label>
            <select name="trinh_do" class="form-select"><option value="">—</option>@foreach(\App\Models\HoSoGiaoVien::TRINH_DO as $t)<option {{ old('trinh_do', $hoSo->trinh_do) == $t ? 'selected' : '' }}>{{ $t }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">Ngày vào ngành</label><input type="date" name="ngay_vao_nganh" class="form-control" value="{{ old('ngay_vao_nganh', $hoSo->ngay_vao_nganh?->toDateString()) }}"></div>
        <div class="col-md-4"><label class="form-label">Chuyên môn</label><input name="chuyen_mon" class="form-control" maxlength="100" value="{{ old('chuyen_mon', $hoSo->chuyen_mon) }}" placeholder="VD: Toán"></div>
        <div class="col-md-4"><label class="form-label">Tổ chuyên môn</label><input name="to_chuyen_mon" class="form-control" maxlength="100" value="{{ old('to_chuyen_mon', $hoSo->to_chuyen_mon) }}" placeholder="VD: Tổ Toán - Tin"></div>
        <div class="col-md-4"><label class="form-label">Chức vụ</label>
            <select name="chuc_vu" class="form-select">@foreach(\App\Models\HoSoGiaoVien::CHUC_VU as $c)<option {{ old('chuc_vu', $hoSo->chuc_vu) == $c ? 'selected' : '' }}>{{ $c }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">Định mức (tiết/tuần)</label><input type="number" name="dinh_muc_tiet" class="form-control" min="0" max="30" value="{{ old('dinh_muc_tiet', $hoSo->dinh_muc_tiet) }}"></div>
        <div class="col-md-3"><label class="form-label">Giảm trừ kiêm nhiệm (tiết/tuần)</label><input type="number" name="giam_tru_tiet" class="form-control" min="0" max="30" value="{{ old('giam_tru_tiet', $hoSo->giam_tru_tiet) }}"></div>
        <div class="col-md-6"><label class="form-label">Lý do giảm trừ</label><input name="ly_do_giam_tru" class="form-control" maxlength="255" value="{{ old('ly_do_giam_tru', $hoSo->ly_do_giam_tru) }}" placeholder="VD: Tổ trưởng chuyên môn"></div>
    </div>
    <div class="card-footer bg-white text-end"><button class="btn btn-primary"><i class="bi bi-save me-2"></i>Lưu</button></div>
</form>
@endsection
