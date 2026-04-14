@extends('layouts.app')
@section('title', 'Thêm môn học')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Thêm môn học mới</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('mon-hoc.index') }}">Quản lý môn học</a>
                </li>
                <li class="breadcrumb-item active">Thêm mới</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('mon-hoc.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Quay lại
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card table-card">
            <div class="card-header bg-white py-3">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-book text-primary me-2"></i>Thông tin môn học
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('mon-hoc.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Mã môn <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="ma_mon"
                                   class="form-control @error('ma_mon') is-invalid @enderror"
                                   value="{{ old('ma_mon') }}"
                                   placeholder="VD: TOAN, VAN, ANH..."
                                   style="text-transform:uppercase"
                                   required>
                            @error('ma_mon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-semibold">
                                Tên môn học <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="ten_mon"
                                   class="form-control @error('ten_mon') is-invalid @enderror"
                                   value="{{ old('ten_mon') }}"
                                   placeholder="VD: Toán, Ngữ văn, Tiếng Anh..."
                                   required>
                            @error('ten_mon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Áp dụng cho khối <span class="text-danger">*</span>
                            </label>
                            <select name="khoi"
                                    class="form-select @error('khoi') is-invalid @enderror"
                                    required>
                                <option value="">-- Chọn khối --</option>
                                <option value="Tất cả" {{ old('khoi')=='Tất cả' ? 'selected':'' }}>
                                    Tất cả khối (10, 11, 12)
                                </option>
                                <option value="10" {{ old('khoi')=='10' ? 'selected':'' }}>
                                    Chỉ Khối 10
                                </option>
                                <option value="11" {{ old('khoi')=='11' ? 'selected':'' }}>
                                    Chỉ Khối 11
                                </option>
                                <option value="12" {{ old('khoi')=='12' ? 'selected':'' }}>
                                    Chỉ Khối 12
                                </option>
                            </select>
                            @error('khoi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Số tiết/tuần <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" name="so_tiet_tuan"
                                       class="form-control @error('so_tiet_tuan') is-invalid @enderror"
                                       value="{{ old('so_tiet_tuan', 1) }}"
                                       min="1" max="10" required>
                                <span class="input-group-text">tiết</span>
                            </div>
                            @error('so_tiet_tuan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                       name="is_active" id="is_active" value="1" checked>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    Môn học đang được giảng dạy
                                </label>
                            </div>
                        </div>

                        <div class="col-12 mt-2">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>Lưu môn học
                                </button>
                                <a href="{{ route('mon-hoc.index') }}"
                                   class="btn btn-outline-secondary">Hủy bỏ</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Gợi ý môn học mặc định THPT --}}
        <div class="card table-card mt-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-lightbulb text-warning me-2"></i>Gợi ý môn học THPT
                </h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Nhấn để điền nhanh thông tin môn học:
                </p>
                <div class="d-flex flex-wrap gap-2">
                    @php
                        $goiY = [
                            ['TOAN','Toán',4],
                            ['VAN','Ngữ văn',3],
                            ['ANH','Tiếng Anh',3],
                            ['LY','Vật lý',2],
                            ['HOA','Hóa học',2],
                            ['SINH','Sinh học',2],
                            ['SU','Lịch sử',2],
                            ['DIA','Địa lý',2],
                            ['GDCD','GDCD',1],
                            ['TIN','Tin học',2],
                            ['TD','Thể dục',2],
                            ['CNGT','Công nghệ',1],
                        ];
                    @endphp
                    @foreach($goiY as [$ma, $ten, $tiet])
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary"
                                onclick="diemNhanh('{{ $ma }}','{{ $ten }}',{{ $tiet }})">
                            {{ $ten }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function diemNhanh(ma, ten, tiet) {
    document.querySelector('[name=ma_mon]').value  = ma;
    document.querySelector('[name=ten_mon]').value = ten;
    document.querySelector('[name=so_tiet_tuan]').value = tiet;
    document.querySelector('[name=khoi]').value = 'Tất cả';
}
</script>
@endpush
@endsection