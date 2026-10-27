<?php

namespace Tests\Feature\QuanLy;

use App\Models\HocSinh;

class KiemTraDuLieuTest extends QuanLyTestCase
{
    private function hoSo(array $ghiDe = []): array
    {
        return $ghiDe + [
            'ho_ten' => 'Nguyễn Văn An', 'ngay_sinh' => today()->subYears(16)->toDateString(),
            'gioi_tinh' => 'Nam', 'trang_thai' => 'Đang học',
        ];
    }

    public function test_thong_bao_loi_tieng_viet(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $this->actingAs($admin)->post('/hoc-sinh', $this->hoSo(['ho_ten' => '']))
            ->assertSessionHasErrors(['ho_ten' => 'Vui lòng nhập họ và tên.']);
        $this->actingAs($admin)->post('/hoc-sinh', $this->hoSo(['email' => 'abc']))
            ->assertSessionHasErrors(['email' => 'Email phải là địa chỉ email hợp lệ.']);
    }

    public function test_ho_so_hoc_sinh_chuan_hoa_va_kiem_tra_sdt_cccd_ho_ten_ngay_sinh(): void
    {
        $admin = $this->taoUser('quan-tri-vien');

        // Số gõ có khoảng trắng / dấu chấm / +84 được chuẩn hóa rồi mới lưu
        $this->actingAs($admin)->post('/hoc-sinh', $this->hoSo([
            'so_dien_thoai' => '091 234 5678', 'so_dien_thoai_me' => '+84 98.765.4321', 'cccd' => '001 210 012345',
            'ho_ten_me' => "Trần Thị B'Lan",
        ]))->assertSessionHasNoErrors();
        $hs = HocSinh::first();
        $this->assertSame(['0912345678', '0987654321', '001210012345'], [$hs->so_dien_thoai, $hs->so_dien_thoai_me, $hs->cccd]);

        $truongHopSai = [
            'so_dien_thoai' => '0123456',          // quá ngắn
            'so_dien_thoai_cha' => '0112345678',   // đầu số không tồn tại
            'cccd' => '999210012345',              // mã tỉnh không hợp lệ
            'ho_ten' => 'Nguyen Van 123',          // có chữ số
            'ho_ten_cha' => 'Hùng',                // thiếu họ
        ];
        foreach ($truongHopSai as $truong => $giaTri) {
            $this->actingAs($admin)->post('/hoc-sinh', $this->hoSo([$truong => $giaTri]))->assertSessionHasErrors($truong);
        }
        $this->actingAs($admin)->post('/hoc-sinh', $this->hoSo(['ngay_sinh' => today()->subYears(3)->toDateString()]))
            ->assertSessionHasErrors(['ngay_sinh' => 'Học sinh phải từ 10 tuổi trở lên, kiểm tra lại ngày sinh.']);
        $this->actingAs($admin)->post('/hoc-sinh', $this->hoSo(['cccd' => '001210012345']))->assertSessionHasErrors('cccd');
        $this->assertSame(1, HocSinh::count());
    }

    public function test_lop_hoc_nam_hoc_ten_lop_va_gvcn(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $gvcn = $this->taoUser('giao-vien-chu-nhiem');
        $keToan = $this->taoUser('ke-toan');
        $lop = fn (array $x) => $x + ['ten_lop' => '10A1', 'khoi' => '10', 'nam_hoc' => '2025-2026'];

        $this->actingAs($admin)->post('/lop-hoc', $lop(['nam_hoc' => '2025-2030']))->assertSessionHasErrors('nam_hoc');
        $this->actingAs($admin)->post('/lop-hoc', $lop(['ten_lop' => '11A1']))
            ->assertSessionHasErrors(['ten_lop' => 'Tên lớp phải bắt đầu bằng khối 10 (VD: 10A1).']);
        $this->actingAs($admin)->post('/lop-hoc', $lop(['giao_vien_chu_nhiem_id' => $keToan->id]))->assertSessionHasErrors('giao_vien_chu_nhiem_id');

        $this->actingAs($admin)->post('/lop-hoc', $lop(['ten_lop' => ' 10a1 ', 'giao_vien_chu_nhiem_id' => $gvcn->id]))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('lop_hoc', ['ten_lop' => '10A1']);
        $this->actingAs($admin)->post('/lop-hoc', $lop([]))->assertSessionHasErrors('ten_lop');
        $this->actingAs($admin)->post('/lop-hoc', $lop(['ten_lop' => '10A2', 'giao_vien_chu_nhiem_id' => $gvcn->id]))
            ->assertSessionHasErrors(['giao_vien_chu_nhiem_id' => 'Giáo viên này đã chủ nhiệm một lớp khác trong năm học này.']);
    }

    public function test_ren_luyen_diem_so_va_thoi_khoa_bieu_rang_buoc_cheo(): void
    {
        $admin = $this->taoUser('quan-tri-vien');
        $gv = $this->taoUser('giao-vien-bo-mon');
        $lop = $this->taoLop();
        $lopKhac = $this->taoLop(['ten_lop' => '10A2']);
        $hs = $this->taoHocSinh($lop);
        $hsKhac = $this->taoHocSinh($lopKhac, 'Lê Thị Khác');
        $mon = $this->taoMon();

        $hk = fn (array $dong) => ['lop_hoc_id' => $lop->id, 'hoc_ky' => '1', 'nam_hoc' => $lop->nam_hoc, 'hanh_kiem' => $dong];
        $this->actingAs($admin)->post('/hanh-kiem/luu', $hk([$hs->id => ['xep_loai' => 'Tốt', 'so_buoi_hoc' => 10, 'vang_co_phep' => 6, 'vang_khong_phep' => 5]]))
            ->assertSessionHasErrors("hanh_kiem.{$hs->id}.vang_khong_phep");
        $this->actingAs($admin)->post('/hanh-kiem/luu', $hk([$hsKhac->id => ['xep_loai' => 'Tốt']]))->assertSessionHasErrors("hanh_kiem.{$hsKhac->id}");
        $this->assertDatabaseCount('hanh_kiem', 0);

        $diem = fn (array $x) => $x + ['lop_hoc_id' => $lop->id, 'mon_hoc_id' => $mon->id, 'hoc_ky' => '1', 'nam_hoc' => $lop->nam_hoc];
        $this->actingAs($admin)->post('/diem-so/luu-diem', $diem(['diem' => [$hs->id => ['diem_ck' => '8.333']]]))->assertSessionHasErrors();
        $this->actingAs($admin)->post('/diem-so/luu-diem', $diem(['nam_hoc' => '2030-2031', 'diem' => [$hs->id => ['diem_ck' => 8]]]))->assertSessionHasErrors('nam_hoc');
        $this->assertDatabaseCount('diem_so', 0);

        $tkb = ['lop_hoc_id' => $lop->id, 'mon_hoc_id' => $mon->id, 'giao_vien_id' => $gv->id, 'thu' => 2, 'tiet_bat_dau' => 9, 'so_tiet' => 3, 'hoc_ky' => '1', 'nam_hoc' => $lop->nam_hoc];
        $this->actingAs($admin)->post('/thoi-khoa-bieu', $tkb)->assertSessionHasErrors('so_tiet');
        $this->actingAs($admin)->post('/thoi-khoa-bieu', ['giao_vien_id' => $admin->id, 'so_tiet' => 1] + $tkb)->assertSessionHasErrors('giao_vien_id');
    }
}
