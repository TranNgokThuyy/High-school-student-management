@extends('layouts.app')
@section('title', $tinNhan->tieu_de)

@section('content')
@use('App\Models\TinNhanNguoiNhan', 'NN')
@php
    $mau = [NN::DA_GUI => 'success', NN::CHO_GUI => 'info', NN::LOI => 'danger', NN::THIEU_DIA_CHI => 'warning', NN::KHONG_GUI => 'light text-muted'];
    $nn = $tinNhan->nguoiNhan;
    $soLoi = $nn->filter(fn ($r) => in_array(NN::LOI, [$r->trang_thai_email, $r->trang_thai_sms, $r->trang_thai_zalo], true))->count();
@endphp
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">{{ $tinNhan->tieu_de }}</h4>
        <div class="text-muted small">{{ $tinNhan->loai }} · {{ $tinNhan->pham_vi }} · {{ $tinNhan->nguoiGui?->name }} · {{ $tinNhan->created_at->format('H:i d/m/Y') }}</div>
    </div>
    <div class="d-flex gap-2">
        @if($soLoi)
        <form method="POST" action="{{ route('tin-nhan.gui-lai', $tinNhan) }}">
            @csrf
            <button class="btn btn-warning"><i class="bi bi-arrow-repeat me-1"></i>Gửi lại {{ $soLoi }} tin lỗi</button>
        </form>
        @endif
        <a href="{{ route('tin-nhan.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Lịch sử</a>
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach([
        ['Người nhận', $nn->count(), 'primary'],
        ['Đã xem trên web', $nn->whereNotNull('da_xem_luc')->count(), 'success'],
        ['Email đã gửi', $nn->where('trang_thai_email', NN::DA_GUI)->count() . ' / ' . $nn->where('trang_thai_email', '!=', NN::KHONG_GUI)->count(), 'info'],
        ['SMS đã gửi', $nn->where('trang_thai_sms', NN::DA_GUI)->count() . ' / ' . $nn->where('trang_thai_sms', '!=', NN::KHONG_GUI)->count(), 'info'],
        ['Zalo đã gửi', $nn->where('trang_thai_zalo', NN::DA_GUI)->count() . ' / ' . $nn->where('trang_thai_zalo', '!=', NN::KHONG_GUI)->count(), 'info'],
    ] as [$nhan, $giaTri, $m])
    <div class="col-6 col-md">
        <div class="card stat-card"><div class="card-body py-3"><div class="text-muted small">{{ $nhan }}</div><div class="fs-5 fw-bold text-{{ $m }}">{{ $giaTri }}</div></div></div>
    </div>
    @endforeach
</div>

<div class="card table-card mb-4">
    <div class="card-header bg-white py-3"><h6 class="fw-semibold mb-0">Nội dung mẫu</h6></div>
    <div class="card-body" style="white-space: pre-line">{{ $tinNhan->noi_dung }}</div>
</div>

<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0 align-middle small">
            <thead><tr><th>Học sinh</th><th>Lớp</th><th>Email</th><th>SMS</th><th>Zalo</th><th>Đã xem</th><th>Ghi chú</th></tr></thead>
            <tbody>
                @foreach($nn->sortBy(fn ($r) => [$r->hocSinh?->lopHoc?->ten_lop, $r->hocSinh?->ho_ten]) as $r)
                <tr>
                    <td class="fw-semibold">{{ $r->hocSinh?->ho_ten }}</td>
                    <td>{{ $r->hocSinh?->lopHoc?->ten_lop }}</td>
                    <td><span class="badge bg-{{ $mau[$r->trang_thai_email] ?? 'secondary' }}">{{ $r->trang_thai_email }}</span> <span class="text-muted">{{ $r->email }}</span></td>
                    <td><span class="badge bg-{{ $mau[$r->trang_thai_sms] ?? 'secondary' }}">{{ $r->trang_thai_sms }}</span> <span class="text-muted">{{ $r->so_dien_thoai }}</span></td>
                    <td><span class="badge bg-{{ $mau[$r->trang_thai_zalo] ?? 'secondary' }}">{{ $r->trang_thai_zalo }}</span></td>
                    <td>{{ $r->da_xem_luc?->format('H:i d/m') ?? '—' }}</td>
                    <td class="text-danger" style="max-width:280px">{{ $r->loi }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
