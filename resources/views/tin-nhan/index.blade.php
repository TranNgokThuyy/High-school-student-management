@extends('layouts.app')
@section('title', 'Tin nhắn phụ huynh')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-chat-dots text-primary me-2"></i>Tin nhắn phụ huynh</h4>
    <a href="{{ route('tin-nhan.create') }}" class="btn btn-primary"><i class="bi bi-pencil-square me-2"></i>Soạn tin nhắn</a>
</div>

<form method="GET" class="mb-3" style="max-width:260px">
    <select name="loai" class="form-select" onchange="this.form.submit()">
        <option value="">-- Tất cả loại tin --</option>
        @foreach(\App\Models\TinNhan::LOAI as $l)<option {{ request('loai') == $l ? 'selected' : '' }}>{{ $l }}</option>@endforeach
    </select>
</form>

<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr><th>Thời gian</th><th>Tiêu đề</th><th>Loại</th><th>Gửi tới</th><th>Kênh</th>
                    <th class="text-center">Người nhận</th><th class="text-center">Đã xem</th><th class="text-center">Email / SMS</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($tinNhan as $tn)
                <tr>
                    <td class="small text-nowrap">{{ $tn->created_at->format('H:i d/m/Y') }}<div class="text-muted">{{ $tn->nguoiGui?->name }}</div></td>
                    <td class="fw-semibold">{{ $tn->tieu_de }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $tn->loai }}</span></td>
                    <td class="small">{{ $tn->pham_vi }}</td>
                    <td class="small">{{ collect($tn->kenh)->map(fn ($k) => \App\Models\TinNhan::KENH[$k] ?? $k)->implode(', ') }}</td>
                    <td class="text-center">{{ $tn->nguoi_nhan_count }}</td>
                    <td class="text-center">{{ $tn->da_xem_count }}</td>
                    <td class="text-center small">
                        {{ $tn->email_da_gui_count }} / {{ $tn->sms_da_gui_count }}
                        @if($tn->loi_count)<span class="badge bg-danger ms-1">{{ $tn->loi_count }} lỗi</span>@endif
                    </td>
                    <td><a href="{{ route('tin-nhan.show', $tn) }}" class="btn btn-sm btn-outline-primary">Chi tiết</a></td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4"><i class="bi bi-inbox d-block fs-2 mb-2"></i>Chưa gửi tin nhắn nào</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tinNhan->hasPages())<div class="card-footer bg-white">{{ $tinNhan->links() }}</div>@endif
</div>
@endsection
