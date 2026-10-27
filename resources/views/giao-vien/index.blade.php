@extends('layouts.app')
@section('title', 'Giáo viên & giờ dạy')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-person-badge text-primary me-2"></i>Giáo viên & giờ dạy</h4>
    <form method="GET">
        <select name="nam_hoc" class="form-select" onchange="this.form.submit()">
            @foreach($dsNamHoc as $nh)<option value="{{ $nh }}" {{ $namHoc == $nh ? 'selected' : '' }}>Năm học {{ $nh }}</option>@endforeach
        </select>
    </form>
</div>

<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle small">
            <thead>
                <tr>
                    <th rowspan="2">Giáo viên</th><th rowspan="2">Chuyên môn / Tổ</th><th rowspan="2">Trình độ</th><th rowspan="2">Chủ nhiệm</th>
                    <th rowspan="2" class="text-center">Định mức<br><span class="fw-normal">(tiết/tuần)</span></th>
                    @foreach($soTuan as $hk => $tuan)<th colspan="2" class="text-center">Học kỳ {{ $hk }} ({{ $tuan }} tuần)</th>@endforeach
                    <th rowspan="2"></th>
                </tr>
                <tr>@foreach($soTuan as $hk => $tuan)<th class="text-center">Tiết/tuần</th><th class="text-center">Thừa / thiếu cả kỳ</th>@endforeach</tr>
            </thead>
            <tbody>
                @forelse($bang as $r)
                <tr>
                    <td class="fw-semibold">{{ $r['giao_vien']->name }}<div class="text-muted fw-normal">{{ $r['ho_so']->chuc_vu ?? 'Giáo viên' }}</div></td>
                    <td>{{ $r['ho_so']->chuyen_mon ?? '—' }}<div class="text-muted">{{ $r['ho_so']->to_chuyen_mon }}</div></td>
                    <td>{{ $r['ho_so']->trinh_do ?? '—' }}</td>
                    <td>{{ $r['lop_chu_nhiem'] ?: '—' }}</td>
                    <td class="text-center" title="Định mức {{ $r['ho_so']->dinh_muc_tiet ?? 17 }} − kiêm nhiệm {{ $r['ho_so']->giam_tru_tiet ?? 0 }}{{ $r['lop_chu_nhiem'] ? ' − chủ nhiệm ' . $giamChuNhiem : '' }}">{{ $r['dinh_muc'] }}</td>
                    @foreach($r['theo_ky'] as $hk => $k)
                        <td class="text-center">{{ $k['tiet_tuan'] }}</td>
                        <td class="text-center fw-semibold {{ $k['chenh_ky'] > 0 ? 'text-success' : ($k['chenh_ky'] < 0 ? 'text-danger' : '') }}">
                            {{ $k['chenh_ky'] > 0 ? '+' : '' }}{{ $k['chenh_ky'] }} tiết
                        </td>
                    @endforeach
                    <td class="text-end"><a href="{{ route('giao-vien.edit', $r['giao_vien']) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center text-muted py-4">Chưa có giáo viên</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white small text-muted">
        Số tiết lấy từ <a href="{{ route('phan-cong.index', ['nam_hoc' => $namHoc]) }}">phân công giảng dạy</a> × số tiết/tuần của môn.
        Định mức thực = định mức − giảm trừ kiêm nhiệm − {{ $giamChuNhiem }} tiết nếu chủ nhiệm (chỉnh hằng số trong <code>GioDayService</code> nếu quy định tại trường khác).
        Số dương: dạy thừa giờ; số âm: thiếu giờ.
    </div>
</div>
@endsection
