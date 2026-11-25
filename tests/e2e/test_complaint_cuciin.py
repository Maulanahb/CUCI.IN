from pathlib import Path
import re

from playwright.sync_api import Page, expect
import pytest

from tests.e2e.conftest import BASE_URL, E2eDbFactory

"""
LAB 8 + ALUR 11, 12, 16: Pengaduan Pelanggan, Penanganan Staf, & Anti-IDOR
Anggota Bertanggung Jawab: Maulana Ahmad Bukhori (Lead) & Gargarina Nanda (Ayin)

Menguji secara nyata:
1. Customer mengajukan komplain baru lengkap dengan unggahan foto kendala -> tersimpan 'Menunggu' & persisten.
2. Staf menindaklanjuti komplain pada panel /staff/complaints -> ubah ke 'Selesai' dengan tanggapan resmi
   -> Customer melihat status 'Tuntas Selesai' dan balasan Staf setelah reload.
3. Transaksi berstatus 'Dibatalkan' dikecualikan dari opsi pengajuan komplain.
4. Anti-IDOR lintas pelanggan: Customer B ditolak HTTP 403 saat mengakses detail komplain maupun
   berkas bukti pembayaran milik Customer A.
"""


@pytest.mark.smoke
@pytest.mark.complaint
def test_pengajuan_komplain_hingga_ditangani_staff_dan_dilihat_customer(
    staff_page: Page,
    make_logged_in_page,
    e2e_db: E2eDbFactory,
    sample_png_path: Path,
):
    """Alur End-to-End penuh: Customer buat tiket + foto -> Staf tangani & balas -> Customer lihat solusi."""
    cust_page, cust_user = make_logged_in_page("customer", name="E2E Pelanggan Komplain")
    trx = e2e_db.create_transaction(
        customer_id=cust_user["customer_id"],
        weight=4.0,
        total_amount=40000.0,
        status="Diproses",
        payment_mode="paid",
    )

    # 1. Customer mengisi formulir pengaduan baru
    cust_page.goto(f"{BASE_URL}/customer/complaints/create?transaction_code={trx['transaction_code']}")
    expect(cust_page.locator("#transaction_id")).to_have_value(str(trx["transaction_id"]))

    cust_page.locator("#subject").fill("Kemeja putih masih terdapat noda minyak")
    cust_page.locator("#description").fill(
        "Saat paket cucian diperiksa, bagian kerah dan lengan kanan kemeja putih masih berbintik noda hitam."
    )
    cust_page.locator("#photo").set_input_files(str(sample_png_path))
    expect(cust_page.locator("body")).to_contain_text("Foto Siap Diunggah")

    cust_page.get_by_role("button", name="Kirim Pengaduan").click()
    expect(cust_page).to_have_url(re.compile(rf"^{re.escape(BASE_URL)}/customer/complaints/\d+$"))

    complaint_url = cust_page.url
    expect(cust_page.locator("body")).to_contain_text("Menunggu Penanganan")
    expect(cust_page.locator("body")).to_contain_text("Kemeja putih masih terdapat noda minyak")

    # Reload untuk memastikan persistensi di database
    cust_page.reload()
    expect(cust_page.locator("body")).to_contain_text("Kemeja putih masih terdapat noda minyak")

    # 2. Staf mencari komplain tersebut dan menyelesaikan tindak lanjut dengan tanggapan
    staff_page.goto(f"{BASE_URL}/staff/complaints?search={trx['transaction_code']}")
    row = staff_page.locator("tbody tr", has_text=trx["transaction_code"])
    expect(row).to_be_visible()
    row.click()

    expect(staff_page.locator("#drawer_status")).to_be_visible()
    staff_page.locator("#drawer_status").select_option("Selesai")
    staff_page.locator("#drawer_response").fill(
        "Mohon maaf atas kendalanya. Kemeja sudah kami cuci ulang gratis dan noda sudah bersih."
    )
    staff_page.get_by_role("button", name="Simpan Tindak Lanjut").click()

    expect(staff_page.locator("body")).to_contain_text("berhasil diperbarui.")
    expect(staff_page.locator("body")).to_contain_text("Selesai")

    # 3. Customer reload halaman detail komplain dan memverifikasi solusi tampil
    cust_page.goto(complaint_url)
    expect(cust_page.locator("body")).to_contain_text("Tuntas Selesai")
    expect(cust_page.locator("body")).to_contain_text(
        "Kemeja sudah kami cuci ulang gratis dan noda sudah bersih."
    )


@pytest.mark.complaint
def test_transaksi_dibatalkan_tidak_dapat_dikomplain(
    make_logged_in_page,
    e2e_db: E2eDbFactory,
):
    """Aturan Bisnis Komplain: Transaksi berstatus 'Dibatalkan' tidak muncul sebagai nota yang dapat dikomplain."""
    cust_page, cust_user = make_logged_in_page("customer", name="E2E Pelanggan Batal Komplain")
    e2e_db.create_transaction(
        customer_id=cust_user["customer_id"],
        weight=2.0,
        total_amount=20000.0,
        status="Dibatalkan",
        payment_mode="unpaid",
    )

    cust_page.goto(f"{BASE_URL}/customer/complaints/create")
    expect(cust_page.locator("body")).to_contain_text("Tidak Ada Transaksi yang Dapat Dilaporkan")


@pytest.mark.complaint
def test_anti_idor_customer_b_ditolak_403_pada_komplain_dan_bukti_bayar_customer_a(
    make_logged_in_page,
    e2e_db: E2eDbFactory,
):
    """Keamanan Anti-IDOR (Policy): Customer B wajib mendapat HTTP 403 pada komplain & bukti bayar Customer A."""
    _, cust_a = make_logged_in_page("customer", name="E2E Pemilik Asli A")
    trx_a = e2e_db.create_transaction(
        customer_id=cust_a["customer_id"],
        weight=3.0,
        total_amount=30000.0,
        status="Diproses",
        payment_mode="pending_proof",
    )
    complaint_a = e2e_db.create_complaint(
        transaction_id=trx_a["transaction_id"],
        subject="E2E Rahasia Komplain A",
    )

    cust_b_page, _ = make_logged_in_page("customer", name="E2E Pelanggan Lain B")

    resp_complaint = cust_b_page.goto(f"{BASE_URL}/customer/complaints/{complaint_a['complaint_id']}")
    assert resp_complaint is not None
    assert resp_complaint.status == 403

    resp_proof = cust_b_page.goto(f"{BASE_URL}/customer/payments/{trx_a['payment_id']}/proof")
    assert resp_proof is not None
    assert resp_proof.status == 403
