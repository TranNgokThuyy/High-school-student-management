<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Bước 1: Lấy tất cả foreign key của bảng diem_so
        $fks = DB::select("
            SELECT
                kcu.CONSTRAINT_NAME,
                kcu.COLUMN_NAME,
                kcu.REFERENCED_TABLE_NAME,
                kcu.REFERENCED_COLUMN_NAME,
                rc.DELETE_RULE,
                rc.UPDATE_RULE
            FROM information_schema.KEY_COLUMN_USAGE kcu
            JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
                ON rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
                AND rc.CONSTRAINT_SCHEMA = kcu.TABLE_SCHEMA
            WHERE kcu.TABLE_SCHEMA = DATABASE()
              AND kcu.TABLE_NAME = 'diem_so'
              AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
        ");

        // Bước 2: Drop tất cả FK
        foreach ($fks as $fk) {
            DB::statement("ALTER TABLE diem_so DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        // Bước 3: Xóa data trùng (giữ bản ghi mới nhất)
        DB::statement('
            DELETE d1 FROM diem_so d1
            INNER JOIN diem_so d2
            WHERE d1.id < d2.id
              AND d1.hoc_sinh_id = d2.hoc_sinh_id
              AND d1.mon_hoc_id  = d2.mon_hoc_id
              AND d1.lop_hoc_id  = d2.lop_hoc_id
              AND d1.hoc_ky      = d2.hoc_ky
              AND d1.nam_hoc     = d2.nam_hoc
        ');

        // Bước 4: Drop tất cả index (trừ PRIMARY)
        $indexes = DB::select("SHOW INDEX FROM diem_so WHERE Key_name != 'PRIMARY'");
        $dropped = [];
        foreach ($indexes as $index) {
            if (!in_array($index->Key_name, $dropped)) {
                try {
                    DB::statement("ALTER TABLE diem_so DROP INDEX `{$index->Key_name}`");
                    $dropped[] = $index->Key_name;
                } catch (\Exception $e) {
                    // bỏ qua
                }
            }
        }

        // Bước 5: Tạo lại unique mới đủ 5 cột
        DB::statement('
            ALTER TABLE diem_so
            ADD UNIQUE KEY diem_so_5col_unique
            (hoc_sinh_id, mon_hoc_id, lop_hoc_id, hoc_ky, nam_hoc)
        ');

        // Bước 6: Tạo lại các index thường cho FK columns
        DB::statement('ALTER TABLE diem_so ADD INDEX idx_diem_so_hoc_sinh (hoc_sinh_id)');
        DB::statement('ALTER TABLE diem_so ADD INDEX idx_diem_so_mon_hoc (mon_hoc_id)');
        DB::statement('ALTER TABLE diem_so ADD INDEX idx_diem_so_lop_hoc (lop_hoc_id)');

        // Bước 7: Tạo lại FK
        foreach ($fks as $fk) {
            $onDelete = $fk->DELETE_RULE ?? 'CASCADE';
            $onUpdate = $fk->UPDATE_RULE ?? 'CASCADE';
            try {
                DB::statement("
                    ALTER TABLE diem_so
                    ADD CONSTRAINT `{$fk->CONSTRAINT_NAME}`
                    FOREIGN KEY (`{$fk->COLUMN_NAME}`)
                    REFERENCES `{$fk->REFERENCED_TABLE_NAME}` (`{$fk->REFERENCED_COLUMN_NAME}`)
                    ON DELETE {$onDelete}
                    ON UPDATE {$onUpdate}
                ");
            } catch (\Exception $e) {
                // bỏ qua nếu FK đã tồn tại
            }
        }
    }

    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE diem_so DROP INDEX diem_so_5col_unique');
        } catch (\Exception $e) {}

        try {
            DB::statement('
                ALTER TABLE diem_so
                ADD UNIQUE KEY diem_so_hoc_sinh_id_mon_hoc_id_hoc_ky_nam_hoc_unique
                (hoc_sinh_id, mon_hoc_id, hoc_ky, nam_hoc)
            ');
        } catch (\Exception $e) {}
    }
};