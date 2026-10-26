<?php

namespace Tests\Feature\QuanLy;

use App\Models\User;

class PhanQuyenTest extends QuanLyTestCase
{
    public function test_giao_vien_khong_vao_duoc_quan_ly_tai_khoan(): void
    {
        $gv = $this->taoUser('giao-vien-chu-nhiem');

        $this->actingAs($gv)->get('/tai-khoan')->assertForbidden();
        $this->actingAs($gv)->get('/tai-khoan/create')->assertForbidden();
        $this->actingAs($gv)->post('/tai-khoan', [
            'name' => 'Hacker', 'email' => 'x@x.com', 'password' => '12345678',
            'password_confirmation' => '12345678', 'role' => 'quan-tri-vien',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'x@x.com']);
    }

    public function test_giao_vien_khong_tao_duoc_lop_mon_tkb(): void
    {
        $gv = $this->taoUser('giao-vien-chu-nhiem');

        $this->actingAs($gv)->get('/lop-hoc/create')->assertForbidden();
        $this->actingAs($gv)->get('/mon-hoc/create')->assertForbidden();
        $this->actingAs($gv)->get('/thoi-khoa-bieu/them')->assertForbidden();
        $this->actingAs($gv)->get('/phan-cong/them')->assertForbidden();
    }

    public function test_quan_tri_vien_vao_duoc_cac_trang_quan_ly(): void
    {
        $admin = $this->taoUser('quan-tri-vien');

        foreach (['/tai-khoan', '/lop-hoc/create', '/mon-hoc/create', '/phan-cong', '/diem-danh', '/tong-ket', '/khen-thuong'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_tai_khoan_chua_kich_hoat_khong_dang_nhap_duoc(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
