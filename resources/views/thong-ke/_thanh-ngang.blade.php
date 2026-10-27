{{--
    Biểu đồ thanh ngang.
    $id, $tieuDe, $hang: [nhãn hàng => [tên chuỗi => giá trị]], $chuoi: [tên chuỗi => màu],
    $phanTram: chuẩn hóa mỗi hàng về 100%, $max: giá trị tối đa trục (mặc định lớn nhất), $donVi: hậu tố,
    $chuDam: các chuỗi có nền sáng cần chữ tối
--}}
@php
    $phanTram = $phanTram ?? false;
    $donVi = $donVi ?? '';
    $chuDam = $chuDam ?? [];
    $dinhDang = fn ($v) => number_format($v, fmod($v, 1) ? 1 : 0, ',', '.') . $donVi;
    $max = $max ?? max(1, collect($hang)->map(fn ($r) => array_sum($r))->max() ?? 1);
@endphp
<div class="tk-chart" id="{{ $id }}">
    <div class="d-flex justify-content-between align-items-baseline flex-wrap gap-2 mb-2">
        <h6 class="fw-semibold mb-0">{{ $tieuDe }}</h6>
        @if(count($chuoi) > 1)
        <div class="tk-legend">
            @foreach($chuoi as $ten => $mau)
                <span><i style="background: {{ $mau }}"></i>{{ $ten }}</span>
            @endforeach
        </div>
        @endif
    </div>

    @forelse($hang as $nhan => $giaTri)
        @php $tong = array_sum($giaTri); @endphp
        <div class="tk-row">
            <div class="tk-label" title="{{ $nhan }}">{{ $nhan }}</div>
            <div class="tk-track">
                @foreach($chuoi as $ten => $mau)
                    @php
                        $v = $giaTri[$ten] ?? 0;
                        $rong = $phanTram ? ($tong ? $v / $tong * 100 : 0) : $v / $max * 100;
                        $tip = "{$nhan} · {$ten}: " . $dinhDang($v) . ($phanTram && $tong ? ' (' . number_format($v / $tong * 100, 1, ',', '.') . '%)' : '');
                    @endphp
                    @if($v > 0)
                    <div class="tk-seg" tabindex="0" style="width: {{ $rong }}%; background: {{ $mau }}; color: {{ in_array($ten, $chuDam, true) ? '#0b0b0b' : '#fff' }}"
                         data-tip="{{ $tip }}" aria-label="{{ $tip }}">
                        @if($phanTram && $rong >= 14){{ round($rong) }}%@endif
                    </div>
                    @endif
                @endforeach
            </div>
            <div class="tk-end">{{ $phanTram ? $tong . ' HS' : $dinhDang($tong) }}</div>
        </div>
    @empty
        <div class="text-muted small py-3">Chưa có dữ liệu</div>
    @endforelse

    @if(count($hang))
    <details class="mt-2">
        <summary class="small text-muted">Xem bảng số liệu</summary>
        <table class="table table-sm small mt-2 mb-0">
            <thead><tr><th></th>@foreach($chuoi as $ten => $mau)<th class="text-end">{{ $ten }}</th>@endforeach<th class="text-end">Tổng</th></tr></thead>
            <tbody>
                @foreach($hang as $nhan => $giaTri)
                <tr><td>{{ $nhan }}</td>@foreach($chuoi as $ten => $mau)<td class="text-end">{{ $dinhDang($giaTri[$ten] ?? 0) }}</td>@endforeach<td class="text-end fw-semibold">{{ $dinhDang(array_sum($giaTri)) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </details>
    @endif
</div>
