<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('khen_thuong_ky_luat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoc_sinh_id')->constrained('hoc_sinh')->cascadeOnDelete();
            $table->enum('loai', ['Khen thưởng', 'Kỷ luật']);
            $table->string('hinh_thuc');          // VD: Giấy khen, Khiển trách, Cảnh cáo...
            $table->text('noi_dung');
            $table->date('ngay_quyet_dinh');
            $table->string('so_quyet_dinh', 50)->nullable();
            $table->string('nam_hoc', 20);
            $table->foreignId('nguoi_tao_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['nam_hoc', 'loai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('khen_thuong_ky_luat');
    }
};
