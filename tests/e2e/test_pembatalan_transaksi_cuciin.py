from playwright.sync_api import Page, expect
import pytest

from tests.e2e.conftest import BASE_URL, E2eDbFactory, accept_next_dialog

"""
LAB 5 & LAB 6 + ALUR 10: Siklus Status Cucian, Rollback Beralasan, & Pembatalan
Anggota Bertanggung Jawab: Muh. Zaky Dawamul Busro

Menguji:
1. Siklus status normal bertahap: Diterima -> Diproses -> Selesai -> Diambil + audit trail & imutabilitas terminal.
2. Rollback status (Diproses -> Diterima) wajib menyertakan alasan (#notes) dan tercatat di audit trail.
3. Pembatalan valid pada status Diterima dengan alasan wajib -> status 'Dibatalkan' persisten pasca-reload.
4. Proteksi validasi pembatalan tanpa alasan dan penolakan mutasi pada status terminal.
"""


@pytest.mark.smoke
@pytest.mark.lifecycle
def test_siklus_status_normal_hingga_diambil_dan_terkunci_terminal(
    staff_page: Page,
    e2e_db: E2eDbFactory,
):
    """Siklus penuh Diterima -> Diproses -> Selesai -> Diambil menghasilkan status terminal terkunci."""
    trx = e2e_db.create_transaction(
        weight=4.0,
        total_amount=40000.0,
        status="Diterima",
        payment_mode="paid",
    )
    detail_url = f"{BASE_URL}/staff/transactions/{trx['transaction_id']}"
    staff_page.goto(detail_url)

    # 1. Diterima -> Diproses
    accept_next_dialog(staff_page, "Diproses")
    staff_page.get_by_role("button", name="Majukan ke Diproses").click()
    expect(staff_page.locator("body")).to_contain_text("Status transaksi berhasil diperbarui.")
    expect(staff_page.locator("body")).to_contain_text("Diproses")

    # 2. Diproses -> Selesai
    accept_next_dialog(staff_page, "Selesai")
    staff_page.get_by_role("button", name="Majukan ke Selesai").click()
    expect(staff_page.locator("body")).to_contain_text("Selesai")

    # 3. Selesai -> Diambil
    accept_next_dialog(staff_page, "Diambil")
    staff_page.get_by_role("button", name="Majukan ke Diambil").click()
    expect(staff_page.locator("body")).to_contain_text("Transaksi Telah Selesai & Cucian Diambil")
    expect(staff_page.locator("#status")).to_be_hidden()

    # 4. Verifikasi audit trail mencatat riwayat transisi
    staff_page.locator("details summary", has_text="Riwayat Pembaruan Status").click()
    expect(staff_page.locator("details table")).to_contain_text("Diambil")

    # 5. Verifikasi server menolak mutasi ulang status terminal 'Diambil'
    csrf = staff_page.locator('meta[name="csrf-token"]').get_attribute("content")
    assert csrf is not None
    resp = staff_page.context.request.post(
        f"{BASE_URL}/staff/transactions/{trx['transaction_id']}/status",
        headers={"Accept": "application/json", "X-CSRF-TOKEN": csrf},
        form={"_method": "PATCH", "status": "Diproses", "notes": "Coba ubah paksa status terminal"},
    )
    assert resp.status == 422
    assert "tidak dapat diubah lagi" in resp.text()


@pytest.mark.lifecycle
def test_rollback_status_wajib_alasan_dan_tercatat_audit_trail(
    staff_page: Page,
    transaksi_sedang_diproses: dict,
):
    """Memundurkan status dari Diproses ke Diterima wajib mengisi #notes dan tercatat di audit trail."""
    detail_url = f"{BASE_URL}/staff/transactions/{transaksi_sedang_diproses['transaction_id']}"
    staff_page.goto(detail_url)

    staff_page.locator("#status").select_option("Diterima")
    notes_field = staff_page.locator("#notes")
    expect(notes_field).to_be_visible()

    # Validasi HTML5 mewajibkan alasan saat rollback
    assert notes_field.evaluate("el => el.checkValidity()") is False

    alasan = "Perlu pencucian ulang karena noda belum tuntas"
    notes_field.fill(alasan)
    assert notes_field.evaluate("el => el.checkValidity()") is True

    staff_page.get_by_role("button", name="Perbarui Status").click()
    expect(staff_page.locator("body")).to_contain_text("Status transaksi berhasil diperbarui.")

    staff_page.reload()
    staff_page.locator("details summary", has_text="Riwayat Pembaruan Status").click()
    expect(staff_page.locator("details table")).to_contain_text(alasan)


@pytest.mark.lifecycle
def test_pembatalan_transaksi_diterima_berhasil(
    staff_page: Page,
    transaksi_baru_diterima: dict,
):
    """Pembatalan transaksi berstatus Diterima dengan alasan wajib berhasil dan mengunci status."""
    detail_url = f"{BASE_URL}/staff/transactions/{transaksi_baru_diterima['transaction_id']}"
    staff_page.goto(detail_url)

    staff_page.get_by_role("button", name="Batalkan transaksi").click()
    cancel_notes = staff_page.locator("#cancel-notes")
    expect(cancel_notes).to_be_visible()

    # Tanpa alasan pembatalan, form native browser menolak submit
    assert cancel_notes.evaluate("el => el.checkValidity()") is False

    alasan_batal = "Pelanggan membatalkan pesanan sebelum proses pencucian"
    cancel_notes.fill(alasan_batal)
    staff_page.get_by_role("button", name="Ya, Batalkan Transaksi").click()

    expect(staff_page.locator("body")).to_contain_text("Transaksi Dibatalkan")
    expect(staff_page.locator("#status")).to_be_hidden()

    # Reload memastikan pembatalan persisten dan alasan terekam di riwayat
    staff_page.reload()
    expect(staff_page.locator("body")).to_contain_text("Transaksi Dibatalkan")
    staff_page.locator("details summary", has_text="Riwayat Pembaruan Status").click()
    expect(staff_page.locator("details table")).to_contain_text(alasan_batal)


@pytest.mark.lifecycle
def test_pembatalan_tanpa_alasan_ditolak_validasi_server(
    staff_page: Page,
    transaksi_sedang_diproses: dict,
):
    """Server wajib menolak permintaan pembatalan apabila notes/alasan pembatalan dikosongkan."""
    detail_url = f"{BASE_URL}/staff/transactions/{transaksi_sedang_diproses['transaction_id']}"
    staff_page.goto(detail_url)

    csrf = staff_page.locator('meta[name="csrf-token"]').get_attribute("content")
    assert csrf is not None
    resp = staff_page.context.request.post(
        f"{BASE_URL}/staff/transactions/{transaksi_sedang_diproses['transaction_id']}/status",
        headers={"Accept": "application/json", "X-CSRF-TOKEN": csrf},
        form={"_method": "PATCH", "status": "Dibatalkan", "notes": ""},
    )
    assert resp.status == 422
    assert "Alasan pembatalan wajib diisi" in resp.text()
