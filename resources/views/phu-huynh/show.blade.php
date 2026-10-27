@extends('layouts.app')
@section('title', $hocSinh->ho_ten)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">{{ $hocSinh->ho_ten }}</h4>
        <div class="text-muted">{{ $hocSinh->ma_hoc_sinh }} · Lớp {{ $hocSinh->lopHoc?->ten_lop ?? '—' }} · Năm học {{ $namHoc }}</div>
    </div>
    <div class="d-flex gap-2 no-print">
        <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer me-1"></i>In</button>
        <form method="POST" action="{{ route('phu-huynh.bo-lien-ket', $hocSinh) }}" onsubmit="return confirm('Bỏ liên kết với học sinh này? Muốn liên kết lại cần mã tra cứu.')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger"><i class="bi bi-link-45deg me-1"></i>Bỏ liên kết</button>
        </form>
    </div>
</div>

@include('tra-cuu._noi-dung')
@endsection
