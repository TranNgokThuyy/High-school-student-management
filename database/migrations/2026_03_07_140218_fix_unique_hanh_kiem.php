<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Bước 1: Xem các foreign key đang dùng index này
        $fks = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'hanh_kiem'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        // Bước 2: Xóa tất cả foreign key trước
        foreach ($fks as $fk) {
            DB::statement("ALTER TABLE hanh_kiem DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        // Bước 3: Xóa unique cũ
        $indexes = DB::select("SHOW INDEX FROM hanh_kiem WHERE Key_name != 'PRIMARY'");
        $dropped = [];
        foreach ($indexes as $index) {
            if (!in_array($index->Key_name, $dropped)) {
                try {
                    DB::statement("ALTER TABLE hanh_kiem DROP INDEX `{$index->Key_name}`");
                    $dropped[] = $index->Key_name;
                } catch (\Exception $e) {
                    // Bỏ qua nếu không drop được
                }
            }
        }

        // Bước 4: Tạo lại foreign keys
        foreach ($fks as $fk) {
            $col = DB::selectOne("
                SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'hanh_kiem'
                  AND CONSTRAINT_NAME = ?
            ", [$fk->CONSTRAINT_NAME]);

            if ($col) {
                DB::statement("
                    ALTER TABLE hanh_kiem
                    ADD CONSTRAINT `{$fk->CONSTRAINT_NAME}`
                    FOREIGN KEY (`{$col->COLUMN_NAME}`)
                    REFERENCES `{$col->REFERENCED_TABLE_NAME}` (`{$col->REFERENCED_COLUMN_NAME}`)
                    ON DELETE CASCADE
                ");
            }
        }

        // Bước 5: Tạo unique mới đúng 4 cột
        DB::statement('
            ALTER TABLE hanh_kiem
            ADD UNIQUE KEY hanh_kiem_4col_unique
            (hoc_sinh_id, lop_hoc_id, hoc_ky, nam_hoc)
        ');
    }

    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE hanh_kiem DROP INDEX hanh_kiem_4col_unique');
        } catch (\Exception $e) {}

        DB::statement('
            ALTER TABLE hanh_kiem
            ADD UNIQUE KEY hanh_kiem_hoc_sinh_id_hoc_ky_nam_hoc_unique
            (hoc_sinh_id, hoc_ky, nam_hoc)
        ');
    }
};