@extends('layouts.app')
@section('title', 'Thêm lớp học')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Thêm lớp học mới</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('lop-hoc.index') }}">Quản lý lớp học</a></li>
                <li class="breadcrumb-item active">Thêm mới</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('lop-hoc.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Quay lại
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card table-card">
            <div class="card-header bg-white py-3">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-building text-primary me-2"></i>Thông tin lớp học
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('lop-hoc.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Tên lớp <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="ten_lop"
                                   class="form-control @error('ten_lop') is-invalid @enderror"
                                   value="{{ old('ten_lop') }}"
                                   placeholder="VD: 10A1, 11B2, 12C3..." required>
                            @error('ten_lop')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                Khối <span class="text-danger">*</span>
                            </label>
                            <select name="khoi" class="form-select @error('khoi') is-invalid @enderror" required>
                                <option value="">-- Chọn --</option>
                                <option value="10" {{ old('khoi') == '10' ? 'selected' : '' }}>Khối 10</option>
                                <option value="11" {{ old('khoi') == '11' ? 'selected' : '' }}>Khối 11</option>
                                <option value="12" {{ old('khoi') == '12' ? 'selected' : '' }}>Khối 12</option>
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
                                   value="{{ old('nam_hoc', date('Y') . '-' . (date('Y')+1)) }}"
                                   placeholder="2024-2025" required>
                            @error('nam_hoc')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Định dạng: YYYY-YYYY</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Giáo viên chủ nhiệm</label>
                            <select name="giao_vien_chu_nhiem_id" class="form-select">
                                <option value="">-- Chưa phân công --</option>
                                @foreach($giaoVien as $gv)
                                    <option value="{{ $gv->id }}" {{ old('giao_vien_chu_nhiem_id') == $gv->id ? 'selected' : '' }}>
                                        {{ $gv->name }} ({{ $gv->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Ghi chú</label>
                            <textarea name="ghi_chu" class="form-control" rows="3"
                                      placeholder="Ghi chú thêm về lớp học...">{{ old('ghi_chu') }}</textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                       id="is_active" value="1" checked>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    Lớp đang hoạt động
                                </label>
                            </div>
                        </div>

                        <div class="col-12 mt-2">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>Tạo lớp học
                                </button>
                                <a href="{{ route('lop-hoc.index') }}" class="btn btn-outline-secondary">
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