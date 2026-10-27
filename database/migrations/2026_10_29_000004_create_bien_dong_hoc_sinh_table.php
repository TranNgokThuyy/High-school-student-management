<?php
use App\Support\PhanQuyen;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Biến động học sinh có quyết định: chuyển đi, chuyển đến, thôi học, học lại
return new class extends Migration {
    public function up(): void
    {
        Schema::create('bien_dong_hoc_sinh', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoc_sinh_id')->constrained('hoc_sinh')->cascadeOnDelete();
            $table->string('loai', 20);
            $table->date('ngay');
            $table->string('so_quyet_dinh', 50)->nullable();
            $table->string('truong')->nullable();        // trường chuyển đến / chuyển đi từ
            $table->text('ly_do')->nullable();
            $table->foreignId('lop_hoc_id')->nullable()->constrained('lop_hoc')->nullOnDelete();
            $table->string('nam_hoc', 20);
            $table->foreignId('nguoi_tao_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['nam_hoc', 'loai']);
        });

        PhanQuyen::dongBo();
    }

    public function down(): void
    {
        Schema::dropIfExists('bien_dong_hoc_sinh');
    }
};
