<?php

namespace Tests\Feature\QuanLy;

use App\Models\User;
use App\Support\Totp;

class BaoMatTest extends QuanLyTestCase
{
    public function test_totp_dung_vector_rfc_6238(): void
    {
        $khoa = Totp::base32Encode('12345678901234567890');
        $this->assertSame('GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ', $khoa);
        $this->assertSame('287082', Totp::ma($khoa, Totp::buocHienTai(59)));
        $this->assertSame('081804', Totp::ma($khoa, Totp::buocHienTai(1111111109)));
        $this->assertSame('12345678901234567890', Totp::base32Decode($khoa));
        // Lệch một bước vẫn chấp nhận, mã đã dùng thì không
        $this->assertSame(1, Totp::kiemTra($khoa, '287082', null, 80));
        $this->assertNull(Totp::kiemTra($khoa, '287082', 1, 80));
        $this->assertNull(Totp::kiemTra($khoa, '287082', null, 200));
    }

    public function test_tai_khoan_do_quan_tri_tao_phai_doi_mat_khau(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $this->actingAs($admin)->post('/tai-khoan', [
            'name' => 'GV Mới', 'email' => 'moi@school.com', 'password' => 'CapPhat123', 'password_confirmation' => 'CapPhat123',
            'role' => 'giao-vien-bo-mon', 'is_active' => 1,
        ])->assertRedirect();
        $gv = User::where('email', 'moi@school.com')->first();
        $this->assertTrue($gv->phai_doi_mat_khau);

        $this->actingAs($gv)->get('/dashboard')->assertRedirect('/doi-mat-khau');
        $this->actingAs($gv)->getJson('/api/me')->assertOk();
        $this->actingAs($gv)->getJson('/api/hoc-sinh')->assertForbidden();

        // Không được giữ nguyên mật khẩu cũ
        $this->actingAs($gv)->put('/password', ['current_password' => 'CapPhat123', 'password' => 'CapPhat123', 'password_confirmation' => 'CapPhat123'])
            ->assertSessionHasErrorsIn('updatePassword', 'password');
        $this->actingAs($gv)->put('/password', ['current_password' => 'CapPhat123', 'password' => 'RiengToi456', 'password_confirmation' => 'RiengToi456'])
            ->assertRedirect('/');
        $this->assertFalse($gv->fresh()->phai_doi_mat_khau);
        $this->actingAs($gv->fresh())->get('/dashboard')->assertOk();
    }

    public function test_bat_hai_buoc_va_dang_nhap_bang_ma(): void
    {
        $gv = User::factory()->create(['email' => 'gv@school.com']);
        $gv->assignRole('giao-vien-bo-mon');

        $this->actingAs($gv)->get('/bao-mat')->assertOk()->assertSee('<svg', false);
        $khoa = session('khoa_2fa_tam');
        $this->actingAs($gv)->post('/bao-mat', ['ma' => '000000'])->assertSessionHasErrors('ma');
        $this->actingAs($gv)->post('/bao-mat', ['ma' => Totp::ma($khoa, Totp::buocHienTai())])
            ->assertRedirect('/bao-mat')->assertSessionHas('ma_khoi_phuc');
        $gv->refresh();
        $this->assertTrue($gv->daBatHaiBuoc());
        $maKhoiPhuc = $gv->two_factor_recovery_codes[0];
        auth()->logout();

        // Mật khẩu đúng → chưa đăng nhập, chuyển sang bước nhập mã
        $this->post('/login', ['email' => 'gv@school.com', 'password' => 'password'])->assertRedirect('/xac-thuc-2-buoc');
        $this->assertGuest();
        $this->post('/xac-thuc-2-buoc', ['ma' => '123456'])->assertSessionHasErrors('ma');
        $this->assertGuest();

        // Mã khôi phục dùng được một lần
        $this->post('/xac-thuc-2-buoc', ['ma' => strtolower($maKhoiPhuc)])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($gv);
        $this->assertNotContains($maKhoiPhuc, $gv->fresh()->two_factor_recovery_codes);
        auth()->logout();

        $this->post('/login', ['email' => 'gv@school.com', 'password' => 'password']);
        $this->post('/xac-thuc-2-buoc', ['ma' => $maKhoiPhuc])->assertSessionHasErrors('ma');

        // Không vào thẳng bước 2 khi chưa nhập mật khẩu
        $this->flushSession();
        $this->get('/xac-thuc-2-buoc')->assertRedirect('/login');

        // API cũng phải gửi kèm mã
        $this->postJson('/api/login', ['email' => 'gv@school.com', 'password' => 'password'])->assertStatus(422)->assertJsonValidationErrors('ma_xac_thuc');
        $this->assertGuest();
    }

    public function test_quan_tri_vien_bat_buoc_hai_buoc(): void
    {
        config(['auth.bat_buoc_2fa_quan_tri' => true]);
        $admin = $this->taoUser('quan-tri-vien');
        $gv = $this->taoUser('giao-vien-bo-mon');

        $this->actingAs($admin)->get('/hoc-sinh')->assertRedirect('/bao-mat');
        $this->actingAs($gv)->get('/hoc-sinh')->assertOk();

        $admin->forceFill(['two_factor_secret' => Totp::taoKhoa(), 'two_factor_confirmed_at' => now(), 'two_factor_recovery_codes' => []])->save();
        $this->actingAs($admin->fresh())->get('/hoc-sinh')->assertOk();
        $this->actingAs($admin->fresh())->delete('/bao-mat', ['current_password' => 'password'])->assertSessionHas('error');
        $this->assertTrue($admin->fresh()->daBatHaiBuoc());

        // Quản trị viên gỡ 2FA cho người dùng mất điện thoại
        $gv->forceFill(['two_factor_secret' => Totp::taoKhoa(), 'two_factor_confirmed_at' => now()])->save();
        $this->actingAs($admin->fresh())->put("/tai-khoan/{$gv->id}", [
            'name' => 'Lê Văn Giáo', 'email' => $gv->email, 'role' => 'giao-vien-bo-mon', 'is_active' => 1, 'dat_lai_2fa' => 1,
        ])->assertRedirect();
        $this->assertFalse($gv->fresh()->daBatHaiBuoc());
    }
}
