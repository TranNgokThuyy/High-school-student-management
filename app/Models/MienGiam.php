<?php
namespace App\Models;

use App\Models\Concerns\GhiNhatKy;
use Illuminate\Database\Eloquent\Model;

class MienGiam extends Model
{
    use GhiNhatKy;

    protected $table = 'mien_giam';

    // Đối tượng thường gặp (Nghị định 81/2021/NĐ-CP); có thể nhập đối tượng khác
    public const DOI_TUONG = [
        'Con thương binh, liệt sĩ' => 100,
        'Học sinh khuyết tật' => 100,
        'Mồ côi cả cha lẫn mẹ' => 100,
        'Hộ nghèo' => 100,
        'Dân tộc thiểu số rất ít người' => 100,
        'Hộ cận nghèo' => 50,
        'Con cán bộ, công nhân bị tai nạn lao động' => 50,
        'Dân tộc thiểu số vùng đặc biệt khó khăn' => 70,
    ];

    protected $fillable = ['hoc_sinh_id', 'nam_hoc', 'doi_tuong', 'phan_tram', 'ghi_chu'];

    protected $casts = ['phan_tram' => 'integer'];

    public function hocSinh()
    {
        return $this->belongsTo(HocSinh::class, 'hoc_sinh_id');
    }

    public function moTaNhatKy(): string
    {
        return "Miễn giảm {$this->phan_tram}% {$this->nam_hoc} — {$this->hocSinh?->ho_ten}";
    }
}
