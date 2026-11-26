import pytest
from playwright.sync_api import Page, expect

from tests.e2e.conftest import BASE_URL, login_as

"""
LAB 1 + ALUR 3: Pengujian Autentikasi Login & Logout CUCI.IN
Anggota Bertanggung Jawab: Daysyani Sophi Masayu

Menguji alur otentikasi seluruh peran dan penolakan kredensial/status akun:
1. Login Staff valid -> /staff/dashboard, lalu Logout -> /login
2. Login gagal karena kata sandi salah -> pesan error validasi tampil & URL tetap /login
3. Login Admin valid -> /admin/dashboard
4. Login Customer valid -> /customer/dashboard
5. Login akun nonaktif (nonaktif@cuci.in) -> ditolak masuk
"""


@pytest.mark.smoke
@pytest.mark.auth
def test_login_staff_berhasil(guest_page: Page):
    """Uji Positif: Staf login berhasil ke /staff/dashboard dan dapat logout dengan bersih."""
    login_as(guest_page, "staff@cuci.in", "password")
    expect(guest_page).to_have_url(f"{BASE_URL}/staff/dashboard")

    # Logout via sidebar/form POST /logout
    guest_page.locator("#logout-button").click()
    expect(guest_page).to_have_url(f"{BASE_URL}/login")
    expect(guest_page.locator("body")).to_contain_text("keluar dari sistem")


@pytest.mark.auth
def test_login_gagal_kata_sandi_salah(guest_page: Page):
    """Uji Negatif Lab 1: Kata sandi salah harus menampilkan error validasi dan tetap di /login."""
    guest_page.goto(f"{BASE_URL}/login")

    guest_page.get_by_label("Email").fill("staff@cuci.in")
    guest_page.locator("#password").fill("PasswordSalah123!")
    guest_page.locator("#login-submit-button").click()

    pesan_error = guest_page.locator("p.text-rose-600")
    expect(pesan_error.first).to_be_visible()
    expect(pesan_error.first).to_contain_text("Email atau password yang Anda masukkan salah.")
    expect(guest_page).to_have_url(f"{BASE_URL}/login")


@pytest.mark.auth
def test_login_admin_berhasil(guest_page: Page):
    """Uji Positif: Administrator login berhasil ke /admin/dashboard."""
    login_as(guest_page, "admin@cuci.in", "password")
    expect(guest_page).to_have_url(f"{BASE_URL}/admin/dashboard")


@pytest.mark.auth
def test_login_customer_berhasil(guest_page: Page):
    """Uji Positif: Customer terdaftar login berhasil ke /customer/dashboard."""
    login_as(guest_page, "customer@cuci.in", "password")
    expect(guest_page).to_have_url(f"{BASE_URL}/customer/dashboard")


@pytest.mark.auth
def test_login_akun_nonaktif_ditolak(guest_page: Page):
    """Uji Negatif: Akun staf nonaktif tidak diizinkan masuk ke aplikasi."""
    guest_page.goto(f"{BASE_URL}/login")

    guest_page.get_by_label("Email").fill("nonaktif@cuci.in")
    guest_page.locator("#password").fill("password")
    guest_page.locator("#login-submit-button").click()

    expect(guest_page).to_have_url(f"{BASE_URL}/login")
    expect(guest_page.locator("p.text-rose-600").first).to_contain_text("dinonaktifkan")
