@extends('layouts.app')
@section('title', 'Kiểm tra lại & rèn luyện hè')

@section('content')
@use('App\Support\Diem')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-sun text-warning me-2"></i>Kiểm tra lại & rèn luyện hè</h4>
    <a href="{{ route('tong-ket.len-lop', ['lop_hoc_id' => $lop?->id]) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Xét lên lớp</a>
</div>

<div class="card table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Lớp</label>
                <select name="lop_hoc_id" class="form-select" required>
                    <option value="">-- Chọn lớp --</option>
                    @foreach($lopHoc as $l)
                        <option value="{{ $l->id }}" {{ $lop?->id == $l->id ? 'selected' : '' }}>{{ $l->ten_lop }} ({{ $l->nam_hoc }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Xem</button></div>
        </form>
    </div>
</div>

<div class="alert alert-info small">
    Theo TT22: học sinh có kết quả học tập cả năm <b>Chưa đạt</b> được kiểm tra lại các môn có ĐTB cả năm dưới 5.0 hoặc nhận xét Chưa đạt;
    điểm kiểm tra lại thay ĐTB môn cả năm để xếp loại lại. Học sinh rèn luyện cả năm <b>Chưa đạt</b> rèn luyện trong hè.
    Sau khi nhập, kết quả xét lên lớp được tính lại; nếu vẫn chưa đạt thì <b>ở lại lớp</b>. Học sinh nghỉ quá 45 buổi không thuộc diện này.
</div>

@if($lop)
<form method="POST" action="{{ route('tong-ket.luu-he') }}">
    @csrf
    <input type="hidden" name="lop_hoc_id" value="{{ $lop->id }}">
    <div class="card table-card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Học sinh</th><th>Lần đầu</th><th>Kiểm tra lại</th><th>Rèn luyện hè</th><th>Kết quả xét</th></tr></thead>
                <tbody>
                    @forelse($danhSach as $r)
                    @php $hs = $r['hoc_sinh']; @endphp
                    <tr>
                        <td class="fw-semibold">{{ $hs->ho_ten }}<div class="small text-muted">{{ $hs->ma_hoc_sinh }}</div></td>
                        <td class="small">Học tập: <b>{{ $r['hoc_tap'] }}</b><br>Rèn luyện: <b>{{ $r['ren_luyen'] ?? '—' }}</b></td>
                        <td>
                            @forelse($r['mon_kiem_tra_lai'] as $mon)
                                @php $daNhap = $r['kiem_tra_lai'][$mon->id] ?? null; @endphp
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="small" style="min-width:120px">{{ $mon->ten_mon }} <span class="text-danger">({{ Diem::hienThi($r['diem_mon'][$mon->id]) }})</span></span>
                                    @if($mon->laMonNhanXet())
                                        <select name="kiem_tra_lai[{{ $hs->id }}][{{ $mon->id }}]" class="form-select form-select-sm" style="width:120px">
                                            <option value="">—</option>
                                            @foreach(['Đạt', 'Chưa đạt'] as $kq)<option {{ $daNhap === $kq ? 'selected' : '' }}>{{ $kq }}</option>@endforeach
                                        </select>
                                    @else
                                        <input type="number" step="0.1" min="0" max="10" name="kiem_tra_lai[{{ $hs->id }}][{{ $mon->id }}]"
                                               value="{{ $daNhap }}" class="form-control form-control-sm" style="width:90px" placeholder="Điểm KTL">
                                    @endif
                                </div>
                            @empty
                                <span class="text-muted small">—</span>
                            @endforelse
                            @if($r['hoc_tap_sau_ktl'])<div class="small">Sau KTL: <b>{{ $r['hoc_tap_sau_ktl'] }}</b></div>@endif
                        </td>
                        <td>
                            @if($r['ren_luyen'] === 'Chưa đạt')
                                <select name="ren_luyen_he[{{ $hs->id }}]" class="form-select form-select-sm" style="width:130px">
                                    <option value="">—</option>
                                    @foreach(['Đạt', 'Chưa đạt'] as $kq)<option {{ $r['ren_luyen_sau_he'] === $kq ? 'selected' : '' }}>{{ $kq }}</option>@endforeach
                                </select>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $r['len_lop']['dat'] ? 'success' : ($r['len_lop']['ket_qua'] === 'Ở lại lớp' ? 'danger' : 'warning text-dark') }}">{{ $r['len_lop']['ket_qua'] }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Lớp không có học sinh phải kiểm tra lại hoặc rèn luyện hè</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($danhSach->isNotEmpty())
        <div class="card-footer bg-white text-end">
            <button class="btn btn-primary"><i class="bi bi-save me-2"></i>Lưu & xét lại lên lớp</button>
        </div>
        @endif
    </div>
</form>
@endif
@endsection
