<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('phan_cong_giang_day', function (Blueprint $table) {
            $table->id();
            $table->foreignId('giao_vien_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mon_hoc_id')->constrained('mon_hoc')->cascadeOnDelete();
            $table->foreignId('lop_hoc_id')->constrained('lop_hoc')->cascadeOnDelete();
            $table->string('nam_hoc');
            $table->enum('hoc_ky', ['1', '2', 'Ca năm']);
            $table->timestamps();
            
            $table->unique(['giao_vien_id', 'mon_hoc_id', 'lop_hoc_id', 'nam_hoc', 'hoc_ky'], 'pcgd_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phan_cong_giang_day');
    }
};