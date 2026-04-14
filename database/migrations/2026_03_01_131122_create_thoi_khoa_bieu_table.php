<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('thoi_khoa_bieu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lop_hoc_id')->constrained('lop_hoc')->onDelete('cascade');
            $table->foreignId('mon_hoc_id')->constrained('mon_hoc')->onDelete('cascade');
            $table->foreignId('giao_vien_id')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('thu')->comment('2=Thứ 2, 3=Thứ 3,..., 7=Thứ 7');
            $table->tinyInteger('tiet_bat_dau')->comment('1-5');
            $table->tinyInteger('so_tiet')->default(1);
            $table->string('phong_hoc', 20)->nullable();
            $table->string('hoc_ky', 10)->default('1');
            $table->string('nam_hoc', 20);
            $table->timestamps();

            // Không trùng lịch: cùng lớp + thứ + tiết + học kỳ + năm học
            $table->unique(
                ['lop_hoc_id','thu','tiet_bat_dau','hoc_ky','nam_hoc'],
                'unique_lop_thu_tiet'
            );
            // Không trùng phòng: cùng phòng + thứ + tiết + học kỳ + năm học
            $table->index(['giao_vien_id','thu','hoc_ky','nam_hoc']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thoi_khoa_bieu');
    }
};