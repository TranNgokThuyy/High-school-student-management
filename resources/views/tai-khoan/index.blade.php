@extends('layouts.app')
@section('title', 'Quản lý tài khoản')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-people-fill text-primary me-2"></i>Quản lý tài khoản</h4>
        <p class="text-muted mb-0">Tổng cộng: {{ $users->total() }} tài khoản</p>
    </div>
    @can('them-tai-khoan')
    <a href="{{ route('tai-khoan.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-2"></i>Thêm tài khoản
    </a>
    @endcan
</div>

{{-- Tìm kiếm --}}
<div class="card table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control"
                           placeholder="Tên hoặc email..."
                           value="{{ request('search') }}">
                </div>
            </div>
           <div class="col-md-3">
    <select name="role" class="form-select">
        <option value="">-- Tất cả vai trò --</option>
        <option value="quan-tri-vien"
            {{ request('role') === 'quan-tri-vien' ? 'selected' : '' }}>
            Quản trị viên
        </option>
        <option value="giao-vien-chu-nhiem"
            {{ request('role') === 'giao-vien-chu-nhiem' ? 'selected' : '' }}>
            Giáo viên
        </option>
    </select>
</div>
            <div class="col-md-2">
                <select name="is_active" class="form-select">
                    <option value="">-- Trạng thái --</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Bị vô hiệu hóa</option>
                </select>
            </div>
            <div class="col-md-2">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel"></i> Lọc
                    </button>
                    <a href="{{ route('tai-khoan.index') }}" class="btn btn-outline-secondary">
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
                    <th>Tên</th>
                    <th>Email</th>
                    <th>Số điện thoại</th>
                    <th>Vai trò</th>
                    <th>Đăng nhập lần cuối</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                 style="width:38px;height:38px;font-size:14px;flex-shrink:0">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $user->name }}</div>
                                @if($user->id === auth()->id())
                                    <small class="text-success"><i class="bi bi-check-circle"></i> Tài khoản của bạn</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->phone ?? '—' }}</td>
                    <td>
                        @foreach($user->getRoleNames() as $role)
                            @php
                                $roleLabels = [
                                    'quan-tri-vien' => ['label' => 'Quản trị viên', 'color' => 'danger'],
                                    'giao-vien-chu-nhiem' => ['label' => 'Giáo viên', 'color' => 'warning'],
                                ];
                                $r = $roleLabels[$role] ?? ['label' => $role, 'color' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $r['color'] }}">
            {{ $r['label'] }}
            @if($role === 'quan-tri-vien')
                <i class="bi bi-shield-fill ms-1" title="Vai trò được bảo vệ"></i>
            @endif
        </span>
                        @endforeach
                    </td>
                    <td>
                        @if($user->last_login_at)
                            <span title="{{ $user->last_login_at->format('d/m/Y H:i') }}">
                                {{ $user->last_login_at->diffForHumans() }}
                            </span>
                        @else
                            <span class="text-muted">Chưa đăng nhập</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($user->is_active)
                            <span class="badge bg-success">Hoạt động</span>
                        @else
                            <span class="badge bg-danger">Vô hiệu hóa</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            @can('sua-tai-khoan')
                            <a href="{{ route('tai-khoan.edit', $user) }}"
                               class="btn btn-outline-warning" title="Chỉnh sửa">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('tai-khoan.toggle-active', $user) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="btn btn-outline-{{ $user->is_active ? 'secondary' : 'success' }}"
                                        title="{{ $user->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }}"
                                        onclick="return confirm('{{ $user->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }} tài khoản này?')">
                                    <i class="bi bi-{{ $user->is_active ? 'lock' : 'unlock' }}"></i>
                                </button>
                            </form>
                            @endcan
                            @can('xoa-tai-khoan')
                            @if($user->id !== auth()->id())
                            <button class="btn btn-outline-danger" title="Xóa"
                                    onclick="confirmDelete('{{ route('tai-khoan.destroy', $user) }}', '{{ $user->name }}')">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endif
                            @endcan
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                        <span class="text-muted">Không tìm thấy tài khoản nào</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="card-footer bg-white">
        {{ $users->links() }}
    </div>
    @endif
</div>

<form id="deleteForm" method="POST">
    @csrf @method('DELETE')
</form>

@push('scripts')
<script>
function confirmDelete(url, name) {
    if (confirm(`Xóa tài khoản "${name}"?\nHành động này không thể hoàn tác!`)) {
        document.getElementById('deleteForm').action = url;
        document.getElementById('deleteForm').submit();
    }
}
</script>
@endpush
@endsection