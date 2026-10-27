<?php
use App\Support\PhanQuyen;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hoc_sinh', function (Blueprint $table) {
            $table->string('email_phu_huynh')->nullable()->after('so_dien_thoai_me');
        });

        // Mỗi lần gửi: tiêu đề, nội dung mẫu (có thể chứa {ho_ten}, {lop}...), kênh gửi
        Schema::create('tin_nhan', function (Blueprint $table) {
            $table->id();
            $table->string('tieu_de');
            $table->text('noi_dung');
            $table->enum('loai', ['Thông báo chung', 'Chuyên cần', 'Học phí', 'Kết quả học tập', 'Khen thưởng - Kỷ luật'])
                ->default('Thông báo chung');
            $table->string('pham_vi');          // Mô tả người nhận, VD: "Lớp 10A1", "Toàn trường"
            $table->json('kenh');               // ['web', 'email', 'sms']
            $table->foreignId('nguoi_gui_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Từng phụ huynh nhận tin (theo học sinh), nội dung đã điền thông tin riêng
        Schema::create('tin_nhan_nguoi_nhan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tin_nhan_id')->constrained('tin_nhan')->cascadeOnDelete();
            $table->foreignId('hoc_sinh_id')->constrained('hoc_sinh')->cascadeOnDelete();
            $table->text('noi_dung');
            $table->string('email')->nullable();
            $table->string('so_dien_thoai', 20)->nullable();
            $table->string('trang_thai_email', 20)->default('Không gửi');
            $table->string('trang_thai_sms', 20)->default('Không gửi');
            $table->text('loi')->nullable();
            $table->timestamp('da_xem_luc')->nullable();
            $table->timestamps();

            $table->index('hoc_sinh_id');
        });

        PhanQuyen::dongBo();
    }

    public function down(): void
    {
        Schema::dropIfExists('tin_nhan_nguoi_nhan');
        Schema::dropIfExists('tin_nhan');
        Schema::table('hoc_sinh', function (Blueprint $table) {
            $table->dropColumn('email_phu_huynh');
        });
    }
};
