@extends('layouts.app')
@section('title', 'Soạn tin nhắn phụ huynh')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-pencil-square text-primary me-2"></i>Soạn tin nhắn gửi phụ huynh</h4>
    <a href="{{ route('tin-nhan.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Lịch sử</a>
</div>

@if($errors->any())
<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

<form method="POST" action="{{ route('tin-nhan.store') }}">
    @csrf
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card table-card">
                <div class="card-header bg-white py-3"><h6 class="fw-semibold mb-0">1. Nội dung</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Tiêu đề</label>
                            <input type="text" name="tieu_de" class="form-control" required maxlength="200" value="{{ old('tieu_de') }}"
                                   placeholder="VD: Thông báo họp phụ huynh cuối học kỳ I">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Loại tin</label>
                            <select name="loai" class="form-select">
                                @foreach(\App\Models\TinNhan::LOAI as $l)<option {{ old('loai') == $l ? 'selected' : '' }}>{{ $l }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nội dung</label>
                            <textarea name="noi_dung" id="noiDung" rows="7" class="form-control" required maxlength="2000"
                                      placeholder="Kính mời phụ huynh em {ho_ten} lớp {lop} đến dự họp...">{{ old('noi_dung') }}</textarea>
                            <div class="form-text">
                                Chèn thông tin riêng từng học sinh:
                                @foreach($bienThayThe as $bien => $moTa)
                                    <button type="button" class="btn btn-sm btn-light border py-0 px-1 me-1" title="{{ $moTa }}"
                                            onclick="chenBien('{{ $bien }}')">{{ $bien }}</button>
                                @endforeach
                                · <span id="demKyTu">0</span>/2000 ký tự
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Kênh gửi</label>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" checked disabled>
                                    <label class="form-check-label">Trang tra cứu phụ huynh <span class="text-muted small">(luôn có)</span></label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="kenh[]" value="email" id="kenhEmail" {{ in_array('email', old('kenh', ['email'])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="kenhEmail">Email phụ huynh</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="kenh[]" value="sms" id="kenhSms" {{ in_array('sms', old('kenh', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="kenhSms">SMS (số điện thoại mẹ, nếu không có thì cha)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card table-card">
                <div class="card-header bg-white py-3"><h6 class="fw-semibold mb-0">2. Gửi tới</h6></div>
                <div class="card-body">
                    @php $phamVi = old('pham_vi', $hocSinhChon ? 'hoc_sinh' : 'lop'); @endphp
                    <div class="mb-3">
                        @if($guiToanTruong)
                        <div class="form-check"><input class="form-check-input pham-vi" type="radio" name="pham_vi" value="toan_truong" id="pvTruong" {{ $phamVi == 'toan_truong' ? 'checked' : '' }}><label class="form-check-label" for="pvTruong">Toàn trường</label></div>
                        <div class="form-check"><input class="form-check-input pham-vi" type="radio" name="pham_vi" value="khoi" id="pvKhoi" {{ $phamVi == 'khoi' ? 'checked' : '' }}><label class="form-check-label" for="pvKhoi">Theo khối</label></div>
                        @endif
                        <div class="form-check"><input class="form-check-input pham-vi" type="radio" name="pham_vi" value="lop" id="pvLop" {{ $phamVi == 'lop' ? 'checked' : '' }}><label class="form-check-label" for="pvLop">Theo lớp</label></div>
                        <div class="form-check"><input class="form-check-input pham-vi" type="radio" name="pham_vi" value="hoc_sinh" id="pvHs" {{ $phamVi == 'hoc_sinh' ? 'checked' : '' }}><label class="form-check-label" for="pvHs">Chọn từng học sinh</label></div>
                    </div>

                    <div class="pv-khoi d-none mb-3">
                        <select name="khoi" class="form-select">
                            @foreach(['10', '11', '12'] as $k)<option value="{{ $k }}" {{ old('khoi') == $k ? 'selected' : '' }}>Khối {{ $k }}</option>@endforeach
                        </select>
                    </div>

                    <div class="pv-lop d-none">
                        @forelse($lopHoc as $lop)
                            <input type="checkbox" class="btn-check" name="lop_hoc_ids[]" value="{{ $lop->id }}" id="lop{{ $lop->id }}" {{ in_array($lop->id, old('lop_hoc_ids', [])) ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary btn-sm mb-1" for="lop{{ $lop->id }}">{{ $lop->ten_lop }} ({{ $lop->hocSinh->count() }})</label>
                        @empty
                            <div class="text-muted small">Không có lớp nào của năm học hiện tại.</div>
                        @endforelse
                    </div>

                    <div class="pv-hoc_sinh d-none" style="max-height: 340px; overflow-y: auto">
                        @foreach($lopHoc as $lop)
                            <div class="fw-semibold small text-primary mt-2">Lớp {{ $lop->ten_lop }}</div>
                            @foreach($lop->hocSinh as $hs)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="hoc_sinh_ids[]" value="{{ $hs->id }}" id="hs{{ $hs->id }}"
                                       {{ in_array($hs->id, old('hoc_sinh_ids', $hocSinhChon ? [$hocSinhChon->id] : [])) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="hs{{ $hs->id }}">
                                    {{ $hs->ho_ten }}
                                    @if(!$hs->email_phu_huynh)<i class="bi bi-envelope-slash text-muted" title="Chưa có email phụ huynh"></i>@endif
                                    @if(!$hs->so_dien_thoai_phu_huynh)<i class="bi bi-telephone-x text-muted" title="Chưa có SĐT phụ huynh"></i>@endif
                                </label>
                            </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <button class="btn btn-primary w-100" onclick="return confirm('Gửi tin nhắn tới phụ huynh?')"><i class="bi bi-send me-2"></i>Gửi tin nhắn</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
const noiDung = document.getElementById('noiDung');
function chenBien(bien) {
    const v = noiDung.selectionStart ?? noiDung.value.length;
    noiDung.value = noiDung.value.slice(0, v) + bien + noiDung.value.slice(noiDung.selectionEnd ?? v);
    noiDung.focus();
    demKyTu();
}
function demKyTu() { document.getElementById('demKyTu').textContent = noiDung.value.length; }
noiDung.addEventListener('input', demKyTu);
demKyTu();

function hienPhamVi() {
    const chon = document.querySelector('.pham-vi:checked')?.value;
    ['khoi', 'lop', 'hoc_sinh'].forEach(pv => document.querySelector('.pv-' + pv).classList.toggle('d-none', pv !== chon));
}
document.querySelectorAll('.pham-vi').forEach(r => r.addEventListener('change', hienPhamVi));
hienPhamVi();
</script>
@endpush
