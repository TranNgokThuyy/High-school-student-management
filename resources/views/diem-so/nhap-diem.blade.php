@extends('layouts.app')
@section('title', 'Nhập điểm')

@push('styles')
<style>
.diem-input {
    max-width: 70px;
    text-align: center;
    padding: 4px 6px;
}
.diem-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13,110,253,.15);
}
.diem-input.is-invalid {
    border-color: #dc3545;
    background-color: #fff8f8;
}
tr:hover .diem-input {
    background-color: #f8f9ff;
}
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Nhập điểm số</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('diem-so.bang-diem') }}">Quản lý điểm</a>
                </li>
                <li class="breadcrumb-item active">Nhập điểm</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('diem-so.bang-diem', request()->all()) }}"
       class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Quay lại
    </a>
</div>

{{-- Thông tin đang nhập --}}
<div class="alert alert-info d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-info-circle fs-5 flex-shrink-0"></i>
    <div>
        Đang nhập điểm môn <strong>{{ $mon->ten_mon }}</strong>
        — Lớp <strong>{{ $lop->ten_lop }}</strong>
        — Học kỳ <strong>{{ $request->hoc_ky }}</strong>
        — Năm học <strong>{{ $request->nam_hoc }}</strong>
        ({{ $lop->hocSinh->count() }} học sinh)
    </div>
</div>

<form method="POST" action="{{ route('diem-so.luu-diem') }}">
    @csrf
    <input type="hidden" name="lop_hoc_id" value="{{ $request->lop_hoc_id }}">
    <input type="hidden" name="mon_hoc_id" value="{{ $request->mon_hoc_id }}">
    <input type="hidden" name="hoc_ky"     value="{{ $request->hoc_ky }}">
    <input type="hidden" name="nam_hoc"    value="{{ $request->nam_hoc }}">

    <div class="card table-card">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h6 class="fw-semibold mb-0">
                        Bảng nhập điểm — {{ $lop->ten_lop }} — {{ $mon->ten_mon }}
                    </h6>
                </div>
                <div class="col-auto">
                    <div class="alert alert-warning py-1 px-3 mb-0 small">
                        <i class="bi bi-calculator me-1"></i>
                        Công thức: <strong>ĐTB = (TX×1 + GK×2 + CK×3) / (số TX + 5)</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr class="table-light">
                        <th style="width:45px" class="text-center">STT</th>
                        <th style="min-width:160px">Họ và tên</th>

                        {{-- Điểm thường xuyên (hệ số 1) --}}
                        <th class="text-center" style="width:75px">
                            TX1
                            <div class="text-muted fw-normal" style="font-size:0.65rem">Hệ số 1</div>
                        </th>
                        <th class="text-center" style="width:75px">
                            TX2
                            <div class="text-muted fw-normal" style="font-size:0.65rem">Hệ số 1</div>
                        </th>
                        <th class="text-center" style="width:75px">
                            TX3
                            <div class="text-muted fw-normal" style="font-size:0.65rem">Hệ số 1</div>
                        </th>
                        <th class="text-center" style="width:75px">
                            TX4
                            <div class="text-muted fw-normal" style="font-size:0.65rem">Hệ số 1</div>
                        </th>

                        {{-- Giữa kỳ (hệ số 2) --}}
                        <th class="text-center table-warning" style="width:85px">
                            Giữa kỳ
                            <div class="text-muted fw-normal" style="font-size:0.65rem">Hệ số 2</div>
                        </th>

                        {{-- Cuối kỳ (hệ số 3) --}}
                        <th class="text-center table-danger" style="width:85px">
                            Cuối kỳ
                            <div class="text-muted fw-normal" style="font-size:0.65rem">Hệ số 3</div>
                        </th>

                        {{-- Điểm TB (tự tính) --}}
                        <th class="text-center table-success" style="width:80px">
                            ĐTB
                            <div class="text-muted fw-normal" style="font-size:0.65rem">Tự tính</div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lop->hocSinh->sortBy('ho_ten') as $index => $hs)
                        @php $d = $diemHienCo[$hs->id] ?? null; @endphp
                        <tr>
                            <td class="text-center text-muted">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-semibold">{{ $hs->ho_ten }}</div>
                                <small class="text-muted">{{ $hs->ma_hoc_sinh }}</small>
                            </td>

                            {{-- TX1 --}}
                            <td class="text-center">
                                <input type="number"
                                       name="diem[{{ $hs->id }}][diem_tx1]"
                                       class="form-control form-control-sm diem-input"
                                       value="{{ old("diem.{$hs->id}.diem_tx1", $d?->diem_tx1) }}"
                                       min="0" max="10" step="0.25"
                                       placeholder="—">
                            </td>

                            {{-- TX2 --}}
                            <td class="text-center">
                                <input type="number"
                                       name="diem[{{ $hs->id }}][diem_tx2]"
                                       class="form-control form-control-sm diem-input"
                                       value="{{ old("diem.{$hs->id}.diem_tx2", $d?->diem_tx2) }}"
                                       min="0" max="10" step="0.25"
                                       placeholder="—">
                            </td>

                            {{-- TX3 --}}
                            <td class="text-center">
                                <input type="number"
                                       name="diem[{{ $hs->id }}][diem_tx3]"
                                       class="form-control form-control-sm diem-input"
                                       value="{{ old("diem.{$hs->id}.diem_tx3", $d?->diem_tx3) }}"
                                       min="0" max="10" step="0.25"
                                       placeholder="—">
                            </td>

                            {{-- TX4 --}}
                            <td class="text-center">
                                <input type="number"
                                       name="diem[{{ $hs->id }}][diem_tx4]"
                                       class="form-control form-control-sm diem-input"
                                       value="{{ old("diem.{$hs->id}.diem_tx4", $d?->diem_tx4) }}"
                                       min="0" max="10" step="0.25"
                                       placeholder="—">
                            </td>

                            {{-- Giữa kỳ --}}
                            <td class="text-center table-warning bg-opacity-25">
                                <input type="number"
                                       name="diem[{{ $hs->id }}][diem_gk]"
                                       class="form-control form-control-sm diem-input border-warning"
                                       value="{{ old("diem.{$hs->id}.diem_gk", $d?->diem_gk) }}"
                                       min="0" max="10" step="0.25"
                                       placeholder="—">
                            </td>

                            {{-- Cuối kỳ --}}
                            <td class="text-center table-danger bg-opacity-10">
                                <input type="number"
                                       name="diem[{{ $hs->id }}][diem_ck]"
                                       class="form-control form-control-sm diem-input border-danger"
                                       value="{{ old("diem.{$hs->id}.diem_ck", $d?->diem_ck) }}"
                                       min="0" max="10" step="0.25"
                                       placeholder="—">
                            </td>

                            {{-- ĐTB hiển thị realtime --}}
                            <td class="text-center table-success bg-opacity-10">
                                <span class="fw-bold dtb-display text-success"
                                      id="dtb_{{ $hs->id }}">
                                    @if($d?->diem_trung_binh)
                                        {{ number_format($d->diem_trung_binh, 2) }}
                                    @else
                                        —
                                    @endif
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox d-block fs-2 mb-2"></i>
                                Lớp chưa có học sinh nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <small class="text-muted">
                <i class="bi bi-info-circle me-1"></i>
                Để trống nếu chưa có điểm &nbsp;|&nbsp;
                Thang điểm 10 &nbsp;|&nbsp;
                Bước nhập: 0.25
            </small>
            <div class="d-flex gap-2">
                <a href="{{ route('diem-so.bang-diem', request()->all()) }}"
                   class="btn btn-outline-secondary">
                    Hủy bỏ
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-floppy me-2"></i>Lưu điểm
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
// Lấy tất cả hàng học sinh
const rows = document.querySelectorAll('tbody tr');

rows.forEach(row => {
    const inputs = row.querySelectorAll('.diem-input');
    const hsId   = row.querySelector('input[name*="diem_tx1"]')
                      ?.name?.match(/\[(\d+)\]/)?.[1];
    if (!hsId) return;

    inputs.forEach(input => {
        input.addEventListener('input', () => {
            validateInput(input);
            tinhDTB(row, hsId);
        });
        // Tab sang ô kế tiếp
        input.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const allInputs = [...document.querySelectorAll('.diem-input')];
                const idx = allInputs.indexOf(input);
                if (idx < allInputs.length - 1) allInputs[idx + 1].focus();
            }
        });
    });
});

// Validate điểm 0-10
function validateInput(input) {
    const val = parseFloat(input.value);
    if (input.value !== '' && (isNaN(val) || val < 0 || val > 10)) {
        input.classList.add('is-invalid');
        input.value = '';
    } else {
        input.classList.remove('is-invalid');
    }
}

// Tính ĐTB realtime
function tinhDTB(row, hsId) {
    const get = name => {
        const el = row.querySelector(`input[name="diem[${hsId}][${name}]"]`);
        const v  = parseFloat(el?.value);
        return isNaN(v) ? null : v;
    };

    const tx = [get('diem_tx1'), get('diem_tx2'), get('diem_tx3'), get('diem_tx4')]
                   .filter(v => v !== null);
    const gk = get('diem_gk');
    const ck = get('diem_ck');

    const dtbEl = document.getElementById(`dtb_${hsId}`);
    if (!dtbEl) return;

    if (tx.length === 0 || gk === null || ck === null) {
        dtbEl.textContent = '—';
        dtbEl.className   = 'fw-bold dtb-display text-muted';
        return;
    }

    const tongTX = tx.reduce((a, b) => a + b, 0);
    const heSo   = tx.length + 2 + 3;
    const dtb    = (tongTX + gk * 2 + ck * 3) / heSo;
    const dtbRound = Math.round(dtb * 100) / 100;

    dtbEl.textContent = dtbRound.toFixed(2);
    dtbEl.className   = 'fw-bold dtb-display ' +
        (dtbRound >= 8.0 ? 'text-success' :
         dtbRound >= 5.0 ? 'text-primary' : 'text-danger');
}
</script>
@endpush