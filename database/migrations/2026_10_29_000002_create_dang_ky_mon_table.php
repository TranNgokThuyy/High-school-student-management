<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Chương trình GDPT 2018: môn bắt buộc / môn lựa chọn / chuyên đề học tập; học sinh đăng ký môn lựa chọn theo năm học
return new class extends Migration {
    public function up(): void
    {
        Schema::table('mon_hoc', function (Blueprint $table) {
            $table->string('loai_mon', 20)->default('Bắt buộc')->after('khoi');
        });

        Schema::create('dang_ky_mon', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoc_sinh_id')->constrained('hoc_sinh')->cascadeOnDelete();
            $table->foreignId('mon_hoc_id')->constrained('mon_hoc')->cascadeOnDelete();
            $table->string('nam_hoc', 20);
            $table->timestamps();

            $table->unique(['hoc_sinh_id', 'mon_hoc_id', 'nam_hoc']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dang_ky_mon');
        Schema::table('mon_hoc', function (Blueprint $table) {
            $table->dropColumn('loai_mon');
        });
    }
};
