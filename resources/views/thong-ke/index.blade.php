@extends('layouts.app')
@section('title', 'Thống kê')

@push('styles')
<style>
.tk-chart { --surface: #ffffff; --ink-2: #52514e; --muted: #898781; --grid: #e1e0d9; }
.tk-legend { display: flex; flex-wrap: wrap; gap: 12px; font-size: .8rem; color: var(--ink-2); }
.tk-legend i { display: inline-block; width: 10px; height: 10px; border-radius: 2px; margin-right: 5px; vertical-align: -1px; }
.tk-row { display: grid; grid-template-columns: 92px 1fr 78px; align-items: center; gap: 10px; margin: 7px 0; }
.tk-label { font-size: .85rem; color: var(--ink-2); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tk-track { display: flex; height: 22px; border-left: 1px solid #c3c2b7; }
.tk-seg { height: 100%; border-right: 2px solid var(--surface); font-size: .72rem; font-weight: 600; display: flex; align-items: center;
          justify-content: center; overflow: hidden; cursor: default; min-width: 3px; }
.tk-seg:last-child { border-right: 0; border-radius: 0 4px 4px 0; }
.tk-seg:hover, .tk-seg:focus { filter: brightness(1.08); outline: 2px solid #0b0b0b33; outline-offset: 1px; }
.tk-end { font-size: .8rem; color: var(--ink-2); font-variant-numeric: tabular-nums; }
.tk-tip { position: fixed; z-index: 2000; pointer-events: none; background: #0b0b0b; color: #fff; font-size: .78rem;
          padding: 5px 8px; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,.2); display: none; max-width: 260px; }
.tk-so { font-size: 1.9rem; font-weight: 700; color: #0b0b0b; line-height: 1.1; }
@media (max-width: 576px) { .tk-row { grid-template-columns: 70px 1fr 56px; gap: 6px; } }
</style>
@endpush

@section('content')
@php
    // Thang thứ bậc một màu (xanh): Tốt đậm nhất → Chưa đạt nhạt nhất (đã kiểm tra bằng validator)
    $mauMuc = ['Tốt' => '#184f95', 'Khá' => '#2a78d6', 'Đạt' => '#5598e7', 'Chưa đạt' => '#86b6ef'];
    $chuDamMuc = ['Chưa đạt'];
    $chiLay = fn ($dem) => array_intersect_key($dem ?? [], $mauMuc);
@endphp
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-graph-up text-primary me-2"></i>Thống kê</h4>
    <form method="GET">
        <select name="nam_hoc" class="form-select" onchange="this.form.submit()">
            @foreach($dsNamHoc->reverse() as $nh)<option value="{{ $nh }}" {{ $namHoc == $nh ? 'selected' : '' }}>Năm học {{ $nh }}</option>@endforeach
        </select>
    </form>
</div>

<div class="row g-3 mb-4">
    @foreach([
        ['Học sinh đang học', $chiSo['hoc_sinh'], ''],
        ['Đủ điều kiện lên lớp', $chiSo['len_lop'], '%'],
        ['Tỷ lệ chuyên cần', $chiSo['chuyen_can'], '%'],
        ['Tỷ lệ thu học phí', $chiSo['thu_hoc_phi'], '%'],
    ] as [$nhan, $so, $dv])
    <div class="col-6 col-lg-3">
        <div class="card stat-card h-100"><div class="card-body">
            <div class="text-muted small mb-1">{{ $nhan }}</div>
            <div class="tk-so">{{ is_null($so) ? '—' : number_format($so, fmod($so, 1) ? 1 : 0, ',', '.') . $dv }}</div>
        </div></div>
    </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card table-card h-100"><div class="card-body">
            @include('thong-ke._thanh-ngang', ['id' => 'tkHocTap', 'tieuDe' => 'Kết quả học tập cả năm theo khối',
                'hang' => collect($theoKhoi)->mapWithKeys(fn ($k, $khoi) => ["Khối {$khoi}" => $chiLay($k['hoc_tap'] ?? [])])->all(),
                'chuoi' => $mauMuc, 'phanTram' => true, 'chuDam' => $chuDamMuc])
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card table-card h-100"><div class="card-body">
            @include('thong-ke._thanh-ngang', ['id' => 'tkRenLuyen', 'tieuDe' => 'Kết quả rèn luyện cả năm theo khối',
                'hang' => collect($theoKhoi)->mapWithKeys(fn ($k, $khoi) => ["Khối {$khoi}" => $chiLay($k['ren_luyen'] ?? [])])->all(),
                'chuoi' => $mauMuc, 'phanTram' => true, 'chuDam' => $chuDamMuc])
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card table-card h-100"><div class="card-body">
            @include('thong-ke._thanh-ngang', ['id' => 'tkSiSo', 'tieuDe' => 'Sĩ số theo khối',
                'hang' => $siSo->mapWithKeys(fn ($g, $khoi) => ["Khối {$khoi}" => $g])->all(),
                'chuoi' => ['Nam' => '#2a78d6', 'Nữ' => '#eb6834']])
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card table-card h-100"><div class="card-body">
            @include('thong-ke._thanh-ngang', ['id' => 'tkQuaNam', 'tieuDe' => 'Kết quả học tập toàn trường qua các năm',
                'hang' => collect($quaCacNam)->map($chiLay)->all(), 'chuoi' => $mauMuc, 'phanTram' => true, 'chuDam' => $chuDamMuc])
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card table-card h-100"><div class="card-body">
            @include('thong-ke._thanh-ngang', ['id' => 'tkChuyenCan', 'tieuDe' => 'Tỷ lệ chuyên cần theo lớp (từ điểm danh)',
                'hang' => collect($chuyenCan)->map(fn ($v) => ['Chuyên cần' => $v])->all(),
                'chuoi' => ['Chuyên cần' => '#2a78d6'], 'max' => 100, 'donVi' => '%'])
        </div></div>
    </div>
    @if($hocPhi)
    <div class="col-lg-6">
        <div class="card table-card h-100"><div class="card-body">
            @include('thong-ke._thanh-ngang', ['id' => 'tkHocPhi', 'tieuDe' => 'Thu học phí & các khoản theo lớp',
                'hang' => $hocPhi, 'chuoi' => ['Đã thu' => '#2a78d6', 'Còn nợ' => '#eb6834'], 'donVi' => ' đ'])
        </div></div>
    </div>
    @endif
</div>
<p class="small text-muted mt-3 mb-0">Kết quả học tập / rèn luyện là kết quả cả năm lần đầu (trước kiểm tra lại, rèn luyện hè); học sinh chưa đủ điểm không tính vào biểu đồ.</p>
<div class="tk-tip" id="tkTip" role="tooltip"></div>
@endsection

@push('scripts')
<script>
// Tooltip khi rê chuột / focus bàn phím vào từng đoạn thanh
(() => {
    const tip = document.getElementById('tkTip');
    const hien = (el, x, y) => { tip.textContent = el.dataset.tip; tip.style.display = 'block'; tip.style.left = (x + 12) + 'px'; tip.style.top = (y + 12) + 'px'; };
    document.querySelectorAll('.tk-seg').forEach(el => {
        el.addEventListener('mousemove', e => hien(el, e.clientX, e.clientY));
        el.addEventListener('mouseleave', () => tip.style.display = 'none');
        el.addEventListener('focus', () => { const r = el.getBoundingClientRect(); hien(el, r.left, r.bottom); });
        el.addEventListener('blur', () => tip.style.display = 'none');
    });
})();
</script>
@endpush
