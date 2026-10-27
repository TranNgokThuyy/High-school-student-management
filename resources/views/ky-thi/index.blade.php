@extends('layouts.app')
@section('title', 'Kỳ thi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-pencil-square text-primary me-2"></i>Kỳ thi</h4>
</div>

@if($errors->any())
<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card table-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead><tr><th>Kỳ thi</th><th>Năm học / HK</th><th>Khối</th><th class="text-center">Môn</th><th class="text-center">Phòng</th><th class="text-center">Thí sinh</th><th></th></tr></thead>
                    <tbody>
                        @forelse($kyThi as $kt)
                        <tr>
                            <td class="fw-semibold"><a href="{{ route('ky-thi.show', $kt) }}" class="text-decoration-none">{{ $kt->ten }}</a></td>
                            <td>{{ $kt->nam_hoc }} · HK{{ $kt->hoc_ky }}</td>
                            <td>{{ $kt->khoi }}</td>
                            <td class="text-center">{{ $kt->lich_thi_count }}</td>
                            <td class="text-center">{{ $kt->phong_thi_count }}</td>
                            <td class="text-center">{{ $kt->thi_sinh_count }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('ky-thi.destroy', $kt) }}" onsubmit="return confirm('Xóa kỳ thi {{ $kt->ten }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Chưa có kỳ thi</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <form method="POST" action="{{ route('ky-thi.store') }}" class="card table-card">
            @csrf
            <div class="card-header bg-white py-3"><h6 class="fw-semibold mb-0">Tạo kỳ thi</h6></div>
            <div class="card-body">
                <div class="mb-2"><label class="form-label">Tên kỳ thi</label><input name="ten" class="form-control" required maxlength="150" value="{{ old('ten') }}" placeholder="VD: Kiểm tra cuối học kỳ I"></div>
                <div class="row g-2 mb-2">
                    <div class="col-6"><label class="form-label">Năm học</label><input name="nam_hoc" class="form-control" required pattern="\d{4}-\d{4}" value="{{ old('nam_hoc', $namHoc) }}"></div>
                    <div class="col-3"><label class="form-label">HK</label><select name="hoc_ky" class="form-select">@foreach(['1', '2'] as $hk)<option {{ old('hoc_ky', $hocKy) == $hk ? 'selected' : '' }}>{{ $hk }}</option>@endforeach</select></div>
                    <div class="col-3"><label class="form-label">Khối</label><select name="khoi" class="form-select">@foreach(['Tất cả', '10', '11', '12'] as $k)<option {{ old('khoi') == $k ? 'selected' : '' }}>{{ $k }}</option>@endforeach</select></div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6"><label class="form-label">Thí sinh / phòng</label><input type="number" name="suc_chua_phong" class="form-control" min="5" max="60" value="{{ old('suc_chua_phong', 24) }}"></div>
                    <div class="col-6"><label class="form-label">Tiền tố SBD</label><input name="tien_to_sbd" class="form-control text-uppercase" maxlength="10" value="{{ old('tien_to_sbd', 'HK') }}"></div>
                </div>
                <div class="mb-2"><label class="form-label">Ghi chú</label><input name="ghi_chu" class="form-control" maxlength="500" value="{{ old('ghi_chu') }}"></div>
            </div>
            <div class="card-footer bg-white text-end"><button class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tạo</button></div>
        </form>
    </div>
</div>
@endsection
