@extends('layouts.app')
@section('title', 'Biến động học sinh')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-arrow-left-right text-primary me-2"></i>Biến động học sinh</h4>
    @can('them-hoc-sinh')
    <a href="{{ route('hoc-sinh.create', ['chuyen_den' => 1]) }}" class="btn btn-primary"><i class="bi bi-person-plus me-2"></i>Tiếp nhận HS chuyển đến</a>
    @endcan
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-auto">
        <select name="nam_hoc" class="form-select" onchange="this.form.submit()">
            @foreach($dsNamHoc as $nh)<option value="{{ $nh }}" {{ $namHoc == $nh ? 'selected' : '' }}>Năm học {{ $nh }}</option>@endforeach
        </select>
    </div>
    <div class="col-auto">
        <select name="loai" class="form-select" onchange="this.form.submit()">
            <option value="">Tất cả loại</option>
            @foreach(array_keys(\App\Models\BienDongHocSinh::LOAI) as $l)<option {{ request('loai') == $l ? 'selected' : '' }}>{{ $l }}</option>@endforeach
        </select>
    </div>
    <div class="col d-flex gap-2 align-items-center flex-wrap">
        @foreach(\App\Models\BienDongHocSinh::MAU as $l => $m)
            <span class="badge bg-{{ $m }}">{{ $l }}: {{ $thongKe[$l] ?? 0 }}</span>
        @endforeach
    </div>
</form>

<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead><tr><th>Ngày</th><th>Học sinh</th><th>Lớp</th><th>Loại</th><th>Số QĐ</th><th>Trường đi / đến</th><th>Lý do</th><th>Người ghi</th></tr></thead>
            <tbody>
                @forelse($bienDong as $bd)
                <tr>
                    <td class="text-nowrap">{{ $bd->ngay->format('d/m/Y') }}</td>
                    <td><a href="{{ route('hoc-sinh.show', $bd->hoc_sinh_id) }}" class="fw-semibold text-decoration-none">{{ $bd->hocSinh?->ho_ten }}</a></td>
                    <td>{{ $bd->lopHoc?->ten_lop }}</td>
                    <td><span class="badge bg-{{ \App\Models\BienDongHocSinh::MAU[$bd->loai] ?? 'secondary' }}">{{ $bd->loai }}</span></td>
                    <td>{{ $bd->so_quyet_dinh ?? '—' }}</td>
                    <td>{{ $bd->truong ?? '—' }}</td>
                    <td class="small">{{ $bd->ly_do }}</td>
                    <td class="small text-muted">{{ $bd->nguoiTao?->name }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Chưa có biến động trong năm học {{ $namHoc }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($bienDong->hasPages())<div class="card-footer bg-white">{{ $bienDong->links() }}</div>@endif
</div>
@endsection
