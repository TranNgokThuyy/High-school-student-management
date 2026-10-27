@extends('layouts.app')
@section('title', 'Con của tôi')

@section('content')
@use('App\Support\Tien')
<h4 class="fw-bold mb-4"><i class="bi bi-people text-primary me-2"></i>Con của tôi</h4>

@if($errors->any())
<div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="row g-4 mb-4">
    @forelse($con as $hs)
    @php $t = $tomTat[$hs->id]; @endphp
    <div class="col-md-6 col-xl-4">
        <a href="{{ route('phu-huynh.show', $hs) }}" class="card table-card h-100 text-decoration-none text-body">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <div class="fw-bold fs-5">{{ $hs->ho_ten }}</div>
                        <div class="text-muted small">{{ $hs->ma_hoc_sinh }} · Lớp {{ $hs->lopHoc?->ten_lop ?? '—' }} · {{ $hs->pivot->quan_he }}</div>
                    </div>
                    @if($hs->trang_thai !== 'Đang học')<span class="badge bg-secondary">{{ $hs->trang_thai }}</span>@endif
                </div>
                <div class="row text-center mt-3 g-2 small">
                    <div class="col-4"><div class="fw-bold fs-5 {{ $t['tin_moi'] ? 'text-danger' : '' }}">{{ $t['tin_moi'] }}</div>tin mới</div>
                    <div class="col-4"><div class="fw-bold fs-5 {{ $t['vang'] ? 'text-warning' : '' }}">{{ $t['vang'] }}</div>buổi vắng (30 ngày)</div>
                    <div class="col-4"><div class="fw-bold {{ $t['con_no'] > 0 ? 'text-danger' : 'text-success' }}" style="line-height:1.9rem">{{ $t['con_no'] > 0 ? Tien::dinhDang($t['con_no']) : 'Đã nộp đủ' }}</div>{{ $t['con_no'] > 0 ? 'học phí còn nợ' : 'học phí' }}</div>
                </div>
            </div>
        </a>
    </div>
    @empty
    <div class="col-12"><div class="alert alert-info mb-0">Tài khoản chưa liên kết học sinh nào. Nhập mã học sinh và mã tra cứu bên dưới.</div></div>
    @endforelse
</div>

<div class="card table-card" style="max-width:720px">
    <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-person-plus me-2 text-primary"></i>Thêm con</div>
    <div class="card-body">
        <form method="POST" action="{{ route('phu-huynh.them-con') }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-sm-4"><label class="form-label small">Mã học sinh</label><input name="ma_hoc_sinh" class="form-control" required maxlength="30" value="{{ old('ma_hoc_sinh') }}" autocomplete="off"></div>
            <div class="col-sm-3"><label class="form-label small">Mã tra cứu</label><input name="ma_tra_cuu" class="form-control text-uppercase" required maxlength="20" autocomplete="off"></div>
            <div class="col-sm-3"><label class="form-label small">Quan hệ</label>
                <select name="quan_he" class="form-select">@foreach($quanHe as $q)<option>{{ $q }}</option>@endforeach</select></div>
            <div class="col-sm-2"><button class="btn btn-primary w-100">Thêm</button></div>
        </form>
        <div class="form-text">Mã tra cứu ghi trên phiếu nhà trường phát. Mất phiếu: liên hệ giáo viên chủ nhiệm để cấp lại.</div>
    </div>
</div>
@endsection
