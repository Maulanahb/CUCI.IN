import re
import time
import uuid

from playwright.sync_api import Page, expect
import pytest

from tests.e2e.conftest import BASE_URL, E2eDbFactory

"""
ALUR 5 & 6: Kasir POS Checkout, Quick-Add Pelanggan, Idempotency Button, & Struk Thermal
Mencakup TC-POS-001, TC-POS-002, TC-POS-003, TC-POS-004.
"""


@pytest.mark.smoke
@pytest.mark.pos
def test_pos_checkout_multi_item_bayar_dp_dan_cetak_struk_thermal(
    staff_page: Page,
    e2e_db: E2eDbFactory,
    active_services: list[dict],
):
    """Alur POS penuh: quick-add pelanggan, pilih 2 layanan, bayar DP, redirect detail, & cetak struk QR."""
    assert len(active_services) >= 2, "Minimal dibutuhkan 2 layanan aktif untuk uji POS multi-item."
    svc_first = active_services[0]
    svc_second = active_services[1]

    suffix = uuid.uuid4().hex[:6].upper()
    quick_name = f"E2E Quick {suffix}"
    quick_phone = f"0899{str(int(time.time()))[-8:]}"

    staff_page.goto(f"{BASE_URL}/staff/transactions/create")
    expect(
        staff_page.get_by_role("heading", name="Kasir POS — Transaksi Baru")
    ).to_be_visible()

    # 1. Uji Quick-Add Pelanggan lewat drawer #modal-quick-customer
    staff_page.locator("#btn-open-quick-customer").click()
    expect(staff_page.locator("#form-quick-customer")).to_be_visible()
    staff_page.locator("#quick_customer_name").fill(quick_name)
    staff_page.locator("#quick_customer_phone").fill(quick_phone)
    staff_page.locator("#quick_customer_address").fill("Jl. Pos Merdeka No. 12")
    staff_page.locator("#btn-save-quick-customer").click()

    # Tunggu preview pelanggan muncul dan select terisi
    preview_card = staff_page.locator("#customer-preview-card")
    expect(preview_card).to_be_visible()
    expect(preview_card).to_contain_text(quick_name)
    selected_customer_id = staff_page.locator("#customer_id").input_value()
    assert selected_customer_id.isdigit()
    e2e_db.track_customer_id(int(selected_customer_id))

    # 2. Pilih layanan pertama di Item #1 (berat 3 kg) lalu uji Tambah Baris untuk layanan kedua (berat 2 kg)
    staff_page.locator('select[name="items[0][service_id]"]').select_option(str(svc_first["id"]))
    staff_page.locator('input[name="items[0][weight]"]').fill("3")

    staff_page.get_by_role("button", name="Tambah Baris").click()
    staff_page.locator('select[name="items[1][service_id]"]').select_option(str(svc_second["id"]))
    staff_page.locator('input[name="items[1][weight]"]').fill("2")

    # 3. Uji pencarian katalog cepat: klik layanan pertama menaikkan berat Item #1 (3 -> 4.00 kg)
    search_box = staff_page.locator("#service-search-input")
    search_box.fill(svc_first["name"])
    staff_page.get_by_role("button", name=f"Tambah {svc_first['name']} ke nota").click()
    expect(staff_page.locator('input[name="items[0][weight]"]')).to_have_value("4.00")
    search_box.fill("")

    # 4. Pilih pembayaran DP Tunai sebesar Rp 10.000
    staff_page.get_by_role("button", name="Bayar DP").click()
    staff_page.locator("#payment_amount").fill("10000")

    # Submit nota POS dan pastikan redirect ke detail transaksi
    staff_page.locator("#btn-submit-transaction").click()
    expect(staff_page).to_have_url(re.compile(rf"^{re.escape(BASE_URL)}/staff/transactions/\d+$"))

    expect(staff_page.locator("body")).to_contain_text("Transaksi berhasil disimpan")
    expect(staff_page.locator("body")).to_contain_text(quick_name)
    expect(staff_page.locator("body")).to_contain_text("DP")

    trx_code = staff_page.locator("p.font-mono.tabular-nums").first.inner_text().strip()
    assert trx_code.startswith("TRX-")
    e2e_db.track_transaction_code(trx_code)

    # 5. Verifikasi halaman struk thermal (Code128 barcode & QR code SVG)
    print_href = staff_page.locator("#btn-print-receipt").get_attribute("href")
    assert print_href is not None
    staff_page.goto(print_href)

    receipt = staff_page.locator("#receipt")
    expect(receipt).to_be_visible()
    expect(receipt).to_contain_text("CUCI.IN LAUNDRY")
    expect(receipt).to_contain_text(trx_code)
    expect(receipt).to_contain_text(quick_name)
    expect(receipt).to_contain_text(svc_first["name"])
    expect(receipt).to_contain_text(svc_second["name"])
    expect(receipt.locator(".barcode-section svg")).to_be_visible()
    expect(receipt.locator(".qr-section svg")).to_be_visible()

    # Uji toggle ukuran kertas thermal 58mm vs 80mm
    staff_page.locator('button[data-size="58mm"]').click()
    expect(receipt).to_have_class(re.compile(r"paper-58mm"))


@pytest.mark.pos
def test_pos_bayar_nanti_simpan_nota_belum_bayar_dan_persisten(
    staff_page: Page,
    e2e_db: E2eDbFactory,
    active_services: list[dict],
):
    """Alur POS Bayar Nanti: menghasilkan status cucian Diterima & status pembayaran Belum Bayar."""
    customer = e2e_db.create_customer(name="E2E Pelanggan Bayar Nanti")
    svc = active_services[0]

    staff_page.goto(f"{BASE_URL}/staff/transactions/create")
    staff_page.locator("#customer_id").select_option(str(customer["customer_id"]))
    staff_page.locator("#service_id").select_option(str(svc["id"]))
    staff_page.locator("#weight").fill("4")

    # Mode default adalah 'Bayar Nanti'; pastikan hidden idempotency_key UUID terisi
    idemp_key = staff_page.locator('input[name="idempotency_key"]').input_value()
    assert len(idemp_key) >= 32

    staff_page.locator("#btn-submit-transaction").click()
    expect(staff_page).to_have_url(re.compile(rf"^{re.escape(BASE_URL)}/staff/transactions/\d+$"))

    expect(staff_page.locator("body")).to_contain_text("Diterima")
    expect(staff_page.locator("body")).to_contain_text("Belum Bayar")

    # Lapisan 2: Reload halaman detail untuk verifikasi persistensi
    staff_page.reload()
    expect(staff_page.locator("body")).to_contain_text(customer["name"])
    expect(staff_page.locator("body")).to_contain_text("Belum Bayar")
