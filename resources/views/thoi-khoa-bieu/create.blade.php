@extends('layouts.app')
@section('title', 'Thêm lịch học')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Thêm lịch học</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('thoi-khoa-bieu.index') }}">Thời khóa biểu</a>
                </li>
                <li class="breadcrumb-item active">Thêm lịch</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('thoi-khoa-bieu.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Quay lại
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-calendar-plus text-primary me-2"></i>
                    Thông tin lịch học
                </h6>
            </div>
            <div class="card-body">
                @if(session('error'))
                <div class="alert alert-danger d-flex gap-2 mb-4">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                    {{ session('error') }}
                </div>
                @endif

                <form method="POST" action="{{ route('thoi-khoa-bieu.store') }}">
                    @csrf
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Lớp học <span class="text-danger">*</span>
                            </label>
                            <select name="lop_hoc_id"
                                    class="form-select @error('lop_hoc_id') is-invalid @enderror"
                                    required>
                                <option value="">-- Chọn lớp --</option>
                                @foreach($lopHoc as $lop)
                                    <option value="{{ $lop->id }}"
                                        {{ (old('lop_hoc_id', $selectedLopId) == $lop->id) ? 'selected' : '' }}>
                                        {{ $lop->ten_lop }} ({{ $lop->nam_hoc }})
                                    </option>
                                @endforeach
                            </select>
                            @error('lop_hoc_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Môn học <span class="text-danger">*</span>
                            </label>
                            <select name="mon_hoc_id"
                                    class="form-select @error('mon_hoc_id') is-invalid @enderror"
                                    required>
                                <option value="">-- Chọn môn --</option>
                                @foreach($monHoc as $mon)
                                    <option value="{{ $mon->id }}"
                                        {{ old('mon_hoc_id') == $mon->id ? 'selected' : '' }}>
                                        {{ $mon->ten_mon }}
                                    </option>
                                @endforeach
                            </select>
                            @error('mon_hoc_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Giáo viên phụ trách <span class="text-danger">*</span>
                            </label>
                            <select name="giao_vien_id"
                                    class="form-select @error('giao_vien_id') is-invalid @enderror"
                                    required>
                                <option value="">-- Chọn giáo viên --</option>
                                @foreach($giaoVien as $gv)
                                    <option value="{{ $gv->id }}"
                                        {{ old('giao_vien_id') == $gv->id ? 'selected' : '' }}>
                                        {{ $gv->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('giao_vien_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Thứ <span class="text-danger">*</span>
                            </label>
                            <select name="thu"
                                    class="form-select @error('thu') is-invalid @enderror"
                                    required>
                                <option value="">-- Chọn thứ --</option>
                                @foreach($thuLabels as $val => $label)
                                    <option value="{{ $val }}"
                                        {{ old('thu') == $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('thu')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Tiết bắt đầu <span class="text-danger">*</span>
                            </label>
                            <select name="tiet_bat_dau"
                                    class="form-select @error('tiet_bat_dau') is-invalid @enderror"
                                    required>
                                <option value="">-- Chọn tiết --</option>
                                @foreach($tietLabels as $val => $label)
                                    <option value="{{ $val }}"
                                        {{ old('tiet_bat_dau') == $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tiet_bat_dau')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Số tiết</label>
                            <select name="so_tiet" class="form-select">
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}"
                                        {{ old('so_tiet', 1) == $i ? 'selected' : '' }}>
                                        {{ $i }} tiết
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Phòng học</label>
                            <input type="text" name="phong_hoc"
                                   class="form-control"
                                   value="{{ old('phong_hoc') }}"
                                   placeholder="VD: P.101, P.Lý...">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Học kỳ <span class="text-danger">*</span>
                            </label>
                            <select name="hoc_ky" class="form-select" required>
                                <option value="1" {{ old('hoc_ky','1') == '1' ? 'selected':'' }}>
                                    Học kỳ 1
                                </option>
                                <option value="2" {{ old('hoc_ky') == '2' ? 'selected':'' }}>
                                    Học kỳ 2
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Năm học <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nam_hoc"
                                   class="form-control @error('nam_hoc') is-invalid @enderror"
                                   value="{{ old('nam_hoc', $namHocHienTai) }}"
                                   placeholder="2024-2025" required>
                        </div>

                        <div class="col-12 mt-2">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>Lưu lịch học
                                </button>
                                <a href="{{ route('thoi-khoa-bieu.index') }}"
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