<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NhatKy extends Model
{
    protected $table = 'nhat_ky';

    public const UPDATED_AT = null;

    // Tên hiển thị của các loại đối tượng được ghi nhật ký
    public const TEN_DOI_TUONG = [
        'DiemSo' => 'Điểm số', 'HanhKiem' => 'Hạnh kiểm', 'HocSinh' => 'Học sinh',
        'PhieuThu' => 'Phiếu thu', 'KhoanThu' => 'Khoản thu', 'MienGiam' => 'Miễn giảm',
        'KhenThuongKyLuat' => 'Khen thưởng - Kỷ luật', 'LopHoc' => 'Lớp học', 'MonHoc' => 'Môn học',
        'PhanCongGiangDay' => 'Phân công giảng dạy', 'User' => 'Tài khoản',
    ];

    // Tên hiển thị của các trường dữ liệu hay gặp
    public const TEN_TRUONG = [
        'diem_tx1' => 'TX1', 'diem_tx2' => 'TX2', 'diem_tx3' => 'TX3', 'diem_tx4' => 'TX4',
        'diem_gk' => 'Giữa kỳ', 'diem_ck' => 'Cuối kỳ', 'diem_trung_binh' => 'ĐTB',
        'nhan_xet' => 'Nhận xét', 'ket_qua_nhan_xet' => 'Kết quả NX',
        'xep_loai_hanh_kiem' => 'Hạnh kiểm', 'so_buoi_vang_co_phep' => 'Vắng CP', 'so_buoi_vang_khong_phep' => 'Vắng KP',
        'so_buoi_hoc' => 'Số buổi', 'so_buoi_di_tre' => 'Đi trễ', 'ghi_chu' => 'Ghi chú',
        'ho_ten' => 'Họ tên', 'ngay_sinh' => 'Ngày sinh', 'gioi_tinh' => 'Giới tính', 'lop_hoc_id' => 'Lớp',
        'trang_thai' => 'Trạng thái', 'so_tien' => 'Số tiền', 'da_huy' => 'Đã hủy', 'ly_do_huy' => 'Lý do hủy',
        'phan_tram' => 'Mức giảm (%)', 'is_active' => 'Hoạt động', 'password' => 'Mật khẩu', 'email' => 'Email',
        'name' => 'Họ tên', 'ten_lop' => 'Tên lớp', 'ten_mon' => 'Tên môn', 'giao_vien_chu_nhiem_id' => 'GVCN',
        'ma_tra_cuu' => 'Mã tra cứu',
        'giao_vien_id' => 'Giáo viên', 'mon_hoc_id' => 'Môn', 'hoc_sinh_id' => 'Học sinh', 'hoc_ky' => 'Học kỳ',
        'nam_hoc' => 'Năm học', 'khoan_thu_id' => 'Khoản thu', 'so_phieu' => 'Số phiếu', 'hinh_thuc' => 'Hình thức',
        'ngay_thu' => 'Ngày thu', 'nguoi_thu_id' => 'Người thu', 'doi_tuong' => 'Đối tượng', 'loai' => 'Loại',
        'noi_dung' => 'Nội dung', 'ten' => 'Tên', 'cccd' => 'CCCD', 'so_dien_thoai' => 'SĐT', 'email_phu_huynh' => 'Email PH',
    ];

    protected $fillable = [
        'user_id', 'hanh_dong', 'doi_tuong_loai', 'doi_tuong_id', 'hoc_sinh_id', 'mo_ta', 'thay_doi', 'ip',
    ];

    protected $casts = [
        'thay_doi' => 'array',
        'created_at' => 'datetime',
    ];

    public function nguoiThaoTac()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function hocSinh()
    {
        return $this->belongsTo(HocSinh::class, 'hoc_sinh_id')->withTrashed();
    }
}
