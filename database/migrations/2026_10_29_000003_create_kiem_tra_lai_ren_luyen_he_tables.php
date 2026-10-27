<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Thông tư 22/2021 Điều 12, 13: kiểm tra lại các môn chưa đạt và rèn luyện trong kỳ nghỉ hè
return new class extends Migration {
    public function up(): void
    {
        Schema::create('kiem_tra_lai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoc_sinh_id')->constrained('hoc_sinh')->cascadeOnDelete();
            $table->foreignId('lop_hoc_id')->constrained('lop_hoc')->cascadeOnDelete();
            $table->foreignId('mon_hoc_id')->constrained('mon_hoc')->cascadeOnDelete();
            $table->string('nam_hoc', 20);
            $table->decimal('diem', 4, 2)->nullable();              // môn đánh giá bằng điểm số
            $table->string('ket_qua_nhan_xet', 10)->nullable();     // môn đánh giá bằng nhận xét
            $table->foreignId('nguoi_nhap_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['hoc_sinh_id', 'mon_hoc_id', 'nam_hoc']);
        });

        Schema::create('ren_luyen_he', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoc_sinh_id')->constrained('hoc_sinh')->cascadeOnDelete();
            $table->foreignId('lop_hoc_id')->constrained('lop_hoc')->cascadeOnDelete();
            $table->string('nam_hoc', 20);
            $table->string('ket_qua', 10);                          // Đạt / Chưa đạt
            $table->string('nhan_xet')->nullable();
            $table->foreignId('nguoi_nhap_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['hoc_sinh_id', 'nam_hoc']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ren_luyen_he');
        Schema::dropIfExists('kiem_tra_lai');
    }
};
