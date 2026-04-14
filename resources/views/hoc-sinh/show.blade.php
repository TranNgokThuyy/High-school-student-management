@extends('layouts.app')
@section('title', 'Chi tiết học sinh')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-person-lines-fill text-primary me-2"></i>{{ $hocSinh->ho_ten }}
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('hoc-sinh.index') }}">Quản lý học sinh</a></li>
                <li class="breadcrumb-item active">{{ $hocSinh->ho_ten }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        @can('sua-hoc-sinh')
        <a href="{{ route('hoc-sinh.edit', $hocSinh) }}" class="btn btn-warning">
            <i class="bi bi-pencil me-2"></i>Chỉnh sửa
        </a>
        @endcan
        <a href="{{ route('hoc-sinh.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Quay lại
        </a>
    </div>
</div>

<div class="row g-4">

    {{-- CỘT TRÁI --}}
    <div class="col-lg-4">

        {{-- Ảnh thẻ + trạng thái --}}
        <div class="card table-card mb-4">
            <div class="card-body text-center py-4">
                @if($hocSinh->anh_the)
                    <img src="{{ Storage::url($hocSinh->anh_the) }}"
                         class="rounded-3 border shadow-sm mb-3"
                         style="width:140px;height:175px;object-fit:cover">
                @else
                    <div class="bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center mx-auto mb-3"
                         style="width:140px;height:175px">
                        <i class="bi bi-person-bounding-box text-primary" style="font-size:60px"></i>
                    </div>
                @endif

                <h5 class="fw-bold mb-1">{{ $hocSinh->ho_ten }}</h5>
                <p class="text-muted mb-2 small">{{ $hocSinh->ma_hoc_sinh }}</p>

                @php
                    $trangThaiColors = [
                        'Đang học'       => 'success',
                        'Đã tốt nghiệp'  => 'info',
                        'Thôi học'       => 'danger',
                        'Chuyển trường'  => 'warning',
                    ];
                    $mauTrangThai = $trangThaiColors[$hocSinh->trang_thai] ?? 'secondary';
                @endphp

                <span class="badge bg-{{ $mauTrangThai }} fs-6 px-3 py-2">
                    {{ $hocSinh->trang_thai }}
                </span>

                @if($hocSinh->lopHoc)
                <div class="mt-3 p-2 bg-light rounded-2">
                    <small class="text-muted d-block">Lớp hiện tại</small>
                    <strong class="text-primary">{{ $hocSinh->lopHoc->ten_lop }}</strong>
                    <small class="text-muted">({{ $hocSinh->lopHoc->nam_hoc }})</small>
                </div>
                @endif
            </div>
        </div>

        {{-- Thông tin gia đình --}}
        <div class="card table-card">
            <div class="card-header bg-white py-3">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-house text-success me-2"></i>Thông tin gia đình
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-borderless mb-0 small">
                    <tr class="border-bottom">
                        <td colspan="2" class="fw-semibold text-muted bg-light py-2 px-3">
                            <i class="bi bi-person me-1"></i> Bố
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3" style="width:40%">Họ tên</td>
                        <td class="fw-semibold">{{ $hocSinh->ho_ten_cha ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Nghề nghiệp</td>
                        <td>{{ $hocSinh->nghe_nghiep_cha ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">SĐT</td>
                        <td>
                            @if($hocSinh->so_dien_thoai_cha)
                                <a href="tel:{{ $hocSinh->so_dien_thoai_cha }}" class="text-decoration-none">
                                    {{ $hocSinh->so_dien_thoai_cha }}
                                </a>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                    <tr class="border-bottom border-top">
                        <td colspan="2" class="fw-semibold text-muted bg-light py-2 px-3">
                            <i class="bi bi-person me-1"></i> Mẹ
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Họ tên</td>
                        <td class="fw-semibold">{{ $hocSinh->ho_ten_me ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Nghề nghiệp</td>
                        <td>{{ $hocSinh->nghe_nghiep_me ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">SĐT</td>
                        <td>
                            @if($hocSinh->so_dien_thoai_me)
                                <a href="tel:{{ $hocSinh->so_dien_thoai_me }}" class="text-decoration-none">
                                    {{ $hocSinh->so_dien_thoai_me }}
                                </a>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

    </div>{{-- end col-lg-4 --}}

    {{-- CỘT PHẢI --}}
    <div class="col-lg-8">

        {{-- Thông tin cá nhân --}}
        <div class="card table-card mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-person-vcard text-primary me-2"></i>Thông tin cá nhân
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Mã học sinh</small>
                        <span class="fw-semibold">{{ $hocSinh->ma_hoc_sinh }}</span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Ngày sinh</small>
                        <span class="fw-semibold">{{ $hocSinh->ngay_sinh->format('d/m/Y') }}</span>
                        <small class="text-muted">({{ $hocSinh->tuoi }} tuổi)</small>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Giới tính</small>
                        <span class="fw-semibold">{{ $hocSinh->gioi_tinh }}</span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Dân tộc</small>
                        <span>{{ $hocSinh->dan_toc ?? '—' }}</span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Tôn giáo</small>
                        <span>{{ $hocSinh->ton_giao ?? '—' }}</span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">CCCD/CMND</small>
                        <span>{{ $hocSinh->cccd ?? '—' }}</span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Số điện thoại</small>
                        @if($hocSinh->so_dien_thoai)
                            <a href="tel:{{ $hocSinh->so_dien_thoai }}" class="text-decoration-none">
                                {{ $hocSinh->so_dien_thoai }}
                            </a>
                        @else
                            <span>—</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Email</small>
                        @if($hocSinh->email)
                            <a href="mailto:{{ $hocSinh->email }}" class="text-decoration-none">
                                {{ $hocSinh->email }}
                            </a>
                        @else
                            <span>—</span>
                        @endif
                    </div>
                    <div class="col-12">
                        <small class="text-muted d-block">Địa chỉ thường trú</small>
                        <span>{{ $hocSinh->dia_chi_thuong_tru ?? '—' }}</span>
                    </div>
                    @if($hocSinh->dia_chi_tam_tru)
                    <div class="col-12">
                        <small class="text-muted d-block">Địa chỉ tạm trú</small>
                        <span>{{ $hocSinh->dia_chi_tam_tru }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Bảng điểm --}}
        @if($hocSinh->diemSo->count() > 0)
        <div class="card table-card mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-journal-text text-info me-2"></i>
                    Điểm số ({{ $hocSinh->diemSo->count() }} bản ghi)
                </h6>
                @can('xem-diem')
                @if($hocSinh->lop_hoc_id)
                <a href="{{ route('diem-so.bang-diem', ['lop_hoc_id' => $hocSinh->lop_hoc_id]) }}"
                   class="btn btn-sm btn-outline-info">
                    <i class="bi bi-eye me-1"></i>Xem tất cả
                </a>
                @endif
                @endcan
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 small">
                    <thead>
                        <tr>
                            <th>Môn học</th>
                            <th class="text-center">Học kỳ</th>
                            <th class="text-center">TX</th>
                            <th class="text-center">GK</th>
                            <th class="text-center">CK</th>
                            <th class="text-center">Điểm TB</th>
                            <th class="text-center">Xếp loại</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hocSinh->diemSo->sortByDesc('created_at')->take(10) as $diem)
                        <tr>
                            <td class="fw-semibold">{{ $diem->monHoc->ten_mon }}</td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">
                                    HK{{ $diem->hoc_ky }}/{{ $diem->nam_hoc }}
                                </span>
                            </td>
                            <td class="text-center text-muted small">
                                @php
                                    $dsTX = collect([
                                        $diem->diem_tx1,
                                        $diem->diem_tx2,
                                        $diem->diem_tx3,
                                        $diem->diem_tx4,
                                    ])->filter()->values();
                                @endphp
                                {{ $dsTX->isNotEmpty() ? $dsTX->implode(', ') : '—' }}
                            </td>
                            <td class="text-center">{{ $diem->diem_gk ?? '—' }}</td>
                            <td class="text-center">{{ $diem->diem_ck ?? '—' }}</td>
                            <td class="text-center">
                                @if(!is_null($diem->diem_trung_binh))
                                    <strong class="{{ $diem->diem_trung_binh >= 5 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($diem->diem_trung_binh, 2) }}
                                    </strong>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(!is_null($diem->diem_trung_binh))
                                    @php
                                        $xepLoai = $diem->xep_loai;
                                        $mauXepLoai = [
                                            'Giỏi'       => 'success',
                                            'Khá'        => 'primary',
                                            'Trung bình' => 'warning',
                                            'Yếu'        => 'danger',
                                            'Kém'        => 'dark',
                                        ];
                                        $mau = $mauXepLoai[$xepLoai] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $mau }}">{{ $xepLoai }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="card table-card mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-journal-text text-info me-2"></i>Điểm số
                </h6>
            </div>
            <div class="card-body text-center py-4 text-muted">
                <i class="bi bi-inbox d-block fs-2 mb-2"></i>
                Chưa có dữ liệu điểm số
            </div>
        </div>
        @endif

        {{-- Hạnh kiểm --}}
        @if($hocSinh->hanhKiem->count() > 0)
        <div class="card table-card">
            <div class="card-header bg-white py-3">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-clipboard-check text-warning me-2"></i>
                    Hạnh kiểm & Chuyên cần
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 small">
                    <thead>
                        <tr>
                            <th>Năm học</th>
                            <th class="text-center">Học kỳ</th>
                            <th class="text-center">Hạnh kiểm</th>
                            <th class="text-center">Tổng buổi</th>
                            <th class="text-center">Vắng CP</th>
                            <th class="text-center">Vắng KP</th>
                            <th class="text-center">Tỷ lệ CC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hocSinh->hanhKiem->sortByDesc('nam_hoc') as $hk)
                        <tr>
                            <td>{{ $hk->nam_hoc }}</td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">
                                    {{ $hk->hoc_ky }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($hk->xep_loai_hanh_kiem)
                                    @php
                                        $hkColors = [
                                            'Tốt'        => 'success',
                                            'Khá'        => 'primary',
                                            'Trung bình' => 'warning',
                                            'Yếu'        => 'danger',
                                        ];
                                        $hkMau = $hkColors[$hk->xep_loai_hanh_kiem] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $hkMau }}">
                                        {{ $hk->xep_loai_hanh_kiem }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center fw-semibold">
                                {{ $hk->so_buoi_hoc }}
                            </td>
                            <td class="text-center text-warning fw-semibold">
                                {{ $hk->so_buoi_vang_co_phep > 0 ? $hk->so_buoi_vang_co_phep : '—' }}
                            </td>
                            <td class="text-center text-danger fw-semibold">
                                {{ $hk->so_buoi_vang_khong_phep > 0 ? $hk->so_buoi_vang_khong_phep : '—' }}
                            </td>
                            <td class="text-center">
                                @php $tyLe = $hk->ty_le_chuyen_can; @endphp
                                <span class="fw-semibold {{ $tyLe >= 80 ? 'text-success' : 'text-danger' }}">
                                    {{ $tyLe }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="card table-card">
            <div class="card-header bg-white py-3">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-clipboard-check text-warning me-2"></i>
                    Hạnh kiểm & Chuyên cần
                </h6>
            </div>
            <div class="card-body text-center py-4 text-muted">
                <i class="bi bi-inbox d-block fs-2 mb-2"></i>
                Chưa có dữ liệu hạnh kiểm
            </div>
        </div>
        @endif

    </div>{{-- end col-lg-8 --}}

</div>{{-- end row --}}
@endsection