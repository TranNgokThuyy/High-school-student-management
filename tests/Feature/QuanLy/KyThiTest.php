<?php

namespace Tests\Feature\QuanLy;

use App\Models\KyThi;
use App\Models\ThiSinh;

class KyThiTest extends QuanLyTestCase
{
    public function test_tao_ky_thi_lich_thi_va_xep_phong(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $lop10 = $this->taoLop();
        $lop11 = $this->taoLop(['ten_lop' => '11A1', 'khoi' => '11']);
        foreach (['Trần Văn Bình', 'Nguyễn Thị An', 'Lê Văn Cường', 'Phạm Văn An', 'Đỗ Thị Dung'] as $ten) {
            $this->taoHocSinh($lop10, $ten);
        }
        $this->taoHocSinh($lop11, 'Khác Khối');
        $toan = $this->taoMon('TOAN');
        $van = $this->taoMon('VAN');

        $this->actingAs($admin)->post('/ky-thi', [
            'ten' => 'Cuối HK1', 'nam_hoc' => $lop10->nam_hoc, 'hoc_ky' => '1', 'khoi' => '10',
            'suc_chua_phong' => 5, 'tien_to_sbd' => 'K10',
        ])->assertRedirect();
        $kyThi = KyThi::first();
        $kyThi->update(['suc_chua_phong' => 2]);

        $this->actingAs($admin)->post("/ky-thi/{$kyThi->id}/lich", ['mon_hoc_id' => $toan->id, 'ngay' => '2026-01-10', 'gio_bat_dau' => '07:30', 'thoi_gian' => 90])
            ->assertSessionHas('success');
        // Văn 08:30 chồng giờ với Toán 07:30–09:00
        $this->actingAs($admin)->post("/ky-thi/{$kyThi->id}/lich", ['mon_hoc_id' => $van->id, 'ngay' => '2026-01-10', 'gio_bat_dau' => '08:30', 'thoi_gian' => 90])
            ->assertSessionHas('error');

        $this->actingAs($admin)->post("/ky-thi/{$kyThi->id}/xep-phong")->assertSessionHas('success');

        // 5 học sinh khối 10, 2 em/phòng → 3 phòng; sắp theo tên: An (Nguyễn Thị), An (Phạm Văn), Bình, Cường, Dung; SBD liên tục cả kỳ thi
        $this->assertSame(3, $kyThi->phongThi()->count());
        $thuTu = ThiSinh::where('ky_thi_id', $kyThi->id)->with('hocSinh')->orderBy('phong_thi_id')->orderBy('so_bao_danh')
            ->get()->map(fn ($t) => $t->so_bao_danh . ' ' . $t->hocSinh->ho_ten)->all();
        $this->assertSame([
            'K100001 Nguyễn Thị An', 'K100002 Phạm Văn An',
            'K100003 Trần Văn Bình', 'K100004 Lê Văn Cường',
            'K100005 Đỗ Thị Dung',
        ], $thuTu);

        $this->actingAs($admin)->get("/ky-thi/{$kyThi->id}/in")->assertOk()->assertSee('PHÒNG 03')->assertSee('Đỗ Thị Dung');
        $this->actingAs($this->taoUser('giao-vien-chu-nhiem'))->get('/ky-thi')->assertForbidden();
    }
}
