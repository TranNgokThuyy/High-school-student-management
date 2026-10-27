<?php
use App\Support\PhanQuyen;
use Illuminate\Database\Migrations\Migration;

// Bổ sung quyền mới (TKB, điểm danh, tổng kết, khen thưởng) và vai trò giáo viên bộ môn
// cho các CSDL đã chạy seeder trước đó.
return new class extends Migration {
    public function up(): void
    {
        PhanQuyen::dongBo();
    }

    public function down(): void
    {
        // Không xóa quyền để tránh mất phân quyền đã gán
    }
};
