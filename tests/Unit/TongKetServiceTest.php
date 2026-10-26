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

    public function test_nam_hoc_hien_tai_doi_vao_thang_8(): void
    {
        $this->assertSame('2025-2026', NamHoc::hienTai(Carbon::create(2026, 3, 1)));
        $this->assertSame('2026-2027', NamHoc::hienTai(Carbon::create(2026, 8, 1)));
        $this->assertSame('2027-2028', NamHoc::keTiep('2026-2027'));
        $this->assertSame('2', NamHoc::hocKyHienTai(Carbon::create(2026, 2, 1)));
    }
}
