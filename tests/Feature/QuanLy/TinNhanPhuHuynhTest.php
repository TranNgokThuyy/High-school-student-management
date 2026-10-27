<?php

namespace Tests\Feature\QuanLy;

use App\Mail\TinNhanPhuHuynhMail;
use App\Models\HocSinh;
use App\Models\KhoanThu;
use App\Models\LopHoc;
use App\Models\TinNhan;
use App\Models\TinNhanNguoiNhan;
use App\Support\NamHoc;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class TinNhanPhuHuynhTest extends QuanLyTestCase
{
    private function taoLopNamNay(array $thuocTinh = []): LopHoc
    {
        return $this->taoLop($thuocTinh + ['nam_hoc' => NamHoc::hienTai()]);
    }

    private function taoHsCoLienLac(LopHoc $lop, string $ten, ?string $email = null, ?string $sdt = null): HocSinh
    {
        $hs = $this->taoHocSinh($lop, $ten);
        $hs->update(['email_phu_huynh' => $email, 'so_dien_thoai_me' => $sdt]);

        return $hs;
    }

    public function test_soan_tin_theo_lop_dien_thong_tin_rieng_va_gui_email(): void
    {
        Mail::fake();
        $admin = $this->taoUser('quan-tri-vien');
        $lop = $this->taoLopNamNay();
        $an = $this->taoHsCoLienLac($lop, 'Nguyễn An', 'ph.an@example.com');
        $binh = $this->taoHsCoLienLac($lop, 'Trần Bình');

        $this->actingAs($admin)->post('/tin-nhan', [
            'pham_vi' => 'lop', 'lop_hoc_ids' => [$lop->id], 'loai' => 'Thông báo chung',
            'tieu_de' => 'Họp phụ huynh', 'noi_dung' => 'Mời phụ huynh em {ho_ten} lớp {lop} dự họp.', 'kenh' => ['email'],
        ])->assertRedirect();

        $tinNhan = TinNhan::first();
        $this->assertSame('Lớp 10A1', $tinNhan->pham_vi);
        $this->assertSame(['web', 'email'], $tinNhan->kenh);

        $nnAn = TinNhanNguoiNhan::where('hoc_sinh_id', $an->id)->first();
        $this->assertSame('Mời phụ huynh em Nguyễn An lớp 10A1 dự họp.', $nnAn->noi_dung);
        $this->assertSame(TinNhanNguoiNhan::DA_GUI, $nnAn->trang_thai_email);
        $this->assertSame(TinNhanNguoiNhan::THIEU_DIA_CHI, TinNhanNguoiNhan::where('hoc_sinh_id', $binh->id)->value('trang_thai_email'));

        Mail::assertSent(TinNhanPhuHuynhMail::class, fn ($mail) => $mail->hasTo('ph.an@example.com'));
        Mail::assertSentCount(1);
    }

    public function test_gvcn_chi_gui_lop_minh(): void
    {
        $gv = $this->taoUser('giao-vien-chu-nhiem');
        $lopMinh = $this->taoLopNamNay(['giao_vien_chu_nhiem_id' => $gv->id]);
        $lopKhac = $this->taoLopNamNay(['ten_lop' => '10A2']);
        $hsKhac = $this->taoHocSinh($lopKhac);
        $duLieu = ['loai' => 'Thông báo chung', 'tieu_de' => 'x', 'noi_dung' => 'x'];

        $this->actingAs($gv)->post('/tin-nhan', $duLieu + ['pham_vi' => 'toan_truong'])->assertForbidden();
        $this->actingAs($gv)->post('/tin-nhan', $duLieu + ['pham_vi' => 'hoc_sinh', 'hoc_sinh_ids' => [$hsKhac->id]])
            ->assertSessionHas('error');
        $this->actingAs($gv)->get("/tin-nhan/soan?hoc_sinh_id={$hsKhac->id}")->assertForbidden();
        $this->assertSame(0, TinNhan::count());

        $this->taoHocSinh($lopMinh);
        $this->actingAs($gv)->post('/tin-nhan', $duLieu + ['pham_vi' => 'lop', 'lop_hoc_ids' => [$lopMinh->id, $lopKhac->id]]);
        $this->assertSame(1, TinNhanNguoiNhan::count());
    }

    public function test_giao_vien_bo_mon_khong_gui_duoc(): void
    {
        $this->actingAs($this->taoUser('giao-vien-bo-mon'))->get('/tin-nhan')->assertForbidden();
    }

    public function test_diem_danh_vang_khong_phep_bao_phu_huynh_mot_lan(): void
    {
        Mail::fake();
        $gv = $this->taoUser('giao-vien-chu-nhiem');
        $lop = $this->taoLopNamNay(['giao_vien_chu_nhiem_id' => $gv->id]);
        $vang = $this->taoHsCoLienLac($lop, 'Vắng', 'ph.vang@example.com');
        $coMat = $this->taoHsCoLienLac($lop, 'Có mặt', 'ph.comat@example.com');
        $duLieu = [
            'lop_hoc_id' => $lop->id, 'ngay' => today()->toDateString(), 'bao_phu_huynh' => 1,
            'diem_danh' => [$vang->id => ['trang_thai' => 'Vắng không phép'], $coMat->id => ['trang_thai' => 'Có mặt']],
        ];

        $this->actingAs($gv)->post('/diem-danh', $duLieu)->assertSessionHas('success');
        $this->actingAs($gv)->post('/diem-danh', $duLieu); // lưu lại không báo trùng

        $this->assertSame(1, TinNhanNguoiNhan::count());
        $this->assertSame($vang->id, TinNhanNguoiNhan::first()->hoc_sinh_id);
        Mail::assertSent(TinNhanPhuHuynhMail::class, fn ($mail) => $mail->hasTo('ph.vang@example.com'));
    }

    public function test_nhac_hoc_phi_noi_dung_rieng_tung_hoc_sinh(): void
    {
        $keToan = $this->taoUser('ke-toan');
        $lop = $this->taoLopNamNay();
        $hs = $this->taoHocSinh($lop, 'Còn Nợ');
        KhoanThu::create(['ten' => 'Học phí HK1', 'nam_hoc' => $lop->nam_hoc, 'so_tien' => 1_200_000]);

        $this->actingAs($keToan)->post('/hoc-phi/nhac-no', ['lop_hoc_id' => $lop->id])->assertRedirect();

        $noiDung = TinNhanNguoiNhan::where('hoc_sinh_id', $hs->id)->value('noi_dung');
        $this->assertStringContainsString('Học phí HK1: 1.200.000 đ', $noiDung);
        $this->assertSame('Học phí', TinNhan::first()->loai);
    }

    public function test_sms_webhook_loi_thi_gui_lai_duoc(): void
    {
        config(['services.sms.driver' => 'webhook', 'services.sms.webhook_url' => 'https://sms.example.test/send', 'services.sms.token' => 'bi-mat']);
        Http::fakeSequence('sms.example.test/*')->push('Lỗi', 500)->push(['ok' => true], 200);

        $admin = $this->taoUser('quan-tri-vien');
        $lop = $this->taoLopNamNay();
        $hs = $this->taoHsCoLienLac($lop, 'Có SĐT', null, '0912345678');

        $this->actingAs($admin)->post('/tin-nhan', [
            'pham_vi' => 'hoc_sinh', 'hoc_sinh_ids' => [$hs->id], 'loai' => 'Thông báo chung',
            'tieu_de' => 'Thử SMS', 'noi_dung' => 'Xin chào', 'kenh' => ['sms'],
        ]);

        $nn = TinNhanNguoiNhan::first();
        $this->assertSame(TinNhanNguoiNhan::LOI, $nn->trang_thai_sms);
        $this->assertStringContainsString('SMS', $nn->loi);

        $this->actingAs($admin)->post("/tin-nhan/{$nn->tin_nhan_id}/gui-lai")->assertSessionHas('success');
        $this->assertSame(TinNhanNguoiNhan::DA_GUI, $nn->fresh()->trang_thai_sms);
        $this->assertNull($nn->fresh()->loi);

        Http::assertSent(fn ($req) => $req['to'] === '0912345678'
            && $req->hasHeader('Authorization', 'Bearer bi-mat')
            && str_contains($req['message'], 'Xin chao'));
    }

    public function test_phu_huynh_xem_tin_tren_trang_tra_cuu(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $lop = $this->taoLopNamNay();
        $hs = $this->taoHocSinh($lop, 'Học Sinh Tra Cứu');

        $this->actingAs($admin)->post('/tin-nhan', [
            'pham_vi' => 'hoc_sinh', 'hoc_sinh_ids' => [$hs->id], 'loai' => 'Thông báo chung',
            'tieu_de' => 'Lịch nghỉ Tết', 'noi_dung' => 'Học sinh nghỉ Tết từ ngày 20/01.',
        ]);
        auth()->logout();

        $this->post('/tra-cuu', ['ma_hoc_sinh' => $hs->ma_hoc_sinh, 'ngay_sinh' => '2010-01-01'])
            ->assertOk()->assertSee('Lịch nghỉ Tết')->assertSee('1 mới');

        $this->assertNotNull(TinNhanNguoiNhan::first()->da_xem_luc);
    }

    public function test_gui_ket_qua_hoc_ky_va_bao_khen_thuong(): void
    {
        $gv = $this->taoUser('giao-vien-chu-nhiem');
        $lop = $this->taoLopNamNay(['giao_vien_chu_nhiem_id' => $gv->id]);
        $hs = $this->taoHocSinh($lop, 'Học Giỏi');
        $mon = $this->taoMon('TOAN');
        \App\Models\DiemSo::create(['hoc_sinh_id' => $hs->id, 'mon_hoc_id' => $mon->id, 'lop_hoc_id' => $lop->id,
            'hoc_ky' => '1', 'nam_hoc' => $lop->nam_hoc, 'diem_trung_binh' => 8.5]);

        $this->actingAs($gv)->post('/tong-ket/gui-ket-qua', ['lop_hoc_id' => $lop->id, 'hoc_ky' => '1'])->assertRedirect();
        $noiDung = TinNhanNguoiNhan::where('hoc_sinh_id', $hs->id)->value('noi_dung');
        $this->assertStringContainsString('TOAN: 8.5', $noiDung);
        $this->assertStringContainsString('Kết quả học tập', $noiDung);

        $this->actingAs($gv)->post('/khen-thuong', [
            'hoc_sinh_id' => $hs->id, 'loai' => 'Khen thưởng', 'hinh_thuc' => 'Giấy khen',
            'noi_dung' => 'Giải Nhất HSG Toán', 'ngay_quyet_dinh' => today()->toDateString(), 'bao_phu_huynh' => 1,
        ])->assertRedirect('/khen-thuong');
        $this->assertSame('Khen thưởng - Kỷ luật', TinNhan::latest('id')->value('loai'));

        // Lớp không chủ nhiệm thì không gửi được kết quả
        $lopKhac = $this->taoLopNamNay(['ten_lop' => '10A2']);
        $this->actingAs($gv)->post('/tong-ket/gui-ket-qua', ['lop_hoc_id' => $lopKhac->id, 'hoc_ky' => '1'])->assertForbidden();
    }
}
