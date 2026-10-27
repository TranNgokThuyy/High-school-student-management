<?php
use App\Support\PhanQuyen;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Kỳ thi (học kỳ / khảo sát): lịch thi từng môn, phòng thi, số báo danh
return new class extends Migration {
    public function up(): void
    {
        Schema::create('ky_thi', function (Blueprint $table) {
            $table->id();
            $table->string('ten');
            $table->string('nam_hoc', 20);
            $table->string('hoc_ky', 10)->default('1');
            $table->string('khoi', 10)->default('Tất cả');
            $table->unsignedSmallInteger('suc_chua_phong')->default(24);
            $table->string('tien_to_sbd', 10)->default('TS');
            $table->text('ghi_chu')->nullable();
            $table->timestamps();
        });

        Schema::create('lich_thi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ky_thi_id')->constrained('ky_thi')->cascadeOnDelete();
            $table->foreignId('mon_hoc_id')->constrained('mon_hoc')->cascadeOnDelete();
            $table->date('ngay');
            $table->string('gio_bat_dau', 5);       // HH:MM
            $table->unsignedSmallInteger('thoi_gian'); // phút
            $table->timestamps();

            $table->unique(['ky_thi_id', 'mon_hoc_id']);
        });

        Schema::create('phong_thi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ky_thi_id')->constrained('ky_thi')->cascadeOnDelete();
            $table->string('ten_phong', 30);
            $table->unsignedSmallInteger('thu_tu');
            $table->timestamps();
        });

        Schema::create('thi_sinh', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ky_thi_id')->constrained('ky_thi')->cascadeOnDelete();
            $table->foreignId('hoc_sinh_id')->constrained('hoc_sinh')->cascadeOnDelete();
            $table->foreignId('phong_thi_id')->constrained('phong_thi')->cascadeOnDelete();
            $table->string('so_bao_danh', 20);
            $table->timestamps();

            $table->unique(['ky_thi_id', 'hoc_sinh_id']);
            $table->unique(['ky_thi_id', 'so_bao_danh']);
        });

        PhanQuyen::dongBo();
    }

    public function down(): void
    {
        Schema::dropIfExists('thi_sinh');
        Schema::dropIfExists('phong_thi');
        Schema::dropIfExists('lich_thi');
        Schema::dropIfExists('ky_thi');
    }
};
