<?php

namespace Tests\Unit;

use App\Support\ShopRules;
use PHPUnit\Framework\TestCase;

class Anggota5WhiteBoxTest extends TestCase
{
    /**
     * Anggota 5: Muh. Zaky Dawamul Busro
     * Kasus TC10-05: Subtotal Keranjang Layanan Laundry (2 paket cuci komplit @25k + 1 bed cover @15k)
     * Expected: 2 * 25.000 + 1 * 15.000 = 65.000
     */
    public function test_anggota_5_white_box(): void
    {
        $keranjangLayanan = [
            'cuci_komplit' => 2,
            'bed_cover'    => 1,
        ];

        $subtotal = ShopRules::subtotal($keranjangLayanan);

        $this->assertSame(65000, $subtotal);
    }
}
