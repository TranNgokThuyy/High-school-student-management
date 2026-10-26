@extends('layouts.public')
@section('title', 'Tra cứu kết quả học tập')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card the">
            <div class="card-body p-4">
                <h4 class="fw-bold text-center mb-1">Tra cứu kết quả học tập</h4>
                <p class="text-muted text-center small mb-4">Dành cho phụ huynh và học sinh</p>

                @if($errors->any())
                <div class="alert alert-danger small">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('tra-cuu.ket-qua') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mã học sinh</label>
                        <input type="text" name="ma_hoc_sinh" class="form-control" required maxlength="30"
                               value="{{ old('ma_hoc_sinh') }}" placeholder="VD: HS20260001" autocomplete="off">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Ngày sinh</label>
                        <input type="date" name="ngay_sinh" class="form-control" required value="{{ old('ngay_sinh') }}">
                    </div>
                    <button class="btn btn-primary w-100"><i class="bi bi-search me-2"></i>Tra cứu</button>
                </form>
                <p class="small text-muted mt-3 mb-0">Mã học sinh được ghi trên thẻ học sinh hoặc liên hệ giáo viên chủ nhiệm.</p>
            </div>
        </div>
    </div>
</div>
@endsection
