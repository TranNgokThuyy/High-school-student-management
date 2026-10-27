<?php
namespace App\Services;

use App\Jobs\GuiTinNhanPhuHuynh;
use App\Models\HocSinh;
use App\Models\TinNhan;
use App\Models\TinNhanNguoiNhan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ThongBaoPhuHuynhService
{
    public const BIEN_THAY_THE = [
        '{ho_ten}'      => 'Họ tên học sinh',
        '{ma_hoc_sinh}' => 'Mã học sinh',
        '{lop}'         => 'Tên lớp',
        '{ngay_sinh}'   => 'Ngày sinh',
    ];

    /**
     * Tạo tin nhắn và đưa vào hàng đợi gửi.
     *
     * @param Collection<int, HocSinh> $hocSinh
     * @param callable|null $noiDungRieng fn(HocSinh): ?string — nội dung riêng cho từng học sinh;
     *                                    trả về null để bỏ qua học sinh đó
     */
    public function gui(
        string $tieuDe,
        string $noiDung,
        string $loai,
        string $phamVi,
        Collection $hocSinh,
        array $kenh,
        ?int $nguoiGuiId,
        ?callable $noiDungRieng = null,
    ): TinNhan {
        $kenh = array_values(array_unique(array_merge(['web'], array_intersect($kenh, array_keys(TinNhan::KENH)))));

        $tinNhan = DB::transaction(function () use ($tieuDe, $noiDung, $loai, $phamVi, $hocSinh, $kenh, $nguoiGuiId, $noiDungRieng) {
            $tinNhan = TinNhan::create([
                'tieu_de' => $tieuDe, 'noi_dung' => $noiDung, 'loai' => $loai,
                'pham_vi' => $phamVi, 'kenh' => $kenh, 'nguoi_gui_id' => $nguoiGuiId,
            ]);

            foreach ($hocSinh as $hs) {
                $hs->loadMissing('lopHoc');
                $noiDungHs = $noiDungRieng ? $noiDungRieng($hs) : $this->dienThongTin($noiDung, $hs);
                if ($noiDungHs === null) {
                    continue;
                }

                $soDienThoai = $hs->so_dien_thoai_phu_huynh;
                // Hồ sơ chưa có email phụ huynh thì dùng email tài khoản phụ huynh đã liên kết
                $email = $hs->email_phu_huynh ?: $hs->taiKhoanPhuHuynh()->where('is_active', true)->value('email');
                $tinNhan->nguoiNhan()->create([
                    'hoc_sinh_id'      => $hs->id,
                    'noi_dung'         => $noiDungHs,
                    'email'            => $email,
                    'so_dien_thoai'    => $soDienThoai,
                    'trang_thai_email' => $this->trangThaiBanDau(in_array('email', $kenh), $email),
                    'trang_thai_sms'   => $this->trangThaiBanDau(in_array('sms', $kenh), $soDienThoai),
                ]);
            }

            return $tinNhan;
        });

        $this->dayVaoHangDoi($tinNhan);

        return $tinNhan;
    }

    // Gửi lại các kênh bị lỗi của một tin nhắn
    public function guiLai(TinNhan $tinNhan): int
    {
        $soLuong = 0;
        foreach ($tinNhan->nguoiNhan()->get() as $nn) {
            $canGui = false;
            foreach (['trang_thai_email', 'trang_thai_sms'] as $cot) {
                if ($nn->$cot === TinNhanNguoiNhan::LOI) {
                    $nn->$cot = TinNhanNguoiNhan::CHO_GUI;
                    $canGui = true;
                }
            }
            if ($canGui) {
                $nn->save();
                GuiTinNhanPhuHuynh::dispatch($nn->id);
                $soLuong++;
            }
        }

        return $soLuong;
    }

    public function dienThongTin(string $noiDung, HocSinh $hs): string
    {
        return strtr($noiDung, [
            '{ho_ten}'      => $hs->ho_ten,
            '{ma_hoc_sinh}' => $hs->ma_hoc_sinh,
            '{lop}'         => $hs->lopHoc?->ten_lop ?? '',
            '{ngay_sinh}'   => $hs->ngay_sinh?->format('d/m/Y') ?? '',
        ]);
    }

    private function trangThaiBanDau(bool $chonKenh, ?string $diaChi): string
    {
        if (!$chonKenh) {
            return TinNhanNguoiNhan::KHONG_GUI;
        }

        return $diaChi ? TinNhanNguoiNhan::CHO_GUI : TinNhanNguoiNhan::THIEU_DIA_CHI;
    }

    private function dayVaoHangDoi(TinNhan $tinNhan): void
    {
        $tinNhan->nguoiNhan()
            ->where(fn ($q) => $q->where('trang_thai_email', TinNhanNguoiNhan::CHO_GUI)
                ->orWhere('trang_thai_sms', TinNhanNguoiNhan::CHO_GUI))
            ->pluck('id')
            ->each(fn ($id) => GuiTinNhanPhuHuynh::dispatch($id));
    }
}
