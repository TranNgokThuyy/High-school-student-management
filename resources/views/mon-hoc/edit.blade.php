@extends('layouts.app')
@section('title', 'Chỉnh sửa môn học')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Chỉnh sửa: {{ $monHoc->ten_mon }}</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('mon-hoc.index') }}">Quản lý môn học</a>
                </li>
                <li class="breadcrumb-item active">{{ $monHoc->ten_mon }}</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('mon-hoc.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Quay lại
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card table-card">
            <div class="card-header bg-white py-3">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-pencil text-warning me-2"></i>Cập nhật thông tin môn học
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('mon-hoc.update', $monHoc) }}">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Mã môn <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="ma_mon"
                                   class="form-control @error('ma_mon') is-invalid @enderror"
                                   value="{{ old('ma_mon', $monHoc->ma_mon) }}"
                                   style="text-transform:uppercase"
                                   required>
                            @error('ma_mon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-semibold">
                                Tên môn học <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="ten_mon"
                                   class="form-control @error('ten_mon') is-invalid @enderror"
                                   value="{{ old('ten_mon', $monHoc->ten_mon) }}"
                                   required>
                            @error('ten_mon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Áp dụng cho khối <span class="text-danger">*</span>
                            </label>
                            <select name="khoi"
                                    class="form-select @error('khoi') is-invalid @enderror"
                                    required>
                                <option value="Tất cả" {{ old('khoi',$monHoc->khoi)=='Tất cả' ? 'selected':'' }}>
                                    Tất cả khối
                                </option>
                                <option value="10" {{ old('khoi',$monHoc->khoi)=='10' ? 'selected':'' }}>
                                    Chỉ Khối 10
                                </option>
                                <option value="11" {{ old('khoi',$monHoc->khoi)=='11' ? 'selected':'' }}>
                                    Chỉ Khối 11
                                </option>
                                <option value="12" {{ old('khoi',$monHoc->khoi)=='12' ? 'selected':'' }}>
                                    Chỉ Khối 12
                                </option>
                            </select>
                            @error('khoi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Số tiết/tuần <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" name="so_tiet_tuan"
                                       class="form-control @error('so_tiet_tuan') is-invalid @enderror"
                                       value="{{ old('so_tiet_tuan', $monHoc->so_tiet_tuan) }}"
                                       min="1" max="10" required>
                                <span class="input-group-text">tiết</span>
                            </div>
                            @error('so_tiet_tuan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                       name="is_active" id="is_active" value="1"
                                       {{ old('is_active', $monHoc->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    Môn học đang được giảng dạy
                                </label>
                            </div>
                        </div>

                        <div class="col-12 mt-2">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-check-circle me-2"></i>Lưu thay đổi
                                </button>
                                <a href="{{ route('mon-hoc.index') }}"
                                   class="btn btn-outline-secondary">Hủy bỏ</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection