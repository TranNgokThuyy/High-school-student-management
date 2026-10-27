<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Token Zalo OA: access token sống ~25 giờ, refresh token 3 tháng và chỉ dùng được một lần → phải lưu lại mỗi lần làm mới
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zalo_token', function (Blueprint $table) {
            $table->id();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('het_han_luc')->nullable();
            $table->timestamp('refresh_het_han_luc')->nullable();
            $table->timestamp('lam_moi_luc')->nullable();
            $table->text('loi_gan_nhat')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zalo_token');
    }
};
