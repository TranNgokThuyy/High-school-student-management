<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mon_hoc', function (Blueprint $table) {
            $table->id();
            $table->string('ma_mon')->unique();
            $table->string('ten_mon');
            $table->integer('so_tiet_tuan')->default(1);
            $table->enum('khoi', ['10', '11', '12', 'Tất cả']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mon_hoc');
    }
};