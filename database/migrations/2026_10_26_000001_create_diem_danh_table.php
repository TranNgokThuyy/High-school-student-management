<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('diem_danh', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoc_sinh_id')->constrained('hoc_sinh')->cascadeOnDelete();
            $table->foreignId('lop_hoc_id')->constrained('lop_hoc')->cascadeOnDelete();
            $table->date('ngay');
            $table->enum('trang_thai', ['Có mặt', 'Vắng có phép', 'Vắng không phép', 'Đi trễ'])->default('Có mặt');
            $table->string('ghi_chu')->nullable();
            $table->foreignId('nguoi_diem_danh_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['hoc_sinh_id', 'ngay']);
            $table->index(['lop_hoc_id', 'ngay']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diem_danh');
    }
};
