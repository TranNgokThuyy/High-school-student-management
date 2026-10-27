<?php
use App\Support\PhanQuyen;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ho_so_giao_vien', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->date('ngay_sinh')->nullable();
            $table->string('gioi_tinh', 10)->nullable();
            $table->string('trinh_do', 30)->nullable();       // Cao đẳng / Đại học / Thạc sĩ / Tiến sĩ
            $table->string('chuyen_mon', 100)->nullable();    // VD: Toán, Ngữ văn
            $table->string('to_chuyen_mon', 100)->nullable();
            $table->string('chuc_vu', 50)->default('Giáo viên');
            $table->date('ngay_vao_nganh')->nullable();
            $table->unsignedTinyInteger('dinh_muc_tiet')->default(17);  // tiết/tuần
            $table->unsignedTinyInteger('giam_tru_tiet')->default(0);   // kiêm nhiệm (tổ trưởng, công đoàn...)
            $table->string('ly_do_giam_tru')->nullable();
            $table->timestamps();
        });

        PhanQuyen::dongBo();
    }

    public function down(): void
    {
        Schema::dropIfExists('ho_so_giao_vien');
    }
};
