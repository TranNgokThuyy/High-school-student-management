<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('thong_bao', function (Blueprint $table) {
            $table->id();
            $table->string('tieu_de');
            $table->text('noi_dung');
            $table->enum('muc_do', ['Thông thường', 'Quan trọng', 'Khẩn'])->default('Thông thường');
            $table->json('doi_tuong')->nullable(); // danh sách vai trò nhận; null = tất cả
            $table->boolean('ghim')->default(false);
            $table->date('het_han')->nullable();
            $table->foreignId('nguoi_dang_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thong_bao');
    }
};
