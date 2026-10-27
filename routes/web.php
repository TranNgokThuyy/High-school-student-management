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
use App\Http\Controllers\ThongBaoController;
use App\Http\Controllers\PhieuDiemController;
use App\Http\Controllers\TraCuuController;
use App\Http\Controllers\TinNhanPhuHuynhController;
use App\Http\Controllers\MaTraCuuController;
use App\Http\Controllers\NhatKyController;
use App\Http\Controllers\SaoLuuController;
use App\Http\Controllers\SoDiemController;
use App\Http\Controllers\DangKyMonController;
use App\Http\Controllers\NhapDiemExcelController;
use App\Http\Controllers\BienDongController;
use App\Http\Controllers\ThongKeController;
use App\Http\Controllers\KyThiController;
use App\Http\Controllers\GiaoVienController;
use App\Http\Controllers\PhuHuynhController;
use App\Http\Controllers\BaoMatController;
use App\Http\Controllers\Auth\XacThucHaiBuocController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ThoiKhoaBieuController;
use App\Http\Controllers\Auth\RegisterController;
// Trang chủ redirect
Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    return redirect()->route(auth()->user()->laPhuHuynh() ? 'phu-huynh.index' : 'dashboard');
});

// Dùng chung cho cán bộ và phụ huynh
Route::middleware(['auth', 'verified', 'check.active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::view('/doi-mat-khau', 'doi-mat-khau')->name('doi-mat-khau');

    // Xác thực 2 bước
    Route::get('/bao-mat',                 [BaoMatController::class, 'index'])->name('bao-mat.index');
    Route::post('/bao-mat',                [BaoMatController::class, 'bat'])->name('bao-mat.bat')->middleware('throttle:10,1');
    Route::post('/bao-mat/ma-khoi-phuc',   [BaoMatController::class, 'taoLaiMaKhoiPhuc'])->name('bao-mat.ma-khoi-phuc');
    Route::delete('/bao-mat',              [BaoMatController::class, 'tat'])->name('bao-mat.tat');
});

// Bước nhập mã xác thực khi đăng nhập
Route::middleware('guest')->group(function () {
    Route::get('/xac-thuc-2-buoc', [XacThucHaiBuocController::class, 'create'])->name('xac-thuc-hai-buoc');
    Route::post('/xac-thuc-2-buoc', [XacThucHaiBuocController::class, 'store'])->middleware('throttle:5,1');
});

// ===== CỔNG PHỤ HUYNH =====
Route::get('/phu-huynh/dang-ky', [PhuHuynhController::class, 'dangKyForm'])->name('phu-huynh.dang-ky')->middleware('guest');
Route::post('/phu-huynh/dang-ky', [PhuHuynhController::class, 'dangKy'])->middleware(['guest', 'throttle:5,1']);
Route::prefix('phu-huynh')->name('phu-huynh.')->middleware(['auth', 'check.active', 'role:phu-huynh'])->group(function () {
    Route::get('/',                      [PhuHuynhController::class, 'index'])->name('index');
    Route::post('/con',                  [PhuHuynhController::class, 'themCon'])->name('them-con')->middleware('throttle:5,1');
    Route::get('/con/{hocSinh}',         [PhuHuynhController::class, 'show'])->name('show');
    Route::delete('/con/{hocSinh}',      [PhuHuynhController::class, 'boLienKet'])->name('bo-lien-ket');
});

// Routes của cán bộ nhà trường (yêu cầu đăng nhập, tài khoản active, không phải phụ huynh)
Route::middleware(['auth', 'verified', 'check.active', 'can-bo'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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
        Route::get('/mau-excel', [NhapDiemExcelController::class, 'taiMau'])->name('mau-excel')->middleware('permission:them-diem');
        Route::post('/nhap-excel', [NhapDiemExcelController::class, 'taiLen'])->name('nhap-excel')->middleware('permission:them-diem');
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
        Route::get('/thong-ke', [ThongKeController::class, 'index'])->name('thong-ke')->middleware('permission:xem-bao-cao');
        Route::get('/diem-lop', [BaoCaoController::class, 'baoCaoDiemLop'])->name('diem-lop')->middleware('permission:xem-bao-cao');
        Route::get('/xuat-pdf', [BaoCaoController::class, 'xuatPDF'])->name('xuat-pdf')->middleware('permission:xuat-bao-cao');
        Route::get('/xuat-excel', [BaoCaoController::class, 'xuatExcel'])->name('xuat-excel')->middleware('permission:xuat-bao-cao');
        // Chứa mã định danh, địa chỉ... nên chỉ quản trị viên
        Route::get('/xuat-du-lieu', [BaoCaoController::class, 'xuatDuLieu'])->name('xuat-du-lieu')->middleware('role:quan-tri-vien');
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
        Route::post('/gui-ket-qua', [TongKetController::class, 'guiKetQua'])->name('gui-ket-qua')->middleware(['permission:xem-tong-ket', 'permission:gui-tin-phu-huynh']);
        Route::get('/he',       [TongKetController::class, 'he'])->name('he')->middleware('permission:xet-len-lop');
        Route::post('/he',      [TongKetController::class, 'luuHe'])->name('luu-he')->middleware('permission:xet-len-lop');
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

    // ===== PHIẾU ĐIỂM CÁ NHÂN =====
    Route::get('/hoc-sinh/{hocSinh}/phieu-diem', [PhieuDiemController::class, 'show'])->name('hoc-sinh.phieu-diem')
        ->middleware('permission:xem-diem');
    Route::get('/hoc-sinh/{hocSinh}/hoc-ba', [PhieuDiemController::class, 'hocBa'])->name('hoc-sinh.hoc-ba')
        ->middleware('permission:xem-diem');
    Route::get('/lop-hoc/{lopHoc}/so-diem', [SoDiemController::class, 'xuat'])->name('lop-hoc.so-diem')
        ->middleware('permission:xem-diem');

    // ===== NHẬT KÝ THAO TÁC & SAO LƯU (chỉ quản trị viên) =====
    Route::middleware('role:quan-tri-vien')->group(function () {
        Route::get('/nhat-ky', [NhatKyController::class, 'index'])->name('nhat-ky.index')->middleware('permission:xem-nhat-ky');

        Route::middleware('permission:quan-ly-sao-luu')->prefix('sao-luu')->name('sao-luu.')->group(function () {
            Route::get('/',                 [SaoLuuController::class, 'index'])->name('index');
            Route::post('/',                [SaoLuuController::class, 'store'])->name('store');
            Route::get('/{tenTep}/tai-ve',  [SaoLuuController::class, 'download'])->name('download');
            Route::delete('/{tenTep}',      [SaoLuuController::class, 'destroy'])->name('destroy');
        });
    });

    // ===== HỒ SƠ GIÁO VIÊN & GIỜ DẠY =====
    Route::middleware('permission:quan-ly-giao-vien')->group(function () {
        Route::get('/giao-vien', [GiaoVienController::class, 'index'])->name('giao-vien.index');
        Route::get('/giao-vien/{giaoVien}/ho-so', [GiaoVienController::class, 'edit'])->name('giao-vien.edit');
        Route::put('/giao-vien/{giaoVien}/ho-so', [GiaoVienController::class, 'update'])->name('giao-vien.update');
    });

    // ===== KỲ THI: LỊCH THI, PHÒNG THI, SỐ BÁO DANH =====
    Route::prefix('ky-thi')->name('ky-thi.')->middleware('permission:quan-ly-ky-thi')->group(function () {
        Route::get('/',                          [KyThiController::class, 'index'])->name('index');
        Route::post('/',                         [KyThiController::class, 'store'])->name('store');
        Route::get('/{kyThi}',                   [KyThiController::class, 'show'])->name('show');
        Route::delete('/{kyThi}',                [KyThiController::class, 'destroy'])->name('destroy');
        Route::post('/{kyThi}/lich',             [KyThiController::class, 'themLich'])->name('them-lich');
        Route::delete('/{kyThi}/lich/{lichThi}', [KyThiController::class, 'xoaLich'])->name('xoa-lich');
        Route::post('/{kyThi}/xep-phong',        [KyThiController::class, 'xepPhong'])->name('xep-phong');
        Route::get('/{kyThi}/in',                [KyThiController::class, 'inDanhSach'])->name('in');
    });

    // ===== BIẾN ĐỘNG HỌC SINH: CHUYỂN TRƯỜNG / THÔI HỌC =====
    Route::middleware('permission:quan-ly-bien-dong')->group(function () {
        Route::get('/bien-dong', [BienDongController::class, 'index'])->name('bien-dong.index');
        Route::get('/hoc-sinh/{hocSinh}/bien-dong', [BienDongController::class, 'create'])->name('bien-dong.create');
        Route::post('/hoc-sinh/{hocSinh}/bien-dong', [BienDongController::class, 'store'])->name('bien-dong.store');
    });

    // ===== ĐĂNG KÝ MÔN LỰA CHỌN (GDPT 2018) =====
    Route::get('/lop-hoc/{lopHoc}/mon-lua-chon', [DangKyMonController::class, 'edit'])->name('dang-ky-mon.edit')->middleware('permission:sua-hoc-sinh');
    Route::put('/lop-hoc/{lopHoc}/mon-lua-chon', [DangKyMonController::class, 'update'])->name('dang-ky-mon.update')->middleware('permission:sua-hoc-sinh');

    // ===== MÃ TRA CỨU PHỤ HUYNH =====
    Route::get('/lop-hoc/{lopHoc}/phieu-tra-cuu', [MaTraCuuController::class, 'inPhieuLop'])->name('ma-tra-cuu.phieu-lop')
        ->middleware('permission:xem-hoc-sinh');
    Route::post('/hoc-sinh/{hocSinh}/cap-lai-ma-tra-cuu', [MaTraCuuController::class, 'capLai'])->name('ma-tra-cuu.cap-lai')
        ->middleware('permission:sua-hoc-sinh');
    Route::delete('/hoc-sinh/{hocSinh}/tai-khoan-phu-huynh/{taiKhoan}', [MaTraCuuController::class, 'goTaiKhoan'])->name('ma-tra-cuu.go-tai-khoan')
        ->middleware('permission:sua-hoc-sinh');

    // ===== TIN NHẮN PHỤ HUYNH =====
    Route::prefix('tin-nhan')->name('tin-nhan.')->middleware('permission:gui-tin-phu-huynh')->group(function () {
        Route::get('/',                    [TinNhanPhuHuynhController::class, 'index'])->name('index');
        Route::get('/soan',                [TinNhanPhuHuynhController::class, 'create'])->name('create');
        Route::post('/',                   [TinNhanPhuHuynhController::class, 'store'])->name('store');
        Route::get('/{tinNhan}',           [TinNhanPhuHuynhController::class, 'show'])->name('show');
        Route::post('/{tinNhan}/gui-lai',  [TinNhanPhuHuynhController::class, 'guiLai'])->name('gui-lai');
    });

    // ===== THÔNG BÁO =====
    Route::get('/thong-bao', [ThongBaoController::class, 'index'])->name('thong-bao.index');
    Route::post('/thong-bao', [ThongBaoController::class, 'store'])->name('thong-bao.store')->middleware('permission:dang-thong-bao');
    Route::delete('/thong-bao/{thongBao}', [ThongBaoController::class, 'destroy'])->name('thong-bao.destroy')->middleware('permission:dang-thong-bao');

    // ===== HỌC PHÍ & KHOẢN THU =====
    Route::prefix('hoc-phi')->name('hoc-phi.')->group(function () {
        Route::get('/',                           [HocPhiController::class, 'index'])->name('index')->middleware('permission:xem-hoc-phi');
        Route::post('/nhac-no',                   [HocPhiController::class, 'nhacNo'])->name('nhac-no')->middleware(['permission:xem-hoc-phi', 'permission:gui-tin-phu-huynh']);
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
// ===== TRA CỨU CHO PHỤ HUYNH (không cần đăng nhập, giới hạn 10 lượt/phút) =====
Route::get('/tra-cuu', [TraCuuController::class, 'form'])->name('tra-cuu');
Route::post('/tra-cuu', [TraCuuController::class, 'traCuu'])->name('tra-cuu.ket-qua')->middleware('throttle:10,1');

Route::get('/register',  [RegisterController::class, 'showForm'])->name('register')->middleware('guest');
Route::post('/register', [RegisterController::class, 'register'])->middleware('guest');
require __DIR__.'/auth.php';
