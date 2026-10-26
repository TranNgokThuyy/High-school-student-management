<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Các khoản thu: học phí, BHYT, quỹ, dịch vụ... (số tiền tính bằng đồng)
        Schema::create('khoan_thu', function (Blueprint $table) {
            $table->id();
            $table->string('ten');
            $table->enum('loai', ['Học phí', 'Bảo hiểm', 'Quỹ', 'Dịch vụ', 'Khác'])->default('Học phí');
            $table->string('nam_hoc', 20);
            $table->enum('hoc_ky', ['1', '2', 'Ca năm'])->default('Ca năm');
            $table->enum('khoi', ['10', '11', '12', 'Tất cả'])->default('Tất cả');
            $table->unsignedBigInteger('so_tien');
            $table->date('han_nop')->nullable();
            $table->boolean('ap_dung_mien_giam')->default(true);
            $table->text('mo_ta')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['nam_hoc', 'is_active']);
        });

        // Miễn giảm theo đối tượng chính sách, áp dụng cả năm học
        Schema::create('mien_giam', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoc_sinh_id')->constrained('hoc_sinh')->cascadeOnDelete();
            $table->string('nam_hoc', 20);
            $table->string('doi_tuong');
            $table->unsignedTinyInteger('phan_tram');
            $table->string('ghi_chu')->nullable();
            $table->timestamps();

            $table->unique(['hoc_sinh_id', 'nam_hoc']);
        });

        Schema::create('phieu_thu', function (Blueprint $table) {
            $table->id();
            $table->string('so_phieu', 30)->unique();
            $table->foreignId('hoc_sinh_id')->constrained('hoc_sinh')->restrictOnDelete();
            $table->foreignId('khoan_thu_id')->constrained('khoan_thu')->restrictOnDelete();
            $table->unsignedBigInteger('so_tien');
            $table->date('ngay_thu');
            $table->enum('hinh_thuc', ['Tiền mặt', 'Chuyển khoản'])->default('Tiền mặt');
            $table->string('nguoi_nop')->nullable();
            $table->string('ghi_chu')->nullable();
            $table->foreignId('nguoi_thu_id')->nullable()->constrained('users')->nullOnDelete();
            // Không xóa phiếu đã lập; chỉ hủy để giữ dấu vết
            $table->boolean('da_huy')->default(false);
            $table->string('ly_do_huy')->nullable();
            $table->foreignId('nguoi_huy_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('huy_luc')->nullable();
            $table->timestamps();

            $table->index(['hoc_sinh_id', 'khoan_thu_id']);
            $table->index('ngay_thu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phieu_thu');
        Schema::dropIfExists('mien_giam');
        Schema::dropIfExists('khoan_thu');
    }
};
