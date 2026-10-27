@extends('layouts.app')
@section('title', 'Đổi mật khẩu')

@section('content')
<h4 class="fw-bold mb-4"><i class="bi bi-key text-primary me-2"></i>Đổi mật khẩu</h4>

<div class="card table-card" style="max-width:520px">
    <div class="card-body">
        @if(session('status') === 'password-updated')
            <div class="alert alert-success">Đã đổi mật khẩu.</div>
        @endif
        @if($errors->updatePassword->any())
            <div class="alert alert-danger">@foreach($errors->updatePassword->all() as $e)<div>{{ $e }}</div>@endforeach</div>
        @endif
        <form method="POST" action="{{ route('password.update') }}">
            @csrf @method('PUT')
            <div class="mb-3"><label class="form-label">Mật khẩu hiện tại</label><input type="password" name="current_password" class="form-control" required autocomplete="current-password"></div>
            <div class="mb-3"><label class="form-label">Mật khẩu mới</label><input type="password" name="password" class="form-control" required autocomplete="new-password">
                <div class="form-text">Ít nhất 8 ký tự, có chữ hoa, chữ thường và chữ số.</div></div>
            <div class="mb-4"><label class="form-label">Nhập lại mật khẩu mới</label><input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password"></div>
            <button class="btn btn-primary"><i class="bi bi-save me-2"></i>Đổi mật khẩu</button>
        </form>
    </div>
</div>
@endsection
