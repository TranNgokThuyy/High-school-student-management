@extends('layouts.app')
@section('title', 'Thêm phân công giảng dạy')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-diagram-3 text-primary me-2"></i>Thêm phân công giảng dạy</h4>
    <a href="{{ route('phan-cong.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Quay lại</a>
</div>

@if($errors->any())
<div class="alert alert-danger">
    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
</div>
@endif

<form method="POST" action="{{ route('phan-cong.store') }}">
    @csrf
    <div class="card table-card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Giáo viên <span class="text-danger">*</span></label>
                    <select name="giao_vien_id" class="form-select" required>
                        <option value="">-- Chọn giáo viên --</option>
                        @foreach($giaoVien as $gv)
                            <option value="{{ $gv->id }}" {{ old('giao_vien_id') == $gv->id ? 'selected' : '' }}>{{ $gv->name }} ({{ $gv->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Môn học <span class="text-danger">*</span></label>
                    <select name="mon_hoc_id" class="form-select" required>
                        <option value="">-- Chọn môn --</option>
                        @foreach($monHoc as $mon)
                            <option value="{{ $mon->id }}" {{ old('mon_hoc_id') == $mon->id ? 'selected' : '' }}>{{ $mon->ten_mon }} ({{ $mon->so_tiet_tuan }} tiết/tuần)</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Học kỳ <span class="text-danger">*</span></label>
                    <select name="hoc_ky" class="form-select" required>
                        @foreach(['Ca năm' => 'Cả năm', '1' => 'Học kỳ 1', '2' => 'Học kỳ 2'] as $v => $l)
                            <option value="{{ $v }}" {{ old('hoc_ky', 'Ca năm') == $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Lớp được phân công <span class="text-danger">*</span></label>
                    <div class="small text-muted mb-2">Có thể chọn nhiều lớp. Năm học lấy theo năm học của lớp.</div>
                    @foreach($lopHoc->groupBy('nam_hoc') as $nh => $dsLop)
                        <div class="mb-2">
                            <div class="fw-semibold small text-primary mb-1">Năm học {{ $nh }}</div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($dsLop as $lop)
                                <input type="checkbox" class="btn-check" name="lop_hoc_ids[]" value="{{ $lop->id }}"
                                       id="lop{{ $lop->id }}" {{ in_array($lop->id, old('lop_hoc_ids', [])) ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary btn-sm" for="lop{{ $lop->id }}">{{ $lop->ten_lop }}</label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="card-footer bg-white text-end">
            <button class="btn btn-primary"><i class="bi bi-save me-2"></i>Lưu phân công</button>
        </div>
    </div>
</form>
@endsection
