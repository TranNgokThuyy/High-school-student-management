<?php

namespace Tests\Feature\QuanLy;

use App\Models\DiemSo;
use App\Models\MonHoc;
use App\Models\ThongBao;

class TienIchTest extends QuanLyTestCase
{
    public function test_thong_bao_chi_hien_cho_vai_tro_duoc_gui(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $gvcn = $this->taoUser('giao-vien-chu-nhiem');
        $gvbm = $this->taoUser('giao-vien-bo-mon');

        $this->actingAs($admin)->post('/thong-bao', [
            'tieu_de' => 'Họp GVCN', 'noi_dung' => 'Thứ 6 tuần này', 'muc_do' => 'Quan trọng',
            'doi_tuong' => ['giao-vien-chu-nhiem'],
        ])->assertRedirect('/thong-bao');

        $this->actingAs($gvcn)->get('/dashboard')->assertSee('Họp GVCN');
        $this->actingAs($gvbm)->get('/dashboard')->assertDontSee('Họp GVCN');
        $this->actingAs($gvbm)->post('/thong-bao', ['tieu_de' => 'x', 'noi_dung' => 'x', 'muc_do' => 'Khẩn'])->assertForbidden();
    }

    public function test_thong_bao_het_han_khong_hien_tren_dashboard(): void
    {
        $gv = $this->taoUser('giao-vien-chu-nhiem');
        ThongBao::create(['tieu_de' => 'Cũ rồi', 'noi_dung' => 'x', 'het_han' => today()->subDay()]);

        $this->actingAs($gv)->get('/dashboard')->assertDontSee('Cũ rồi');
    }

    public function test_phu_huynh_tra_cuu_bang_ma_va_ngay_sinh(): void
    {
        $lop = $this->taoLop();
        $hs = $this->taoHocSinh($lop, 'Nguyễn Tra Cứu');
        $hs->update(['cccd' => '012345678901', 'dia_chi_thuong_tru' => 'Số 1 Phố Bí Mật']);
        $mon = MonHoc::create(['ma_mon' => 'TOAN', 'ten_mon' => 'Toán', 'so_tiet_tuan' => 4, 'khoi' => 'Tất cả']);
        DiemSo::create(['hoc_sinh_id' => $hs->id, 'mon_hoc_id' => $mon->id, 'lop_hoc_id' => $lop->id,
            'hoc_ky' => '1', 'nam_hoc' => $lop->nam_hoc, 'diem_trung_binh' => 8.25]);

        $this->post('/tra-cuu', ['ma_hoc_sinh' => strtolower($hs->ma_hoc_sinh), 'ngay_sinh' => '2010-01-01'])
            ->assertOk()
            ->assertSee('Nguyễn Tra Cứu')
            ->assertSee('8.3')
            ->assertDontSee('012345678901')
            ->assertDontSee('Số 1 Phố Bí Mật');

        $this->post('/tra-cuu', ['ma_hoc_sinh' => $hs->ma_hoc_sinh, 'ngay_sinh' => '2010-01-02'])
            ->assertSessionHasErrors('ma_hoc_sinh');
    }

    public function test_tra_cuu_gioi_han_so_lan(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/tra-cuu', ['ma_hoc_sinh' => 'HS0', 'ngay_sinh' => '2010-01-01']);
        }

        $this->post('/tra-cuu', ['ma_hoc_sinh' => 'HS0', 'ngay_sinh' => '2010-01-01'])->assertStatus(429);
    }

    public function test_in_phieu_diem_pdf(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $gvKhac = $this->taoUser('giao-vien-chu-nhiem');
        $lop = $this->taoLop();
        $hs = $this->taoHocSinh($lop);
        $mon = MonHoc::create(['ma_mon' => 'VAN', 'ten_mon' => 'Ngữ văn', 'so_tiet_tuan' => 3, 'khoi' => 'Tất cả']);
        DiemSo::create(['hoc_sinh_id' => $hs->id, 'mon_hoc_id' => $mon->id, 'lop_hoc_id' => $lop->id,
            'hoc_ky' => '1', 'nam_hoc' => $lop->nam_hoc, 'diem_trung_binh' => 7]);

        $this->actingAs($admin)->get("/hoc-sinh/{$hs->id}/phieu-diem")
            ->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->actingAs($gvKhac)->get("/hoc-sinh/{$hs->id}/phieu-diem")->assertForbidden();
    }
}
