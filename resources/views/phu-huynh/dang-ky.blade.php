@extends('layouts.public')
@section('title', 'Phụ huynh đăng ký tài khoản')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card the">
            <div class="card-body p-4">
                <h4 class="fw-bold text-center mb-1">Phụ huynh đăng ký tài khoản</h4>
                <p class="text-muted text-center small mb-4">Đăng ký một lần, theo dõi kết quả học tập, chuyên cần, học phí và tin nhắn của nhà trường cho tất cả các con.</p>

                @if($errors->any())
                <div class="alert alert-danger small">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
                @endif

                <form method="POST" action="{{ route('phu-huynh.dang-ky') }}" class="row g-3">
                    @csrf
                    <div class="col-12"><div class="small fw-semibold text-uppercase text-muted">Thông tin học sinh (ghi trên phiếu nhà trường phát)</div></div>
                    <div class="col-sm-6">
                        <label class="form-label">Mã học sinh</label>
                        <input name="ma_hoc_sinh" class="form-control" required maxlength="30" value="{{ old('ma_hoc_sinh') }}" autocomplete="off" placeholder="VD: HS20260001">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">Mã tra cứu</label>
                        <input name="ma_tra_cuu" class="form-control text-uppercase" required maxlength="20" autocomplete="off" placeholder="8 ký tự">
                    </div>

                    <div class="col-12 mt-4"><div class="small fw-semibold text-uppercase text-muted">Thông tin phụ huynh</div></div>
                    <div class="col-sm-8">
                        <label class="form-label">Họ và tên</label>
                        <input name="name" class="form-control" required maxlength="255" value="{{ old('name') }}" autocomplete="name">
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label">Quan hệ</label>
                        <select name="quan_he" class="form-select" required>
                            @foreach($quanHe as $q)<option {{ old('quan_he') == $q ? 'selected' : '' }}>{{ $q }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">Email (dùng để đăng nhập)</label>
                        <input type="email" name="email" class="form-control" required maxlength="255" value="{{ old('email') }}" autocomplete="email">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">Số điện thoại</label>
                        <input type="tel" name="phone" class="form-control" maxlength="20" value="{{ old('phone') }}" autocomplete="tel">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">Mật khẩu</label>
                        <input type="password" name="password" class="form-control" required autocomplete="new-password">
                        <div class="form-text">Ít nhất 8 ký tự, có chữ hoa, chữ thường và chữ số.</div>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">Nhập lại mật khẩu</label>
                        <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                    </div>
                    <div class="col-12 mt-4"><button class="btn btn-primary w-100"><i class="bi bi-person-plus me-2"></i>Đăng ký</button></div>
                </form>
                <p class="small text-muted text-center mt-3 mb-0">Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a> · <a href="{{ route('tra-cuu') }}">Tra cứu nhanh không cần tài khoản</a></p>
            </div>
        </div>
    </div>
</div>
@endsection
