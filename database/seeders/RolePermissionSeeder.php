<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Support\PhanQuyen;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ===== TẠO PERMISSIONS & ROLES (dùng chung với migration) =====
        PhanQuyen::dongBo();

        // ===== TẠO TÀI KHOẢN ADMIN MẶC ĐỊNH =====
        $admin = User::firstOrCreate(['email' => 'admin@school.com'], [
            'name' => 'Quản Trị Viên',
            'password' => Hash::make('Admin@123456'),
            'phone' => '0123456789',
            'is_active' => true,
        ]);
        $admin->assignRole('quan-tri-vien');

        // Tạo tài khoản giáo viên mẫu
        $gvcn = User::firstOrCreate(['email' => 'hoa.gvcn@school.com'], [
            'name' => 'Nguyễn Thị Hoa',
            'password' => Hash::make('Gvcn@123456'),
            'phone' => '0987654321',
            'is_active' => true,
        ]);
        $gvcn->assignRole('giao-vien-chu-nhiem');


        $this->command->info('✅ Đã tạo roles, permissions và tài khoản mặc định!');
        $this->command->info('Admin: admin@school.com / Admin@123456');
    }
}