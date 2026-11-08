# Pemodelan Diagram Rekayasa Sistem CUCI.IN (Milestone 2)

**Sistem Informasi Manajemen Operasional Laundry Berbasis Web (Full Laravel MPA)**  
*D4 Sistem Informasi Bisnis — Politeknik Negeri Malang*  
*Penyusun: Gargarina Nanda Iswati / Ayin (`ayinanda`)*

---

Dokumen ini memuat rekonstruksi diagram alur rekayasa perangkat lunak (*Activity Diagram* dan *Sequence Diagram*) yang selaras dengan arsitektur kode program aktual (*reverse engineering*) sistem **CUCI.IN** untuk pemenuhan deliverable Milestone 2 (Minggu 9–12) dan materi Pertemuan 9–10 Pemrograman Web Lanjut (PWL).

---

## 1. Activity Diagrams (AD)

### AD-01: Alur Pembuatan Transaksi Kasir POS (Multi-Layanan & SOP Kilat)
Diagram ini menggambarkan alur kerja kasir dalam membuat pesanan laundry multi-layanan, integrasi indikator paket kilat (*express*), dan penyimpanan nota dengan proteksi idempotency.

```mermaid
flowchart TD
    Start(["Mulai"]) --> OpenPOS["Kasir Membuka Halaman POS (/staff/pos)"]
    OpenPOS --> SelectCustomer{"Pelanggan Sudah Terdaftar?"}
    
    SelectCustomer -- "Ya" --> PickCustomer["Pilih Pelanggan dari Daftar Dropdown"]
    SelectCustomer -- "Tidak" --> QuickCreateCustomer["Klik Alt+P: Tambah Data Pelanggan Baru via Modal"]
    QuickCreateCustomer --> PickCustomer
    
    PickCustomer --> AddServiceRow["Tambah Baris Layanan (Klik Alt+T)"]
    AddServiceRow --> SelectService["Pilih Layanan (Reguler / Kiloan / Satuan / Kilat)"]
    SelectService --> InputWeight["Input Berat Cucian (kg) atau Jumlah Satuan (pcs)"]
    
    InputWeight --> CheckExpress{"Layanan Mengandung Paket Kilat (Express)?"}
    CheckExpress -- "Ya" --> ShowExpressAlert["Tampilkan Banner SOP Express: Pasang Pita/Label Fisik Kilat"]
    CheckExpress -- "Tidak" --> CalcSubtotal["Hitung Subtotal Item"]
    ShowExpressAlert --> CalcSubtotal
    
    CalcSubtotal --> MoreItems{"Ada Layanan Tambahan Lain?"}
    MoreItems -- "Ya" --> AddServiceRow
    MoreItems -- "Tidak" --> CalcTotal["Hitung Grand Total & Estimasi Selesai (Alpine.js)"]
    
    CalcTotal --> SubmitPOS["Kasir Menekan Simpan Transaksi (Ctrl+Enter)"]
    SubmitPOS --> GenIdempotency["Alpine.js Membuat UUID Idempotency-Key & Disable Tombol"]
    GenIdempotency --> SendRequest["Kirim HTTP POST ke /staff/pos"]
    
    SendRequest --> ValidateBackend{"Validasi Backend & Idempotency Check"}
    ValidateBackend -- "Gagal / Duplikat" --> ReturnError["Tampilkan Pesan Kesalahan / Cegah Transaksi Ganda"]
    ReturnError --> CalcTotal
    
    ValidateBackend -- "Lolos" --> SaveDB["Database Menyimpan Header Transaksi, Detail Items, & Status Diterima"]
    SaveDB --> ShowReceipt["Tampilkan Nota Transaksi & Opsi Cetak Struk Termal (58mm/80mm)"]
    ShowReceipt --> End(["Selesai"])
```

---

### AD-02: Alur Pelacakan Cucian Publik & Pembayaran Digital
Diagram ini memetakan alur pelanggan umum/tamu melacak status pesanan secara mandiri tanpa login, mengunggah bukti bayar transfer/QRIS, hingga kasir melakukan verifikasi persetujuan.

```mermaid
flowchart TD
    Start(["Mulai"]) --> OpenLookup["Pelanggan Buka Halaman Pelacakan Publik (/lookup)"]
    OpenLookup --> InputTrxCode["Input Nomor Nota Transaksi (TRX-XXXX) / Scan Barcode"]
    InputTrxCode --> SearchTrx["Kirim Permintaan Pencarian"]
    
    SearchTrx --> CheckFound{"Data Transaksi Ditemukan?"}
    CheckFound -- "Tidak" --> ShowNotFound["Tampilkan Peringatan: Nomor Nota Tidak Ditemukan"]
    ShowNotFound --> InputTrxCode
    
    CheckFound -- "Ya" --> DisplayStatus["Tampilkan Status Cucian, Rincian Layanan & Sisa Tagihan"]
    DisplayStatus --> CheckPaid{"Tagihan Sudah Lunas?"}
    
    CheckPaid -- "Ya" --> ViewOnly["Tampilkan Status Lunas (Tanpa Form Pembayaran)"]
    ViewOnly --> End(["Selesai"])
    
    CheckPaid -- "Tidak" --> ChoosePayment["Pelanggan Memilih Opsi Unggah Bukti Bayar"]
    ChoosePayment --> UploadProof["Pilih Berkas Bukti Transfer Bank / Struk QRIS (JPG/PNG maks 2MB)"]
    UploadProof --> SubmitProof["Kirim Form Bukti Pembayaran Digital"]
    
    SubmitProof --> ValidateFile{"Validasi Server (MIME & Ukuran File)"}
    ValidateFile -- "Gagal" --> ShowFileError["Tampilkan Pesan: Format Berkas Tidak Valid"]
    ShowFileError --> UploadProof
    
    ValidateFile -- "Lolos" --> SaveProof["Simpan File ke Disk Privat (payments/proofs) Status: Menunggu Verifikasi"]
    SaveProof --> NotifyCustomer["Tampilkan Notifikasi: Bukti Bayar Terkirim & Menunggu Kasir"]
    
    NotifyCustomer --> CashierReview["Kasir Membuka Panel Verifikasi Pembayaran (/staff/payments)"]
    CashierReview --> OpenDetail["Kasir Memeriksa Pratinjau Gambar Bukti Bayar & Rekening"]
    OpenDetail --> Decision{"Keputusan Kasir?"}
    
    Decision -- "Setujui (Approve)" --> MarkApproved["Ubah Status: Terverifikasi, Kurangi Sisa Tagihan Transaksi"]
    Decision -- "Tolak (Reject)" --> MarkRejected["Ubah Status: Ditolak, Isi Catatan Alasan Penolakan"]
    
    MarkApproved --> FinishReview["Perbarui Riwayat Status Pembayaran Transaksi"]
    MarkRejected --> FinishReview
    FinishReview --> End
```

---

### AD-03: Alur Penanganan Tiket Pengaduan Pelanggan (Complaint Handling)
Diagram ini menggambarkan penanganan keluhan pelanggan (noda pakaian tertinggal, kerusakan, atau komplain waktu pengerjaan) hingga penyelesaian oleh staf operasional.

```mermaid
flowchart TD
    Start(["Mulai"]) --> OpenComplaintForm["Pelanggan Membuka Formulir Komplain via Dashboard atau /lookup"]
    OpenComplaintForm --> FillForm["Mengisi Nomor Nota, Deskripsi Keluhan, & Unggah Foto Bukti Fisik"]
    FillForm --> SubmitComplaint["Kirim Pengajuan Komplain"]
    
    SubmitComplaint --> ValidateComplaint{"Validasi Kepemilikan Nota & Integritas File"}
    ValidateComplaint -- "Gagal" --> RejectForm["Tampilkan Pesan Kesalahan Validasi"]
    RejectForm --> FillForm
    
    ValidateComplaint -- "Lolos" --> CreateTicket["Sistem Membuat Tiket Pengaduan (CMP-XXXX) Status: Menunggu"]
    CreateTicket --> DispatchQueue["Dispatch Antrean Background Job Pemrosesan & Notifikasi Staf"]
    
    DispatchQueue --> StaffOpenTickets["Staf Membuka Menu Pengaduan (/staff/complaints)"]
    StaffOpenTickets --> ReviewPhoto["Staf Meninjau Deskripsi Kendala dan Foto Pakaian"]
    StaffOpenTickets --> ProcessTicket["Ubah Status Tiket: Diproses (Mulai Cuci Ulang / Perbaikan)"]
    
    ProcessTicket --> FinishWork["Pekerjaan Penanganan Selesai Dilakukan"]
    FinishWork --> CloseTicket["Staf Menutup Tiket: Status Selesai Beserta Catatan Solusi"]
    CloseTicket --> CustomerFeedback["Pelanggan Menerima Pembaruan Status pada Dashboard"]
    CustomerFeedback --> End(["Selesai"])
```

---

### AD-04: Alur Mesin Ekspor Laporan Keuangan & Operasional (P9 Memory-Safe)
Diagram ini memetakan mekanisme penarikan laporan operasional dan keuangan berbasis rentang tanggal dengan manajemen memori server ramah RAM 1 GB.

```mermaid
flowchart TD
    Start(["Mulai"]) --> OpenReports["Admin Membuka Halaman Laporan (/admin/reports)"]
    OpenReports --> PickDateRange["Pilih Filter Rentang Tanggal (Dari Tanggal - Sampai Tanggal)"]
    PickDateRange --> ApplyEndOfDay["Backend Mengamankan Batas Akhir Hari dengan ->endOfDay()"]
    
    ApplyEndOfDay --> ChooseFormat{"Pilih Format Unduhan"}
    
    ChooseFormat -- "PDF Eksekutif" --> RunDomPDF["Panggil DomPDF: Render Template A4 Ringkas (resources/views/admin/reports/pdf.blade.php)"]
    RunDomPDF --> StreamPDF["Download Berkas PDF Siap Cetak"]
    
    ChooseFormat -- "Excel (.xlsx)" --> RunExcelQuery["Panggil Laravel Excel (FromQuery) dengan Indeks Komposit"]
    RunExcelQuery --> StreamExcel["Download Berkas Spreadsheet Excel Analitis"]
    
    ChooseFormat -- "CSV Dataset Besar" --> RunCSVStream["Panggil Streaming: response()->streamDownload() + lazyById(500)"]
    RunCSVStream --> StreamCSV["Kirim Potongan Baris Data Bertahap ke Browser (RAM Server Tetap Ringan)"]
    
    StreamPDF --> End(["Selesai"])
    StreamExcel --> End
    StreamCSV --> End
```

---

## 2. Sequence Diagrams (SD)

### SD-01: Transaksi Kasir POS & Pencegahan Double-Submit (Idempotency)
Menggambarkan interaksi antar-komponen saat kasir menyimpan transaksi baru dengan proteksi double-submit UUID.

```mermaid
sequenceDiagram
    autonumber
    actor Kasir as Kasir Outlet
    participant UI as Browser (Blade + Alpine.js)
    participant Middleware as EnsureIdempotency Middleware
    participant Controller as Staff\TransactionController
    participant Service as CreateTransaction Service
    participant DB as Basis Data (PostgreSQL)

    Kasir->>UI: Input layanan, timbang pakaian, klik Simpan Transaksi (Ctrl+Enter)
    UI->>UI: Generate UUID v4 (Idempotency-Key) & disable tombol checkout
    UI->>Middleware: HTTP POST /staff/pos (Header: Idempotency-Key=UUID)
    
    activate Middleware
    Middleware->>DB: Cek apakah Idempotency-Key sudah pernah diproses?
    DB-->>Middleware: Hasil cek (Key belum ada / unik)
    
    alt Idempotency Key Sudah Terdaftar (Duplikat)
        Middleware-->>UI: HTTP 409 Conflict / Kembalikan respon transaksi sebelumnya
    else Idempotency Key Baru
        Middleware->>Controller: Teruskan request (valid)
        activate Controller
        Controller->>Service: execute(StoreTransactionRequest $request)
        activate Service
        
        Service->>DB: BEGIN TRANSACTION
        Service->>DB: INSERT INTO transactions (grand_total, status='Diterima', ...)
        Service->>DB: INSERT INTO transaction_details (service_id, weight, subtotal, ...)
        Service->>DB: INSERT INTO laundry_status_histories (status='Diterima')
        Service->>DB: INSERT INTO idempotency_keys (key=UUID, status='processed')
        Service->>DB: COMMIT TRANSACTION
        
        DB-->>Service: Transaksi Berhasil Disimpan
        Service-->>Controller: Model Transaction instance
        deactivate Service
        
        Controller-->>UI: HTTP 302 Redirect ke /staff/transactions/{id} (Flash: Sukses)
        deactivate Controller
        deactivate Middleware
    end
    
    UI-->>Kasir: Tampilkan rincian nota & dialog cetak struk termal otomatis
```

---

### SD-02: Public Tracking API Endpoint via Eloquent API Resource (P10)
Menggambarkan pemanggilan API publik pelacakan nota cucian oleh sistem eksternal / mobile app dengan proteksi N+1 menggunakan `TransactionResource`.

```mermaid
sequenceDiagram
    autonumber
    actor Client as Klien API / Aplikasi Mobile
    participant Route as routes/api.php
    participant Controller as Api\TrackingController
    participant Model as Eloquent Model (Transaction)
    participant Resource as TransactionResource & DetailResource
    participant DB as Basis Data (PostgreSQL)

    Client->>Route: HTTP GET /api/v1/track/TRX-0001
    Route->>Controller: show("TRX-0001")
    activate Controller
    
    Controller->>Model: query()->with(['customer', 'details.service', 'payments'])->where('transaction_code', ...)->first()
    activate Model
    Model->>DB: SELECT * FROM transactions WHERE UPPER(transaction_code) = 'TRX-0001'
    Model->>DB: SELECT * FROM customers WHERE id IN (...)
    Model->>DB: SELECT * FROM transaction_details WHERE transaction_id IN (...)
    Model->>DB: SELECT * FROM services WHERE id IN (...)
    Model->>DB: SELECT * FROM payments WHERE transaction_id IN (...) ORDER BY id DESC
    DB-->>Model: Data transaksi lengkap (Eager Loaded)
    Model-->>Controller: $transaction (Instance dengan relasi ter-load)
    deactivate Model
    
    alt Transaksi Tidak Ditemukan
        Controller-->>Client: HTTP 404 Not Found {"success": false, "message": "Transaksi tidak ditemukan."}
    else Transaksi Ditemukan
        Controller->>Resource: new TransactionResource($transaction)
        activate Resource
        Resource->>Resource: Transform total_amount, total_paid, remaining_amount ke INTEGER
        Resource->>Resource: Format tanggal created_at & estimated ke ISO 8601
        Resource->>Resource: whenLoaded('details') -> TransactionDetailResource::collection
        Resource-->>Controller: Array data terformat aman & bersih
        deactivate Resource
        
        Controller-->>Client: HTTP 200 OK {"success": true, "data": {...}}
    end
    deactivate Controller
```

---

### SD-03: Alur Unggah & Verifikasi Pembayaran Digital (Kasir & Pelanggan)
Menggambarkan interaksi pengiriman bukti transfer bank / struk QRIS dan proses validasi oleh kasir laundry.

```mermaid
sequenceDiagram
    autonumber
    actor Pelanggan as Pelanggan Umum
    participant GuestUI as Tampilan /lookup
    participant GuestCtrl as GuestPaymentProofController
    participant Storage as File Storage (Private Disk)
    actor Kasir as Kasir Staf
    participant StaffUI as Panel /staff/payments
    participant StaffCtrl as Staff\PaymentController
    participant Service as VerifyPayment Service
    participant DB as Basis Data (PostgreSQL)

    Pelanggan->>GuestUI: Buka /lookup, cari nomor nota, pilih Unggah Bukti Bayar
    Pelanggan->>GuestUI: Pilih file bukti transfer (bukti.png) & input nominal
    GuestUI->>GuestCtrl: HTTP POST /lookup/{code}/payment-proof (Multipart Form)
    activate GuestCtrl
    
    GuestCtrl->>GuestCtrl: Validasi GuestPaymentProofRequest (mimes:jpg,png | max:2048)
    GuestCtrl->>Storage: Storage::disk('local')->putFile('payments/proofs', $file)
    Storage-->>GuestCtrl: Path privat ('payments/proofs/hash.png')
    
    GuestCtrl->>DB: INSERT INTO payments (proof_file, verification_status='Menunggu Verifikasi', ...)
    DB-->>GuestCtrl: Payment ID tersimpan
    GuestCtrl-->>GuestUI: HTTP 302 Redirect (Pesan: Bukti bayar berhasil diunggah)
    deactivate GuestCtrl
    GuestUI-->>Pelanggan: Tampilkan status 'Menunggu Verifikasi Kasir'

    Kasir->>StaffUI: Membuka antarmuka verifikasi pembayaran (/staff/payments)
    StaffUI->>StaffCtrl: HTTP GET /staff/payments
    StaffCtrl->>DB: Ambil daftar payments where verification_status = 'Menunggu Verifikasi'
    DB-->>StaffCtrl: Daftar transaksi pending
    StaffCtrl-->>StaffUI: Render tabel verifikasi kasir
    
    Kasir->>StaffUI: Periksa pratinjau struk & klik tombol 'Setujui Pembayaran'
    StaffUI->>StaffCtrl: HTTP POST /staff/payments/{id}/verify (action='approve')
    activate StaffCtrl
    
    StaffCtrl->>Service: execute(Payment $payment, 'approve', $notes)
    activate Service
    Service->>DB: UPDATE payments SET verification_status='Terverifikasi', paid_at=NOW()
    Service->>DB: Hitung ulang total_paid transaksi & perbarui sisa tagihan
    DB-->>Service: Update berhasil
    Service-->>StaffCtrl: Status pembayaran diperbarui
    deactivate Service
    
    StaffCtrl-->>StaffUI: HTTP 302 Redirect (Pesan: Pembayaran berhasil diverifikasi)
    deactivate StaffCtrl
    StaffUI-->>Kasir: Halaman memuat ulang, sisa tagihan transaksi menjadi lunas
```

---

### SD-04: Ekspor Laporan CSV Streaming Bebas Memory-Leak (Materi P9 PWL)
Menggambarkan penanganan ekspor data puluhan ribu transaksi tanpa membebani kapasitas RAM server AWS.

```mermaid
sequenceDiagram
    autonumber
    actor Admin as Super Administrator
    participant Browser as Web Browser Admin
    participant Controller as Admin\ReportController
    participant DateService as DateRange Service
    participant DB as Basis Data (PostgreSQL Cursor)

    Admin->>Browser: Pilih filter rentang tanggal, klik 'Ekspor CSV Streaming'
    Browser->>Controller: HTTP GET /admin/reports/export/csv?from=2026-10-01&to=2026-10-31
    activate Controller
    
    Controller->>DateService: parseRange($request)
    DateService-->>Controller: [$startOfDay, $endOfDay] (Batas tanggal terjamin aman)
    
    Controller->>Controller: Inisialisasi Keran Aliran: response()->streamDownload()
    Controller-->>Browser: HTTP 200 OK (Transfer-Encoding: chunked, Content-Type: text/csv)
    
    Note over Controller,Browser: Koneksi HTTP terbuka secara streaming
    Controller->>Browser: Kirim Baris Header CSV ("Kode Transaksi,Pelanggan,Total,Status,Tanggal\n")
    
    Controller->>DB: Transaction::whereBetween(...)->lazyById(chunk: 500)
    activate DB
    
    loop Setiap 500 Rekord (Cursor Batch)
        DB-->>Controller: 500 Data Transaksi
        Controller->>Controller: Format baris CSV di memori lokal kecil
        Controller->>Browser: Flush chunk baris data ke browser secara langsung
        Browser->>Browser: Browser menulis baris berkas secara berkala ke hard drive lokal
    end
    deactivate DB
    
    Controller->>Browser: Tutup aliran koneksi HTTP (Stream Complete)
    deactivate Controller
    Browser-->>Admin: Unduhan file laporan_operasional_laundry.csv selesai 100%
```

---

## 3. Matriks Traceability Diagram Terhadap Kode Program

| Kode Diagram | Komponen Tampilan (Blade / Route) | Pengendali (Controller / Service) | Model & Migrasi Terkait |
|:---:|---|---|---|
| **AD-01 / SD-01** | `resources/views/staff/transactions/create.blade.php`<br>`routes/web.php` (`staff/pos`) | `Staff\TransactionController`<br>`EnsureIdempotency`<br>`CreateTransaction` | `Transaction`<br>`TransactionDetail`<br>`IdempotencyKey` |
| **AD-02 / SD-02** | `resources/views/lookup/_content.blade.php`<br>`routes/api.php` (`/v1/track/{trx}`) | `Api\TrackingController`<br>`GuestPaymentProofController` | `TransactionResource`<br>`TransactionDetailResource`<br>`Payment` |
| **AD-03** | `resources/views/staff/complaints/index.blade.php`<br>`resources/views/customer/dashboard.blade.php` | `Staff\ComplaintController`<br>`Customer\ComplaintController` | `Complaint`<br>`ComplaintPolicy`<br>`LaundryStatusHistory` |
| **AD-04 / SD-04** | `resources/views/admin/reports/index.blade.php`<br>`resources/views/admin/reports/pdf.blade.php` | `Admin\ReportController`<br>`TransactionsExport`<br>`DateRange` | `Transaction` (composite indexes: `created_at`, `status`) |

---
*Dokumen ini merupakan bagian dari deliverable resmi Milestone 2 CUCI.IN (SIB D4 Politeknik Negeri Malang).*

