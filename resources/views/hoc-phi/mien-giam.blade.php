@extends('layouts.app')
@section('title', 'Miễn giảm học phí')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-percent text-primary me-2"></i>Miễn giảm học phí</h4>
    <a href="{{ route('hoc-phi.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Công nợ</a>
</div>

@if($errors->any())
<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <form method="GET" class="mb-3" style="max-width:220px">
            <select name="nam_hoc" class="form-select" onchange="this.form.submit()">
                @foreach($dsNamHoc as $nh)<option value="{{ $nh }}" {{ $namHoc == $nh ? 'selected' : '' }}>Năm học {{ $nh }}</option>@endforeach
            </select>
        </form>
        <div class="card table-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead><tr><th>Học sinh</th><th>Lớp</th><th>Đối tượng</th><th class="text-center">Mức giảm</th><th>Ghi chú</th><th></th></tr></thead>
                    <tbody>
                        @forelse($mienGiam as $mg)
                        <tr>
                            <td class="fw-semibold">{{ $mg->hocSinh?->ho_ten }}</td>
                            <td>{{ $mg->hocSinh?->lopHoc?->ten_lop }}</td>
                            <td>{{ $mg->doi_tuong }}</td>
                            <td class="text-center"><span class="badge bg-info">{{ $mg->phan_tram }}%</span></td>
                            <td class="small text-muted">{{ $mg->ghi_chu }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('hoc-phi.mien-giam.destroy', $mg) }}" onsubmit="return confirm('Xóa miễn giảm?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Chưa có học sinh được miễn giảm trong năm học {{ $namHoc }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white small text-muted">Miễn giảm chỉ áp dụng cho các khoản thu có đánh dấu "MG" (thường là học phí).</div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card table-card">
            <div class="card-header bg-white py-3"><h6 class="fw-semibold mb-0">Thêm / cập nhật miễn giảm</h6></div>
            <form method="POST" action="{{ route('hoc-phi.mien-giam.store') }}" class="card-body">
                @csrf
                <input type="hidden" name="nam_hoc" value="{{ $namHoc }}">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Học sinh</label>
                    <select name="hoc_sinh_id" class="form-select" required>
                        <option value="">-- Chọn học sinh --</option>
                        @foreach($lopHoc as $lop)
                            <optgroup label="Lớp {{ $lop->ten_lop }}">
                                @foreach($lop->hocSinh as $hs)<option value="{{ $hs->id }}">{{ $hs->ho_ten }}</option>@endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Đối tượng</label>
                    <input type="text" name="doi_tuong" id="doiTuong" class="form-control" list="dsDoiTuong" required maxlength="150"
                           onchange="const m = {{ json_encode($doiTuong) }}[this.value]; if (m) document.getElementById('phanTram').value = m;">
                    <datalist id="dsDoiTuong">
                        @foreach($doiTuong as $ten => $pt)<option value="{{ $ten }}">{{ $pt }}%</option>@endforeach
                    </datalist>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Mức giảm (%)</label>
                    <input type="number" name="phan_tram" id="phanTram" class="form-control" min="1" max="100" required value="100">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Ghi chú</label>
                    <input type="text" name="ghi_chu" class="form-control" maxlength="255" placeholder="VD: Số giấy chứng nhận hộ nghèo">
                </div>
                <button class="btn btn-primary w-100"><i class="bi bi-save me-2"></i>Lưu</button>
            </form>
        </div>
    </div>
</div>
@endsection
