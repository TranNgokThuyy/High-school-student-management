@extends('layouts.app')
@section('title', 'Báo cáo & Thống kê')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-bar-chart-line text-primary me-2"></i>Báo cáo & Thống kê
        </h4>
        <p class="text-muted mb-0">Xuất báo cáo điểm số, hạnh kiểm học sinh</p>
    </div>
</div>

@role('quan-tri-vien')
<div class="card table-card mb-4">
    <div class="card-body d-flex flex-wrap align-items-center gap-3">
        <div>
            <div class="fw-semibold"><i class="bi bi-database-down text-success me-1"></i>Xuất dữ liệu toàn trường (nộp / đối chiếu với Sở)</div>
            <div class="small text-muted">Hồ sơ học sinh, kết quả cuối năm, điểm các môn, biến động, thống kê theo khối — tệp tổng hợp, đối chiếu cột với mẫu của Sở trước khi nộp.</div>
        </div>
        <form method="GET" action="{{ route('bao-cao.xuat-du-lieu') }}" class="d-flex gap-2 ms-auto">
            <input type="text" name="nam_hoc" class="form-control" style="width:130px" pattern="\d{4}-\d{4}" required
                   value="{{ \App\Support\NamHoc::hienTai() }}">
            <button class="btn btn-success"><i class="bi bi-file-earmark-excel me-1"></i>Xuất Excel</button>
        </form>
    </div>
</div>
@endrole

<div class="row g-4">
    {{-- Card báo cáo điểm --}}
    <div class="col-lg-8">
        <div class="card table-card">
            <div class="card-header bg-white py-3">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-journal-text text-primary me-2"></i>Báo cáo điểm theo lớp
                </h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('bao-cao.diem-lop') }}" id="formBaoCao">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Lớp học <span class="text-danger">*</span>
                            </label>
                            <select name="lop_hoc_id" class="form-select" required>
                                <option value="">-- Chọn lớp --</option>
                                @foreach($lopHoc as $lop)
                                    <option value="{{ $lop->id }}">
                                        {{ $lop->ten_lop }} ({{ $lop->nam_hoc }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                Học kỳ <span class="text-danger">*</span>
                            </label>
                            <select name="hoc_ky" class="form-select" required>
                                <option value="1">Học kỳ 1</option>
                                <option value="2">Học kỳ 2</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                Năm học <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nam_hoc" class="form-control"
                                   value="{{ \App\Support\NamHoc::hienTai() }}"
                                   placeholder="2024-2025" required>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-eye me-2"></i>Xem báo cáo
                        </button>
                        <button type="submit"
                                formaction="{{ route('bao-cao.xuat-pdf') }}"
                                class="btn btn-danger">
                            <i class="bi bi-file-pdf me-2"></i>Xuất PDF
                        </button>
            
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Hướng dẫn --}}
    <div class="col-lg-4">
        <div class="card table-card h-100 border-start border-4 border-primary">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">
                    <i class="bi bi-lightbulb text-warning me-2"></i>Hướng dẫn
                </h6>
                <ul class="list-unstyled small text-muted">
                    <li class="mb-2">
                        <i class="bi bi-eye text-primary me-2"></i>
                        <strong>Xem báo cáo</strong>: Hiển thị bảng điểm trực tiếp trên màn hình
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-file-pdf text-danger me-2"></i>
                        <strong>Xuất PDF</strong>: Tải file PDF để in ấn hoặc lưu trữ
                    </li>
                
                </ul>
                <hr>
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Báo cáo bao gồm điểm trung bình tất cả các môn theo học kỳ đã chọn.
                </small>
            </div>
        </div>
    </div>
</div>
@endsection