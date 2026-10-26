<?php

namespace Tests\Unit;

use App\Support\Tien;
use PHPUnit\Framework\TestCase;

class TienTest extends TestCase
{
    public function test_doc_so_tien_bang_chu(): void
    {
        $this->assertSame('Không đồng', Tien::bangChu(0));
        $this->assertSame('Một trăm linh năm đồng', Tien::bangChu(105));
        $this->assertSame('Hai mươi mốt nghìn đồng', Tien::bangChu(21000));
        $this->assertSame('Một trăm mười lăm nghìn đồng', Tien::bangChu(115000));
        $this->assertSame('Một triệu không trăm linh năm nghìn đồng', Tien::bangChu(1005000));
        $this->assertSame('Hai triệu không trăm hai mươi tư nghìn đồng', Tien::bangChu(2024000));
        $this->assertSame('Tám trăm tám mươi tư nghìn năm trăm hai mươi đồng', Tien::bangChu(884520));
        $this->assertSame('Một tỷ đồng', Tien::bangChu(1_000_000_000));
    }

    public function test_dinh_dang_tien(): void
    {
        $this->assertSame('1.250.000 đ', Tien::dinhDang(1250000));
    }
}
