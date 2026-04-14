@extends('layouts.app')
@section('title', 'Quản lý học sinh')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-person-lines-fill text-primary me-2"></i>Quản lý học sinh</h4>
        <p class="text-muted mb-0">Tổng cộng: {{ $hocSinh->total() }} học sinh</p>
    </div>
    @can('them-hoc-sinh')
    <a href="{{ route('hoc-sinh.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-2"></i>Thêm học sinh
    </a>
    @endcan
</div>

{{-- Form tìm kiếm --}}
<div class="card table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Tên, mã HS, CCCD..." 
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <select name="lop_hoc_id" class="form-select">
                    <option value="">-- Tất cả lớp --</option>
                    @foreach($lopHoc as $lop)
                        <option value="{{ $lop->id }}" {{ request('lop_hoc_id') == $lop->id ? 'selected' : '' }}>
                            {{ $lop->ten_lop }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="gioi_tinh" class="form-select">
                    <option value="">-- Giới tính --</option>
                    <option value="Nam" {{ request('gioi_tinh') == 'Nam' ? 'selected' : '' }}>Nam</option>
                    <option value="Nữ" {{ request('gioi_tinh') == 'Nữ' ? 'selected' : '' }}>Nữ</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="trang_thai" class="form-select">
                    <option value="">-- Trạng thái --</option>
                    <option value="Đang học" {{ request('trang_thai') == 'Đang học' ? 'selected' : '' }}>Đang học</option>
                    <option value="Đã tốt nghiệp" {{ request('trang_thai') == 'Đã tốt nghiệp' ? 'selected' : '' }}>Đã tốt nghiệp</option>
                    <option value="Thôi học" {{ request('trang_thai') == 'Thôi học' ? 'selected' : '' }}>Thôi học</option>
                    <option value="Chuyển trường" {{ request('trang_thai') == 'Chuyển trường' ? 'selected' : '' }}>Chuyển trường</option>
                </select>
            </div>
            <div class="col-md-2">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel"></i> Lọc
                    </button>
                    <a href="{{ route('hoc-sinh.index') }}" class="btn btn-outline-secondary">
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
                    <th>Mã HS</th>
                    <th>Họ và tên</th>
                    <th>Ngày sinh</th>
                    <th>Giới tính</th>
                    <th>Lớp</th>
                    <th>SĐT</th>
                    <th>Trạng thái</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($hocSinh as $hs)
                <tr>
                    <td>
                        <span class="badge bg-light text-dark fw-semibold">{{ $hs->ma_hoc_sinh }}</span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($hs->anh_the)
                                <img src="{{ Storage::url($hs->anh_the) }}" 
                                     class="rounded-circle" 
                                     style="width:36px;height:36px;object-fit:cover">
                            @else
                                <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center text-secondary fw-bold"
                                     style="width:36px;height:36px;font-size:14px">
                                    {{ strtoupper(substr($hs->ho_ten, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <div class="fw-semibold">{{ $hs->ho_ten }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $hs->ngay_sinh->format('d/m/Y') }}</td>
                    <td>{{ $hs->gioi_tinh }}</td>
                    <td>
                        @if($hs->lopHoc)
                            <span class="badge bg-primary bg-opacity-10 text-primary">{{ $hs->lopHoc->ten_lop }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $hs->so_dien_thoai ?? '—' }}</td>
                    <td>
                        @php
                            $colors = ['Đang học' => 'success', 'Đã tốt nghiệp' => 'info', 'Thôi học' => 'danger', 'Chuyển trường' => 'warning'];
                        @endphp
                        <span class="badge bg-{{ $colors[$hs->trang_thai] ?? 'secondary' }}">
                            {{ $hs->trang_thai }}
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('hoc-sinh.show', $hs) }}" 
                               class="btn btn-outline-info" title="Xem chi tiết">
                                <i class="bi bi-eye"></i>
                            </a>
                            @can('sua-hoc-sinh')
                            <a href="{{ route('hoc-sinh.edit', $hs) }}" 
                               class="btn btn-outline-warning" title="Chỉnh sửa">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endcan
                            @can('xoa-hoc-sinh')
                            <button class="btn btn-outline-danger" title="Xóa"
                                    onclick="confirmDelete('{{ route('hoc-sinh.destroy', $hs) }}', '{{ $hs->ho_ten }}')">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                        <span class="text-muted">Không tìm thấy học sinh nào</span>
                        @can('them-hoc-sinh')
                        <div class="mt-2">
                            <a href="{{ route('hoc-sinh.create') }}" class="btn btn-sm btn-primary">
                                Thêm học sinh đầu tiên
                            </a>
                        </div>
                        @endcan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($hocSinh->hasPages())
    <div class="card-footer bg-white">
        {{ $hocSinh->links() }}
    </div>
    @endif
</div>

{{-- Modal xác nhận xóa --}}
<form id="deleteForm" method="POST">
    @csrf @method('DELETE')
</form>

@push('scripts')
<script>
function confirmDelete(url, name) {
    if (confirm(`Bạn có chắc muốn xóa học sinh "${name}" không?\nDữ liệu sẽ được lưu trong thùng rác và có thể khôi phục.`)) {
        document.getElementById('deleteForm').action = url;
        document.getElementById('deleteForm').submit();
    }
}
</script>
@endpush
@endsection