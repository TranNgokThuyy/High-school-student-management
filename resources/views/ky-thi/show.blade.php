@extends('layouts.app')
@section('title', $kyThi->ten)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">{{ $kyThi->ten }}</h4>
        <div class="text-muted small">Năm học {{ $kyThi->nam_hoc }} · HK{{ $kyThi->hoc_ky }} · Khối {{ $kyThi->khoi }} · {{ $soDuThi }} học sinh dự thi · {{ $kyThi->suc_chua_phong }} thí sinh/phòng</div>
    </div>
    <a href="{{ route('ky-thi.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Danh sách kỳ thi</a>
</div>

@if($errors->any())
<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card table-card">
            <div class="card-header bg-white py-3"><h6 class="fw-semibold mb-0"><i class="bi bi-calendar3 me-2"></i>Lịch thi</h6></div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead><tr><th>Ngày</th><th>Giờ</th><th>Môn</th><th class="text-center">Thời gian</th><th></th></tr></thead>
                    <tbody>
                        @forelse($kyThi->lichThi as $l)
                        <tr>
                            <td>{{ $l->ngay->format('d/m/Y') }}</td>
                            <td>{{ $l->gio_bat_dau }}</td>
                            <td class="fw-semibold">{{ $l->monHoc->ten_mon }}</td>
                            <td class="text-center">{{ $l->thoi_gian }} phút</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('ky-thi.xoa-lich', [$kyThi, $l]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-x"></i></button></form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Chưa có môn thi</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <form method="POST" action="{{ route('ky-thi.them-lich', $kyThi) }}" class="card-footer bg-white row g-2 align-items-end mx-0">
                @csrf
                <div class="col-md-4"><select name="mon_hoc_id" class="form-select form-select-sm" required>@foreach($monHoc as $m)<option value="{{ $m->id }}">{{ $m->ten_mon }}</option>@endforeach</select></div>
                <div class="col-md-3"><input type="date" name="ngay" class="form-control form-control-sm" required value="{{ old('ngay') }}"></div>
                <div class="col-md-2"><input type="time" name="gio_bat_dau" class="form-control form-control-sm" required value="{{ old('gio_bat_dau', '07:30') }}"></div>
                <div class="col-md-2"><input type="number" name="thoi_gian" class="form-control form-control-sm" min="15" max="240" value="{{ old('thoi_gian', 90) }}" title="Phút"></div>
                <div class="col-md-1"><button class="btn btn-sm btn-primary w-100"><i class="bi bi-plus"></i></button></div>
            </form>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card table-card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="fw-semibold mb-0"><i class="bi bi-door-open me-2"></i>Phòng thi</h6>
                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('ky-thi.xep-phong', $kyThi) }}"
                          onsubmit="return confirm('{{ $kyThi->phongThi->isEmpty() ? 'Xếp phòng thi và đánh số báo danh?' : 'Xếp lại sẽ thay toàn bộ phòng thi và số báo danh cũ. Tiếp tục?' }}')">
                        @csrf
                        <button class="btn btn-sm btn-success"><i class="bi bi-shuffle me-1"></i>{{ $kyThi->phongThi->isEmpty() ? 'Xếp phòng & SBD' : 'Xếp lại' }}</button>
                    </form>
                    @if($kyThi->phongThi->isNotEmpty())
                    <a href="{{ route('ky-thi.in', $kyThi) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-printer me-1"></i>In tất cả</a>
                    @endif
                </div>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($kyThi->phongThi as $p)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>{{ $p->ten_phong }} <span class="text-muted small">({{ $p->thi_sinh_count }} thí sinh)</span></span>
                    <a href="{{ route('ky-thi.in', [$kyThi, 'phong' => $p->id]) }}" target="_blank" class="btn btn-sm btn-light border"><i class="bi bi-printer"></i></a>
                </li>
                @empty
                <li class="list-group-item text-muted small">Chưa xếp phòng. Học sinh được sắp theo tên A→Z rồi chia phòng.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
