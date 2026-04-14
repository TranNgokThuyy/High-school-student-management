<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('diem_so', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoc_sinh_id')->constrained('hoc_sinh')->cascadeOnDelete();
            $table->foreignId('mon_hoc_id')->constrained('mon_hoc')->cascadeOnDelete();
            $table->foreignId('lop_hoc_id')->constrained('lop_hoc')->cascadeOnDelete();
            $table->enum('hoc_ky', ['1', '2']);
            $table->string('nam_hoc'); // VD: 2024-2025
            // Điểm hệ số 1
            $table->decimal('diem_tx1', 4, 2)->nullable(); // Thường xuyên 1
            $table->decimal('diem_tx2', 4, 2)->nullable(); // Thường xuyên 2
            $table->decimal('diem_tx3', 4, 2)->nullable(); // Thường xuyên 3
            $table->decimal('diem_tx4', 4, 2)->nullable(); // Thường xuyên 4
            // Điểm hệ số 2
            $table->decimal('diem_gk', 4, 2)->nullable();  // Giữa kỳ
            // Điểm hệ số 3
            $table->decimal('diem_ck', 4, 2)->nullable();  // Cuối kỳ
            // Điểm tổng kết
            $table->decimal('diem_trung_binh', 4, 2)->nullable();
            $table->foreignId('giao_vien_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->unique(['hoc_sinh_id', 'mon_hoc_id', 'hoc_ky', 'nam_hoc']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diem_so');
    }
};