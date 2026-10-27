<?php

namespace Tests\Feature\QuanLy;

use App\Models\ZaloToken;
use App\Services\Sms\ZaloTokenService;
use App\Services\Sms\ZaloZnsGateway;
use Illuminate\Support\Facades\Http;

class ZaloTokenTest extends QuanLyTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.zalo.app_id' => 'app123', 'services.zalo.app_secret' => 'bimat', 'services.zalo.template_id' => '999']);
    }

    public function test_lenh_lam_moi_token_khi_sap_het_han_va_luu_refresh_token_moi(): void
    {
        Http::fake(['oauth.zaloapp.com/*' => Http::response(['access_token' => 'tok-moi', 'refresh_token' => 'ref-moi', 'expires_in' => '90000'])]);
        app(ZaloTokenService::class)->luu('tok-cu', 'ref-cu', 3600 * 24);

        // Còn 24 giờ → chưa làm mới
        $this->artisan('zalo:lam-moi-token')->expectsOutputToContain('còn hạn')->assertSuccessful();
        Http::assertNothingSent();

        $this->travel(22)->hours();
        $this->artisan('zalo:lam-moi-token')->assertSuccessful();

        Http::assertSent(fn ($req) => $req->url() === ZaloTokenService::URL
            && $req->hasHeader('secret_key', 'bimat')
            && $req['app_id'] === 'app123' && $req['refresh_token'] === 'ref-cu' && $req['grant_type'] === 'refresh_token');

        $token = ZaloToken::hienTai();
        $this->assertSame('tok-moi', $token->access_token);
        $this->assertSame('ref-moi', $token->refresh_token);
        $this->assertTrue($token->het_han_luc->gt(now()->addHours(24)));
        // Token được mã hóa trong CSDL
        $this->assertStringNotContainsString('tok-moi', (string) \DB::table('zalo_token')->value('access_token'));
    }

    public function test_gui_zns_tu_lam_moi_token_het_han_va_ghi_loi_khi_that_bai(): void
    {
        Http::fake([
            'oauth.zaloapp.com/*' => Http::sequence()
                ->push(['access_token' => 'tok-moi', 'refresh_token' => 'ref-moi', 'expires_in' => 90000])
                ->push(['error' => -14014, 'error_description' => 'Invalid refresh token']),
            'business.openapi.zalo.me/*' => Http::response(['error' => 0]),
        ]);
        app(ZaloTokenService::class)->luu('tok-cu', 'ref-cu', 60);

        app(ZaloZnsGateway::class)->gui('0912345678', ['ho_ten' => 'A'], 'x1');
        Http::assertSent(fn ($req) => str_contains($req->url(), 'business.openapi') && $req->hasHeader('access_token', 'tok-moi'));

        // Làm mới lỗi: ghi lỗi, lệnh báo thất bại, token cũ vẫn giữ
        $this->artisan('zalo:lam-moi-token --force')->assertFailed();
        $token = ZaloToken::hienTai();
        $this->assertStringContainsString('Invalid refresh token', $token->loi_gan_nhat);
        $this->assertSame('tok-moi', $token->access_token);
    }

    public function test_trang_quan_tri_zalo_oa(): void
    {
        Http::fake(['oauth.zaloapp.com/*' => Http::response(['access_token' => 'tok-2', 'refresh_token' => 'ref-2', 'expires_in' => 90000])]);
        $admin = $this->taoUser('quan-tri-vien');

        $this->actingAs($admin)->get('/zalo-oa')->assertOk()->assertSee('Chưa có');
        $this->actingAs($admin)->post('/zalo-oa', ['access_token' => 'tok-1', 'refresh_token' => 'ref-1', 'het_han_gio' => 25])->assertRedirect('/zalo-oa');
        $this->assertSame('ref-1', ZaloToken::hienTai()->refresh_token);
        $this->actingAs($admin)->post('/zalo-oa/lam-moi')->assertSessionHas('success');
        $this->assertSame('tok-2', ZaloToken::hienTai()->access_token);
        $this->actingAs($admin)->get('/zalo-oa')->assertOk()->assertDontSee('tok-2');

        $this->actingAs($this->taoUser('ke-toan'))->get('/zalo-oa')->assertForbidden();
    }
}
