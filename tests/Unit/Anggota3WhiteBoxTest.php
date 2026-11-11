<?php

namespace Tests\Unit;

use App\Support\ShopRules;
use PHPUnit\Framework\TestCase;

class Anggota3WhiteBoxTest extends TestCase
{
    /**
     * Anggota 3: Nayla Annora Nobel Widyonarko (Nora)
     * Kasus TC10-03: Transaksi Laundry di bawah ambang batas (Rp50.000, false)
     * Jalur Keputusan: D1=False, D2=False, D3=False (Return 0)
     */
    public function test_anggota_3_white_box(): void
    {
        $subtotal = 50000;
        $isMember = false;

        $hasilDiskon = ShopRules::discount($subtotal, $isMember);

        $this->assertSame(0, $hasilDiskon);
    }
}
