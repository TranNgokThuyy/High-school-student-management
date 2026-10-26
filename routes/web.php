<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HocSinhController;
use App\Http\Controllers\LopHocController;
use App\Http\Controllers\MonHocController;
use App\Http\Controllers\DiemSoController;
use App\Http\Controllers\HanhKiemController;
use App\Http\Controllers\BaoCaoController;
use App\Http\Controllers\TaiKhoanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PhanCongGiangDayController;
use App\Http\Controllers\DiemDanhController;
use App\Http\Controllers\TongKetController;
use App\Http\Controllers\KhenThuongKyLuatController;
use App\Http\Controllers\HocPhiController;
use App\Http\Controllers\KhoanThuController;
use App\Http\Controllers\MienGiamController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ThoiKhoaBieuController;
use App\Http\Controllers\Auth\RegisterController;
// Trang chủ redirect
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// Routes yêu cầu đăng nhập và tài khoản active
Route::middleware(['auth', 'verified', 'check.active'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile người dùng
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ===== QUẢN LÝ HỌC SINH (phân quyền trong controller) =====
    Route::resource('hoc-sinh', HocSinhController::class);
    Route::get('/hoc-sinh-nhap-excel', [HocSinhController::class, 'showImport'])->name('hoc-sinh.import.form');
    Route::post('/hoc-sinh-nhap-excel', [HocSinhController::class, 'import'])->name('hoc-sinh.import');
    Route::get('/hoc-sinh-mau-excel', [HocSinhController::class, 'downloadTemplate'])->name('hoc-sinh.import.template');

    // ===== QUẢN LÝ LỚP HỌC =====
    // Đăng ký 'create' trước 'show' để /lop-hoc/create không bị hiểu là {lop_hoc}
    Route::resource('lop-hoc', LopHocController::class)->only(['create', 'store'])
        ->middleware('permission:them-lop-hoc');
    Route::resource('lop-hoc', LopHocController::class)->only(['index', 'show'])
        ->middleware('permission:xem-lop-hoc');
    Route::resource('lop-hoc', LopHocController::class)->only(['edit', 'update'])
        ->middleware('permission:sua-lop-hoc');
    Route::resource('lop-hoc', LopHocController::class)->only(['destroy'])
        ->middleware('permission:xoa-lop-hoc');

    // ===== MÔN HỌC =====
    Route::prefix('mon-hoc')->name('mon-hoc.')->group(function () {
        Route::get('/',                    [MonHocController::class, 'index'])->name('index')->middleware('permission:xem-mon-hoc');
        Route::get('/create',              [MonHocController::class, 'create'])->name('create')->middleware('permission:them-mon-hoc');
        Route::post('/',                   [MonHocController::class, 'store'])->name('store')->middleware('permission:them-mon-hoc');
        Route::get('/{monHoc}/edit',       [MonHocController::class, 'edit'])->name('edit')->middleware('permission:sua-mon-hoc');
        Route::put('/{monHoc}',            [MonHocController::class, 'update'])->name('update')->middleware('permission:sua-mon-hoc');
        Route::delete('/{monHoc}',         [MonHocController::class, 'destroy'])->name('destroy')->middleware('permission:xoa-mon-hoc');
        Route::patch('/{monHoc}/toggle',   [MonHocController::class, 'toggleActive'])->name('toggle')->middleware('permission:sua-mon-hoc');
    });

    // ===== QUẢN LÝ ĐIỂM SỐ =====
    Route::prefix('diem-so')->name('diem-so.')->group(function () {
        Route::get('/', [DiemSoController::class, 'bangDiem'])->name('bang-diem')->middleware('permission:xem-diem');
        Route::get('/nhap-diem', [DiemSoController::class, 'nhapDiem'])->name('nhap-diem')->middleware('permission:them-diem');
        Route::post('/luu-diem', [DiemSoController::class, 'luuDiem'])->name('luu-diem')->middleware('permission:them-diem');
    });

    // ===== HẠNH KIỂM =====
    Route::prefix('hanh-kiem')->name('hanh-kiem.')->group(function () {
        Route::get('/',        [HanhKiemController::class, 'index'])->name('index')->middleware('permission:xem-hanh-kiem');
        Route::get('/nhap',    [HanhKiemController::class, 'chonLop'])->name('nhap')->middleware('permission:them-hanh-kiem');
        Route::get('/form',    [HanhKiemController::class, 'nhapHanhKiem'])->name('form')->middleware('permission:them-hanh-kiem');
        Route::post('/luu',    [HanhKiemController::class, 'luuHanhKiem'])->name('luu')->middleware('permission:them-hanh-kiem');
    });

    // ===== BÁO CÁO =====
    Route::prefix('bao-cao')->name('bao-cao.')->group(function () {
        Route::get('/', [BaoCaoController::class, 'index'])->name('index')->middleware('permission:xem-bao-cao');
        Route::get('/diem-lop', [BaoCaoController::class, 'baoCaoDiemLop'])->name('diem-lop')->middleware('permission:xem-bao-cao');
        Route::get('/xuat-pdf', [BaoCaoController::class, 'xuatPDF'])->name('xuat-pdf')->middleware('permission:xuat-bao-cao');
        Route::get('/xuat-excel', [BaoCaoController::class, 'xuatExcel'])->name('xuat-excel')->middleware('permission:xuat-bao-cao');
    });

    // ===== QUẢN LÝ TÀI KHOẢN (Chỉ admin) =====
    Route::middleware('role:quan-tri-vien')->group(function () {
        Route::resource('tai-khoan', TaiKhoanController::class)->only(['index'])
            ->middleware('permission:xem-tai-khoan');
        Route::resource('tai-khoan', TaiKhoanController::class)->only(['create', 'store'])
            ->middleware('permission:them-tai-khoan');
        Route::resource('tai-khoan', TaiKhoanController::class)->only(['edit', 'update'])
            ->middleware('permission:sua-tai-khoan');
        Route::resource('tai-khoan', TaiKhoanController::class)->only(['destroy'])
            ->middleware('permission:xoa-tai-khoan');
        Route::patch('/tai-khoan/{taiKhoan}/toggle-active', [TaiKhoanController::class, 'toggleActive'])
            ->name('tai-khoan.toggle-active')
            ->middleware('permission:sua-tai-khoan');
    });

    // ===== THỜI KHÓA BIỂU =====
    Route::prefix('thoi-khoa-bieu')->name('thoi-khoa-bieu.')->group(function () {
        Route::get('/',         [ThoiKhoaBieuController::class, 'index'])->name('index')->middleware('permission:xem-thoi-khoa-bieu');
        Route::get('/them',     [ThoiKhoaBieuController::class, 'create'])->name('create')->middleware('permission:sua-thoi-khoa-bieu');
        Route::post('/',        [ThoiKhoaBieuController::class, 'store'])->name('store')->middleware('permission:sua-thoi-khoa-bieu');
        Route::delete('/{thoiKhoaBieu}', [ThoiKhoaBieuController::class, 'destroy'])->name('destroy')->middleware('permission:sua-thoi-khoa-bieu');
    });

    // ===== PHÂN CÔNG GIẢNG DẠY =====
    Route::prefix('phan-cong')->name('phan-cong.')->group(function () {
        Route::get('/',             [PhanCongGiangDayController::class, 'index'])->name('index')->middleware('permission:xem-phan-cong');
        Route::get('/them',         [PhanCongGiangDayController::class, 'create'])->name('create')->middleware('permission:them-phan-cong');
        Route::post('/',            [PhanCongGiangDayController::class, 'store'])->name('store')->middleware('permission:them-phan-cong');
        Route::delete('/{phanCong}', [PhanCongGiangDayController::class, 'destroy'])->name('destroy')->middleware('permission:xoa-phan-cong');
    });

    // ===== ĐIỂM DANH =====
    Route::prefix('diem-danh')->name('diem-danh.')->group(function () {
        Route::get('/',         [DiemDanhController::class, 'index'])->name('index')->middleware('permission:xem-diem-danh');
        Route::post('/',        [DiemDanhController::class, 'store'])->name('store')->middleware('permission:diem-danh');
        Route::get('/tong-hop', [DiemDanhController::class, 'tongHop'])->name('tong-hop')->middleware('permission:xem-diem-danh');
    });

    // ===== TỔNG KẾT & LÊN LỚP =====
    Route::prefix('tong-ket')->name('tong-ket.')->group(function () {
        Route::get('/',         [TongKetController::class, 'index'])->name('index')->middleware('permission:xem-tong-ket');
        Route::get('/len-lop',  [TongKetController::class, 'lenLop'])->name('len-lop')->middleware('permission:xet-len-lop');
        Route::post('/len-lop', [TongKetController::class, 'luuLenLop'])->name('luu-len-lop')->middleware('permission:xet-len-lop');
    });

    // ===== KHEN THƯỞNG - KỶ LUẬT =====
    Route::prefix('khen-thuong')->name('khen-thuong.')->group(function () {
        Route::get('/',             [KhenThuongKyLuatController::class, 'index'])->name('index')->middleware('permission:xem-khen-thuong');
        Route::get('/them',         [KhenThuongKyLuatController::class, 'create'])->name('create')->middleware('permission:them-khen-thuong');
        Route::post('/',            [KhenThuongKyLuatController::class, 'store'])->name('store')->middleware('permission:them-khen-thuong');
        Route::delete('/{khenThuong}', [KhenThuongKyLuatController::class, 'destroy'])->name('destroy')->middleware('permission:xoa-khen-thuong');
    });

    // ===== HỌC PHÍ & KHOẢN THU =====
    Route::prefix('hoc-phi')->name('hoc-phi.')->group(function () {
        Route::get('/',                           [HocPhiController::class, 'index'])->name('index')->middleware('permission:xem-hoc-phi');
        Route::get('/xuat-excel',                 [HocPhiController::class, 'xuatExcel'])->name('xuat-excel')->middleware('permission:xem-hoc-phi');
        Route::get('/so-thu',                     [HocPhiController::class, 'soThu'])->name('so-thu')->middleware('permission:thu-hoc-phi');
        Route::get('/hoc-sinh/{hocSinh}',         [HocPhiController::class, 'hocSinh'])->name('hoc-sinh')->middleware('permission:xem-hoc-phi');
        Route::post('/hoc-sinh/{hocSinh}/thu',    [HocPhiController::class, 'thu'])->name('thu')->middleware('permission:thu-hoc-phi');
        Route::get('/phieu-thu/{phieuThu}',       [HocPhiController::class, 'inPhieu'])->name('in-phieu')->middleware('permission:xem-hoc-phi');
        Route::post('/phieu-thu/{phieuThu}/huy',  [HocPhiController::class, 'huyPhieu'])->name('huy-phieu')->middleware('permission:huy-phieu-thu');

        Route::get('/mien-giam',                  [MienGiamController::class, 'index'])->name('mien-giam')->middleware('permission:quan-ly-khoan-thu');
        Route::post('/mien-giam',                 [MienGiamController::class, 'store'])->name('mien-giam.store')->middleware('permission:quan-ly-khoan-thu');
        Route::delete('/mien-giam/{mienGiam}',    [MienGiamController::class, 'destroy'])->name('mien-giam.destroy')->middleware('permission:quan-ly-khoan-thu');
    });
    Route::resource('khoan-thu', KhoanThuController::class)->except(['show'])
        ->parameters(['khoan-thu' => 'khoanThu'])
        ->middleware('permission:quan-ly-khoan-thu');
});
Route::get('/register',  [RegisterController::class, 'showForm'])->name('register')->middleware('guest');
Route::post('/register', [RegisterController::class, 'register'])->middleware('guest');
require __DIR__.'/auth.php';
