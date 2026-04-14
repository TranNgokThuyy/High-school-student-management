@extends('layouts.app')
@section('title', 'Chỉnh sửa lớp học')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Chỉnh sửa lớp: {{ $lopHoc->ten_lop }}</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('lop-hoc.index') }}">Quản lý lớp học</a></li>
                <li class="breadcrumb-item"><a href="{{ route('lop-hoc.show', $lopHoc) }}">{{ $lopHoc->ten_lop }}</a></li>
                <li class="breadcrumb-item active">Chỉnh sửa</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('lop-hoc.show', $lopHoc) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Quay lại
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card table-card">
            <div class="card-header bg-white py-3">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-pencil text-warning me-2"></i>Cập nhật thông tin lớp
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('lop-hoc.update', $lopHoc) }}">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Tên lớp <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="ten_lop"
                                   class="form-control @error('ten_lop') is-invalid @enderror"
                                   value="{{ old('ten_lop', $lopHoc->ten_lop) }}" required>
                            @error('ten_lop')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                Khối <span class="text-danger">*</span>
                            </label>
                            <select name="khoi" class="form-select @error('khoi') is-invalid @enderror" required>
                                <option value="10" {{ old('khoi', $lopHoc->khoi) == '10' ? 'selected' : '' }}>Khối 10</option>
                                <option value="11" {{ old('khoi', $lopHoc->khoi) == '11' ? 'selected' : '' }}>Khối 11</option>
                                <option value="12" {{ old('khoi', $lopHoc->khoi) == '12' ? 'selected' : '' }}>Khối 12</option>
                            </select>
                            @error('khoi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                Năm học <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nam_hoc"
                                   class="form-control @error('nam_hoc') is-invalid @enderror"
                                   value="{{ old('nam_hoc', $lopHoc->nam_hoc) }}" required>
                            @error('nam_hoc')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Giáo viên chủ nhiệm</label>
                            <select name="giao_vien_chu_nhiem_id" class="form-select">
                                <option value="">-- Chưa phân công --</option>
                                @foreach($giaoVien as $gv)
                                    <option value="{{ $gv->id }}"
                                        {{ old('giao_vien_chu_nhiem_id', $lopHoc->giao_vien_chu_nhiem_id) == $gv->id ? 'selected' : '' }}>
                                        {{ $gv->name }} ({{ $gv->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Ghi chú</label>
                            <textarea name="ghi_chu" class="form-control" rows="3">{{ old('ghi_chu', $lopHoc->ghi_chu) }}</textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                       id="is_active" value="1"
                                       {{ old('is_active', $lopHoc->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    Lớp đang hoạt động
                                </label>
                            </div>
                        </div>

                        <div class="col-12 mt-2">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-check-circle me-2"></i>Lưu thay đổi
                                </button>
                                <a href="{{ route('lop-hoc.show', $lopHoc) }}" class="btn btn-outline-secondary">
                                    Hủy bỏ
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection