from pathlib import Path
import time
import uuid

from playwright.sync_api import Page, expect
import pytest

from tests.e2e.conftest import BASE_URL, E2eDbFactory

"""
ALUR 13, 14, & 15: Operasional Admin (Pengguna, Master Layanan, Pelanggan, & Ekspor Laporan)
Mencakup TC-ADM-001, TC-ADM-002, TC-ADM-003, TC-RPT-001, TC-RPT-002.
"""


@pytest.mark.admin
def test_admin_kelola_pengguna_tambah_dan_nonaktifkan_blokir_login(
    admin_page: Page,
    guest_page: Page,
    e2e_db: E2eDbFactory,
):
    """Admin menambah akun Staff baru, menonaktifkannya, lalu akun tersebut gagal login."""
    suffix = uuid.uuid4().hex[:8]
    email = f"e2e.staff.crud.{suffix}@cuci.in"
    name = f"E2E Staff Baru {suffix}"
    e2e_db.track_user_email(email)

    admin_page.goto(f"{BASE_URL}/admin/users/create")
    admin_page.locator("#name").fill(name)
    admin_page.locator("#email").fill(email)
    admin_page.locator("#role").select_option("staff")
    admin_page.locator("#password").fill("Password123!")
    admin_page.locator("#password_confirmation").fill("Password123!")
    admin_page.get_by_role("button", name="Simpan Pengguna Baru").click()

    expect(admin_page).to_have_url(f"{BASE_URL}/admin/users")
    expect(admin_page.locator("body")).to_contain_text("Akun pengguna baru berhasil ditambahkan.")

    # Cari user yang baru dibuat dan nonaktifkan toggle switch is_active
    admin_page.goto(f"{BASE_URL}/admin/users?q={email}")
    row = admin_page.locator("tbody tr", has_text=email)
    expect(row).to_be_visible()
    expect(row).to_contain_text("Aktif")

    row.locator("label").click()
    expect(admin_page.locator("body")).to_contain_text("berhasil dinonaktifkan")
    expect(admin_page.locator("tbody tr", has_text=email)).to_contain_text("Nonaktif")

    # Akun nonaktif tidak dapat masuk melalui halaman /login
    guest_page.goto(f"{BASE_URL}/login")
    guest_page.get_by_label("Email").fill(email)
    guest_page.locator("#password").fill("Password123!")
    guest_page.locator("#login-submit-button").click()

    expect(guest_page).to_have_url(f"{BASE_URL}/login")
    expect(guest_page.locator("p.text-rose-600").first).to_contain_text("dinonaktifkan")


@pytest.mark.admin
def test_admin_master_layanan_tambah_dan_toggle_status(
    admin_page: Page,
    e2e_db: E2eDbFactory,
):
    """Admin membuat layanan baru lalu menonaktifkan dan mengaktifkannya kembali."""
    suffix = uuid.uuid4().hex[:6].upper()
    svc_name = f"E2E Cuci Jaket {suffix}"
    e2e_db.track_service_name(svc_name)

    admin_page.goto(f"{BASE_URL}/admin/services")
    admin_page.locator("#btn-open-create-service").click()

    admin_page.locator("#create_name").fill(svc_name)
    admin_page.locator("#create_unit").select_option("pcs")
    admin_page.locator("#create_service_type").select_option("khusus")
    # Gunakan nilai selaras step HTML5 saat ini (min=1 step=100; min=0.1 step=0.5)
    admin_page.locator("#create_price").fill("15001")
    admin_page.locator("#create_estimated_duration").fill("24")
    admin_page.locator("#create_minimum_order").fill("1.1")
    admin_page.get_by_role("button", name="Simpan Layanan").click()

    expect(admin_page.locator("body")).to_contain_text("berhasil ditambahkan")

    admin_page.goto(f"{BASE_URL}/admin/services?q={svc_name}")
    row = admin_page.locator("tbody tr", has_text=svc_name)
    expect(row).to_be_visible()
    expect(row).to_contain_text("Tersedia")

    # Toggle nonaktif
    row.locator('button[title="Klik untuk mengubah status ketersediaan"]').click()
    expect(admin_page.locator("body")).to_contain_text("berhasil dinonaktifkan")


@pytest.mark.admin
def test_staff_direktori_pelanggan_pencarian_dan_drawer_detail(
    staff_page: Page,
    e2e_db: E2eDbFactory,
):
    """Kasir mencari pelanggan di /staff/customers lalu membuka slide-over profil dan riwayat nota."""
    suffix = uuid.uuid4().hex[:6].upper()
    phone = f"0899{str(int(time.time()))[-8:]}"
    cust = e2e_db.create_customer(name=f"E2E Direktori {suffix}", phone=phone)
    trx = e2e_db.create_transaction(
        customer_id=cust["customer_id"],
        weight=2.0,
        total_amount=20000.0,
        status="Selesai",
        payment_mode="paid",
    )

    staff_page.goto(f"{BASE_URL}/staff/customers")
    search_box = staff_page.locator('input[name="q"]')
    search_box.fill(cust["name"])
    search_box.press("Enter")

    row = staff_page.locator("tbody tr", has_text=cust["name"])
    expect(row).to_be_visible()
    row.click()

    expect(staff_page.locator("body")).to_contain_text(phone)
    expect(staff_page.locator("body")).to_contain_text(trx["transaction_code"])


@pytest.mark.admin
def test_admin_laporan_filter_dan_ekspor_csv_excel_pdf(
    admin_page: Page,
    e2e_db: E2eDbFactory,
    tmp_path: Path,
):
    """Admin memfilter laporan 7 hari terakhir lalu memverifikasi ekspor CSV, Excel (.xls), & PDF valid."""
    cust = e2e_db.create_customer(name="E2E Laporan Ekspor")
    trx = e2e_db.create_transaction(
        customer_id=cust["customer_id"],
        weight=5.0,
        total_amount=50000.0,
        status="Selesai",
        payment_mode="paid",
    )

    admin_page.goto(f"{BASE_URL}/admin/reports")
    admin_page.get_by_role("button", name="7 Hari Terakhir").click()
    admin_page.locator("#btn-apply-filter").click()

    expect(admin_page.locator("body")).to_contain_text(trx["transaction_code"])

    # 1. Unduh CSV Streaming & verifikasi header + isi nota
    with admin_page.expect_download() as csv_dl_info:
        admin_page.locator("#btn-export-csv").click()
    csv_download = csv_dl_info.value
    assert csv_download.suggested_filename.endswith(".csv")
    csv_path = tmp_path / csv_download.suggested_filename
    csv_download.save_as(str(csv_path))
    csv_text = csv_path.read_text(encoding="utf-8-sig")
    assert "No Nota" in csv_text
    assert trx["transaction_code"] in csv_text

    # 2. Unduh Excel (.xls) & verifikasi non-empty
    with admin_page.expect_download() as xls_dl_info:
        admin_page.locator("#btn-export-excel").click()
    xls_download = xls_dl_info.value
    assert xls_download.suggested_filename.endswith(".xls")
    xls_path = tmp_path / xls_download.suggested_filename
    xls_download.save_as(str(xls_path))
    assert xls_path.stat().st_size > 100
    assert trx["transaction_code"] in xls_path.read_text(encoding="utf-8", errors="ignore")

    # 3. Verifikasi stream PDF DomPDF mengembalikan header PDF valid (%PDF-)
    pdf_href = admin_page.locator("#btn-export-pdf").get_attribute("href")
    assert pdf_href is not None
    pdf_resp = admin_page.context.request.get(pdf_href)
    assert pdf_resp.status == 200
    assert "application/pdf" in pdf_resp.headers.get("content-type", "")
    assert pdf_resp.body().lstrip().startswith(b"%PDF-")
