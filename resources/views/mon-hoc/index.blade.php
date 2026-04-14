@extends('layouts.app')
@section('title', 'Quản lý môn học')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-book text-primary me-2"></i>Quản lý môn học
        </h4>
        <p class="text-muted mb-0">Tổng: {{ $monHoc->total() }} môn học</p>
    </div>
    <a href="{{ route('mon-hoc.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Thêm môn học
    </a>
</div>

{{-- Tìm kiếm --}}
<div class="card table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control"
                           placeholder="Tên môn hoặc mã môn..."
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="khoi" class="form-select">
                    <option value="">-- Tất cả khối --</option>
                    <option value="10"    {{ request('khoi')=='10'     ? 'selected':'' }}>Khối 10</option>
                    <option value="11"    {{ request('khoi')=='11'     ? 'selected':'' }}>Khối 11</option>
                    <option value="12"    {{ request('khoi')=='12'     ? 'selected':'' }}>Khối 12</option>
                    <option value="Tất cả" {{ request('khoi')=='Tất cả'? 'selected':'' }}>Tất cả khối</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-funnel"></i> Lọc
                </button>
                <a href="{{ route('mon-hoc.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Bảng danh sách --}}
<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Mã môn</th>
                    <th>Tên môn học</th>
                    <th class="text-center">Khối</th>
                    <th class="text-center">Số tiết/tuần</th>
                    <th class="text-center">Số bản ghi điểm</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($monHoc as $mon)
                <tr>
                    <td>
                        <span class="badge bg-light text-dark border fw-semibold">
                            {{ $mon->ma_mon }}
                        </span>
                    </td>
                    <td class="fw-semibold">{{ $mon->ten_mon }}</td>
                    <td class="text-center">
                        @php
                            $khoiColors = ['10'=>'primary','11'=>'success','12'=>'warning','Tất cả'=>'info'];
                        @endphp
                        <span class="badge bg-{{ $khoiColors[$mon->khoi] ?? 'secondary' }}">
                            {{ $mon->khoi == 'Tất cả' ? 'Tất cả' : 'Khối '.$mon->khoi }}
                        </span>
                    </td>
                    <td class="text-center">{{ $mon->so_tiet_tuan }} tiết</td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border">
                            {{ number_format($mon->diem_so_count) }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if($mon->is_active)
                            <span class="badge bg-success">Đang dạy</span>
                        @else
                            <span class="badge bg-secondary">Tạm ẩn</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('mon-hoc.edit', $mon) }}"
                               class="btn btn-outline-warning" title="Chỉnh sửa">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST"
                                  action="{{ route('mon-hoc.toggle', $mon) }}"
                                  class="d-inline">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="btn btn-outline-{{ $mon->is_active ? 'secondary' : 'success' }}"
                                        title="{{ $mon->is_active ? 'Tạm ẩn' : 'Kích hoạt' }}"
                                        onclick="return confirm('{{ $mon->is_active ? 'Tạm ẩn' : 'Kích hoạt' }} môn này?')">
                                    <i class="bi bi-{{ $mon->is_active ? 'eye-slash' : 'eye' }}"></i>
                                </button>
                            </form>
                            <button class="btn btn-outline-danger" title="Xóa"
                                    onclick="confirmDelete('{{ route('mon-hoc.destroy', $mon) }}', '{{ $mon->ten_mon }}')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                        <span class="text-muted">Chưa có môn học nào</span>
                        <div class="mt-2">
                            <a href="{{ route('mon-hoc.create') }}" class="btn btn-sm btn-primary">
                                Thêm môn học đầu tiên
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($monHoc->hasPages())
    <div class="card-footer bg-white">
        {{ $monHoc->links() }}
    </div>
    @endif
</div>

<form id="deleteForm" method="POST">
    @csrf @method('DELETE')
</form>

@push('scripts')
<script>
function confirmDelete(url, name) {
    if (confirm(`Xóa môn học "${name}"?\nChỉ xóa được nếu chưa có dữ liệu điểm liên quan.`)) {
        document.getElementById('deleteForm').action = url;
        document.getElementById('deleteForm').submit();
    }
}
</script>
@endpush
@endsection