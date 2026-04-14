@extends('layouts.app')
@section('title', 'Tạo tài khoản mới')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Tạo tài khoản mới</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('tai-khoan.index') }}">Quản lý tài khoản</a></li>
                <li class="breadcrumb-item active">Tạo mới</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('tai-khoan.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Quay lại
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card table-card">
            <div class="card-header bg-white py-3">
                <h6 class="fw-semibold mb-0"><i class="bi bi-person-plus text-primary me-2"></i>Thông tin tài khoản</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('tai-khoan.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="Nguyễn Văn A" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Số điện thoại</label>
                            <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone') }}" placeholder="09xxxxxxxx">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="example@school.com" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mật khẩu <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password" id="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Tối thiểu 8 ký tự" required>
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="togglePassword('password')">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                       class="form-control" placeholder="Nhập lại mật khẩu" required>
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="togglePassword('password_confirmation')">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

<div class="col-md-6">
    <label class="form-label fw-semibold">
        Vai trò <span class="text-danger">*</span>
    </label>
    <select name="role"
            class="form-select @error('role') is-invalid @enderror"
            required>
        <option value="">-- Chọn vai trò --</option>
        <option value="quan-tri-vien"
            {{ old('role') === 'quan-tri-vien' ? 'selected' : '' }}>
            Quản trị viên
        </option>
        <option value="giao-vien-chu-nhiem"
            {{ old('role') === 'giao-vien-chu-nhiem' ? 'selected' : '' }}>
            Giáo viên
        </option>
    </select>
    @error('role')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>                       

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Trạng thái</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                       id="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">Tài khoản đang hoạt động</label>
                            </div>
                        </div>

                        <div class="col-12 mt-2">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>Tạo tài khoản
                                </button>
                                <a href="{{ route('tai-khoan.index') }}" class="btn btn-outline-secondary">Hủy</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
@endpush
@endsection