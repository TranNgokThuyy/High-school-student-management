<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hanh_kiem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoc_sinh_id')->constrained('hoc_sinh')->cascadeOnDelete();
            $table->foreignId('lop_hoc_id')->constrained('lop_hoc')->cascadeOnDelete();
            $table->enum('hoc_ky', ['1', '2', 'Ca năm']);
            $table->string('nam_hoc');
            $table->enum('xep_loai_hanh_kiem', ['Tốt', 'Khá', 'Trung bình', 'Yếu'])->nullable();
            // Chuyên cần
            $table->integer('so_buoi_hoc')->default(0);       // Tổng số buổi học
            $table->integer('so_buoi_vang_co_phep')->default(0);  // Vắng có phép
            $table->integer('so_buoi_vang_khong_phep')->default(0); // Vắng không phép
            $table->integer('so_buoi_di_tre')->default(0);
            // Ghi chú vi phạm
            $table->text('ghi_chu')->nullable();
            $table->foreignId('giao_vien_chu_nhiem_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->unique(['hoc_sinh_id', 'hoc_ky', 'nam_hoc']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hanh_kiem');
    }
};