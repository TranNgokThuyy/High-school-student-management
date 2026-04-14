@extends('layouts.app')
@section('title', 'Quản lý lớp học')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-building text-primary me-2"></i>Quản lý lớp học</h4>
        <p class="text-muted mb-0">Tổng cộng: {{ $lopHoc->total() }} lớp học</p>
    </div>
    @can('them-lop-hoc')
    <a href="{{ route('lop-hoc.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Thêm lớp học
    </a>
    @endcan
</div>

{{-- Tìm kiếm --}}
<div class="card table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control"
                           placeholder="Tên lớp..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <select name="khoi" class="form-select">
                    <option value="">-- Tất cả khối --</option>
                    <option value="10" {{ request('khoi') == '10' ? 'selected' : '' }}>Khối 10</option>
                    <option value="11" {{ request('khoi') == '11' ? 'selected' : '' }}>Khối 11</option>
                    <option value="12" {{ request('khoi') == '12' ? 'selected' : '' }}>Khối 12</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="nam_hoc" class="form-select">
                    <option value="">-- Tất cả năm học --</option>
                    @foreach($namHoc as $nh)
                        <option value="{{ $nh }}" {{ request('nam_hoc') == $nh ? 'selected' : '' }}>
                            {{ $nh }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel"></i> Lọc
                    </button>
                    <a href="{{ route('lop-hoc.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
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
                    <th>Tên lớp</th>
                    <th class="text-center">Khối</th>
                    <th>Năm học</th>
                    <th>GVCN</th>
                    <th class="text-center">Sĩ số</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lopHoc as $lop)
                <tr>
                    <td>
                        <div class="fw-semibold fs-6">{{ $lop->ten_lop }}</div>
                        @if($lop->ghi_chu)
                            <small class="text-muted">{{ Str::limit($lop->ghi_chu, 40) }}</small>
                        @endif
                    </td>
                    <td class="text-center">
                        @php
                            $khoiColors = ['10' => 'primary', '11' => 'success', '12' => 'warning'];
                        @endphp
                        <span class="badge bg-{{ $khoiColors[$lop->khoi] ?? 'secondary' }} rounded-pill">
                            Khối {{ $lop->khoi }}
                        </span>
                    </td>
                    <td>{{ $lop->nam_hoc }}</td>
                    <td>
                        @if($lop->giaoVienChuNhiem)
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center text-success fw-bold"
                                     style="width:30px;height:30px;font-size:12px">
                                    {{ strtoupper(substr($lop->giaoVienChuNhiem->name, 0, 1)) }}
                                </div>
                                <span>{{ $lop->giaoVienChuNhiem->name }}</span>
                            </div>
                        @else
                            <span class="text-muted fst-italic">Chưa phân công</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border">
                            {{ $lop->hoc_sinh_count }} HS
                        </span>
                    </td>
                    <td class="text-center">
                        @if($lop->is_active)
                            <span class="badge bg-success">Đang hoạt động</span>
                        @else
                            <span class="badge bg-secondary">Ngừng hoạt động</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('lop-hoc.show', $lop) }}"
                               class="btn btn-outline-info" title="Xem chi tiết">
                                <i class="bi bi-eye"></i>
                            </a>
                            @can('sua-lop-hoc')
                            <a href="{{ route('lop-hoc.edit', $lop) }}"
                               class="btn btn-outline-warning" title="Chỉnh sửa">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endcan
                            @can('xoa-lop-hoc')
                            <button class="btn btn-outline-danger" title="Xóa"
                                    onclick="confirmDelete('{{ route('lop-hoc.destroy', $lop) }}', '{{ $lop->ten_lop }}', {{ $lop->hoc_sinh_count }})">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                        <span class="text-muted">Chưa có lớp học nào</span>
                        @can('them-lop-hoc')
                        <div class="mt-2">
                            <a href="{{ route('lop-hoc.create') }}" class="btn btn-sm btn-primary">
                                Tạo lớp học đầu tiên
                            </a>
                        </div>
                        @endcan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($lopHoc->hasPages())
    <div class="card-footer bg-white">
        {{ $lopHoc->links() }}
    </div>
    @endif
</div>

<form id="deleteForm" method="POST">
    @csrf @method('DELETE')
</form>

@push('scripts')
<script>
function confirmDelete(url, name, siSo) {
    if (siSo > 0) {
        alert(`Không thể xóa lớp "${name}" vì đang có ${siSo} học sinh!\nVui lòng chuyển học sinh sang lớp khác trước.`);
        return;
    }
    if (confirm(`Xóa lớp học "${name}"?\nHành động này không thể hoàn tác!`)) {
        document.getElementById('deleteForm').action = url;
        document.getElementById('deleteForm').submit();
    }
}
</script>
@endpush
@endsection