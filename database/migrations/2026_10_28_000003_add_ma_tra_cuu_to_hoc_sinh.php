<?php
use App\Models\HocSinh;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Mã tra cứu ngẫu nhiên cho phụ huynh, thay cho việc tra cứu bằng ngày sinh (dễ đoán)
return new class extends Migration {
    public function up(): void
    {
        Schema::table('hoc_sinh', function (Blueprint $table) {
            $table->string('ma_tra_cuu', 12)->nullable()->unique()->after('ma_hoc_sinh');
        });

        DB::table('hoc_sinh')->whereNull('ma_tra_cuu')->orderBy('id')->pluck('id')->each(
            fn ($id) => DB::table('hoc_sinh')->where('id', $id)->update(['ma_tra_cuu' => HocSinh::taoMaTraCuu()])
        );
    }

    public function down(): void
    {
        Schema::table('hoc_sinh', function (Blueprint $table) {
            $table->dropUnique(['ma_tra_cuu']);
            $table->dropColumn('ma_tra_cuu');
        });
    }
};
