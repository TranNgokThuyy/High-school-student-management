<?php
use App\Support\PhanQuyen;
use Illuminate\Database\Migrations\Migration;

// Bổ sung quyền học phí, thông báo và vai trò kế toán cho CSDL đã có
return new class extends Migration {
    public function up(): void
    {
        PhanQuyen::dongBo();
    }

    public function down(): void
    {
    }
};
