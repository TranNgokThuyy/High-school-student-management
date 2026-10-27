@extends('layouts.app')
@section('title', 'Khoản thu')

@section('content')
@use('App\Support\Tien')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-list-check text-primary me-2"></i>Danh mục khoản thu</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('hoc-phi.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Công nợ</a>
        <a href="{{ route('khoan-thu.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Thêm khoản thu</a>
    </div>
</div>

<form method="GET" class="mb-3" style="max-width:220px">
    <select name="nam_hoc" class="form-select" onchange="this.form.submit()">
        @foreach($dsNamHoc as $nh)<option value="{{ $nh }}" {{ $namHoc == $nh ? 'selected' : '' }}>Năm học {{ $nh }}</option>@endforeach
    </select>
</form>

<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead><tr><th>Khoản thu</th><th>Loại</th><th>Áp dụng</th><th class="text-end">Mức thu</th><th>Hạn nộp</th><th class="text-end">Đã thu</th><th>Trạng thái</th><th></th></tr></thead>
            <tbody>
                @forelse($khoanThu as $kt)
                <tr class="{{ $kt->is_active ? '' : 'text-muted' }}">
                    <td class="fw-semibold">{{ $kt->ten }}
                        @if($kt->ap_dung_mien_giam)<span class="badge bg-info-subtle text-info border ms-1" title="Áp dụng miễn giảm">MG</span>@endif
                        @if($kt->mo_ta)<div class="small text-muted fw-normal">{{ $kt->mo_ta }}</div>@endif
                    </td>
                    <td>{{ $kt->loai }}</td>
                    <td class="small">Khối {{ $kt->khoi }} · {{ $kt->hoc_ky === 'Ca năm' ? 'Cả năm' : 'HK' . $kt->hoc_ky }}</td>
                    <td class="text-end">{{ Tien::dinhDang($kt->so_tien) }}</td>
                    <td>{{ $kt->han_nop?->format('d/m/Y') ?? '—' }}</td>
                    <td class="text-end text-success">{{ Tien::dinhDang((int) $kt->da_thu) }}</td>
                    <td>{!! $kt->is_active ? '<span class="badge bg-success">Đang áp dụng</span>' : '<span class="badge bg-secondary">Ngừng</span>' !!}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('khoan-thu.edit', $kt) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('khoan-thu.destroy', $kt) }}" class="d-inline" onsubmit="return confirm('Xóa khoản thu {{ $kt->ten }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4"><i class="bi bi-inbox d-block fs-2 mb-2"></i>Chưa có khoản thu cho năm học {{ $namHoc }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
