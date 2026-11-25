import time
import uuid

import pytest
from playwright.sync_api import Page, expect

from tests.e2e.conftest import BASE_URL, E2eDbFactory

"""
ALUR 3 & 4: Pengujian Registrasi Pelanggan & Matriks RBAC Multi-Role
Mencakup TC-AUT-004, TC-RBAC-001, dan TC-RBAC-002.
"""


@pytest.mark.auth
def test_registrasi_customer_baru_berhasil(guest_page: Page, e2e_db: E2eDbFactory):
    """Alur Positif Registrasi: Pelanggan mendaftar via /register dan langsung masuk dashboard customer."""
    suffix = uuid.uuid4().hex[:8]
    email = f"e2e.reg.{suffix}@cuci.in"
    phone = f"0812{str(int(time.time()))[-8:]}"
    e2e_db.track_user_email(email)

    guest_page.goto(f"{BASE_URL}/register")
    guest_page.get_by_label("Nama Lengkap").fill(f"E2E Pendaftar {suffix}")
    guest_page.get_by_label("Nomor WhatsApp / HP").fill(phone)
    guest_page.get_by_label("Email").fill(email)
    guest_page.locator("#password").fill("PasswordKuat123!")
    guest_page.locator("#password_confirmation").fill("PasswordKuat123!")

    # RegisterRequest menerapkan proteksi anti-bot minimal 2 detik dari render form (_hp_timestamp)
    time.sleep(2.1)
    guest_page.locator("#register-submit-button").click()

    expect(guest_page).to_have_url(f"{BASE_URL}/customer/dashboard")
    expect(
        guest_page.get_by_role("heading", name=f"Selamat Datang, E2E Pendaftar {suffix}")
    ).to_be_visible()


@pytest.mark.auth
def test_guest_diarahkan_ke_login_pada_rute_terproteksi(guest_page: Page):
    """Matriks RBAC Guest: Mengakses rute staf/admin/customer tanpa sesi dialihkan ke /login."""
    for protected_path in [
        "/staff/transactions",
        "/staff/payments",
        "/admin/users",
        "/customer/dashboard",
    ]:
        guest_page.goto(f"{BASE_URL}{protected_path}")
        expect(guest_page).to_have_url(f"{BASE_URL}/login")


@pytest.mark.auth
def test_staff_ditolak_403_pada_manajemen_pengguna_admin(staff_page: Page):
    """Matriks RBAC Staff: Staf/kasir mendapat HTTP 403 saat membuka /admin/users."""
    response = staff_page.goto(f"{BASE_URL}/admin/users")
    assert response is not None
    assert response.status == 403


@pytest.mark.auth
def test_customer_ditolak_403_pada_area_staff_dan_admin(customer_page: Page):
    """Matriks RBAC Customer: Pelanggan mendapat HTTP 403 di rute operasional staf dan admin."""
    for forbidden_path in [
        "/staff/transactions",
        "/staff/payments",
        "/staff/complaints",
        "/admin/users",
        "/admin/services",
    ]:
        response = customer_page.goto(f"{BASE_URL}{forbidden_path}")
        assert response is not None
        assert response.status == 403
