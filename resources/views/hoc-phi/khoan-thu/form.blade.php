@extends('layouts.app')
@section('title', $khoanThu->exists ? 'Sửa khoản thu' : 'Thêm khoản thu')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-list-check text-primary me-2"></i>{{ $khoanThu->exists ? 'Sửa khoản thu' : 'Thêm khoản thu' }}</h4>
    <a href="{{ route('khoan-thu.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Quay lại</a>
</div>

@if($errors->any())
<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

<form method="POST" action="{{ $khoanThu->exists ? route('khoan-thu.update', $khoanThu) : route('khoan-thu.store') }}">
    @csrf
    @if($khoanThu->exists) @method('PUT') @endif
    <div class="card table-card">
        <div class="card-body row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Tên khoản thu <span class="text-danger">*</span></label>
                <input type="text" name="ten" class="form-control" required maxlength="150" value="{{ old('ten', $khoanThu->ten) }}" placeholder="VD: Học phí học kỳ 1, Bảo hiểm y tế...">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Loại</label>
                <select name="loai" class="form-select">
                    @foreach(\App\Models\KhoanThu::LOAI as $l)<option {{ old('loai', $khoanThu->loai) == $l ? 'selected' : '' }}>{{ $l }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Mức thu (đồng) <span class="text-danger">*</span></label>
                <input type="number" name="so_tien" class="form-control" required min="0" step="1000" value="{{ old('so_tien', $khoanThu->so_tien) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Năm học</label>
                <input type="text" name="nam_hoc" class="form-control" required pattern="\d{4}-\d{4}" value="{{ old('nam_hoc', $khoanThu->nam_hoc) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Học kỳ</label>
                <select name="hoc_ky" class="form-select">
                    @foreach(['Ca năm' => 'Cả năm', '1' => 'Học kỳ 1', '2' => 'Học kỳ 2'] as $v => $l)<option value="{{ $v }}" {{ old('hoc_ky', $khoanThu->hoc_ky) == $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Áp dụng khối</label>
                <select name="khoi" class="form-select">
                    @foreach(['Tất cả', '10', '11', '12'] as $k)<option value="{{ $k }}" {{ old('khoi', $khoanThu->khoi) == $k ? 'selected' : '' }}>{{ $k === 'Tất cả' ? 'Tất cả' : 'Khối ' . $k }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Hạn nộp</label>
                <input type="date" name="han_nop" class="form-control" value="{{ old('han_nop', $khoanThu->han_nop?->toDateString()) }}">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Mô tả</label>
                <input type="text" name="mo_ta" class="form-control" maxlength="500" value="{{ old('mo_ta', $khoanThu->mo_ta) }}">
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input type="hidden" name="ap_dung_mien_giam" value="0">
                    <input class="form-check-input" type="checkbox" name="ap_dung_mien_giam" value="1" id="mg" {{ old('ap_dung_mien_giam', $khoanThu->ap_dung_mien_giam) ? 'checked' : '' }}>
                    <label class="form-check-label" for="mg">Áp dụng miễn giảm cho học sinh thuộc diện chính sách</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active" {{ old('is_active', $khoanThu->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="active">Đang áp dụng</label>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white text-end">
            <button class="btn btn-primary"><i class="bi bi-save me-2"></i>Lưu</button>
        </div>
    </div>
</form>
@endsection
