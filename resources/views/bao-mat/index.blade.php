@extends('layouts.app')
@section('title', 'Xác thực 2 bước')

@section('content')
<h4 class="fw-bold mb-4"><i class="bi bi-shield-lock text-primary me-2"></i>Xác thực 2 bước</h4>

@if($errors->any())
<div class="alert alert-danger" style="max-width:720px">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

@if($maKhoiPhuc)
<div class="alert alert-warning" style="max-width:720px">
    <div class="fw-semibold mb-2"><i class="bi bi-exclamation-triangle me-1"></i>Mã khôi phục — chỉ hiện một lần, hãy in hoặc chép ra giấy</div>
    <div class="row row-cols-2 row-cols-sm-4 g-2 font-monospace">
        @foreach($maKhoiPhuc as $ma)<div class="col"><div class="bg-white border rounded px-2 py-1 text-center">{{ $ma }}</div></div>@endforeach
    </div>
    <div class="small mt-2">Mỗi mã dùng được một lần thay cho mã trên điện thoại khi bạn mất hoặc đổi điện thoại.</div>
</div>
@endif

<div class="card table-card" style="max-width:720px">
    <div class="card-body">
        @if($user->daBatHaiBuoc())
            <p class="mb-1"><span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Đang bật</span>
                từ {{ $user->two_factor_confirmed_at->format('d/m/Y H:i') }}</p>
            <p class="text-muted small">Mỗi lần đăng nhập, sau khi nhập mật khẩu bạn cần nhập mã 6 số trong ứng dụng xác thực.
                Còn {{ count($user->two_factor_recovery_codes ?? []) }} mã khôi phục chưa dùng.</p>

            <form method="POST" action="{{ route('bao-mat.ma-khoi-phuc') }}" class="row g-2 align-items-end mb-3">
                @csrf
                <div class="col-sm-6"><label class="form-label small">Mật khẩu hiện tại</label><input type="password" name="current_password" class="form-control" required autocomplete="current-password"></div>
                <div class="col-sm-6"><button class="btn btn-outline-primary w-100"><i class="bi bi-arrow-repeat me-1"></i>Tạo lại mã khôi phục</button></div>
            </form>

            @unless($user->batBuocHaiBuoc())
            <form method="POST" action="{{ route('bao-mat.tat') }}" class="row g-2 align-items-end" onsubmit="return confirm('Tắt xác thực 2 bước?')">
                @csrf @method('DELETE')
                <div class="col-sm-6"><label class="form-label small">Mật khẩu hiện tại</label><input type="password" name="current_password" class="form-control" required autocomplete="current-password"></div>
                <div class="col-sm-6"><button class="btn btn-outline-danger w-100">Tắt xác thực 2 bước</button></div>
            </form>
            @else
            <p class="small text-muted mb-0">Tài khoản quản trị viên bắt buộc dùng xác thực 2 bước. Mất điện thoại và hết mã khôi phục: nhờ một quản trị viên khác gỡ ở trang Quản lý tài khoản.</p>
            @endunless
        @else
            @if($user->batBuocHaiBuoc())
                <div class="alert alert-info small">Tài khoản quản trị viên phải bật xác thực 2 bước để tiếp tục sử dụng hệ thống.</div>
            @endif
            <ol class="mb-3">
                <li>Cài ứng dụng <b>Google Authenticator</b> hoặc <b>Microsoft Authenticator</b> trên điện thoại.</li>
                <li>Trong ứng dụng chọn thêm tài khoản → quét mã QR bên dưới (hoặc nhập khóa thủ công).</li>
                <li>Nhập mã 6 số ứng dụng hiển thị để hoàn tất.</li>
            </ol>
            <div class="d-flex flex-wrap gap-4 align-items-center mb-3">
                <div class="bg-white p-2 border rounded" style="width:216px">{!! $qr !!}</div>
                <div>
                    <div class="small text-muted">Khóa nhập thủ công</div>
                    <code class="fs-6 user-select-all">{{ $khoa }}</code>
                </div>
            </div>
            <form method="POST" action="{{ route('bao-mat.bat') }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-sm-6"><label class="form-label">Mã 6 số</label>
                    <input name="ma" class="form-control fs-5 text-center font-monospace" required inputmode="numeric" autocomplete="one-time-code" maxlength="7" placeholder="000000" autofocus></div>
                <div class="col-sm-6"><button class="btn btn-primary w-100"><i class="bi bi-shield-check me-1"></i>Bật xác thực 2 bước</button></div>
            </form>
        @endif
    </div>
</div>
@endsection
