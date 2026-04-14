@extends('layouts.app')
@section('title', 'Chỉnh sửa học sinh')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Chỉnh sửa: {{ $hocSinh->ho_ten }}</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('hoc-sinh.index') }}">Quản lý học sinh</a></li>
                <li class="breadcrumb-item"><a href="{{ route('hoc-sinh.show', $hocSinh) }}">{{ $hocSinh->ho_ten }}</a></li>
                <li class="breadcrumb-item active">Chỉnh sửa</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('hoc-sinh.show', $hocSinh) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Quay lại
    </a>
</div>

<form method="POST" action="{{ route('hoc-sinh.update', $hocSinh) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="row g-4">

        {{-- Thông tin cơ bản --}}
        <div class="col-lg-8">
            <div class="card table-card">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-semibold mb-0"><i class="bi bi-person text-primary me-2"></i>Thông tin cơ bản</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="ho_ten"
                                   class="form-control @error('ho_ten') is-invalid @enderror"
                                   value="{{ old('ho_ten', $hocSinh->ho_ten) }}" required>
                            @error('ho_ten')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Ngày sinh <span class="text-danger">*</span></label>
                            <input type="date" name="ngay_sinh"
                                   class="form-control @error('ngay_sinh') is-invalid @enderror"
                                   value="{{ old('ngay_sinh', $hocSinh->ngay_sinh->format('Y-m-d')) }}" required>
                            @error('ngay_sinh')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Giới tính <span class="text-danger">*</span></label>
                            <select name="gioi_tinh" class="form-select @error('gioi_tinh') is-invalid @enderror" required>
                                <option value="Nam"  {{ old('gioi_tinh', $hocSinh->gioi_tinh) == 'Nam'  ? 'selected' : '' }}>Nam</option>
                                <option value="Nữ"   {{ old('gioi_tinh', $hocSinh->gioi_tinh) == 'Nữ'   ? 'selected' : '' }}>Nữ</option>
                                <option value="Khác" {{ old('gioi_tinh', $hocSinh->gioi_tinh) == 'Khác' ? 'selected' : '' }}>Khác</option>
                            </select>
                            @error('gioi_tinh')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">CCCD/CMND</label>
                            <input type="text" name="cccd" class="form-control @error('cccd') is-invalid @enderror"
                                   value="{{ old('cccd', $hocSinh->cccd) }}" maxlength="12">
                            @error('cccd')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Dân tộc</label>
                            <input type="text" name="dan_toc" class="form-control"
                                   value="{{ old('dan_toc', $hocSinh->dan_toc) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tôn giáo</label>
                            <input type="text" name="ton_giao" class="form-control"
                                   value="{{ old('ton_giao', $hocSinh->ton_giao) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Số điện thoại</label>
                            <input type="tel" name="so_dien_thoai" class="form-control"
                                   value="{{ old('so_dien_thoai', $hocSinh->so_dien_thoai) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="{{ old('email', $hocSinh->email) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Địa chỉ thường trú</label>
                            <textarea name="dia_chi_thuong_tru" class="form-control" rows="2">{{ old('dia_chi_thuong_tru', $hocSinh->dia_chi_thuong_tru) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Địa chỉ tạm trú</label>
                            <textarea name="dia_chi_tam_tru" class="form-control" rows="2">{{ old('dia_chi_tam_tru', $hocSinh->dia_chi_tam_tru) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Thông tin gia đình --}}
            <div class="card table-card mt-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-semibold mb-0"><i class="bi bi-house text-success me-2"></i>Thông tin gia đình</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12"><strong class="text-muted small">THÔNG TIN BỐ</strong></div>
                        <div class="col-md-4">
                            <label class="form-label">Họ tên bố</label>
                            <input type="text" name="ho_ten_cha" class="form-control"
                                   value="{{ old('ho_ten_cha', $hocSinh->ho_ten_cha) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nghề nghiệp</label>
                            <input type="text" name="nghe_nghiep_cha" class="form-control"
                                   value="{{ old('nghe_nghiep_cha', $hocSinh->nghe_nghiep_cha) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Số điện thoại</label>
                            <input type="tel" name="so_dien_thoai_cha" class="form-control"
                                   value="{{ old('so_dien_thoai_cha', $hocSinh->so_dien_thoai_cha) }}">
                        </div>
                        <div class="col-12 mt-2"><strong class="text-muted small">THÔNG TIN MẸ</strong></div>
                        <div class="col-md-4">
                            <label class="form-label">Họ tên mẹ</label>
                            <input type="text" name="ho_ten_me" class="form-control"
                                   value="{{ old('ho_ten_me', $hocSinh->ho_ten_me) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nghề nghiệp</label>
                            <input type="text" name="nghe_nghiep_me" class="form-control"
                                   value="{{ old('nghe_nghiep_me', $hocSinh->nghe_nghiep_me) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Số điện thoại</label>
                            <input type="tel" name="so_dien_thoai_me" class="form-control"
                                   value="{{ old('so_dien_thoai_me', $hocSinh->so_dien_thoai_me) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cột phải --}}
        <div class="col-lg-4">

            {{-- Ảnh thẻ --}}
            <div class="card table-card mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-semibold mb-0"><i class="bi bi-image text-warning me-2"></i>Ảnh thẻ</h6>
                </div>
                <div class="card-body text-center">
                    <div id="previewContainer" class="mb-3">
                        @if($hocSinh->anh_the)
                            <img src="{{ Storage::url($hocSinh->anh_the) }}" id="currentImg"
                                 class="rounded-3 border mx-auto d-block"
                                 style="width:120px;height:150px;object-fit:cover;cursor:pointer"
                                 onclick="document.getElementById('anh_the').click()">
                        @else
                            <div class="bg-light rounded-3 d-flex align-items-center justify-content-center mx-auto"
                                 style="width:120px;height:150px;cursor:pointer"
                                 onclick="document.getElementById('anh_the').click()">
                                <div class="text-muted">
                                    <i class="bi bi-person-bounding-box fs-1 d-block"></i>
                                    <small>Chọn ảnh</small>
                                </div>
                            </div>
                        @endif
                    </div>
                    <input type="file" name="anh_the" id="anh_the" class="d-none"
                           accept="image/jpeg,image/png,image/jpg">
                    <label for="anh_the" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-upload me-2"></i>Đổi ảnh
                    </label>
                    <div class="text-muted small mt-2">JPEG, PNG. Tối đa 2MB</div>
                </div>
            </div>

            {{-- Thông tin học tập --}}
            <div class="card table-card">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-semibold mb-0"><i class="bi bi-book text-info me-2"></i>Thông tin học tập</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lớp học</label>
                        <select name="lop_hoc_id" class="form-select">
                            <option value="">-- Chưa xếp lớp --</option>
                            @foreach($lopHoc as $lop)
                                <option value="{{ $lop->id }}"
                                    {{ old('lop_hoc_id', $hocSinh->lop_hoc_id) == $lop->id ? 'selected' : '' }}>
                                    {{ $lop->ten_lop }} ({{ $lop->nam_hoc }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Trạng thái <span class="text-danger">*</span></label>
                        <select name="trang_thai" class="form-select @error('trang_thai') is-invalid @enderror" required>
                            @foreach(['Đang học', 'Đã tốt nghiệp', 'Thôi học', 'Chuyển trường'] as $tt)
                                <option value="{{ $tt }}"
                                    {{ old('trang_thai', $hocSinh->trang_thai) == $tt ? 'selected' : '' }}>
                                    {{ $tt }}
                                </option>
                            @endforeach
                        </select>
                        @error('trang_thai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Lưu thay đổi
                        </button>
                        <a href="{{ route('hoc-sinh.show', $hocSinh) }}" class="btn btn-outline-secondary">
                            Hủy bỏ
                        </a>
                    </div>
                </div>
            </div>

        </div>{{-- end col-lg-4 --}}

    </div>{{-- end row --}}
</form>

@push('scripts')
<script>
document.getElementById('anh_the').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewContainer').innerHTML = `
                <img src="${e.target.result}"
                     class="rounded-3 border mx-auto d-block"
                     style="width:120px;height:150px;object-fit:cover;cursor:pointer"
                     onclick="document.getElementById('anh_the').click()">
            `;
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
@endsection