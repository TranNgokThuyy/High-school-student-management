<?php
namespace App\Jobs;

use App\Mail\TinNhanPhuHuynhMail;
use App\Models\TinNhanNguoiNhan;
use App\Services\Sms\SmsGateway;
use App\Services\Sms\ZaloZnsGateway;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

// Gửi tin cho một phụ huynh qua các kênh đang ở trạng thái "Chờ gửi"
class GuiTinNhanPhuHuynh implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $nguoiNhanId) {}

    public function handle(SmsGateway $sms, ZaloZnsGateway $zalo): void
    {
        $nguoiNhan = TinNhanNguoiNhan::with(['tinNhan', 'hocSinh.lopHoc'])->find($this->nguoiNhanId);
        if (!$nguoiNhan) {
            return;
        }

        $loi = [];

        if ($nguoiNhan->trang_thai_email === TinNhanNguoiNhan::CHO_GUI) {
            try {
                Mail::to($nguoiNhan->email)->send(new TinNhanPhuHuynhMail($nguoiNhan));
                $nguoiNhan->trang_thai_email = TinNhanNguoiNhan::DA_GUI;
            } catch (Throwable $e) {
                $nguoiNhan->trang_thai_email = TinNhanNguoiNhan::LOI;
                $loi[] = 'Email: ' . Str::limit($e->getMessage(), 200);
            }
        }

        if ($nguoiNhan->trang_thai_sms === TinNhanNguoiNhan::CHO_GUI) {
            try {
                $sms->gui($nguoiNhan->so_dien_thoai, $this->noiDungSms($nguoiNhan));
                $nguoiNhan->trang_thai_sms = TinNhanNguoiNhan::DA_GUI;
            } catch (Throwable $e) {
                $nguoiNhan->trang_thai_sms = TinNhanNguoiNhan::LOI;
                $loi[] = 'SMS: ' . Str::limit($e->getMessage(), 200);
            }
        }

        if ($nguoiNhan->trang_thai_zalo === TinNhanNguoiNhan::CHO_GUI) {
            try {
                $zalo->gui($nguoiNhan->so_dien_thoai, [
                    'ho_ten'   => $nguoiNhan->hocSinh->ho_ten,
                    'lop'      => $nguoiNhan->hocSinh->lopHoc?->ten_lop ?? '',
                    'tieu_de'  => Str::limit($nguoiNhan->tinNhan->tieu_de, 100, '...'),
                    'noi_dung' => Str::limit(preg_replace('/\s+/', ' ', $nguoiNhan->noi_dung), 380, '...'),
                ], 'tn-' . $nguoiNhan->id);
                $nguoiNhan->trang_thai_zalo = TinNhanNguoiNhan::DA_GUI;
            } catch (Throwable $e) {
                $nguoiNhan->trang_thai_zalo = TinNhanNguoiNhan::LOI;
                $loi[] = 'Zalo: ' . Str::limit($e->getMessage(), 200);
            }
        }

        $nguoiNhan->loi = $loi ? implode("\n", $loi) : null;
        $nguoiNhan->save();
    }

    // SMS không dấu, gọn để tiết kiệm số tin (1 tin = 160 ký tự)
    private function noiDungSms(TinNhanNguoiNhan $nguoiNhan): string
    {
        $noiDung = config('app.ten_truong') . ': ' . $nguoiNhan->tinNhan->tieu_de . '. ' . $nguoiNhan->noi_dung;

        return Str::limit(preg_replace('/\s+/', ' ', Str::ascii($noiDung)), 459, '...');
    }
}
