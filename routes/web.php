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

    // ===== QUẢN LÝ HỌC SINH =====
    Route::resource('hoc-sinh', HocSinhController::class);

    // ===== QUẢN LÝ LỚP HỌC =====
    Route::resource('lop-hoc', LopHocController::class);
// ===== MÔN HỌC =====
Route::prefix('mon-hoc')->name('mon-hoc.')->group(function () {
    Route::get('/',                    [MonHocController::class, 'index'])->name('index');
    Route::get('/create',              [MonHocController::class, 'create'])->name('create');
    Route::post('/',                   [MonHocController::class, 'store'])->name('store');
    Route::get('/{monHoc}/edit',       [MonHocController::class, 'edit'])->name('edit');
    Route::put('/{monHoc}',            [MonHocController::class, 'update'])->name('update');
    Route::delete('/{monHoc}',         [MonHocController::class, 'destroy'])->name('destroy');
    Route::patch('/{monHoc}/toggle',   [MonHocController::class, 'toggleActive'])->name('toggle');
});
    // ===== QUẢN LÝ ĐIỂM SỐ =====
    Route::prefix('diem-so')->name('diem-so.')->group(function () {
        Route::get('/', [DiemSoController::class, 'bangDiem'])->name('bang-diem');
        Route::get('/nhap-diem', [DiemSoController::class, 'nhapDiem'])->name('nhap-diem');
        Route::post('/luu-diem', [DiemSoController::class, 'luuDiem'])->name('luu-diem');
    });

    // ===== HẠNH KIỂM =====
Route::prefix('hanh-kiem')->name('hanh-kiem.')->group(function () {
    Route::get('/',        [HanhKiemController::class, 'index'])->name('index');
    Route::get('/nhap',    [HanhKiemController::class, 'chonLop'])->name('nhap');
    Route::get('/form',    [HanhKiemController::class, 'nhapHanhKiem'])->name('form');
    Route::post('/luu',    [HanhKiemController::class, 'luuHanhKiem'])->name('luu');
});

    // ===== BÁO CÁO =====
    Route::prefix('bao-cao')->name('bao-cao.')->group(function () {
        Route::get('/', [BaoCaoController::class, 'index'])->name('index');
        Route::get('/diem-lop', [BaoCaoController::class, 'baoCaoDiemLop'])->name('diem-lop');
        Route::get('/xuat-pdf', [BaoCaoController::class, 'xuatPDF'])->name('xuat-pdf');
        Route::get('/xuat-excel', [BaoCaoController::class, 'xuatExcel'])->name('xuat-excel');
    });

    // ===== QUẢN LÝ TÀI KHOẢN (Chỉ admin) =====
    Route::resource('tai-khoan', TaiKhoanController::class);
    Route::patch('/tai-khoan/{taiKhoan}/toggle-active', [TaiKhoanController::class, 'toggleActive'])
        ->name('tai-khoan.toggle-active');
});
// ===== THỜI KHÓA BIỂU =====
Route::prefix('thoi-khoa-bieu')->name('thoi-khoa-bieu.')->group(function () {
    Route::get('/',         [ThoiKhoaBieuController::class, 'index'])->name('index');
    Route::get('/them',     [ThoiKhoaBieuController::class, 'create'])->name('create');
    Route::post('/',        [ThoiKhoaBieuController::class, 'store'])->name('store');
    Route::delete('/{thoiKhoaBieu}', [ThoiKhoaBieuController::class, 'destroy'])->name('destroy');
});
Route::get('/register',  [RegisterController::class, 'showForm'])->name('register')->middleware('guest');
Route::post('/register', [RegisterController::class, 'register'])->middleware('guest');
require __DIR__.'/auth.php';