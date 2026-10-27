<?php
use App\Support\PhanQuyen;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Nhật ký thao tác: ai thêm / sửa / xóa dữ liệu gì, lúc nào, giá trị cũ → mới
return new class extends Migration {
    public function up(): void
    {
        Schema::create('nhat_ky', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('hanh_dong', 10);              // Thêm / Sửa / Xóa
            $table->string('doi_tuong_loai', 50);         // VD: DiemSo, HocSinh
            $table->unsignedBigInteger('doi_tuong_id');
            $table->unsignedBigInteger('hoc_sinh_id')->nullable(); // học sinh liên quan (để xem lịch sử theo học sinh)
            $table->string('mo_ta');
            $table->json('thay_doi')->nullable();         // {"diem_ck": [6.5, 8]}
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['doi_tuong_loai', 'doi_tuong_id']);
            $table->index('hoc_sinh_id');
            $table->index('created_at');
        });

        PhanQuyen::dongBo();
    }

    public function down(): void
    {
        Schema::dropIfExists('nhat_ky');
    }
};
