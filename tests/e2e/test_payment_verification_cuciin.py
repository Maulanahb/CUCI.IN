from pathlib import Path

from playwright.sync_api import Page, expect
import pytest

from tests.e2e.conftest import BASE_URL, E2eDbFactory

"""
ALUR 8 & 9: Upload Bukti Digital Customer & Verifikasi Pembayaran Kasir (Approve / Reject)
Mencakup TC-PAY-005, TC-VER-001, dan TC-VER-002.
"""


@pytest.mark.smoke
@pytest.mark.payment
def test_customer_upload_bukti_lalu_staff_approve_hingga_lunas(
    staff_page: Page,
    make_logged_in_page,
    e2e_db: E2eDbFactory,
    sample_png_path: Path,
):
    """Alur Multi-Aktor Positif: Customer unggah bukti Transfer -> Staff setujui -> Customer Lunas."""
    cust_page, cust_user = make_logged_in_page("customer", name="E2E Pembayar Approve")
    trx = e2e_db.create_transaction(
        customer_id=cust_user["customer_id"],
        weight=4.0,
        total_amount=40000.0,
        status="Diproses",
        payment_mode="unpaid",
    )

    # 1. Customer buka drawer detail transaksi dan unggah bukti Transfer
    cust_page.goto(f"{BASE_URL}/customer/transactions?selected={trx['transaction_code']}")
    upload_form = cust_page.locator('form[action$="/payment-proofs"]')
    expect(upload_form).to_be_visible()

    upload_form.locator("#proof-method").select_option("Transfer")
    upload_form.locator("#proof-amount").fill("40000")
    upload_form.locator("#proof-file").set_input_files(str(sample_png_path))

    submit_btn = upload_form.locator('button[type="submit"]')
    expect(submit_btn).to_be_enabled()
    submit_btn.click()

    expect(cust_page.locator("body")).to_contain_text("sudah terkirim")
    expect(cust_page.locator("body")).to_contain_text("lagi dicek kasir")

    # 2. Staff membuka panel verifikasi pembayaran, meninjau preview gambar, lalu menyetujui
    staff_page.goto(
        f"{BASE_URL}/staff/payments?status=Menunggu+Verifikasi&search={trx['transaction_code']}"
    )
    row = staff_page.locator("tbody tr", has_text=trx["transaction_code"])
    expect(row).to_be_visible()
    row.click()

    approve_btn = staff_page.get_by_role("button", name="Setujui Pembayaran")
    expect(approve_btn).to_be_visible()

    proof_img = staff_page.locator('img[alt="Foto Bukti Pembayaran"]')
    expect(proof_img).to_be_visible()
    proof_src = proof_img.get_attribute("src")
    assert proof_src is not None
    proof_res = staff_page.context.request.get(proof_src)
    assert proof_res.status == 200

    approve_btn.click()
    expect(staff_page.locator("body")).to_contain_text("berhasil diverifikasi")

    # 3. Customer reload melihat status pembayaran menjadi Terverifikasi & Lunas
    cust_page.goto(f"{BASE_URL}/customer/transactions?selected={trx['transaction_code']}")
    expect(cust_page.locator("body")).to_contain_text("Terverifikasi")
    expect(cust_page.locator("body")).to_contain_text("Lunas")


@pytest.mark.payment
def test_staff_tolak_bukti_pembayaran_tampil_alasan_di_customer(
    staff_page: Page,
    make_logged_in_page,
    e2e_db: E2eDbFactory,
    sample_png_path: Path,
):
    """Alur Multi-Aktor Negatif: Customer unggah bukti QRIS -> Staff tolak -> Customer lihat alasan & bisa unggah ulang."""
    cust_page, cust_user = make_logged_in_page("customer", name="E2E Pembayar Reject")
    trx = e2e_db.create_transaction(
        customer_id=cust_user["customer_id"],
        weight=3.0,
        total_amount=30000.0,
        status="Diterima",
        payment_mode="unpaid",
    )

    # 1. Customer unggah bukti QRIS
    cust_page.goto(f"{BASE_URL}/customer/transactions?selected={trx['transaction_code']}")
    upload_form = cust_page.locator('form[action$="/payment-proofs"]')
    upload_form.locator("#proof-method").select_option("QRIS")
    upload_form.locator("#proof-amount").fill("30000")
    upload_form.locator("#proof-file").set_input_files(str(sample_png_path))
    upload_form.locator('button[type="submit"]').click()

    expect(cust_page.locator("body")).to_contain_text("lagi dicek kasir")

    # 2. Staff tolak bukti dengan alasan penolakan
    staff_page.goto(
        f"{BASE_URL}/staff/payments?status=Menunggu+Verifikasi&search={trx['transaction_code']}"
    )
    staff_page.locator("tbody tr", has_text=trx["transaction_code"]).click()

    staff_page.get_by_role("button", name="Tolak Bukti").click()
    staff_page.locator("#drawer_notes").fill("Nominal transfer tidak terbaca pada mutasi.")
    staff_page.get_by_role("button", name="Konfirmasi Tolak").click()

    expect(staff_page.locator("body")).to_contain_text("ditolak")

    # 3. Customer reload melihat status Ditolak, alasan penolakan, dan form unggah ulang terbuka
    cust_page.goto(f"{BASE_URL}/customer/transactions?selected={trx['transaction_code']}")
    expect(cust_page.locator("body")).to_contain_text("Ditolak")
    expect(cust_page.locator("body")).to_contain_text("Alasan ditolak:")
    expect(cust_page.locator("body")).to_contain_text("Nominal transfer tidak terbaca pada mutasi.")
    expect(cust_page.locator('form[action$="/payment-proofs"]')).to_be_visible()
