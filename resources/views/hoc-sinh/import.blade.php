@extends('layouts.app')
@section('title', 'Nhập danh sách học sinh')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-spreadsheet text-success me-2"></i>Nhập danh sách học sinh</h4><p class="text-muted mb-0">Hỗ trợ Excel (.xlsx, .xls) và CSV, tối đa 5 MB</p></div>
    <a href="{{ route('hoc-sinh.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Danh sách học sinh</a>
</div>
@if($errors->any())<div class="alert alert-danger"><strong>Không thể tải tệp:</strong> {{ $errors->first() }}</div>@endif
@if(session('import_result'))
    @php($result = session('import_result'))
    <div class="alert {{ count($result['errors']) ? 'alert-warning' : 'alert-success' }}">Đã nhập thành công <strong>{{ $result['imported'] }}</strong> học sinh. @if(!empty($result['skipped'])) Bỏ qua <strong>{{ $result['skipped'] }}</strong> học sinh đã có trong lớp. @endif @if(count($result['errors'])) Có <strong>{{ count($result['errors']) }}</strong> dòng lỗi cần sửa. @endif</div>
    @if(count($result['errors']))
        <div class="card table-card mb-4"><div class="card-header bg-white fw-semibold">Các dòng cần sửa</div><div class="table-responsive"><table class="table table-sm table-hover mb-0">
            <thead><tr><th>Dòng</th><th>Học sinh</th><th>Lý do</th></tr></thead><tbody>
            @foreach($result['errors'] as $error)<tr><td>{{ $error['line'] }}</td><td>{{ $error['name'] }}</td><td class="text-danger">{{ $error['message'] }}</td></tr>@endforeach
            </tbody></table></div></div>
    @endif
@endif
<div class="row g-4">
    <div class="col-lg-7"><div class="card table-card"><div class="card-header bg-white py-3 fw-semibold">1. Chọn tệp dữ liệu</div><div class="card-body">
        <form method="POST" action="{{ route('hoc-sinh.import') }}" enctype="multipart/form-data">@csrf
            <label for="file" class="form-label">Tệp Excel hoặc CSV</label><input id="file" type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".xlsx,.xls,.csv" required>
            <div class="form-text">Mỗi học sinh nằm trên một dòng. Dòng đầu tiên phải là tên cột.</div><button class="btn btn-success mt-4"><i class="bi bi-cloud-arrow-up me-2"></i>Kiểm tra và nhập dữ liệu</button>
        </form>
    </div></div></div>
    <div class="col-lg-5"><div class="card table-card"><div class="card-header bg-white py-3 fw-semibold">2. Chuẩn bị đúng định dạng</div><div class="card-body">
        <p>Tải file mẫu, mở bằng Excel rồi thay dòng ví dụ bằng dữ liệu thật.</p><a href="{{ route('hoc-sinh.import.template') }}" class="btn btn-outline-success w-100 mb-3"><i class="bi bi-download me-2"></i>Tải file mẫu dùng cho Excel</a>
        <div class="small text-muted"><strong>Cột bắt buộc:</strong> ho_ten, ngay_sinh, gioi_tinh, ten_lop, nam_hoc — hoặc tên tiếng Việt: <em>Họ và tên, Ngày sinh, Giới tính, Lớp, Năm học</em>.<br>Ngày sinh: <code>dd/mm/yyyy</code> hoặc ô ngày của Excel. Giới tính: Nam, Nữ hoặc Khác. Tên lớp và năm học phải tồn tại (xem trang tính "Danh sách lớp" trong file mẫu). CCCD trùng sẽ không được nhập; học sinh đã có trong lớp sẽ được bỏ qua.</div>
    </div></div></div>
</div>
@endsection
