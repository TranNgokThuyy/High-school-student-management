<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ===== TẠO PERMISSIONS =====
        $permissions = [
            // Quản lý học sinh
            'xem-hoc-sinh', 'them-hoc-sinh', 'sua-hoc-sinh', 'xoa-hoc-sinh',
            // Quản lý lớp học
            'xem-lop-hoc', 'them-lop-hoc', 'sua-lop-hoc', 'xoa-lop-hoc',
            // Quản lý điểm
            'xem-diem', 'them-diem', 'sua-diem', 'xoa-diem',
            // Quản lý hạnh kiểm
            'xem-hanh-kiem', 'them-hanh-kiem', 'sua-hanh-kiem', 'xoa-hanh-kiem',
            // Quản lý môn học
            'xem-mon-hoc', 'them-mon-hoc', 'sua-mon-hoc', 'xoa-mon-hoc',
            // Quản lý tài khoản
            'xem-tai-khoan', 'them-tai-khoan', 'sua-tai-khoan', 'xoa-tai-khoan',
            // Báo cáo
            'xem-bao-cao', 'xuat-bao-cao',
            // Phân công giảng dạy
            'xem-phan-cong', 'them-phan-cong', 'sua-phan-cong', 'xoa-phan-cong',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // ===== TẠO ROLES =====

        // Role: Quản trị viên (có tất cả quyền)
        $adminRole = Role::create(['name' => 'quan-tri-vien']);
        $adminRole->givePermissionTo(Permission::all());

        // Role: Giáo viên chủ nhiệm
        $gvcnRole = Role::create(['name' => 'giao-vien-chu-nhiem']);
        $gvcnRole->givePermissionTo([
            'xem-hoc-sinh', 'sua-hoc-sinh',
            'xem-lop-hoc',
            'xem-diem', 'them-diem', 'sua-diem',
            'xem-hanh-kiem', 'them-hanh-kiem', 'sua-hanh-kiem',
            'xem-bao-cao', 'xuat-bao-cao',
        ]);

      

        // ===== TẠO TÀI KHOẢN ADMIN MẶC ĐỊNH =====
        $admin = User::create([
            'name' => 'Quản Trị Viên',
            'email' => 'admin@school.com',
            'password' => Hash::make('Admin@123456'),
            'phone' => '0123456789',
            'is_active' => true,
        ]);
        $admin->assignRole('quan-tri-vien');

        // Tạo tài khoản giáo viên mẫu
        $gvcn = User::create([
            'name' => 'Nguyễn Thị Hoa',
            'email' => 'hoa.gvcn@school.com',
            'password' => Hash::make('Gvcn@123456'),
            'phone' => '0987654321',
            'is_active' => true,
        ]);
        $gvcn->assignRole('giao-vien-chu-nhiem');


        $this->command->info('✅ Đã tạo roles, permissions và tài khoản mặc định!');
        $this->command->info('Admin: admin@school.com / Admin@123456');
    }
}