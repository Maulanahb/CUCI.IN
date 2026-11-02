from playwright.sync_api import Page, expect

"""
LAB 1: Test End-to-End Pertama pada CUCI.IN
Anggota Bertanggung Jawab: Daysyani Sophi Masayu

Menguji alur otentikasi staf/admin pada portal operasional laundry:
1. Alur Positif: Login dengan kredensial staff valid -> redirect ke /staff/dashboard
2. Alur Negatif: Login dengan kata sandi salah -> muncul pesan error validasi Laravel
   dan browser tetap berada di halaman /login.
"""

def test_login_staff_berhasil(page: Page):
    """
    Uji Positif: Staf login berhasil dan masuk ke portal dashboard operasional.
    """
    page.goto("http://localhost:8000/login")

    page.get_by_label("Email").fill("staff@cuci.in")
    # Gunakan selector spesifik role textbox agar tidak bentrok dengan tombol toggle mata password
    page.get_by_role("textbox", name="Password").fill("password")
    page.get_by_role("button", name="Masuk").click()

    # Assert: Berhasil redirect ke dashboard staff
    expect(page).to_have_url("http://localhost:8000/staff/dashboard")


def test_login_gagal_kata_sandi_salah(page: Page):
    """
    Uji Negatif Lab 1: Kata sandi salah harus menampilkan error dan URL tetap di /login.
    """
    page.goto("http://localhost:8000/login")

    page.get_by_label("Email").fill("staff@cuci.in")
    page.get_by_role("textbox", name="Password").fill("PasswordSalah123!")
    page.get_by_role("button", name="Masuk").click()

    # Assert Lapisan 1: Pesan galat validasi Laravel tampil
    pesan_error = page.locator("p.text-red-600")
    expect(pesan_error.first).to_be_visible()

    # Assert Lapisan 2: Halaman browser tidak berpindah
    expect(page).to_have_url("http://localhost:8000/login")
