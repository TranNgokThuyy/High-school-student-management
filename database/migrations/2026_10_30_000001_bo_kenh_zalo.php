<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Bỏ kênh Zalo: xóa bảng token Zalo OA và cột trạng thái gửi Zalo của tin nhắn phụ huynh
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('zalo_token');

        if (Schema::hasColumn('tin_nhan_nguoi_nhan', 'trang_thai_zalo')) {
            Schema::table('tin_nhan_nguoi_nhan', function (Blueprint $table) {
                $table->dropColumn('trang_thai_zalo');
            });
        }

        // Tin đã gửi trước đây: bỏ "zalo" khỏi danh sách kênh
        DB::table('tin_nhan')->where('kenh', 'like', '%zalo%')->orderBy('id')->each(function ($tin) {
            $kenh = array_values(array_diff(json_decode($tin->kenh, true) ?: [], ['zalo']));
            DB::table('tin_nhan')->where('id', $tin->id)->update(['kenh' => json_encode($kenh)]);
        });
    }

    public function down(): void
    {
        Schema::table('tin_nhan_nguoi_nhan', function (Blueprint $table) {
            $table->string('trang_thai_zalo', 20)->default('Không gửi')->after('trang_thai_sms');
        });
    }
};
