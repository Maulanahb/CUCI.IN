from playwright.sync_api import Page, expect
import pytest

from tests.e2e.conftest import BASE_URL, E2eDbFactory

"""
LAB 3: Membuang Sleep, Memakai Auto-Waiting pada CUCI.IN
Anggota Bertanggung Jawab: Nayla Annora (Nora)

Kasus: Halaman Daftar Transaksi Kasir (/staff/transactions)
Daftar transaksi dicari dan difilter menggunakan Playwright web-first assertion expect(...)
tanpa jeda kaku time.sleep().
"""


@pytest.mark.pos
def test_riwayat_transaksi_tanpa_sleep(staff_page: Page, e2e_db: E2eDbFactory):
    """Pencarian nota dan filter status di /staff/transactions memakai auto-waiting expect."""
    customer = e2e_db.create_customer(name="E2E Pelanggan Riwayat")
    trx_diproses = e2e_db.create_transaction(
        customer_id=customer["customer_id"],
        weight=3.0,
        total_amount=30000.0,
        status="Diproses",
        payment_mode="paid",
    )

    staff_page.goto(f"{BASE_URL}/staff/transactions")

    search_input = staff_page.locator("#search")
    expect(search_input).to_be_visible()
    search_input.fill(trx_diproses["transaction_code"])
    staff_page.get_by_role("button", name="Cari", exact=True).click()

    expect(staff_page.locator("table, main").first).to_be_visible()
    expect(staff_page.locator("body")).to_contain_text(trx_diproses["transaction_code"])
    expect(staff_page.locator("body")).to_contain_text(customer["name"])

    # Klik tab filter status Diproses dan pastikan transaksi tetap muncul
    staff_page.locator('div[aria-label="Filter status cucian"] a', has_text="Diproses").click()
    expect(staff_page.locator("body")).to_contain_text(trx_diproses["transaction_code"])

    # Klik baris untuk membuka drawer ringkasan & navigasi ke detail penuh
    staff_page.locator("tbody tr", has_text=trx_diproses["transaction_code"]).click()
    detail_link = staff_page.locator(f'a[href$="/staff/transactions/{trx_diproses["transaction_id"]}"]').first
    expect(detail_link).to_be_visible()
    detail_link.click()

    expect(staff_page).to_have_url(
        f"{BASE_URL}/staff/transactions/{trx_diproses['transaction_id']}"
    )
    expect(staff_page.locator("body")).to_contain_text(trx_diproses["transaction_code"])
