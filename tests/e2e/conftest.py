import pytest
import requests

"""
CUCI.IN - End-to-End Testing Configuration (Playwright + Pytest)
Praktikum PMPL Pertemuan 9: Automatic E2E Testing
Target Aplikasi: CUCI.IN (http://localhost:8000)
"""

BASE_URL = "http://localhost:8000"

class CuciInApiClient:
    """Helper client untuk interaksi langsung ke backend/API CUCI.IN."""
    def __init__(self, base_url=BASE_URL):
        self.base_url = base_url
        self.session = requests.Session()

    def get(self, endpoint, params=None):
        return self.session.get(f"{self.base_url}{endpoint}", params=params)

    def post(self, endpoint, data=None, json=None):
        return self.session.post(f"{self.base_url}{endpoint}", data=data, json=json)

@pytest.fixture(scope="session")
def cuciin_api():
    return CuciInApiClient()

# ==============================================================================
# FIXTURE PRECONDITION LAB 4: Transaksi Laundry Belum Lunas
# ==============================================================================
@pytest.fixture
def transaksi_belum_lunas():
    """
    Menyiapkan data transaksi laundry 'TRX-TEST-001' dengan total Rp50.000,
    status 'Diterima', belum dibayar.
    """
    # Pada pengujian riil, ini dapat memanggil endpoint testing atau Artisan seeder
    transaksi = {
        "transaction_code": "TRX-TEST-001",
        "customer_name": "Budi Santoso",
        "total_amount": 50000,
        "status": "Diterima"
    }
    yield transaksi

# ==============================================================================
# FIXTURE PRECONDITION LAB 5 & 6: Transaksi Sedang Diproses (Anti-Pembatalan)
# ==============================================================================
@pytest.fixture
def transaksi_sedang_diproses():
    """
    Menyiapkan transaksi 'TRX-TEST-002' dengan status 'Diproses'.
    Aturan Bisnis BR-TRX-02: Transaksi berstatus Diproses DILARANG dibatalkan.
    """
    transaksi = {
        "transaction_code": "TRX-TEST-002",
        "customer_name": "Siti Rahma",
        "total_amount": 35000,
        "status": "Diproses"
    }
    yield transaksi

@pytest.fixture
def transaksi_baru_diterima():
    """
    Menyiapkan transaksi 'TRX-TEST-003' dengan status awal 'Diterima'.
    Aturan Bisnis: Transaksi Diterima DIIZINKAN untuk dibatalkan.
    """
    transaksi = {
        "transaction_code": "TRX-TEST-003",
        "customer_name": "Agus Pratama",
        "total_amount": 20000,
        "status": "Diterima"
    }
    yield transaksi

# ==============================================================================
# FIXTURE LAB 8: Transaksi Selesai untuk Modul Komplain Pelanggan
# ==============================================================================
@pytest.fixture
def transaksi_komplain_valid():
    """
    Menyiapkan transaksi 'TRX-TEST-CMP' berstatus 'Diambil' (Selesai),
    sehingga sah untuk diajukan komplain noda/luntur.
    """
    transaksi = {
        "transaction_code": "TRX-TEST-CMP",
        "customer_name": "Ahmad Fauzi",
        "status": "Diambil"
    }
    yield transaksi
