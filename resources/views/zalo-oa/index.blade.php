@extends('layouts.app')
@section('title', 'Kết nối Zalo OA')

@section('content')
<h4 class="fw-bold mb-4"><i class="bi bi-chat-square-text text-primary me-2"></i>Kết nối Zalo OA</h4>

@if($errors->any())
<div class="alert alert-danger" style="max-width:820px">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

<div class="row g-4" style="max-width:1100px">
    <div class="col-lg-6">
        <div class="card table-card h-100">
            <div class="card-header bg-white py-3 fw-semibold">Trạng thái</div>
            <ul class="list-group list-group-flush small">
                <li class="list-group-item d-flex justify-content-between"><span>Access token</span>
                    @if(!$coToken)<span class="text-muted">Chưa có</span>
                    @elseif($token->het_han_luc?->isPast())<span class="text-danger fw-semibold">Đã hết hạn {{ $token->het_han_luc->format('H:i d/m/Y') }}</span>
                    @elseif($token->het_han_luc)<span class="text-success">Còn hạn tới {{ $token->het_han_luc->format('H:i d/m/Y') }}</span>
                    @else<span>Lấy từ ZALO_ZNS_ACCESS_TOKEN (không rõ hạn)</span>@endif
                </li>
                <li class="list-group-item d-flex justify-content-between"><span>Refresh token</span>
                    <span>{{ $token->refresh_token ? 'Đã lưu' . ($token->refresh_het_han_luc ? ', hạn ' . $token->refresh_het_han_luc->format('d/m/Y') : '') : (filled(config('services.zalo.refresh_token')) ? 'Lấy từ ZALO_OA_REFRESH_TOKEN' : 'Chưa có') }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between"><span>Làm mới lần cuối</span><span>{{ $token->lam_moi_luc?->format('H:i d/m/Y') ?? '—' }}</span></li>
                @foreach($cauHinh as $bien => $co)
                <li class="list-group-item d-flex justify-content-between"><code>{{ $bien }}</code>
                    <span class="{{ $co ? 'text-success' : 'text-muted' }}"><i class="bi bi-{{ $co ? 'check-circle' : 'dash-circle' }} me-1"></i>{{ $co ? 'Đã khai báo' : 'Chưa khai báo' }}</span></li>
                @endforeach
                @if($token->loi_gan_nhat)
                <li class="list-group-item text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Lỗi gần nhất: {{ $token->loi_gan_nhat }}</li>
                @endif
            </ul>
            <div class="card-footer bg-white">
                <form method="POST" action="{{ route('zalo-oa.lam-moi') }}">
                    @csrf
                    <button class="btn btn-outline-primary" {{ $coTheLamMoi ? '' : 'disabled' }}><i class="bi bi-arrow-repeat me-1"></i>Làm mới ngay</button>
                </form>
                <div class="form-text">Hệ thống tự làm mới mỗi giờ khi token còn dưới 3 giờ (lệnh <code>zalo:lam-moi-token</code>, cần cron <code>schedule:run</code>).</div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card table-card h-100">
            <div class="card-header bg-white py-3 fw-semibold">Nhập token mới</div>
            <div class="card-body">
                <p class="small text-muted">Lấy access token và refresh token của OA tại <b>developers.zalo.me</b> (công cụ API Explorer, chọn loại OA Access Token). Chỉ cần làm một lần; sau đó hệ thống tự làm mới. Nếu ngừng làm mới quá 3 tháng thì phải nhập lại.</p>
                <form method="POST" action="{{ route('zalo-oa.luu') }}">
                    @csrf
                    <div class="mb-3"><label class="form-label">Access token</label><textarea name="access_token" class="form-control font-monospace small" rows="3" required></textarea></div>
                    <div class="mb-3"><label class="form-label">Refresh token</label><textarea name="refresh_token" class="form-control font-monospace small" rows="2"></textarea></div>
                    <div class="mb-3"><label class="form-label">Hết hạn sau (giờ)</label><input type="number" name="het_han_gio" class="form-control" value="25" min="1" max="48" style="max-width:120px"></div>
                    <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Lưu token</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
