<?php

namespace Tests\Feature\QuanLy;

use App\Models\TinNhan;
use App\Models\User;

class PhuHuynhTest extends QuanLyTestCase
{
    private function dangKy(array $duLieu)
    {
        return $this->post('/phu-huynh/dang-ky', $duLieu + [
            'name' => 'Trần Thị Mẹ', 'email' => 'me@example.com', 'quan_he' => 'Mẹ',
            'password' => 'MatKhau123', 'password_confirmation' => 'MatKhau123',
        ]);
    }

    public function test_dang_ky_bang_ma_tra_cuu_va_xem_ket_qua_cac_con(): void
    {
        $lop = $this->taoLop();
        $anh = $this->taoHocSinh($lop, 'Nguyễn Văn Anh');
        $em = $this->taoHocSinh($lop, 'Nguyễn Thị Em');
        $nguoiKhac = $this->taoHocSinh($lop, 'Lê Văn Khác');

        // Sai mã → không tạo tài khoản
        $this->dangKy(['ma_hoc_sinh' => $anh->ma_hoc_sinh, 'ma_tra_cuu' => 'SAIMA123'])->assertSessionHasErrors('ma_hoc_sinh');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'me@example.com']);

        $this->dangKy(['ma_hoc_sinh' => strtolower($anh->ma_hoc_sinh), 'ma_tra_cuu' => strtolower($anh->ma_tra_cuu)])
            ->assertRedirect('/phu-huynh');
        $ph = User::where('email', 'me@example.com')->first();
        $this->assertTrue($ph->is_active && $ph->laPhuHuynh());
        $this->assertAuthenticatedAs($ph);

        // Thêm con thứ hai
        $this->post('/phu-huynh/con', ['ma_hoc_sinh' => $em->ma_hoc_sinh, 'ma_tra_cuu' => $em->ma_tra_cuu, 'quan_he' => 'Mẹ'])
            ->assertRedirect('/phu-huynh');
        $this->get('/phu-huynh')->assertOk()->assertSee('Nguyễn Văn Anh')->assertSee('Nguyễn Thị Em')->assertDontSee('Lê Văn Khác');

        // Tin nhắn nhà trường hiện ở cổng phụ huynh và được đánh dấu đã xem
        $tin = TinNhan::create(['tieu_de' => 'Họp phụ huynh', 'noi_dung' => 'x', 'loai' => 'Thông báo chung', 'pham_vi' => 'Lớp', 'kenh' => []]);
        $nhan = $tin->nguoiNhan()->create(['hoc_sinh_id' => $anh->id, 'noi_dung' => 'Mời họp lúc 8h']);
        $this->get("/phu-huynh/con/{$anh->id}")->assertOk()->assertSee('Họp phụ huynh')->assertSee('Mời họp lúc 8h');
        $this->assertNotNull($nhan->fresh()->da_xem_luc);

        // Không xem được học sinh không phải con mình
        $this->get("/phu-huynh/con/{$nguoiKhac->id}")->assertNotFound();

        // Bỏ liên kết
        $this->delete("/phu-huynh/con/{$em->id}")->assertRedirect('/phu-huynh');
        $this->assertFalse($ph->con()->whereKey($em->id)->exists());
    }

    public function test_phu_huynh_khong_vao_duoc_trang_can_bo(): void
    {
        $ph = $this->taoUser('phu-huynh');
        $lop = $this->taoLop();
        $hs = $this->taoHocSinh($lop);

        foreach (['/dashboard', '/hoc-sinh', "/hoc-sinh/{$hs->id}", '/thong-bao', '/lop-hoc'] as $url) {
            $this->actingAs($ph)->get($url)->assertRedirect('/phu-huynh');
        }
        $this->actingAs($ph)->post('/thong-bao', ['tieu_de' => 'x'])->assertForbidden();
        $this->actingAs($ph)->getJson('/api/hoc-sinh')->assertForbidden();
        $this->actingAs($ph)->get('/')->assertRedirect('/phu-huynh');
        $this->actingAs($ph)->get('/doi-mat-khau')->assertOk();

        // Cán bộ không vào cổng phụ huynh
        $this->actingAs($this->taoUser('quan-tri-vien'))->get('/phu-huynh')->assertForbidden();
    }

    public function test_dang_nhap_phu_huynh_vao_cong_va_gvcn_go_lien_ket(): void
    {
        $lop = $this->taoLop();
        $hs = $this->taoHocSinh($lop);
        $ph = User::factory()->create(['email' => 'cha@example.com']);
        $ph->assignRole('phu-huynh');
        $ph->con()->attach($hs->id, ['quan_he' => 'Cha']);

        $this->post('/login', ['email' => 'cha@example.com', 'password' => 'password'])->assertRedirect('/phu-huynh');
        auth()->logout();

        $gvcn = $this->taoUser('giao-vien-chu-nhiem');
        $lop->update(['giao_vien_chu_nhiem_id' => $gvcn->id]);
        $this->actingAs($gvcn)->get("/hoc-sinh/{$hs->id}")->assertOk()->assertSee('cha@example.com');
        $this->actingAs($gvcn)->delete("/hoc-sinh/{$hs->id}/tai-khoan-phu-huynh/{$ph->id}")->assertRedirect();
        $this->assertFalse($ph->con()->exists());
    }

    public function test_email_tin_nhan_dung_email_tai_khoan_khi_ho_so_trong(): void
    {
        $lop = $this->taoLop();
        $hs = $this->taoHocSinh($lop);
        $ph = User::factory()->create(['email' => 'me2@example.com']);
        $ph->assignRole('phu-huynh');
        $ph->con()->attach($hs->id);

        $tin = app(\App\Services\ThongBaoPhuHuynhService::class)->gui('Tiêu đề', 'Nội dung', 'Thông báo chung', 'x', collect([$hs]), ['email'], null);
        $this->assertSame('me2@example.com', $tin->nguoiNhan()->first()->email);
    }
}
