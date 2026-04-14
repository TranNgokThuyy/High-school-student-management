<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hoc_sinh', function (Blueprint $table) {
            $table->id();
            $table->string('ma_hoc_sinh')->unique();
            $table->string('ho_ten');
            $table->date('ngay_sinh');
            $table->enum('gioi_tinh', ['Nam', 'Nữ', 'Khác']);
            $table->string('dan_toc')->default('Kinh');
            $table->string('ton_giao')->nullable();
            $table->text('dia_chi_thuong_tru')->nullable();
            $table->text('dia_chi_tam_tru')->nullable();
            $table->string('so_dien_thoai')->nullable();
            $table->string('email')->nullable();
            $table->string('cccd')->nullable()->unique();
            $table->string('ho_ten_cha')->nullable();
            $table->string('nghe_nghiep_cha')->nullable();
            $table->string('so_dien_thoai_cha')->nullable();
            $table->string('ho_ten_me')->nullable();
            $table->string('nghe_nghiep_me')->nullable();
            $table->string('so_dien_thoai_me')->nullable();
            $table->string('anh_the')->nullable();
            $table->enum('trang_thai', ['Đang học', 'Đã tốt nghiệp', 'Thôi học', 'Chuyển trường'])->default('Đang học');
            $table->foreignId('lop_hoc_id')->nullable()->constrained('lop_hoc')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hoc_sinh');
    }
};