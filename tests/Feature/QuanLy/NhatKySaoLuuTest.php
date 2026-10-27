<?php

namespace Tests\Feature\QuanLy;

use App\Models\DiemSo;
use App\Models\HocSinh;
use App\Models\NhatKy;
use App\Models\PhanCongGiangDay;
use App\Services\SaoLuuService;
use Illuminate\Support\Facades\Storage;

class NhatKySaoLuuTest extends QuanLyTestCase
{
    public function test_ghi_nhat_ky_khi_sua_diem(): void
    {
        $gv = $this->taoUser('giao-vien-bo-mon');
        $lop = $this->taoLop();
        $hs = $this->taoHocSinh($lop, 'Nguyễn Nhật Ký');
        $mon = $this->taoMon('TOAN');
        PhanCongGiangDay::create(['giao_vien_id' => $gv->id, 'mon_hoc_id' => $mon->id, 'lop_hoc_id' => $lop->id,
            'nam_hoc' => $lop->nam_hoc, 'hoc_ky' => 'Ca năm']);
        $nhap = fn ($ck) => $this->actingAs($gv)->post('/diem-so/luu-diem', [
            'lop_hoc_id' => $lop->id, 'mon_hoc_id' => $mon->id, 'hoc_ky' => '1', 'nam_hoc' => $lop->nam_hoc,
            'diem' => [$hs->id => ['diem_tx1' => 7, 'diem_gk' => 7, 'diem_ck' => $ck]],
        ]);

        $nhap(6);
        $nhap(9);

        $nhatKy = NhatKy::where('doi_tuong_loai', 'DiemSo')->orderBy('id')->get();
        $this->assertSame(['Thêm', 'Sửa'], $nhatKy->pluck('hanh_dong')->all());

        $sua = $nhatKy->last();
        $this->assertSame($gv->id, $sua->user_id);
        $this->assertSame($hs->id, $sua->hoc_sinh_id);
        $this->assertStringContainsString('Nguyễn Nhật Ký', $sua->mo_ta);
        $this->assertEquals([6, 9], array_map('floatval', $sua->thay_doi['diem_ck']));
        $this->assertArrayHasKey('diem_trung_binh', $sua->thay_doi);
        $this->assertArrayNotHasKey('updated_at', $sua->thay_doi);
    }

    public function test_khong_ghi_mat_khau_va_chi_admin_xem_nhat_ky(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $gv = $this->taoUser('giao-vien-chu-nhiem');
        $gv->update(['password' => 'MatKhauMoi@123']);

        $nk = NhatKy::where('doi_tuong_loai', 'User')->where('hanh_dong', 'Sửa')->latest('id')->first();
        $this->assertSame(['***', '***'], $nk->thay_doi['password']);

        $this->actingAs($gv)->get('/nhat-ky')->assertForbidden();
        $this->actingAs($admin)->get('/nhat-ky?doi_tuong=User')->assertOk()->assertSee('Mật khẩu');
    }

    public function test_sao_luu_va_phuc_hoi(): void
    {
        Storage::fake('local');
        $service = app(SaoLuuService::class);
        $lop = $this->taoLop();
        $hs = $this->taoHocSinh($lop, 'Trước Sao Lưu');
        $mon = $this->taoMon();
        DiemSo::create(['hoc_sinh_id' => $hs->id, 'mon_hoc_id' => $mon->id, 'lop_hoc_id' => $lop->id,
            'hoc_ky' => '1', 'nam_hoc' => $lop->nam_hoc, 'diem_trung_binh' => 8.5]);

        $tenTep = $service->tao();
        $tenTep2 = $service->tao(); // tạo liền nhau không được ghi đè
        $this->assertNotSame($tenTep, $tenTep2);
        $this->assertCount(2, $service->danhSach());

        $hs->update(['ho_ten' => 'Đã Bị Sửa']);
        DiemSo::query()->delete();
        $this->taoHocSinh($lop, 'Thêm Sau Sao Lưu');

        $service->phucHoi($service->duongDan($tenTep));

        $this->assertSame('Trước Sao Lưu', HocSinh::find($hs->id)->ho_ten);
        $this->assertSame(1, HocSinh::count());
        $this->assertEquals(8.5, (float) DiemSo::first()->diem_trung_binh);
        // Tài khoản / quyền vẫn còn sau phục hồi
        $this->assertTrue(\Spatie\Permission\Models\Role::where('name', 'quan-tri-vien')->exists());
    }

    public function test_trang_sao_luu_chi_admin_va_chan_ten_tep_la(): void
    {
        Storage::fake('local');
        $admin = $this->taoUser('quan-tri-vien');
        $keToan = $this->taoUser('ke-toan');

        $this->actingAs($keToan)->get('/sao-luu')->assertForbidden();
        $this->actingAs($admin)->post('/sao-luu')->assertSessionHas('success');

        $tenTep = app(SaoLuuService::class)->danhSach()->first()['ten'];
        $this->actingAs($admin)->get('/sao-luu')->assertOk()->assertSee($tenTep);
        $this->actingAs($admin)->get("/sao-luu/{$tenTep}/tai-ve")->assertOk();
        $this->actingAs($admin)->get('/sao-luu/..%2F..%2F.env/tai-ve')->assertNotFound();
        $this->actingAs($admin)->delete("/sao-luu/{$tenTep}")->assertSessionHas('success');
        $this->assertCount(0, app(SaoLuuService::class)->danhSach());
    }
}
