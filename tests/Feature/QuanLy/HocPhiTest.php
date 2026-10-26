<?php

namespace Tests\Feature\QuanLy;

use App\Models\KhoanThu;
use App\Models\LopHoc;
use App\Models\MienGiam;
use App\Models\PhieuThu;
use App\Services\HocPhiService;

class HocPhiTest extends QuanLyTestCase
{
    private function taoKhoanThu(LopHoc $lop, array $thuocTinh = []): KhoanThu
    {
        return KhoanThu::create($thuocTinh + [
            'ten' => 'Học phí HK1', 'loai' => 'Học phí', 'nam_hoc' => $lop->nam_hoc, 'so_tien' => 1_000_000,
        ]);
    }

    public function test_ke_toan_thu_tien_va_cong_no_giam(): void
    {
        $keToan = $this->taoUser('ke-toan');
        $lop = $this->taoLop();
        $hs = $this->taoHocSinh($lop);
        $kt = $this->taoKhoanThu($lop);

        $this->actingAs($keToan)->post("/hoc-phi/hoc-sinh/{$hs->id}/thu", [
            'khoan_thu_id' => $kt->id, 'so_tien' => 400000, 'ngay_thu' => today()->toDateString(), 'hinh_thuc' => 'Tiền mặt',
        ])->assertRedirect("/hoc-phi/hoc-sinh/{$hs->id}")->assertSessionHas('success');

        $phieu = PhieuThu::first();
        $this->assertMatchesRegularExpression('/^PT\d{4}-\d{6}$/', $phieu->so_phieu);
        $this->assertSame($keToan->id, $phieu->nguoi_thu_id);

        $congNo = app(HocPhiService::class)->congNoHocSinh($hs, $lop)->first();
        $this->assertSame(600000, $congNo['con_no']);

        $this->actingAs($keToan)->get("/hoc-phi/phieu-thu/{$phieu->id}")->assertOk()->assertSee('Bốn trăm nghìn đồng');
        $this->actingAs($keToan)->get('/hoc-phi/so-thu')->assertOk()->assertSee('400.000 đ');
    }

    public function test_khong_thu_vuot_so_con_no(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $lop = $this->taoLop();
        $hs = $this->taoHocSinh($lop);
        $kt = $this->taoKhoanThu($lop);

        $this->actingAs($admin)->post("/hoc-phi/hoc-sinh/{$hs->id}/thu", [
            'khoan_thu_id' => $kt->id, 'so_tien' => 1_000_001, 'ngay_thu' => today()->toDateString(), 'hinh_thuc' => 'Tiền mặt',
        ])->assertSessionHas('error');

        $this->assertSame(0, PhieuThu::count());
    }

    public function test_khong_thu_khoan_cua_khoi_khac(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $lop = $this->taoLop();
        $hs = $this->taoHocSinh($lop);
        $kt = $this->taoKhoanThu($lop, ['khoi' => '12']);

        $this->actingAs($admin)->post("/hoc-phi/hoc-sinh/{$hs->id}/thu", [
            'khoan_thu_id' => $kt->id, 'so_tien' => 1000, 'ngay_thu' => today()->toDateString(), 'hinh_thuc' => 'Tiền mặt',
        ])->assertSessionHas('error');
    }

    public function test_mien_giam_chi_ap_dung_khoan_duoc_danh_dau(): void
    {
        $lop = $this->taoLop();
        $hs = $this->taoHocSinh($lop);
        $hocPhi = $this->taoKhoanThu($lop);
        $bhyt = $this->taoKhoanThu($lop, ['ten' => 'BHYT', 'loai' => 'Bảo hiểm', 'so_tien' => 800000, 'ap_dung_mien_giam' => false]);
        MienGiam::create(['hoc_sinh_id' => $hs->id, 'nam_hoc' => $lop->nam_hoc, 'doi_tuong' => 'Hộ cận nghèo', 'phan_tram' => 50]);

        $congNo = app(HocPhiService::class)->congNoLop($lop);
        $chiTiet = $congNo['hoc_sinh'][0]['chi_tiet'];

        $this->assertSame(500000, $chiTiet[$hocPhi->id]['phai_nop']);
        $this->assertSame(800000, $chiTiet[$bhyt->id]['phai_nop']);
        $this->assertSame(1_300_000, $congNo['tong']['con_no']);
    }

    public function test_huy_phieu_tra_lai_cong_no_va_chi_admin_duoc_huy(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $keToan = $this->taoUser('ke-toan');
        $lop = $this->taoLop();
        $hs = $this->taoHocSinh($lop);
        $kt = $this->taoKhoanThu($lop);
        $phieu = PhieuThu::create([
            'hoc_sinh_id' => $hs->id, 'khoan_thu_id' => $kt->id, 'so_tien' => 1_000_000, 'ngay_thu' => today(), 'hinh_thuc' => 'Tiền mặt',
        ]);

        $this->actingAs($keToan)->post("/hoc-phi/phieu-thu/{$phieu->id}/huy", ['ly_do_huy' => 'x'])->assertForbidden();
        $this->actingAs($admin)->post("/hoc-phi/phieu-thu/{$phieu->id}/huy", [])->assertSessionHasErrors('ly_do_huy');
        $this->actingAs($admin)->post("/hoc-phi/phieu-thu/{$phieu->id}/huy", ['ly_do_huy' => 'Thu nhầm'])->assertSessionHas('success');

        $this->assertTrue($phieu->fresh()->da_huy);
        $this->assertSame(1_000_000, app(HocPhiService::class)->congNoHocSinh($hs, $lop)->first()['con_no']);

        // Khoản thu đã có phiếu (kể cả phiếu hủy) thì không được xóa
        $this->actingAs($admin)->delete("/khoan-thu/{$kt->id}")->assertSessionHas('error');
        $this->assertModelExists($kt);
    }

    public function test_gvcn_chi_xem_cong_no_lop_minh_va_khong_thu_duoc(): void
    {
        $gv = $this->taoUser('giao-vien-chu-nhiem');
        $lopMinh = $this->taoLop(['giao_vien_chu_nhiem_id' => $gv->id]);
        $lopKhac = $this->taoLop(['ten_lop' => '10A2']);
        $hs = $this->taoHocSinh($lopMinh);
        $kt = $this->taoKhoanThu($lopMinh);

        $this->actingAs($gv)->get("/hoc-phi?lop_hoc_id={$lopMinh->id}")->assertOk()->assertSee($hs->ho_ten);
        $this->actingAs($gv)->get("/hoc-phi?lop_hoc_id={$lopKhac->id}")->assertForbidden();
        $this->actingAs($gv)->get("/hoc-phi/xuat-excel?lop_hoc_id={$lopMinh->id}")->assertOk();
        $this->actingAs($gv)->post("/hoc-phi/hoc-sinh/{$hs->id}/thu", [
            'khoan_thu_id' => $kt->id, 'so_tien' => 1000, 'ngay_thu' => today()->toDateString(), 'hinh_thuc' => 'Tiền mặt',
        ])->assertForbidden();
        $this->actingAs($gv)->get('/khoan-thu')->assertForbidden();
    }

    public function test_ke_toan_quan_ly_khoan_thu(): void
    {
        $keToan = $this->taoUser('ke-toan');

        $this->actingAs($keToan)->post('/khoan-thu', [
            'ten' => 'Quỹ hội cha mẹ học sinh', 'loai' => 'Quỹ', 'nam_hoc' => '2026-2027', 'hoc_ky' => 'Ca năm',
            'khoi' => 'Tất cả', 'so_tien' => '200.000', 'is_active' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('khoan_thu', ['ten' => 'Quỹ hội cha mẹ học sinh', 'so_tien' => 200000, 'ap_dung_mien_giam' => false]);
    }
}
