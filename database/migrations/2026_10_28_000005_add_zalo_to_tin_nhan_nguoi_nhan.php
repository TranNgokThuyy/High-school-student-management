<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tin_nhan_nguoi_nhan', function (Blueprint $table) {
            $table->string('trang_thai_zalo', 20)->default('Không gửi')->after('trang_thai_sms');
        });
    }

    public function down(): void
    {
        Schema::table('tin_nhan_nguoi_nhan', function (Blueprint $table) {
            $table->dropColumn('trang_thai_zalo');
        });
    }
};
