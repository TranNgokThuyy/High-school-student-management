<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KyThi extends Model
{
    protected $table = 'ky_thi';

    protected $fillable = ['ten', 'nam_hoc', 'hoc_ky', 'khoi', 'suc_chua_phong', 'tien_to_sbd', 'ghi_chu'];

    public function lichThi()
    {
        return $this->hasMany(LichThi::class, 'ky_thi_id')->orderBy('ngay')->orderBy('gio_bat_dau');
    }

    public function phongThi()
    {
        return $this->hasMany(PhongThi::class, 'ky_thi_id')->orderBy('thu_tu');
    }

    public function thiSinh()
    {
        return $this->hasMany(ThiSinh::class, 'ky_thi_id');
    }

    // Học sinh dự thi: đang học, thuộc các lớp của năm học (và khối) của kỳ thi
    public function hocSinhDuThi(): Collection
    {
        return HocSinh::where('trang_thai', 'Đang học')
            ->whereIn('lop_hoc_id', LopHoc::where('nam_hoc', $this->nam_hoc)
                ->when($this->khoi !== 'Tất cả', fn ($q) => $q->where('khoi', $this->khoi))
                ->pluck('id'))
            ->with('lopHoc')
            ->get();
    }

    // Khóa sắp xếp tên tiếng Việt: theo tên (từ cuối), rồi họ đệm
    public static function khoaTen(string $hoTen): string
    {
        $tu = preg_split('/\s+/', trim($hoTen));
        $ten = array_pop($tu);

        return Str::lower(Str::ascii($ten)) . ' ' . Str::lower(Str::ascii(implode(' ', $tu))) . ' ' . $hoTen;
    }

    /**
     * Xếp phòng thi & đánh số báo danh theo thứ tự tên A→Z, mỗi phòng tối đa suc_chua_phong thí sinh.
     * Xếp lại sẽ xóa phòng / số báo danh cũ. Trả về số thí sinh.
     */
    public function xepPhong(): int
    {
        $hocSinh = $this->hocSinhDuThi()->sortBy(fn ($hs) => self::khoaTen($hs->ho_ten))->values();

        DB::transaction(function () use ($hocSinh) {
            $this->thiSinh()->delete();
            $this->phongThi()->delete();

            foreach ($hocSinh->chunk(max(1, $this->suc_chua_phong)) as $i => $nhom) {
                $phong = $this->phongThi()->create([
                    'ten_phong' => 'Phòng ' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    'thu_tu'    => $i + 1,
                ]);
                foreach ($nhom as $stt => $hs) {
                    $this->thiSinh()->create([
                        'hoc_sinh_id'  => $hs->id,
                        'phong_thi_id' => $phong->id,
                        'so_bao_danh'  => $this->tien_to_sbd . str_pad($stt + 1, 4, '0', STR_PAD_LEFT),
                    ]);
                }
            }
        });

        return $hocSinh->count();
    }
}
