<?php

namespace Tests\Unit;

use App\Services\TongKetService;
use App\Support\NamHoc;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class TongKetServiceTest extends TestCase
{
    public function test_xep_loai_hoc_tap_theo_thong_tu_22(): void
    {
        $this->assertSame('Tốt', TongKetService::xepLoaiHocTap([8, 8, 8, 8, 8, 8, 6.5, 7]));
        // Có môn dưới 6.5 → không đạt Tốt
        $this->assertSame('Khá', TongKetService::xepLoaiHocTap([9, 9, 9, 9, 9, 9, 6.4]));
        $this->assertSame('Khá', TongKetService::xepLoaiHocTap([6.5, 6.5, 6.5, 6.5, 6.5, 6.5, 5.0]));
        // Một môn dưới 5 nhưng không dưới 3.5 → Đạt
        $this->assertSame('Đạt', TongKetService::xepLoaiHocTap([7, 7, 7, 7, 7, 7, 4.9]));
        // Hai môn dưới 5 → Chưa đạt
        $this->assertSame('Chưa đạt', TongKetService::xepLoaiHocTap([7, 7, 7, 7, 7, 4.9, 4.9]));
        // Có môn dưới 3.5 → Chưa đạt
        $this->assertSame('Chưa đạt', TongKetService::xepLoaiHocTap([9, 9, 9, 9, 9, 9, 3.4]));
        $this->assertNull(TongKetService::xepLoaiHocTap([]));
    }

    public function test_muc_dat_can_it_nhat_6_mon_tu_5_diem(): void
    {
        // TT22: Đạt khi ít nhất 6 môn điểm số >= 5.0 và không môn nào < 3.5 (cho phép nhiều môn dưới 5)
        $this->assertSame('Đạt', TongKetService::xepLoaiHocTap([6, 6, 6, 6, 6, 6, 4, 4, 4]));
        $this->assertSame('Chưa đạt', TongKetService::xepLoaiHocTap([6, 6, 6, 6, 6, 4, 4, 4]));
    }

    public function test_xep_loai_co_mon_nhan_xet(): void
    {
        $diemTot = [8, 8, 8, 8, 8, 8, 7];
        $this->assertSame('Tốt', TongKetService::xepLoaiHocTap([...$diemTot, 'Đạt', 'Đạt']));
        // Một môn nhận xét Chưa đạt: không được Tốt / Khá, vẫn có thể Đạt
        $this->assertSame('Đạt', TongKetService::xepLoaiHocTap([...$diemTot, 'Đạt', 'Chưa đạt']));
        // Hai môn nhận xét Chưa đạt: Chưa đạt
        $this->assertSame('Chưa đạt', TongKetService::xepLoaiHocTap([...$diemTot, 'Chưa đạt', 'Chưa đạt']));
        // Môn nhận xét không tính vào số môn đạt 9.0 của danh hiệu
        $this->assertSame('Học sinh Giỏi', TongKetService::danhHieu('Tốt', 'Tốt', [9, 9, 9, 9, 9, 8, 'Đạt', 'Đạt']));
    }

    public function test_ket_qua_mon_nhan_xet(): void
    {
        $diem = new \App\Models\DiemSo();
        $diem->nhan_xet = ['tx1' => 'Đ', 'tx2' => 'CĐ', 'gk' => 'Đ', 'ck' => 'Đ'];
        $this->assertSame('Đạt', $diem->tinhKetQuaNhanXet());          // 3/4 Đạt, cuối kỳ Đạt

        $diem->nhan_xet = ['tx1' => 'Đ', 'tx2' => 'Đ', 'gk' => 'Đ', 'ck' => 'CĐ'];
        $this->assertSame('Chưa đạt', $diem->tinhKetQuaNhanXet());     // cuối kỳ Chưa đạt

        $diem->nhan_xet = ['tx1' => 'CĐ', 'tx2' => 'CĐ', 'gk' => 'Đ', 'ck' => 'Đ'];
        $this->assertSame('Chưa đạt', $diem->tinhKetQuaNhanXet());     // chỉ 2/4 Đạt

        $diem->nhan_xet = ['tx1' => 'Đ'];
        $this->assertNull($diem->tinhKetQuaNhanXet());                 // chưa có cuối kỳ
    }

    public function test_danh_hieu(): void
    {
        $xuatSac = [9, 9, 9, 9, 9, 9, 8];
        $this->assertSame('Học sinh Xuất sắc', TongKetService::danhHieu('Tốt', 'Tốt', $xuatSac));
        $this->assertSame('Học sinh Giỏi', TongKetService::danhHieu('Tốt', 'Tốt', [8, 8, 8, 8, 8, 8, 8]));
        $this->assertNull(TongKetService::danhHieu('Tốt', 'Khá', $xuatSac));
        $this->assertNull(TongKetService::danhHieu('Khá', 'Tốt', $xuatSac));
    }

    public function test_xet_len_lop(): void
    {
        $this->assertTrue(TongKetService::xetLenLop('Đạt', 'Đạt', 45, '10')['dat']);
        $this->assertSame('Hoàn thành chương trình', TongKetService::xetLenLop('Khá', 'Tốt', 0, '12')['ket_qua']);
        $this->assertSame('Ở lại lớp', TongKetService::xetLenLop('Tốt', 'Tốt', 46, '11')['ket_qua']);
        $this->assertSame('Chưa được lên lớp', TongKetService::xetLenLop('Chưa đạt', 'Tốt', 0, '10')['ket_qua']);
        $this->assertSame('Chưa đủ dữ liệu', TongKetService::xetLenLop(null, 'Tốt', 0, '10')['ket_qua']);
    }

    public function test_muc_ren_luyen_quy_doi_tu_hanh_kiem(): void
    {
        $this->assertSame('Đạt', TongKetService::mucRenLuyen('Trung bình'));
        $this->assertSame('Chưa đạt', TongKetService::mucRenLuyen('Yếu'));
        $this->assertNull(TongKetService::mucRenLuyen(null));
    }

    public function test_ren_luyen_ca_nam_theo_tt22(): void
    {
        $caNam = fn ($hk1, $hk2) => \App\Models\HanhKiem::xepLoaiCaNam($hk1, $hk2);
        // Tốt: HK2 Tốt, HK1 từ Khá trở lên
        $this->assertSame('Tốt', $caNam('Khá', 'Tốt'));
        // Khá: HK2 Khá & HK1 >= Đạt; HK2 Đạt & HK1 Tốt; HK2 Tốt & HK1 Đạt/Chưa đạt
        $this->assertSame('Khá', $caNam('Đạt', 'Khá'));
        $this->assertSame('Khá', $caNam('Tốt', 'Đạt'));
        $this->assertSame('Khá', $caNam('Chưa đạt', 'Tốt'));
        // Đạt: HK2 Đạt & HK1 Khá/Đạt/Chưa đạt; HK2 Khá & HK1 Chưa đạt
        $this->assertSame('Đạt', $caNam('Chưa đạt', 'Đạt'));
        $this->assertSame('Đạt', $caNam('Chưa đạt', 'Khá'));
        // Còn lại: Chưa đạt
        $this->assertSame('Chưa đạt', $caNam('Tốt', 'Chưa đạt'));
    }

    public function test_nam_hoc_hien_tai_doi_vao_thang_8(): void
    {
        $this->assertSame('2025-2026', NamHoc::hienTai(Carbon::create(2026, 3, 1)));
        $this->assertSame('2026-2027', NamHoc::hienTai(Carbon::create(2026, 8, 1)));
        $this->assertSame('2027-2028', NamHoc::keTiep('2026-2027'));
        $this->assertSame('2', NamHoc::hocKyHienTai(Carbon::create(2026, 2, 1)));
    }
}
