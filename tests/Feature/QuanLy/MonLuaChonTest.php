<?php

namespace Tests\Feature\QuanLy;

use App\Models\DangKyMon;
use App\Models\DiemSo;
use App\Models\MonHoc;
use App\Services\TongKetService;

class MonLuaChonTest extends QuanLyTestCase
{
    public function test_dang_ky_mon_lua_chon_va_tong_ket_chi_tinh_mon_da_hoc(): void
    {
        $gv = $this->taoUser('giao-vien-chu-nhiem');
        $admin = $this->taoUser('quan-tri-vien');
        $lop = $this->taoLop(['giao_vien_chu_nhiem_id' => $gv->id]);
        $an = $this->taoHocSinh($lop, 'An');
        $binh = $this->taoHocSinh($lop, 'Bình');
        $toan = $this->taoMon('TOAN');
        $ly = MonHoc::create(['ma_mon' => 'LY', 'ten_mon' => 'Vật lý', 'so_tiet_tuan' => 2, 'khoi' => 'Tất cả', 'loai_mon' => 'Lựa chọn']);
        $dia = MonHoc::create(['ma_mon' => 'DIA', 'ten_mon' => 'Địa lý', 'so_tiet_tuan' => 2, 'khoi' => 'Tất cả', 'loai_mon' => 'Lựa chọn']);

        // GVCN đăng ký: An học Lý, Bình học Địa
        $this->actingAs($gv)->get("/lop-hoc/{$lop->id}/mon-lua-chon")->assertOk()->assertSee('Vật lý');
        $this->actingAs($gv)->put("/lop-hoc/{$lop->id}/mon-lua-chon", [
            'dang_ky' => [$an->id => [$ly->id], $binh->id => [$dia->id, $toan->id]], // môn bắt buộc gửi lên bị bỏ qua
        ])->assertRedirect();
        $this->assertSame(2, DangKyMon::count());

        // Nhập điểm Lý chỉ có An, điểm gửi cho Bình bị bỏ qua
        $this->actingAs($admin)->get("/diem-so/nhap-diem?lop_hoc_id={$lop->id}&mon_hoc_id={$ly->id}&hoc_ky=1&nam_hoc={$lop->nam_hoc}")
            ->assertOk()->assertSee('An')->assertDontSee('Bình');
        $this->actingAs($admin)->post('/diem-so/luu-diem', [
            'lop_hoc_id' => $lop->id, 'mon_hoc_id' => $ly->id, 'hoc_ky' => '1', 'nam_hoc' => $lop->nam_hoc,
            'diem' => [$an->id => ['diem_tx1' => 8, 'diem_gk' => 8, 'diem_ck' => 8], $binh->id => ['diem_tx1' => 5, 'diem_gk' => 5, 'diem_ck' => 5]],
        ]);
        $this->assertSame([$an->id], DiemSo::where('mon_hoc_id', $ly->id)->pluck('hoc_sinh_id')->all());

        foreach ([[$an, $toan], [$binh, $toan], [$binh, $dia]] as [$hs, $mon]) {
            DiemSo::create(['hoc_sinh_id' => $hs->id, 'mon_hoc_id' => $mon->id, 'lop_hoc_id' => $lop->id,
                'hoc_ky' => '1', 'nam_hoc' => $lop->nam_hoc, 'diem_trung_binh' => 7]);
        }

        $ketQua = app(TongKetService::class)->tongKetLop($lop, '1')['ket_qua']->keyBy(fn ($r) => $r['hoc_sinh']->id);
        $this->assertEqualsCanonicalizing([$toan->id, $ly->id], array_keys($ketQua[$an->id]['diem_mon']));
        $this->assertEqualsCanonicalizing([$toan->id, $dia->id], array_keys($ketQua[$binh->id]['diem_mon']));
        // Không bị coi là "chưa đủ điểm" vì môn không học
        $this->assertTrue($ketQua[$an->id]['du_diem']);

        $this->actingAs($gv)->get("/tong-ket?lop_hoc_id={$lop->id}&hoc_ky=1")->assertOk();
        $this->actingAs($admin)->get("/hoc-sinh/{$an->id}/phieu-diem?nam_hoc={$lop->nam_hoc}")->assertOk();
    }

    public function test_gvcn_khong_dang_ky_mon_cho_lop_khac(): void
    {
        $gv = $this->taoUser('giao-vien-chu-nhiem');
        $lop = $this->taoLop();

        $this->actingAs($gv)->get("/lop-hoc/{$lop->id}/mon-lua-chon")->assertForbidden();
    }
}
