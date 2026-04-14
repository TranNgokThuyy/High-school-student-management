@extends('layouts.app')
@section('title', 'Quản lý điểm số')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-journal-text text-primary me-2"></i>Quản lý điểm số</h4>
</div>

{{-- Form chọn lớp/môn --}}
<div class="card table-card mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="fw-semibold mb-0">Chọn lớp và môn học</h6>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Lớp học</label>
                <select name="lop_hoc_id" class="form-select" required>
                    <option value="">-- Chọn lớp --</option>
                    @foreach($lopHoc as $lop)
                        <option value="{{ $lop->id }}" {{ request('lop_hoc_id') == $lop->id ? 'selected' : '' }}>
                            {{ $lop->ten_lop }} ({{ $lop->nam_hoc }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Môn học</label>
                <select name="mon_hoc_id" class="form-select" required>
                    <option value="">-- Chọn môn --</option>
                    @foreach($monHoc as $mon)
                        <option value="{{ $mon->id }}" {{ request('mon_hoc_id') == $mon->id ? 'selected' : '' }}>
                            {{ $mon->ten_mon }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Học kỳ</label>
                <select name="hoc_ky" class="form-select" required>
                    <option value="">-- Học kỳ --</option>
                    <option value="1" {{ request('hoc_ky') == '1' ? 'selected' : '' }}>Học kỳ 1</option>
                    <option value="2" {{ request('hoc_ky') == '2' ? 'selected' : '' }}>Học kỳ 2</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Năm học</label>
                <select name="nam_hoc" class="form-select" required>
                    <option value="">-- Năm học --</option>
                    @foreach($namHoc as $nh)
                        <option value="{{ $nh }}" {{ request('nam_hoc') == $nh ? 'selected' : '' }}>{{ $nh }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-2"></i>Xem điểm
                </button>
            </div>
        </form>
    </div>
</div>

@if($selectedLop && $selectedMon)
{{-- Bảng điểm --}}
<div class="card table-card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-semibold mb-0">
                Bảng điểm: {{ $selectedLop->ten_lop }} - {{ $selectedMon->ten_mon }} 
                - HK{{ request('hoc_ky') }} ({{ request('nam_hoc') }})
            </h6>
            <small class="text-muted">{{ $selectedLop->hocSinh->count() }} học sinh</small>
        </div>
        @can('them-diem')
        <a href="{{ route('diem-so.nhap-diem', request()->all()) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil-square me-2"></i>Nhập điểm
        </a>
        @endcan
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Học sinh</th>
                    <th class="text-center">TX1</th>
                    <th class="text-center">TX2</th>
                    <th class="text-center">TX3</th>
                    <th class="text-center">TX4</th>
                    <th class="text-center">Giữa kỳ</th>
                    <th class="text-center">Cuối kỳ</th>
                    <th class="text-center">Điểm TB</th>
                    <th class="text-center">Xếp loại</th>
                </tr>
            </thead>
            <tbody>
                @forelse($selectedLop->hocSinh as $index => $hs)
                    @php $d = $diem[$hs->id] ?? null; @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-semibold">{{ $hs->ho_ten }}</td>
                        <td class="text-center">{{ $d?->diem_tx1 ?? '—' }}</td>
                        <td class="text-center">{{ $d?->diem_tx2 ?? '—' }}</td>
                        <td class="text-center">{{ $d?->diem_tx3 ?? '—' }}</td>
                        <td class="text-center">{{ $d?->diem_tx4 ?? '—' }}</td>
                        <td class="text-center">{{ $d?->diem_gk ?? '—' }}</td>
                        <td class="text-center">{{ $d?->diem_ck ?? '—' }}</td>
                        <td class="text-center">
                            @if($d?->diem_trung_binh)
                                <strong class="{{ $d->diem_trung_binh >= 5 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($d->diem_trung_binh, 2) }}
                                </strong>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($d?->diem_trung_binh)
                                @php
                                    $xepLoai = $d->xep_loai;
                                    $colors = ['Giỏi' => 'success', 'Khá' => 'primary', 'Trung bình' => 'warning', 'Yếu' => 'danger', 'Kém' => 'dark'];
                                @endphp
                                <span class="badge bg-{{ $colors[$xepLoai] ?? 'secondary' }}">{{ $xepLoai }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center py-4 text-muted">Lớp chưa có học sinh</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection