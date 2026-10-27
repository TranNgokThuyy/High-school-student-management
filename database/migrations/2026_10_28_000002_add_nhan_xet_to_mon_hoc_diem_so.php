<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Môn đánh giá bằng nhận xét (Đạt / Chưa đạt) theo Thông tư 22/2021: GDTC, Nghệ thuật, HĐTN-HN, GDĐP
return new class extends Migration {
    public function up(): void
    {
        Schema::table('mon_hoc', function (Blueprint $table) {
            $table->string('hinh_thuc_danh_gia', 20)->default('Điểm số')->after('khoi');
        });

        Schema::table('diem_so', function (Blueprint $table) {
            // {"tx1": "Đ", "tx2": "CĐ", ..., "gk": "Đ", "ck": "Đ"}
            $table->json('nhan_xet')->nullable()->after('diem_ck');
            $table->string('ket_qua_nhan_xet', 10)->nullable()->after('diem_trung_binh');
        });
    }

    public function down(): void
    {
        Schema::table('diem_so', function (Blueprint $table) {
            $table->dropColumn(['nhan_xet', 'ket_qua_nhan_xet']);
        });
        Schema::table('mon_hoc', function (Blueprint $table) {
            $table->dropColumn('hinh_thuc_danh_gia');
        });
    }
};
