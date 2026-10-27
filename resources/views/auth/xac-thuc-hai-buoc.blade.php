@extends('layouts.public')
@section('title', 'Xác thực 2 bước')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card the">
            <div class="card-body p-4">
                <div class="text-center mb-3"><i class="bi bi-shield-lock text-primary" style="font-size:42px"></i></div>
                <h5 class="fw-bold text-center mb-1">Xác thực 2 bước</h5>
                <p class="text-muted text-center small mb-4">Nhập mã 6 số trong ứng dụng xác thực trên điện thoại.</p>
                @if($errors->any())<div class="alert alert-danger small">{{ $errors->first() }}</div>@endif
                <form method="POST" action="{{ route('xac-thuc-hai-buoc') }}">
                    @csrf
                    <input name="ma" class="form-control form-control-lg text-center font-monospace mb-3" required autofocus
                           inputmode="numeric" autocomplete="one-time-code" maxlength="20" placeholder="000000">
                    <button class="btn btn-primary w-100">Xác nhận</button>
                </form>
                <p class="small text-muted mt-3 mb-0">Mất điện thoại? Nhập một <b>mã khôi phục</b> (dạng ABCD-1234) vào ô trên. <a href="{{ route('login') }}">Quay lại đăng nhập</a></p>
            </div>
        </div>
    </div>
</div>
@endsection
