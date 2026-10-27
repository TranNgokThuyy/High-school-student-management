<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Thông tư 22/2021: kết quả rèn luyện 4 mức Tốt / Khá / Đạt / Chưa đạt (thay Trung bình / Yếu)
return new class extends Migration {
    public function up(): void
    {
        Schema::table('hanh_kiem', function (Blueprint $table) {
            $table->string('xep_loai_hanh_kiem', 20)->nullable()->change();
        });

        DB::table('hanh_kiem')->where('xep_loai_hanh_kiem', 'Trung bình')->update(['xep_loai_hanh_kiem' => 'Đạt']);
        DB::table('hanh_kiem')->where('xep_loai_hanh_kiem', 'Yếu')->update(['xep_loai_hanh_kiem' => 'Chưa đạt']);
    }

    public function down(): void
    {
        DB::table('hanh_kiem')->where('xep_loai_hanh_kiem', 'Đạt')->update(['xep_loai_hanh_kiem' => 'Trung bình']);
        DB::table('hanh_kiem')->where('xep_loai_hanh_kiem', 'Chưa đạt')->update(['xep_loai_hanh_kiem' => 'Yếu']);
    }
};
