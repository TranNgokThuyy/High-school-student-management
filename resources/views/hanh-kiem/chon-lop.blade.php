@extends('layouts.app')
@section('title', 'Nhập hạnh kiểm')
{{-- Thêm vào đầu @section('content'), sau thẻ div đầu tiên --}}
@if(auth()->user()->hasRole('giao-vien-chu-nhiem'))
<div class="alert alert-info d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-info-circle-fill flex-shrink-0"></i>
    <div>
        Bạn chỉ có thể nhập hạnh kiểm cho <strong>lớp mình chủ nhiệm</strong>.
        Danh sách bên dưới đã được lọc theo phân công của bạn.
    </div>
</div>
@endif
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-clipboard-check text-primary me-2"></i>Nhập hạnh kiểm & Chuyên cần
        </h4>
        <p class="text-muted mb-0">Chọn lớp và học kỳ để bắt đầu nhập</p>
    </div>
    <a href="{{ route('hanh-kiem.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Quay lại
    </a>
</div>

<div class="row g-4">

    {{-- Form chọn lớp --}}
    <div class="col-lg-5">
        <div class="card table-card">
            <div class="card-header bg-white py-3">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-funnel text-primary me-2"></i>Chọn lớp & Học kỳ
                </h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('hanh-kiem.form') }}">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Lớp học <span class="text-danger">*</span>
                        </label>
                        <select name="lop_hoc_id" class="form-select" required id="selectLop">
                            <option value="">-- Chọn lớp --</option>
                            @foreach($lopHoc as $lop)
                                <option value="{{ $lop->id }}"
                                        data-siso="{{ $lop->hoc_sinh_count }}">
                                    {{ $lop->ten_lop }}
                                    ({{ $lop->nam_hoc }})
                                    — {{ $lop->hoc_sinh_count }} HS
                                </option>
                            @endforeach
                        </select>
                        {{-- Cảnh báo lớp rỗng --}}
                        <div id="warnEmpty" class="alert alert-warning py-2 px-3 mt-2 small d-none">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Lớp này chưa có học sinh!
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Học kỳ <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex gap-2">
                            @foreach(['1'=>'Học kỳ 1','2'=>'Học kỳ 2'] as $val => $label)
                            <div class="form-check flex-grow-1 border rounded-2 p-0 overflow-hidden">
                                <input class="btn-check" type="radio"
                                       name="hoc_ky" id="hk_{{ $val }}"
                                       value="{{ $val }}"
                                       {{ $val == '1' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary w-100 rounded-2"
                                       for="hk_{{ $val }}">
                                    {{ $label }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Năm học <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nam_hoc" class="form-control"
                               value="{{ $namHocHienTai }}"
                               placeholder="2024-2025" required>
                        <div class="form-text">Định dạng: YYYY-YYYY</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="btnNhap">
                        <i class="bi bi-pencil-square me-2"></i>Bắt đầu nhập hạnh kiểm
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Danh sách lớp nhanh --}}
    <div class="col-lg-7">
        <div class="card table-card">
            <div class="card-header bg-white py-3">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-grid text-success me-2"></i>Chọn nhanh lớp học
                </h6>
            </div>
            <div class="card-body">
                @php
                    $lopTheoKhoi = $lopHoc->groupBy('khoi');
                    $khoiColors  = ['10'=>'primary','11'=>'success','12'=>'warning'];
                @endphp

                @foreach($lopTheoKhoi as $khoi => $dsLop)
                <div class="mb-3">
                    <h6 class="text-muted fw-semibold small mb-2">
                        <span class="badge bg-{{ $khoiColors[$khoi] ?? 'secondary' }} me-1">
                            Khối {{ $khoi }}
                        </span>
                    </h6>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($dsLop as $lop)
                        <button type="button"
                                class="btn btn-outline-{{ $khoiColors[$khoi] ?? 'secondary' }} btn-sm btn-chon-lop"
                                data-id="{{ $lop->id }}"
                                data-ten="{{ $lop->ten_lop }}"
                                data-siso="{{ $lop->hoc_sinh_count }}"
                                title="{{ $lop->ten_lop }} — {{ $lop->hoc_sinh_count }} học sinh">
                            {{ $lop->ten_lop }}
                            <span class="badge bg-light text-dark ms-1">
                                {{ $lop->hoc_sinh_count }}
                            </span>
                        </button>
                        @endforeach
                    </div>
                </div>
                @endforeach

                @if($lopHoc->isEmpty())
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox d-block fs-2 mb-2"></i>
                    Chưa có lớp học nào.<br>
                    <a href="{{ route('lop-hoc.create') }}" class="btn btn-sm btn-primary mt-2">
                        Tạo lớp học
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
// Click nhanh vào lớp bên phải → tự điền vào select bên trái
document.querySelectorAll('.btn-chon-lop').forEach(btn => {
    btn.addEventListener('click', function () {
        const id    = this.dataset.id;
        const ten   = this.dataset.ten;
        const siSo  = parseInt(this.dataset.siso);
        const sel   = document.getElementById('selectLop');

        sel.value = id;

        // Highlight nút được chọn
        document.querySelectorAll('.btn-chon-lop').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        // Cảnh báo lớp rỗng
        const warn = document.getElementById('warnEmpty');
        if (siSo === 0) {
            warn.classList.remove('d-none');
            document.getElementById('btnNhap').disabled = true;
        } else {
            warn.classList.add('d-none');
            document.getElementById('btnNhap').disabled = false;
        }

        // Scroll lên form
        sel.scrollIntoView({ behavior: 'smooth', block: 'center' });
        sel.focus();
    });
});

// Kiểm tra khi chọn từ select
document.getElementById('selectLop').addEventListener('change', function () {
    const opt   = this.options[this.selectedIndex];
    const siSo  = parseInt(opt.dataset.siso || 0);
    const warn  = document.getElementById('warnEmpty');
    const btn   = document.getElementById('btnNhap');

    if (this.value && siSo === 0) {
        warn.classList.remove('d-none');
        btn.disabled = true;
    } else {
        warn.classList.add('d-none');
        btn.disabled = false;
    }

    // Bỏ highlight các nút nhanh
    document.querySelectorAll('.btn-chon-lop').forEach(b => {
        b.classList.toggle('active', b.dataset.id === this.value);
    });
});
</script>
@endpush
@endsection