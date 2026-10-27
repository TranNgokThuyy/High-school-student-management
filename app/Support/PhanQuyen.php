<?php
namespace App\Support;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

// Danh sách quyền & vai trò dùng chung cho seeder và migration (idempotent)
class PhanQuyen
{
    public const QUYEN = [
        'xem-hoc-sinh', 'them-hoc-sinh', 'sua-hoc-sinh', 'xoa-hoc-sinh',
        'xem-lop-hoc', 'them-lop-hoc', 'sua-lop-hoc', 'xoa-lop-hoc',
        'xem-diem', 'them-diem', 'sua-diem', 'xoa-diem',
        'xem-hanh-kiem', 'them-hanh-kiem', 'sua-hanh-kiem', 'xoa-hanh-kiem',
        'xem-mon-hoc', 'them-mon-hoc', 'sua-mon-hoc', 'xoa-mon-hoc',
        'xem-tai-khoan', 'them-tai-khoan', 'sua-tai-khoan', 'xoa-tai-khoan',
        'xem-bao-cao', 'xuat-bao-cao',
        'xem-phan-cong', 'them-phan-cong', 'sua-phan-cong', 'xoa-phan-cong',
        'xem-thoi-khoa-bieu', 'sua-thoi-khoa-bieu',
        'xem-diem-danh', 'diem-danh',
        'xem-tong-ket', 'xet-len-lop',
        'xem-khen-thuong', 'them-khen-thuong', 'xoa-khen-thuong',
        'xem-hoc-phi', 'thu-hoc-phi', 'huy-phieu-thu', 'quan-ly-khoan-thu',
        'dang-thong-bao',
        'gui-tin-phu-huynh',
        'xem-nhat-ky', 'quan-ly-sao-luu',
        'quan-ly-bien-dong',
        'quan-ly-ky-thi',
        'quan-ly-giao-vien',
    ];

    public const VAI_TRO = [
        'quan-tri-vien' => '*',
        'giao-vien-chu-nhiem' => [
            'xem-hoc-sinh', 'sua-hoc-sinh',
            'xem-lop-hoc',
            'xem-mon-hoc',
            'xem-diem', 'them-diem', 'sua-diem',
            'xem-hanh-kiem', 'them-hanh-kiem', 'sua-hanh-kiem',
            'xem-bao-cao', 'xuat-bao-cao',
            'xem-phan-cong',
            'xem-thoi-khoa-bieu',
            'xem-diem-danh', 'diem-danh',
            'xem-tong-ket',
            'xem-khen-thuong', 'them-khen-thuong',
            'xem-hoc-phi',
            'gui-tin-phu-huynh',
        ],
        'giao-vien-bo-mon' => [
            'xem-hoc-sinh',
            'xem-lop-hoc',
            'xem-mon-hoc',
            'xem-diem', 'them-diem', 'sua-diem',
            'xem-phan-cong',
            'xem-thoi-khoa-bieu',
        ],
        'ke-toan' => [
            'xem-hoc-sinh',
            'xem-lop-hoc',
            'xem-hoc-phi', 'thu-hoc-phi', 'quan-ly-khoan-thu',
            'xem-bao-cao',
            'gui-tin-phu-huynh',
        ],
    ];

    public const TEN_VAI_TRO = [
        'quan-tri-vien'       => 'Quản trị viên',
        'giao-vien-chu-nhiem' => 'GV chủ nhiệm',
        'giao-vien-bo-mon'    => 'GV bộ môn',
        'ke-toan'             => 'Kế toán',
    ];

    public static function dongBo(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::QUYEN as $quyen) {
            Permission::firstOrCreate(['name' => $quyen, 'guard_name' => 'web']);
        }

        foreach (self::VAI_TRO as $tenVaiTro => $dsQuyen) {
            $vaiTro = Role::firstOrCreate(['name' => $tenVaiTro, 'guard_name' => 'web']);
            $vaiTro->givePermissionTo($dsQuyen === '*' ? Permission::all() : $dsQuyen);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
