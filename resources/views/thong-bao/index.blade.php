@extends('layouts.app')
@section('title', 'Thông báo')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-megaphone text-primary me-2"></i>Thông báo</h4>
</div>

@if($errors->any())
<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

<div class="row g-4">
    <div class="{{ auth()->user()->can('dang-thong-bao') ? 'col-lg-8' : 'col-12' }}">
        @forelse($thongBao as $tb)
        <div class="card table-card mb-3 {{ $tb->conHieuLuc() ? '' : 'opacity-50' }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <h6 class="fw-bold mb-1">
                            @if($tb->ghim)<i class="bi bi-pin-angle-fill text-danger me-1" title="Ghim"></i>@endif
                            {{ $tb->tieu_de }}
                            <span class="badge bg-{{ \App\Models\ThongBao::MUC_DO[$tb->muc_do] }} ms-1">{{ $tb->muc_do }}</span>
                        </h6>
                        <div class="small text-muted mb-2">
                            {{ $tb->nguoiDang?->name }} · {{ $tb->created_at->format('H:i d/m/Y') }}
                            · Gửi: {{ $tb->doi_tuong ? collect($tb->doi_tuong)->map(fn ($r) => $vaiTro[$r] ?? $r)->implode(', ') : 'Tất cả' }}
                            @if($tb->het_han) · Hết hạn {{ $tb->het_han->format('d/m/Y') }} @endif
                        </div>
                    </div>
                    @can('dang-thong-bao')
                    <form method="POST" action="{{ route('thong-bao.destroy', $tb) }}" onsubmit="return confirm('Xóa thông báo này?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                    @endcan
                </div>
                <div style="white-space: pre-line">{{ $tb->noi_dung }}</div>
            </div>
        </div>
        @empty
        <div class="card table-card"><div class="card-body text-center text-muted py-5"><i class="bi bi-inbox d-block fs-2 mb-2"></i>Chưa có thông báo</div></div>
        @endforelse
    </div>

    @can('dang-thong-bao')
    <div class="col-lg-4">
        <div class="card table-card">
            <div class="card-header bg-white py-3"><h6 class="fw-semibold mb-0">Đăng thông báo mới</h6></div>
            <form method="POST" action="{{ route('thong-bao.store') }}" class="card-body">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tiêu đề</label>
                    <input type="text" name="tieu_de" class="form-control" required maxlength="200" value="{{ old('tieu_de') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nội dung</label>
                    <textarea name="noi_dung" rows="5" class="form-control" required maxlength="5000">{{ old('noi_dung') }}</textarea>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Mức độ</label>
                        <select name="muc_do" class="form-select">
                            @foreach(array_keys(\App\Models\ThongBao::MUC_DO) as $md)<option {{ old('muc_do') == $md ? 'selected' : '' }}>{{ $md }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Hết hạn</label>
                        <input type="date" name="het_han" class="form-control" min="{{ today()->toDateString() }}" value="{{ old('het_han') }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Gửi tới</label>
                    <div class="small text-muted mb-1">Không chọn = tất cả mọi người</div>
                    @foreach($vaiTro as $ma => $ten)
                        @continue($ma === 'quan-tri-vien')
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="doi_tuong[]" value="{{ $ma }}" id="dt{{ $ma }}"
                                   {{ in_array($ma, old('doi_tuong', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="dt{{ $ma }}">{{ $ten }}</label>
                        </div>
                    @endforeach
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="ghim" value="1" id="ghim">
                    <label class="form-check-label" for="ghim">Ghim lên đầu</label>
                </div>
                <button class="btn btn-primary w-100"><i class="bi bi-send me-2"></i>Đăng</button>
            </form>
        </div>
    </div>
    @endcan
</div>
@endsection
