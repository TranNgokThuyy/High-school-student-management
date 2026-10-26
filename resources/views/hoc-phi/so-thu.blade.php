@extends('layouts.app')
@section('title', 'Sổ thu')

@section('content')
@use('App\Support\Tien')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-journal-bookmark text-primary me-2"></i>Sổ thu</h4>
    <a href="{{ route('hoc-phi.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Công nợ</a>
</div>

<div class="card table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3"><label class="form-label">Từ ngày</label><input type="date" name="tu_ngay" class="form-control" value="{{ $tuNgay->toDateString() }}"></div>
            <div class="col-md-3"><label class="form-label">Đến ngày</label><input type="date" name="den_ngay" class="form-control" value="{{ $denNgay->toDateString() }}"></div>
            <div class="col-md-2">
                <label class="form-label">Hình thức</label>
                <select name="hinh_thuc" class="form-select">
                    <option value="">Tất cả</option>
                    @foreach(['Tiền mặt', 'Chuyển khoản'] as $ht)<option {{ request('hinh_thuc') == $ht ? 'selected' : '' }}>{{ $ht }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2"><label class="form-label">Số phiếu</label><input type="text" name="so_phieu" class="form-control" value="{{ request('so_phieu') }}"></div>
            <div class="col-md-2"><button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Xem</button></div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach(['Tiền mặt' => 'success', 'Chuyển khoản' => 'primary'] as $ht => $mau)
    <div class="col-md-4">
        <div class="card stat-card"><div class="card-body py-3">
            <div class="text-muted small">{{ $ht }} ({{ $tongHop[$ht]->so_phieu ?? 0 }} phiếu)</div>
            <div class="fs-5 fw-bold text-{{ $mau }}">{{ Tien::dinhDang($tongHop[$ht]->tong ?? 0) }}</div>
        </div></div>
    </div>
    @endforeach
    <div class="col-md-4">
        <div class="card stat-card"><div class="card-body py-3">
            <div class="text-muted small">Tổng thu (không tính phiếu hủy)</div>
            <div class="fs-5 fw-bold">{{ Tien::dinhDang($tongHop->sum('tong')) }}</div>
        </div></div>
    </div>
</div>

<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle small">
            <thead><tr><th>Số phiếu</th><th>Ngày</th><th>Học sinh</th><th>Lớp</th><th>Khoản thu</th><th class="text-end">Số tiền</th><th>Hình thức</th><th>Người thu</th><th></th></tr></thead>
            <tbody>
                @forelse($phieuThu as $p)
                <tr class="{{ $p->da_huy ? 'text-muted' : '' }}">
                    <td class="fw-semibold">{{ $p->so_phieu }} @if($p->da_huy)<span class="badge bg-secondary" title="{{ $p->ly_do_huy }}">Hủy</span>@endif</td>
                    <td>{{ $p->ngay_thu->format('d/m/Y') }}</td>
                    <td><a href="{{ route('hoc-phi.hoc-sinh', $p->hoc_sinh_id) }}" class="text-decoration-none">{{ $p->hocSinh->ho_ten }}</a></td>
                    <td>{{ $p->hocSinh->lopHoc?->ten_lop }}</td>
                    <td>{{ $p->khoanThu->ten }}</td>
                    <td class="text-end {{ $p->da_huy ? 'text-decoration-line-through' : 'fw-semibold' }}">{{ number_format($p->so_tien, 0, ',', '.') }}</td>
                    <td>{{ $p->hinh_thuc }}</td>
                    <td>{{ $p->nguoiThu?->name }}</td>
                    <td><a href="{{ route('hoc-phi.in-phieu', $p) }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">Không có phiếu thu trong khoảng thời gian này</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($phieuThu->hasPages())<div class="card-footer bg-white">{{ $phieuThu->links() }}</div>@endif
</div>
@endsection
