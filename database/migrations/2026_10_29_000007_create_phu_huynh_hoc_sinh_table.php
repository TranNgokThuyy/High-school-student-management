<?php

use App\Support\PhanQuyen;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tài khoản phụ huynh: một phụ huynh liên kết nhiều con, một học sinh có thể có cả cha và mẹ đăng ký
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phu_huynh_hoc_sinh', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('hoc_sinh_id')->constrained('hoc_sinh')->cascadeOnDelete();
            $table->string('quan_he', 30)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'hoc_sinh_id']);
        });

        PhanQuyen::dongBo();
    }

    public function down(): void
    {
        Schema::dropIfExists('phu_huynh_hoc_sinh');
    }
};
