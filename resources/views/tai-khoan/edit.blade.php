@extends('layouts.app')
@section('title', 'Chỉnh sửa tài khoản')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Chỉnh sửa tài khoản</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('tai-khoan.index') }}">Quản lý tài khoản</a></li>
                <li class="breadcrumb-item active">{{ $taiKhoan->name }}</li>
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
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-person-gear text-warning me-2"></i>Cập nhật: {{ $taiKhoan->name }}
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('tai-khoan.update', $taiKhoan) }}">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $taiKhoan->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Số điện thoại</label>
                            <input type="tel" name="phone" class="form-control"
                                   value="{{ old('phone', $taiKhoan->phone) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $taiKhoan->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Mật khẩu mới</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Để trống nếu không đổi mật khẩu">
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="togglePassword('password')">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Xác nhận mật khẩu mới</label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                       class="form-control" placeholder="Nhập lại mật khẩu mới">
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

    @php
        $isAdmin     = $taiKhoan->hasRole('quan-tri-vien');
        $isSelf      = $taiKhoan->id === auth()->id();
        $rolesLocked = $isAdmin || $isSelf;
         $currentRole = $taiKhoan->getRoleNames()->first();
    @endphp
<select name="role"
            class="form-select @error('role') is-invalid @enderror"
            required
            {{ $rolesLocked ? 'disabled' : '' }}>

        <option value="quan-tri-vien"
            {{ $currentRole === 'quan-tri-vien' ? 'selected' : '' }}
            {{ $isAdmin && $currentRole !== 'quan-tri-vien' ? 'disabled' : '' }}>
            Quản trị viên
        </option>

        <option value="giao-vien-chu-nhiem"
            {{ in_array($currentRole, ['giao-vien-chu-nhiem']) ? 'selected' : '' }}
            {{ $isAdmin ? 'disabled' : '' }}>
            Giáo viên
        </option>
   
</select>
    {{-- Gửi kèm giá trị role thực vì disabled không submit --}}
    @if($rolesLocked)
        <input type="hidden" name="role"
               value="{{ $taiKhoan->getRoleNames()->first() }}">
    @endif

    @error('role')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror

    {{-- Thông báo lý do bị khóa --}}
    @if($isAdmin)
        <div class="form-text text-danger">
            <i class="bi bi-lock-fill me-1"></i>
            Không thể thay đổi vai trò Quản trị viên
        </div>
    @elseif($isSelf)
        <div class="form-text text-warning">
            <i class="bi bi-exclamation-triangle me-1"></i>
            Không thể tự thay đổi vai trò của mình
        </div>
    @endif
</div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Trạng thái</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                       id="is_active" value="1"
                                       {{ $taiKhoan->is_active ? 'checked' : '' }}
                                       {{ $taiKhoan->id === auth()->id() ? 'disabled' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Tài khoản đang hoạt động
                                </label>
                            </div>
                            @if($taiKhoan->id === auth()->id())
                                <small class="text-muted">Không thể tự vô hiệu hóa tài khoản của mình</small>
                            @endif
                        </div>

                        <div class="col-12 mt-2">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-check-circle me-2"></i>Lưu thay đổi
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
