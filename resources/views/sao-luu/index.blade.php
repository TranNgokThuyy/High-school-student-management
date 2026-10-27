@extends('layouts.app')
@section('title', 'Sao lưu dữ liệu')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-database-check text-primary me-2"></i>Sao lưu dữ liệu</h4>
    <form method="POST" action="{{ route('sao-luu.store') }}">
        @csrf
        <button class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Sao lưu ngay</button>
    </form>
</div>

<div class="alert alert-info small">
    <div><b>Tự động:</b> hệ thống sao lưu lúc 01:30 mỗi ngày và giữ 14 bản mới nhất (máy chủ cần cron chạy <code>php artisan schedule:run</code> mỗi phút).</div>
    <div><b>Phục hồi:</b> chạy trên máy chủ <code>php artisan sao-luu:phuc-hoi &lt;tên tệp&gt;</code> — dữ liệu hiện tại được tự động sao lưu trước khi ghi đè.</div>
    <div><b>Lưu ý:</b> tệp sao lưu chứa toàn bộ thông tin học sinh, hãy tải về và cất giữ ở nơi an toàn (ổ cứng ngoài / lưu trữ đám mây của trường).</div>
</div>

<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead><tr><th>Tệp</th><th>Thời gian</th><th class="text-end">Dung lượng</th><th></th></tr></thead>
            <tbody>
                @forelse($danhSach as $b)
                <tr>
                    <td class="fw-semibold"><i class="bi bi-file-zip me-1 text-muted"></i>{{ $b['ten'] }}</td>
                    <td>{{ $b['thoi_gian']->timezone(config('app.timezone'))->format('H:i d/m/Y') }}</td>
                    <td class="text-end">{{ number_format($b['kich_thuoc'] / 1024, 1, ',', '.') }} KB</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('sao-luu.download', $b['ten']) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i></a>
                        <form method="POST" action="{{ route('sao-luu.destroy', $b['ten']) }}" class="d-inline" onsubmit="return confirm('Xóa bản sao lưu {{ $b['ten'] }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Chưa có bản sao lưu nào</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
