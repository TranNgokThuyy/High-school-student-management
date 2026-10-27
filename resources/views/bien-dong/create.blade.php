@extends('layouts.app')
@section('title', 'Biến động học sinh')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-arrow-left-right text-primary me-2"></i>Chuyển trường / thôi học — {{ $hocSinh->ho_ten }}</h4>
    <a href="{{ route('hoc-sinh.show', $hocSinh) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Hồ sơ</a>
</div>

@if($errors->any())
<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

<div class="row g-4">
    <div class="col-lg-6">
        <form method="POST" action="{{ route('bien-dong.store', $hocSinh) }}" class="card table-card">
            @csrf
            <div class="card-body">
                <p class="mb-3">Trạng thái hiện tại: <b>{{ $hocSinh->trang_thai }}</b> — Lớp {{ $hocSinh->lopHoc?->ten_lop ?? '—' }}</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Loại biến động</label>
                    <select name="loai" id="loai" class="form-select" onchange="hienTruong()">
                        @if($hocSinh->trang_thai === 'Đang học')
                            <option {{ old('loai') == 'Chuyển đi' ? 'selected' : '' }}>Chuyển đi</option>
                            <option {{ old('loai') == 'Thôi học' ? 'selected' : '' }}>Thôi học</option>
                        @else
                            <option>Học lại</option>
                        @endif
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Ngày</label>
                        <input type="date" name="ngay" class="form-control" required max="{{ today()->toDateString() }}" value="{{ old('ngay', today()->toDateString()) }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Số quyết định</label>
                        <input type="text" name="so_quyet_dinh" class="form-control" maxlength="50" value="{{ old('so_quyet_dinh') }}">
                    </div>
                </div>
                <div class="mb-3 truong-chuyen-di">
                    <label class="form-label fw-semibold">Chuyển đến trường</label>
                    <input type="text" name="truong" class="form-control" maxlength="200" value="{{ old('truong') }}">
                </div>
                <div class="mb-3 lop-hoc-lai d-none">
                    <label class="form-label fw-semibold">Quay lại học lớp</label>
                    <select name="lop_hoc_id" class="form-select">
                        @foreach($lopHoc as $l)<option value="{{ $l->id }}" {{ $hocSinh->lop_hoc_id == $l->id ? 'selected' : '' }}>{{ $l->ten_lop }} ({{ $l->nam_hoc }})</option>@endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Lý do</label>
                    <textarea name="ly_do" class="form-control" rows="2" maxlength="1000">{{ old('ly_do') }}</textarea>
                </div>
            </div>
            <div class="card-footer bg-white text-end">
                <button class="btn btn-primary" onclick="return confirm('Xác nhận ghi nhận biến động?')"><i class="bi bi-save me-2"></i>Ghi nhận</button>
            </div>
        </form>
    </div>
    <div class="col-lg-6">
        <div class="card table-card">
            <div class="card-header bg-white py-3"><h6 class="fw-semibold mb-0">Lịch sử biến động</h6></div>
            <ul class="list-group list-group-flush">
                @forelse($hocSinh->bienDong->sortByDesc('ngay') as $bd)
                <li class="list-group-item small">
                    <span class="badge bg-{{ \App\Models\BienDongHocSinh::MAU[$bd->loai] ?? 'secondary' }}">{{ $bd->loai }}</span>
                    {{ $bd->ngay->format('d/m/Y') }} {{ $bd->so_quyet_dinh ? '— QĐ ' . $bd->so_quyet_dinh : '' }}
                    @if($bd->truong)<div>Trường: {{ $bd->truong }}</div>@endif
                    @if($bd->ly_do)<div class="text-muted">{{ $bd->ly_do }}</div>@endif
                </li>
                @empty
                <li class="list-group-item text-muted small">Chưa có biến động</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function hienTruong() {
    const loai = document.getElementById('loai').value;
    document.querySelector('.truong-chuyen-di').classList.toggle('d-none', loai !== 'Chuyển đi');
    document.querySelector('.lop-hoc-lai').classList.toggle('d-none', loai !== 'Học lại');
}
hienTruong();
</script>
@endpush
