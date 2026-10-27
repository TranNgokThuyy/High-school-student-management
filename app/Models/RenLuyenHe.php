<?php
namespace App\Models;

use App\Models\Concerns\GhiNhatKy;
use Illuminate\Database\Eloquent\Model;

class RenLuyenHe extends Model
{
    use GhiNhatKy;

    protected $table = 'ren_luyen_he';

    protected $fillable = ['hoc_sinh_id', 'lop_hoc_id', 'nam_hoc', 'ket_qua', 'nhan_xet', 'nguoi_nhap_id'];

    public function hocSinh()
    {
        return $this->belongsTo(HocSinh::class, 'hoc_sinh_id');
    }

    public function moTaNhatKy(): string
    {
        return "Rèn luyện hè {$this->nam_hoc} — {$this->hocSinh?->ho_ten}";
    }
}
