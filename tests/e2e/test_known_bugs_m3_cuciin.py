from pathlib import Path
import time
import uuid

from playwright.sync_api import Page, expect
import pytest

from tests.e2e.conftest import BASE_URL, E2eDbFactory

"""
REGRESI BUG TERBUKA M3 (xfail Guard): BUG-03, BUG-05, BUG-06, BUG-07, BUG-08, BUG-11, BUG-12
Sumber: docs/qa_bug_tracker_minggu_13.md (BUG-09 sudah fixed & dipromosikan ke test_landing_and_lookup_cuciin.py)

Seluruh test di berkas ini bertanda @pytest.mark.bug_open dan @pytest.mark.xfail(strict=False).
Selama bug belum diperbaiki hasilnya XFAIL; saat PR perbaikan di-merge, test otomatis naik ke XPASS
dan siap dipromosikan ke suite regresi wajib tanpa flag xfail.
"""


@pytest.mark.bug_open
@pytest.mark.xfail(
    strict=False,
    reason="BUG-03 (Reopened): Foto komplain masih disimpan di disk public tanpa Gate::authorize().",
)
def test_bug03_foto_komplain_tidak_boleh_bisa_diakses_guest_secara_publik(
    guest_page: Page,
    make_logged_in_page,
    e2e_db: E2eDbFactory,
    sample_png_path: Path,
):
    """BUG-03: URL foto komplain seharusnya privat (403/302 untuk guest), bukan terbuka 200 di /storage/."""
    cust_page, cust_user = make_logged_in_page("customer", name="E2E Bug03 Owner")
    trx = e2e_db.create_transaction(
        customer_id=cust_user["customer_id"],
        weight=2.0,
        total_amount=20000.0,
        status="Diproses",
        payment_mode="paid",
    )

    cust_page.goto(f"{BASE_URL}/customer/complaints/create?transaction_code={trx['transaction_code']}")
    cust_page.locator("#subject").fill("E2E Uji Privasi Foto Bug 03")
    cust_page.locator("#description").fill("Memastikan foto kendala tidak dapat dibuka tamu tanpa autentikasi.")
    cust_page.locator("#photo").set_input_files(str(sample_png_path))
    cust_page.get_by_role("button", name="Kirim Pengaduan").click()

    photo_href = cust_page.locator('img[alt^="Foto Bukti Komplain"]').get_attribute("src")
    assert photo_href is not None
    assert "/storage/" not in photo_href, (
        "Foto komplain masih diekspos lewat URL disk publik /storage/."
    )
    guest_resp = guest_page.context.request.get(photo_href, max_redirects=0)
    assert guest_resp.status in (302, 401, 403)


@pytest.mark.bug_open
@pytest.mark.xfail(
    strict=False,
    reason="BUG-05: Header transaksi lunas masih memunculkan tombol Tandai Diambil saat status Diterima.",
)
def test_bug05_transaksi_lunas_diterima_tidak_boleh_langsung_tampilkan_tombol_tandai_diambil(
    staff_page: Page,
    e2e_db: E2eDbFactory,
):
    """BUG-05: Status 'Diterima' (lunas) harus memprioritaskan Diproses sebelum Tandai Diambil."""
    trx = e2e_db.create_transaction(
        weight=3.0,
        total_amount=30000.0,
        status="Diterima",
        payment_mode="paid",
    )

    staff_page.goto(f"{BASE_URL}/staff/transactions/{trx['transaction_id']}")
    expect(staff_page.get_by_role("button", name="Tandai Diambil")).to_be_hidden()


@pytest.mark.bug_open
@pytest.mark.xfail(
    strict=False,
    reason="BUG-06: RegisterRequest masih meloloskan nomor HP 4 digit ('0681').",
)
def test_bug06_registrasi_menolak_nomor_telepon_4_digit(
    guest_page: Page,
    e2e_db: E2eDbFactory,
):
    """BUG-06: Input nomor telepon 4 digit harus ditolak validasi pada /register."""
    email = f"e2e.bug06.{uuid.uuid4().hex[:6]}@cuci.in"
    e2e_db.track_user_email(email)

    guest_page.goto(f"{BASE_URL}/register")
    guest_page.get_by_label("Nama Lengkap").fill("E2E Bug 06")
    guest_page.get_by_label("Nomor WhatsApp / HP").fill("0681")
    guest_page.get_by_label("Email").fill(email)
    guest_page.locator("#password").fill("Password123!")
    guest_page.locator("#password_confirmation").fill("Password123!")

    time.sleep(2.1)
    guest_page.locator("#register-submit-button").click()

    expect(guest_page).to_have_url(f"{BASE_URL}/register")


@pytest.mark.bug_open
@pytest.mark.xfail(
    strict=False,
    reason="BUG-07: Modal layanan memakai min='1' step='100' & min='0.1' step='0.5' sehingga 10000 & 26 mismatch.",
)
def test_bug07_form_layanan_menerima_harga_10000_dan_minimum_order_26(admin_page: Page):
    """BUG-07: Input tarif 10000 dan order minimum 26 harus lolos HTML5 checkValidity()."""
    admin_page.goto(f"{BASE_URL}/admin/services")
    admin_page.locator("#btn-open-create-service").click()

    admin_page.locator("#create_name").fill("E2E Uji Step 10000")
    admin_page.locator("#create_price").fill("10000")
    admin_page.locator("#create_estimated_duration").fill("24")
    admin_page.locator("#create_minimum_order").fill("26")

    price_valid = admin_page.locator("#create_price").evaluate("el => el.checkValidity()")
    min_order_valid = admin_page.locator("#create_minimum_order").evaluate("el => el.checkValidity()")
    assert price_valid is True
    assert min_order_valid is True


@pytest.mark.bug_open
@pytest.mark.xfail(
    strict=False,
    reason="BUG-08: TransactionCalculator & POS mengabaikan minimum_order layanan.",
)
def test_bug08_pos_menerapkan_batas_minimum_order_saat_berat_di_bawah_minimum(
    staff_page: Page,
    e2e_db: E2eDbFactory,
):
    """BUG-08: Berat 1 kg pada layanan min 3 kg (Rp 10.000/kg) seharusnya tertagih Rp 30.000."""
    svc = e2e_db.create_service(
        name=f"E2E Min3 {uuid.uuid4().hex[:5]}",
        price=10000,
        minimum_order=3.0,
        estimated_duration=24,
    )
    cust = e2e_db.create_customer(name="E2E Pelanggan MinOrder")

    staff_page.goto(f"{BASE_URL}/staff/transactions/create")
    staff_page.locator("#customer_id").select_option(str(cust["customer_id"]))
    staff_page.locator("#service_id").select_option(str(svc["service_id"]))
    staff_page.locator("#weight").fill("1")
    staff_page.locator("#btn-submit-transaction").click()

    expect(staff_page.locator("body")).to_contain_text("Rp 30.000")


@pytest.mark.bug_open
@pytest.mark.xfail(
    strict=False,
    reason="BUG-11: RecordPayment.php auto-set Terverifikasi untuk non-tunai POS tanpa antrean verifikasi.",
)
def test_bug11_pembayaran_non_tunai_pos_dapat_masuk_antrean_verifikasi(
    staff_page: Page,
    e2e_db: E2eDbFactory,
    active_services: list[dict],
    sample_png_path: Path,
):
    """BUG-11: Pembayaran Transfer di POS dengan opsi verifikasi tertunda muncul di tab Menunggu Verifikasi."""
    cust = e2e_db.create_customer(name="E2E POS NonTunai Bug11")
    svc = active_services[0]

    staff_page.goto(f"{BASE_URL}/staff/transactions/create")
    staff_page.locator("#customer_id").select_option(str(cust["customer_id"]))
    staff_page.locator("#service_id").select_option(str(svc["id"]))
    staff_page.locator("#weight").fill("2")
    staff_page.get_by_role("button", name="Bayar Lunas").click()
    staff_page.locator('input[name="payment_method"][value="Transfer"]').check(force=True)
    staff_page.locator("#payment-proof").set_input_files(str(sample_png_path))
    staff_page.locator("#btn-submit-transaction").click()

    trx_code = staff_page.locator("p.font-mono.tabular-nums").first.inner_text().strip()
    e2e_db.track_transaction_code(trx_code)

    staff_page.goto(f"{BASE_URL}/staff/payments?status=Menunggu+Verifikasi&search={trx_code}")
    expect(staff_page.locator("tbody tr", has_text=trx_code)).to_be_visible()


@pytest.mark.bug_open
@pytest.mark.xfail(
    strict=False,
    reason="BUG-12: Kalkulator estimasi landing page masih hardcoded array layanan statis.",
)
def test_bug12_kalkulator_landing_page_sinkron_dengan_layanan_database(
    guest_page: Page,
    e2e_db: E2eDbFactory,
):
    """BUG-12: Layanan baru yang ditambahkan ke database seharusnya muncul di kalkulator #estimasi."""
    svc = e2e_db.create_service(
        name=f"E2E Paket Dinamis {uuid.uuid4().hex[:4]}",
        price=12500,
        is_active=True,
    )

    guest_page.goto(f"{BASE_URL}/")
    expect(guest_page.locator("#estimasi")).to_contain_text(svc["name"])
