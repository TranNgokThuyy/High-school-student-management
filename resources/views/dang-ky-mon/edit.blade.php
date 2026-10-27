@extends('layouts.app')
@section('title', 'Đăng ký môn lựa chọn - ' . $lop->ten_lop)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-ui-checks-grid text-primary me-2"></i>Đăng ký môn lựa chọn — Lớp {{ $lop->ten_lop }}</h4>
        <div class="text-muted small">Năm học {{ $lop->nam_hoc }} · Mỗi học sinh chọn {{ $soLuaChon }} môn lựa chọn và {{ $soChuyenDe }} chuyên đề học tập (chương trình GDPT 2018)</div>
    </div>
    <a href="{{ route('lop-hoc.show', $lop) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Lớp học</a>
</div>

@if($monHoc->isEmpty())
<div class="alert alert-warning">
    Chưa có môn nào thuộc loại <b>Lựa chọn</b> / <b>Chuyên đề</b> cho khối {{ $lop->khoi }}.
    @can('sua-mon-hoc') Hãy cấu hình ở <a href="{{ route('mon-hoc.index') }}">Quản lý môn học</a>. @endcan
</div>
@else
<form method="POST" action="{{ route('dang-ky-mon.update', $lop) }}">
    @csrf @method('PUT')
    <div class="card table-card">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Học sinh</th>
                        @foreach($monHoc as $mon)
                            <th class="text-center small">
                                {{ $mon->ten_mon }}
                                <div class="fw-normal"><span class="badge {{ $mon->loai_mon === 'Chuyên đề' ? 'bg-info' : 'bg-secondary' }}">{{ $mon->loai_mon }}</span></div>
                                <input type="checkbox" class="form-check-input mt-1 chon-cot" data-mon="{{ $mon->id }}" title="Chọn cả cột">
                            </th>
                        @endforeach
                        <th class="text-center">Đã chọn</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hocSinh as $hs)
                    <tr data-hs="{{ $hs->id }}">
                        <td class="fw-semibold text-nowrap">{{ $hs->ho_ten }}</td>
                        @foreach($monHoc as $mon)
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input o-dang-ky" name="dang_ky[{{ $hs->id }}][]" value="{{ $mon->id }}"
                                       data-mon="{{ $mon->id }}" data-loai="{{ $mon->loai_mon }}"
                                       {{ isset($daDangKy["{$hs->id}-{$mon->id}"]) ? 'checked' : '' }}>
                            </td>
                        @endforeach
                        <td class="text-center small dem"></td>
                    </tr>
                    @empty
                    <tr><td colspan="{{ $monHoc->count() + 2 }}" class="text-center text-muted py-4">Lớp chưa có học sinh</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <small class="text-muted">Ô đếm tô vàng khi số môn chọn khác quy định. Môn bắt buộc không cần đăng ký.</small>
            <button class="btn btn-primary"><i class="bi bi-save me-2"></i>Lưu đăng ký</button>
        </div>
    </div>
</form>
@endif
@endsection

@push('scripts')
<script>
const SO_LC = {{ $soLuaChon }}, SO_CD = {{ $soChuyenDe }};
function demLai() {
    document.querySelectorAll('tr[data-hs]').forEach(tr => {
        const lc = tr.querySelectorAll('.o-dang-ky[data-loai="Lựa chọn"]:checked').length;
        const cd = tr.querySelectorAll('.o-dang-ky[data-loai="Chuyên đề"]:checked').length;
        const o = tr.querySelector('.dem');
        o.textContent = `${lc} LC · ${cd} CĐ`;
        o.classList.toggle('bg-warning-subtle', lc !== SO_LC || (document.querySelector('.o-dang-ky[data-loai="Chuyên đề"]') && cd !== SO_CD));
    });
}
document.querySelectorAll('.o-dang-ky').forEach(c => c.addEventListener('change', demLai));
document.querySelectorAll('.chon-cot').forEach(c => c.addEventListener('change', () => {
    document.querySelectorAll(`.o-dang-ky[data-mon="${c.dataset.mon}"]`).forEach(o => o.checked = c.checked);
    demLai();
}));
demLai();
</script>
@endpush
