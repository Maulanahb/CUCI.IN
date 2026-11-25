import base64
import json
import os
from pathlib import Path
import struct
import subprocess
import uuid
import zlib

from playwright.sync_api import Browser, BrowserContext, Dialog, Page, expect
import pytest
import requests

"""
CUCI.IN - End-to-End Testing Configuration (Playwright + Pytest)
Milestone 3: Comprehensive 16-Flow QA Suite with Real DB Preconditions & Teardown Cleanup
"""

BASE_URL = os.getenv("E2E_BASE_URL", "http://localhost:8000").rstrip("/")
PROJECT_ROOT = Path(__file__).resolve().parents[2]
DB_BRIDGE = Path(__file__).resolve().parent / "helpers" / "db_bridge.php"
DEFAULT_VIEWPORT = {"width": 1280, "height": 800}


class CuciInApiClient:
    """Helper HTTP client untuk interaksi langsung ke endpoint/API CUCI.IN."""

    def __init__(self, base_url: str = BASE_URL) -> None:
        self.base_url = base_url.rstrip("/")
        self.session = requests.Session()
        self.session.trust_env = False

    def get(self, endpoint: str, params: dict | None = None, headers: dict | None = None) -> requests.Response:
        return self.session.get(
            f"{self.base_url}{endpoint}",
            params=params,
            headers=headers,
            timeout=10,
        )

    def post(
        self,
        endpoint: str,
        data: dict | None = None,
        json_body: dict | None = None,
        headers: dict | None = None,
    ) -> requests.Response:
        return self.session.post(
            f"{self.base_url}{endpoint}",
            data=data,
            json=json_body,
            headers=headers,
            timeout=10,
        )


def call_db_bridge(action: str, payload: dict | None = None) -> dict:
    """Jalankan CLI db_bridge.php untuk membuat/membersihkan data E2E nyata di PostgreSQL."""
    encoded = base64.b64encode(json.dumps(payload or {}).encode("utf-8")).decode("ascii")
    proc = subprocess.run(
        ["php", str(DB_BRIDGE), action, encoded],
        cwd=str(PROJECT_ROOT),
        stdin=subprocess.DEVNULL,
        capture_output=True,
        text=True,
        encoding="utf-8",
        check=False,
    )

    if proc.returncode != 0:
        raise RuntimeError(
            f"db_bridge '{action}' gagal (exit {proc.returncode}): {proc.stderr or proc.stdout}"
        )

    lines = [line.strip() for line in proc.stdout.splitlines() if line.strip()]
    if not lines:
        raise RuntimeError(f"db_bridge '{action}' tidak mengembalikan JSON output.")

    return json.loads(lines[-1])


class E2eDbFactory:
    """Factory data uji nyata dengan pelacakan otomatis agar teardown membersihkan DB."""

    def __init__(self) -> None:
        self.transaction_ids: list[int] = []
        self.transaction_codes: list[str] = []
        self.customer_ids: list[int] = []
        self.user_ids: list[int] = []
        self.user_emails: list[str] = []
        self.service_ids: list[int] = []
        self.service_names: list[str] = []

    def track_transaction_code(self, code: str) -> None:
        if code and code not in self.transaction_codes:
            self.transaction_codes.append(code)

    def track_customer_id(self, customer_id: int) -> None:
        if customer_id and customer_id not in self.customer_ids:
            self.customer_ids.append(int(customer_id))

    def track_user_email(self, email: str) -> None:
        if email and email not in self.user_emails:
            self.user_emails.append(email)

    def track_service_name(self, name: str) -> None:
        if name and name not in self.service_names:
            self.service_names.append(name)

    def create_user(
        self,
        role: str = "customer",
        name: str | None = None,
        email: str | None = None,
        password: str = "password",
        is_active: bool = True,
        with_customer: bool | None = None,
        phone: str | None = None,
    ) -> dict:
        payload: dict = {
            "role": role,
            "password": password,
            "is_active": is_active,
        }
        if name is not None:
            payload["name"] = name
        if email is not None:
            payload["email"] = email
        if with_customer is not None:
            payload["with_customer"] = with_customer
        if phone is not None:
            payload["phone"] = phone

        res = call_db_bridge("create-user", payload)
        self.user_ids.append(int(res["user_id"]))
        self.user_emails.append(res["email"])
        if res.get("customer_id"):
            self.customer_ids.append(int(res["customer_id"]))
        return res

    def create_customer(
        self,
        name: str | None = None,
        phone: str | None = None,
        address: str | None = None,
        with_user: bool = False,
        email: str | None = None,
    ) -> dict:
        payload: dict = {"with_user": with_user}
        if name is not None:
            payload["name"] = name
        if phone is not None:
            payload["phone"] = phone
        if address is not None:
            payload["address"] = address
        if email is not None:
            payload["email"] = email

        res = call_db_bridge("create-customer", payload)
        self.customer_ids.append(int(res["customer_id"]))
        if res.get("user_id"):
            self.user_ids.append(int(res["user_id"]))
        if res.get("email"):
            self.user_emails.append(res["email"])
        return res

    def create_service(
        self,
        name: str | None = None,
        price: float = 10000,
        unit: str = "kg",
        service_type: str = "reguler",
        is_express: bool = False,
        minimum_order: float = 1.0,
        estimated_duration: int = 24,
        is_active: bool = True,
    ) -> dict:
        payload = {
            "price": price,
            "unit": unit,
            "service_type": service_type,
            "is_express": is_express,
            "minimum_order": minimum_order,
            "estimated_duration": estimated_duration,
            "is_active": is_active,
        }
        if name is not None:
            payload["name"] = name

        res = call_db_bridge("create-service", payload)
        self.service_ids.append(int(res["service_id"]))
        self.service_names.append(res["name"])
        return res

    def create_transaction(
        self,
        customer_id: int | None = None,
        service_id: int | None = None,
        weight: float = 5.0,
        total_amount: float | None = None,
        status: str = "Diterima",
        payment_mode: str = "unpaid",
        dp_amount: float | None = None,
        proof_method: str = "Transfer",
    ) -> dict:
        payload: dict = {
            "weight": weight,
            "status": status,
            "payment_mode": payment_mode,
            "proof_method": proof_method,
        }
        if customer_id is not None:
            payload["customer_id"] = customer_id
        if service_id is not None:
            payload["service_id"] = service_id
        if total_amount is not None:
            payload["total_amount"] = total_amount
        if dp_amount is not None:
            payload["dp_amount"] = dp_amount

        res = call_db_bridge("create-transaction", payload)
        self.transaction_ids.append(int(res["transaction_id"]))
        self.transaction_codes.append(res["transaction_code"])
        self.customer_ids.append(int(res["customer_id"]))
        return res

    def create_complaint(
        self,
        transaction_id: int,
        subject: str = "E2E Noda Kemeja Belum Hilang",
        description: str = "Keluhan uji E2E otomatis untuk verifikasi alur komplain.",
        status: str = "Menunggu",
    ) -> dict:
        return call_db_bridge(
            "create-complaint",
            {
                "transaction_id": transaction_id,
                "subject": subject,
                "description": description,
                "status": status,
            },
        )

    def cleanup(self) -> dict:
        return call_db_bridge(
            "cleanup",
            {
                "transaction_ids": self.transaction_ids,
                "transaction_codes": self.transaction_codes,
                "customer_ids": self.customer_ids,
                "user_ids": self.user_ids,
                "user_emails": self.user_emails,
                "service_ids": self.service_ids,
                "service_names": self.service_names,
                "sweep_e2e_prefix": True,
                "sweep_session_actors": False,
            },
        )


def write_valid_png(path: Path, width: int = 240, height: int = 240) -> Path:
    """Buat berkas PNG RGB valid menggunakan stdlib Python (memenuhi syarat min 200x200 px)."""

    def _chunk(tag: bytes, data: bytes) -> bytes:
        crc = zlib.crc32(tag + data) & 0xFFFFFFFF
        return struct.pack(">I", len(data)) + tag + data + struct.pack(">I", crc)

    row = b"\x00" + (b"\x08\x91\xb2" * width)
    raw = row * height
    ihdr = struct.pack(">IIBBBBB", width, height, 8, 2, 0, 0, 0)
    png_bytes = (
        b"\x89PNG\r\n\x1a\n"
        + _chunk(b"IHDR", ihdr)
        + _chunk(b"IDAT", zlib.compress(raw, level=1))
        + _chunk(b"IEND", b"")
    )
    path.write_bytes(png_bytes)
    return path


def login_as(page: Page, email: str, password: str = "password") -> None:
    """Masuk ke CUCI.IN lewat form login standar dengan locator semantik."""
    page.goto(f"{BASE_URL}/login")
    page.get_by_label("Email").fill(email)
    page.locator("#password").fill(password)
    page.locator("#login-submit-button").click()
    expect(page).not_to_have_url(f"{BASE_URL}/login")


def accept_next_dialog(page: Page, expected_substring: str | None = None) -> list[str]:
    """Daftarkan handler satu kali untuk menerima dialog confirm() browser."""
    messages: list[str] = []

    def _on_dialog(dialog: Dialog) -> None:
        messages.append(dialog.message)
        if expected_substring is not None:
            assert expected_substring in dialog.message
        dialog.accept()

    page.once("dialog", _on_dialog)
    return messages


def _capture_storage_state(browser: Browser, email: str, password: str = "password") -> dict:
    context = browser.new_context(viewport=DEFAULT_VIEWPORT)
    page = context.new_page()
    try:
        login_as(page, email, password)
        return context.storage_state()
    finally:
        context.close()


@pytest.fixture(scope="session")
def browser_type_launch_args(browser_type_launch_args: dict) -> dict:
    """Pastikan browser langsung mengakses localhost/127.0.0.1 tanpa terblokir env proxy."""
    existing_args = list(browser_type_launch_args.get("args", []))
    bypass_flag = "--proxy-bypass-list=<-loopback>,127.0.0.1,localhost"
    if bypass_flag not in existing_args:
        existing_args.append(bypass_flag)
    return {**browser_type_launch_args, "args": existing_args}


@pytest.fixture(scope="session", autouse=True)
def ensure_server_and_seed():
    """Pastikan server lokal /up merespons 200 dan data dasar tersedia; sapu sisa E2E saat selesai."""
    session = requests.Session()
    session.trust_env = False
    try:
        resp = session.get(f"{BASE_URL}/up", timeout=5)
    except requests.RequestException as exc:
        pytest.fail(
            f"Server CUCI.IN tidak terjangkau di {BASE_URL}/up ({exc}). "
            "Jalankan 'php artisan serve' sebelum mengeksekusi E2E."
        )

    if resp.status_code != 200:
        pytest.fail(f"Endpoint {BASE_URL}/up mengembalikan status {resp.status_code}.")

    call_db_bridge("cleanup", {"sweep_e2e_prefix": True, "sweep_session_actors": True})
    seed_info = call_db_bridge("ensure-seed")
    yield seed_info
    call_db_bridge("cleanup", {"sweep_e2e_prefix": True, "sweep_session_actors": True})


@pytest.fixture(scope="session")
def e2e_session_actors(ensure_server_and_seed):
    """Sediakan akun E2E sementara per peran untuk storage_state sesi agar aman dari rate limiter login."""
    suffix = uuid.uuid4().hex[:8]
    admin = call_db_bridge(
        "create-user",
        {"role": "admin", "name": "SessionE2E Admin", "email": f"e2e-session.admin.{suffix}@cuci.in"},
    )
    staff = call_db_bridge(
        "create-user",
        {"role": "staff", "name": "SessionE2E Staff", "email": f"e2e-session.staff.{suffix}@cuci.in"},
    )
    customer = call_db_bridge(
        "create-user",
        {
            "role": "customer",
            "name": "SessionE2E Customer",
            "email": f"e2e-session.customer.{suffix}@cuci.in",
            "with_customer": True,
        },
    )

    actors = {
        "admin": admin,
        "staff": staff,
        "customer": customer,
        "services": ensure_server_and_seed["services"],
    }
    yield actors

    call_db_bridge(
        "cleanup",
        {
            "user_ids": [admin["user_id"], staff["user_id"], customer["user_id"]],
            "customer_ids": [customer["customer_id"]] if customer.get("customer_id") else [],
            "sweep_e2e_prefix": True,
            "sweep_session_actors": True,
        },
    )


@pytest.fixture(scope="session")
def admin_storage_state(browser: Browser, e2e_session_actors) -> dict:
    return _capture_storage_state(browser, e2e_session_actors["admin"]["email"])


@pytest.fixture(scope="session")
def staff_storage_state(browser: Browser, e2e_session_actors) -> dict:
    return _capture_storage_state(browser, e2e_session_actors["staff"]["email"])


@pytest.fixture(scope="session")
def customer_storage_state(browser: Browser, e2e_session_actors) -> dict:
    return _capture_storage_state(browser, e2e_session_actors["customer"]["email"])


@pytest.fixture(scope="session")
def cuciin_api() -> CuciInApiClient:
    return CuciInApiClient(BASE_URL)


@pytest.fixture
def e2e_db():
    factory = E2eDbFactory()
    try:
        yield factory
    finally:
        factory.cleanup()


@pytest.fixture
def session_customer(e2e_session_actors) -> dict:
    return e2e_session_actors["customer"]


@pytest.fixture
def active_services(e2e_session_actors) -> list[dict]:
    return e2e_session_actors["services"]


@pytest.fixture
def guest_page(new_context) -> Page:
    context: BrowserContext = new_context(viewport=DEFAULT_VIEWPORT)
    return context.new_page()


@pytest.fixture
def admin_page(new_context, admin_storage_state: dict) -> Page:
    context: BrowserContext = new_context(
        viewport=DEFAULT_VIEWPORT,
        storage_state=admin_storage_state,
    )
    return context.new_page()


@pytest.fixture
def staff_page(new_context, staff_storage_state: dict) -> Page:
    context: BrowserContext = new_context(
        viewport=DEFAULT_VIEWPORT,
        storage_state=staff_storage_state,
    )
    return context.new_page()


@pytest.fixture
def customer_page(new_context, customer_storage_state: dict) -> Page:
    context: BrowserContext = new_context(
        viewport=DEFAULT_VIEWPORT,
        storage_state=customer_storage_state,
    )
    return context.new_page()


@pytest.fixture
def make_logged_in_page(new_context, e2e_db: E2eDbFactory):
    """Buat context browser baru yang login sebagai user E2E terisolasi (ideal untuk skenario Customer A vs B)."""

    def _factory(role: str = "customer", name: str | None = None, with_customer: bool | None = None) -> tuple[Page, dict]:
        user = e2e_db.create_user(role=role, name=name, with_customer=with_customer)
        context: BrowserContext = new_context(viewport=DEFAULT_VIEWPORT)
        page = context.new_page()
        login_as(page, user["email"], "password")
        return page, user

    return _factory


@pytest.fixture
def sample_png_path(tmp_path: Path) -> Path:
    return write_valid_png(tmp_path / "e2e_bukti_240x240.png", width=240, height=240)


@pytest.fixture
def sample_invalid_file_path(tmp_path: Path) -> Path:
    bad_file = tmp_path / "bukan_gambar.txt"
    bad_file.write_text("Konten teks biasa bukan berkas gambar.", encoding="utf-8")
    return bad_file


# ==============================================================================
# FIXTURE DATA TRANSAKSI NYATA (PENGGANTI FIXTURE DICT STATIS SEBELUMNYA)
# ==============================================================================


@pytest.fixture
def transaksi_belum_lunas(e2e_db: E2eDbFactory, session_customer: dict) -> dict:
    """Buat transaksi nyata berstatus 'Diterima', belum dibayar, untuk uji pembayaran kasir."""
    return e2e_db.create_transaction(
        customer_id=session_customer["customer_id"],
        weight=5.0,
        total_amount=50000.0,
        status="Diterima",
        payment_mode="unpaid",
    )


@pytest.fixture
def transaksi_sedang_diproses(e2e_db: E2eDbFactory, session_customer: dict) -> dict:
    """Buat transaksi nyata berstatus 'Diproses' untuk uji rollback beralasan dan larangan pembatalan."""
    return e2e_db.create_transaction(
        customer_id=session_customer["customer_id"],
        weight=3.5,
        total_amount=35000.0,
        status="Diproses",
        payment_mode="unpaid",
    )


@pytest.fixture
def transaksi_baru_diterima(e2e_db: E2eDbFactory, session_customer: dict) -> dict:
    """Buat transaksi nyata berstatus 'Diterima' yang boleh dibatalkan dengan alasan."""
    return e2e_db.create_transaction(
        customer_id=session_customer["customer_id"],
        weight=2.0,
        total_amount=20000.0,
        status="Diterima",
        payment_mode="unpaid",
    )


@pytest.fixture
def transaksi_komplain_valid(e2e_db: E2eDbFactory, session_customer: dict) -> dict:
    """Buat transaksi nyata berstatus 'Diambil' (Lunas) milik session_customer untuk pengajuan komplain."""
    return e2e_db.create_transaction(
        customer_id=session_customer["customer_id"],
        weight=4.0,
        total_amount=40000.0,
        status="Diambil",
        payment_mode="paid",
    )
