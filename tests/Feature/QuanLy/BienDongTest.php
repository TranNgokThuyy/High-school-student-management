<?php

namespace Tests\Feature\QuanLy;

use App\Models\BienDongHocSinh;
use App\Models\HocSinh;

class BienDongTest extends QuanLyTestCase
{
    public function test_chuyen_di_hoc_lai_va_si_so(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $lop = $this->taoLop(['nam_hoc' => \App\Support\NamHoc::hienTai()]);
        $hs = $this->taoHocSinh($lop);
        $this->taoHocSinh($lop, 'Bạn cùng lớp');
        $lop->capNhatSiSo();
        $this->assertSame(2, $lop->fresh()->si_so);

        $this->actingAs($admin)->post("/hoc-sinh/{$hs->id}/bien-dong", ['loai' => 'Chuyển đi', 'ngay' => today()->toDateString()])
            ->assertSessionHasErrors('truong');
        $this->actingAs($admin)->post("/hoc-sinh/{$hs->id}/bien-dong", [
            'loai' => 'Chuyển đi', 'ngay' => today()->toDateString(), 'so_quyet_dinh' => '12/QĐ', 'truong' => 'THPT Lê Lợi',
        ])->assertRedirect("/hoc-sinh/{$hs->id}");

        $this->assertSame('Chuyển trường', $hs->fresh()->trang_thai);
        $this->assertSame(1, $lop->fresh()->si_so);
        $this->assertSame('THPT Lê Lợi', BienDongHocSinh::first()->truong);

        // Đã chuyển đi thì không ghi nhận thôi học được nữa; học lại thì về trạng thái đang học
        $this->actingAs($admin)->post("/hoc-sinh/{$hs->id}/bien-dong", ['loai' => 'Thôi học', 'ngay' => today()->toDateString()])
            ->assertSessionHas('error');
        $this->assertSame(1, BienDongHocSinh::count());
        $this->actingAs($admin)->post("/hoc-sinh/{$hs->id}/bien-dong", [
            'loai' => 'Học lại', 'ngay' => today()->toDateString(), 'lop_hoc_id' => $lop->id,
        ])->assertRedirect();
        $this->assertSame('Đang học', $hs->fresh()->trang_thai);
        $this->assertSame(2, $lop->fresh()->si_so);

        $this->actingAs($admin)->get('/bien-dong')->assertOk()->assertSee('THPT Lê Lợi');
    }

    public function test_tiep_nhan_hoc_sinh_chuyen_den(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $lop = $this->taoLop();

        $this->actingAs($admin)->post('/hoc-sinh', [
            'ho_ten' => 'Chuyển Đến', 'ngay_sinh' => '2010-02-02', 'gioi_tinh' => 'Nữ', 'trang_thai' => 'Đang học',
            'lop_hoc_id' => $lop->id, 'chuyen_den' => 1, 'truong_cu' => 'THPT Trần Phú', 'so_quyet_dinh_den' => '05/QĐ',
        ])->assertRedirect();

        $bd = BienDongHocSinh::first();
        $this->assertSame('Chuyển đến', $bd->loai);
        $this->assertSame(HocSinh::where('ho_ten', 'Chuyển Đến')->value('id'), $bd->hoc_sinh_id);
    }

    public function test_giao_vien_khong_quan_ly_bien_dong(): void
    {
        $gv = $this->taoUser('giao-vien-chu-nhiem');
        $hs = $this->taoHocSinh($this->taoLop());

        $this->actingAs($gv)->get('/bien-dong')->assertForbidden();
        $this->actingAs($gv)->post("/hoc-sinh/{$hs->id}/bien-dong", ['loai' => 'Thôi học', 'ngay' => today()->toDateString()])
            ->assertForbidden();
    }
}
