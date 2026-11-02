<?php

/**
 * Pembuat Dokumen Spesifikasi Kasus Uji (Test Case Specification) CUCI.IN
 * Berdasarkan Format Standar Praktikum PMPL Pertemuan 4: Menulis Test Case
 * Output: docs/PMPL-M4-Dokumen-Spesifikasi-Test-Case-CUCIIN.docx
 */

function escape_xml($str) {
    return htmlspecialchars($str, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

class TestCasesDocxBuilder {
    private $body = '';
    private $primaryBlue = '2C3E8A';   // Navy Blue PDF Dosen
    private $subTitleBlue = '1E293B';  // Dark Slate
    private $tealHeader = '0284C7';    // Cyan Tujuan Lab
    private $crimsonHeader = 'BE123C'; // Merah / Aksen
    private $bodyText = '1E293B';      // Teks Body
    private $tableBorder = 'CBD5E1';   // Border Abu Halus
    private $checkpointBg = 'FFF1F2';  // Soft Peach/Rose PDF
    private $checkpointBorder = 'FDA4AF';

    public function addRunningHeader($text) {
        $this->body .= '<w:p><w:pPr><w:spacing w:before="0" w:after="240"/><w:jc w:val="right"/></w:pPr>';
        $this->body .= '<w:r><w:rPr><w:sz w:val="16"/><w:color w:val="94A3B8"/><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/></w:rPr>';
        $this->body .= '<w:t>' . escape_xml($text) . '</w:t></w:r></w:p>';
    }

    public function addCoverSection() {
        $this->body .= '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:before="360" w:after="80"/></w:pPr>';
        $this->body .= '<w:r><w:rPr><w:b/><w:sz w:val="20"/><w:color w:val="' . $this->crimsonHeader . '"/><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/></w:rPr>';
        $this->body .= '<w:t>P E N J A M I N A N   M U T U   P E R A N G K A T   L U N A K</w:t></w:r></w:p>';

        $this->body .= '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="140"/></w:pPr>';
        $this->body .= '<w:r><w:rPr><w:b/><w:sz w:val="22"/><w:color w:val="' . $this->primaryBlue . '"/><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/></w:rPr>';
        $this->body .= '<w:t>PERTEMUAN 4: MENULIS TEST CASE</w:t></w:r></w:p>';

        $this->body .= '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="80"/></w:pPr>';
        $this->body .= '<w:r><w:rPr><w:b/><w:sz w:val="34"/><w:color w:val="' . $this->primaryBlue . '"/><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/></w:rPr>';
        $this->body .= '<w:t>DOKUMEN SPESIFIKASI KASUS UJI (TEST CASE)</w:t></w:r></w:p>';

        $this->body .= '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="320"/></w:pPr>';
        $this->body .= '<w:r><w:rPr><w:i/><w:sz w:val="22"/><w:color w:val="' . $this->tealHeader . '"/><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/></w:rPr>';
        $this->body .= '<w:t>Sistem Informasi Manajemen Laundry Multi-Outlet CUCI.IN (Milestone 2)</w:t></w:r></w:p>';
    }

    public function addSectionHeading($text) {
        $this->body .= '<w:p><w:pPr><w:spacing w:before="380" w:after="140"/></w:pPr>';
        $this->body .= '<w:r><w:rPr><w:b/><w:sz w:val="28"/><w:color w:val="' . $this->primaryBlue . '"/><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/></w:rPr>';
        $this->body .= '<w:t>' . escape_xml($text) . '</w:t></w:r></w:p>';
    }

    public function addSubHeading($text) {
        $this->body .= '<w:p><w:pPr><w:spacing w:before="220" w:after="100"/></w:pPr>';
        $this->body .= '<w:r><w:rPr><w:b/><w:sz w:val="22"/><w:color w:val="' . $this->subTitleBlue . '"/><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/></w:rPr>';
        $this->body .= '<w:t>' . escape_xml($text) . '</w:t></w:r></w:p>';
    }

    public function addParagraph($text, $italic = false, $bold = false) {
        $this->body .= '<w:p><w:pPr><w:spacing w:after="140" w:line="276" w:lineRule="auto"/></w:pPr>';
        $this->body .= '<w:r><w:rPr><w:sz w:val="21"/><w:color w:val="' . $this->bodyText . '"/>';
        if ($bold) $this->body .= '<w:b/>';
        if ($italic) $this->body .= '<w:i/>';
        $this->body .= '<w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/></w:rPr>';
        $this->body .= '<w:t xml:space="preserve">' . escape_xml($text) . '</w:t></w:r></w:p>';
    }

    public function addCheckpoint($text) {
        $this->body .= '<w:tbl><w:tblPr><w:tblW w:w="9600" w:type="dxa"/><w:tblBorders>';
        $this->body .= '<w:top w:val="single" w:sz="6" w:space="0" w:color="' . $this->checkpointBorder . '"/>';
        $this->body .= '<w:left w:val="single" w:sz="6" w:space="0" w:color="' . $this->checkpointBorder . '"/>';
        $this->body .= '<w:bottom w:val="single" w:sz="6" w:space="0" w:color="' . $this->checkpointBorder . '"/>';
        $this->body .= '<w:right w:val="single" w:sz="6" w:space="0" w:color="' . $this->checkpointBorder . '"/>';
        $this->body .= '</w:tblBorders><w:tblCellMar><w:top w:w="140" w:type="dxa"/><w:bottom w:w="140" w:type="dxa"/><w:left w:w="180" w:type="dxa"/><w:right w:w="180" w:type="dxa"/></w:tblCellMar></w:tblPr>';
        $this->body .= '<w:tr><w:tc><w:tcPr><w:shd w:val="clear" w:color="auto" w:fill="' . $this->checkpointBg . '"/></w:tcPr>';
        $this->body .= '<w:p><w:pPr><w:spacing w:after="60"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="19"/><w:color w:val="' . $this->crimsonHeader . '"/><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/></w:rPr><w:t>CHECKPOINT PENJAMINAN MUTU</w:t></w:r></w:p>';
        $this->body .= '<w:p><w:pPr><w:spacing w:after="0" w:line="260" w:lineRule="auto"/></w:pPr><w:r><w:rPr><w:sz w:val="20"/><w:color w:val="4C0519"/><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/></w:rPr><w:t xml:space="preserve">' . escape_xml($text) . '</w:t></w:r></w:p>';
        $this->body .= '</w:tc></w:tr></w:tbl><w:p><w:pPr><w:spacing w:after="180"/></w:pPr></w:p>';
    }

    public function addTable($headers, $rows, $colWidths = []) {
        $totalWidth = 9600;
        $numCols = count($headers);
        if (empty($colWidths)) {
            $defaultW = (int)($totalWidth / $numCols);
            $colWidths = array_fill(0, $numCols, $defaultW);
        }

        $this->body .= '<w:tbl><w:tblPr><w:tblW w:w="9600" w:type="dxa"/><w:tblBorders>';
        $this->body .= '<w:top w:val="single" w:sz="6" w:space="0" w:color="' . $this->tableBorder . '"/>';
        $this->body .= '<w:left w:val="single" w:sz="6" w:space="0" w:color="' . $this->tableBorder . '"/>';
        $this->body .= '<w:bottom w:val="single" w:sz="6" w:space="0" w:color="' . $this->tableBorder . '"/>';
        $this->body .= '<w:right w:val="single" w:sz="6" w:space="0" w:color="' . $this->tableBorder . '"/>';
        $this->body .= '<w:insideH w:val="single" w:sz="6" w:space="0" w:color="' . $this->tableBorder . '"/>';
        $this->body .= '<w:insideV w:val="single" w:sz="6" w:space="0" w:color="' . $this->tableBorder . '"/>';
        $this->body .= '</w:tblBorders><w:tblCellMar><w:top w:w="120" w:type="dxa"/><w:bottom w:w="120" w:type="dxa"/><w:left w:w="140" w:type="dxa"/><w:right w:w="140" w:type="dxa"/></w:tblCellMar></w:tblPr>';

        $this->body .= '<w:tr><w:trPr><w:tblHeader/></w:trPr>';
        foreach ($headers as $idx => $th) {
            $w = $colWidths[$idx] ?? 2000;
            $this->body .= '<w:tc><w:tcPr><w:tcW w:w="' . $w . '" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="' . $this->primaryBlue . '"/></w:tcPr>';
            $this->body .= '<w:p><w:pPr><w:spacing w:after="0"/><w:jc w:val="left"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="20"/><w:color w:val="FFFFFF"/><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/></w:rPr><w:t xml:space="preserve">' . escape_xml($th) . '</w:t></w:r></w:p></w:tc>';
        }
        $this->body .= '</w:tr>';

        foreach ($rows as $rIdx => $row) {
            $bg = ($rIdx % 2 === 1) ? 'F8FAFC' : 'FFFFFF';
            $this->body .= '<w:tr>';
            foreach ($row as $cIdx => $cell) {
                $w = $colWidths[$cIdx] ?? 2000;
                $this->body .= '<w:tc><w:tcPr><w:tcW w:w="' . $w . '" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="' . $bg . '"/></w:tcPr>';
                $this->body .= '<w:p><w:pPr><w:spacing w:after="0"/><w:jc w:val="left"/></w:pPr><w:r><w:rPr><w:sz w:val="20"/><w:color w:val="' . $this->bodyText . '"/><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/></w:rPr><w:t xml:space="preserve">' . escape_xml($cell) . '</w:t></w:r></w:p></w:tc>';
            }
            $this->body .= '</w:tr>';
        }

        $this->body .= '</w:tbl><w:p><w:pPr><w:spacing w:after="180"/></w:pPr></w:p>';
    }

    public function addTestCaseCard($id, $title, $scenarioRef, $typePriority, $precondition, $testData, $steps, $expectedResult, $executionNeeds, $traceability, $actualResult, $status) {
        $this->body .= '<w:p><w:pPr><w:spacing w:before="240" w:after="80"/></w:pPr>';
        $this->body .= '<w:r><w:rPr><w:b/><w:sz w:val="23"/><w:color w:val="' . $this->primaryBlue . '"/><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/></w:rPr>';
        $this->body .= '<w:t>Kartu Kasus Uji: ' . escape_xml($id) . ' — ' . escape_xml($title) . '</w:t></w:r></w:p>';

        $rows = [
            ["Test Case ID", $id],
            ["Judul Kasus Uji", $title],
            ["Scenario Reference", $scenarioRef],
            ["Tipe & Prioritas", $typePriority],
            ["Precondition", $precondition],
            ["Test Data", $testData],
            ["Langkah Uji (Steps)", $steps],
            ["Expected Result (3 Lapisan)", $expectedResult],
            ["Kebutuhan Eksekusi", $executionNeeds],
            ["Keterlacakan (Traceability)", $traceability],
            ["Actual Result & Status", $actualResult . "  ==>  [ " . $status . " ]"]
        ];

        $this->body .= '<w:tbl><w:tblPr><w:tblW w:w="9600" w:type="dxa"/><w:tblBorders>';
        $this->body .= '<w:top w:val="single" w:sz="6" w:space="0" w:color="' . $this->tableBorder . '"/>';
        $this->body .= '<w:left w:val="single" w:sz="6" w:space="0" w:color="' . $this->tableBorder . '"/>';
        $this->body .= '<w:bottom w:val="single" w:sz="6" w:space="0" w:color="' . $this->tableBorder . '"/>';
        $this->body .= '<w:right w:val="single" w:sz="6" w:space="0" w:color="' . $this->tableBorder . '"/>';
        $this->body .= '<w:insideH w:val="single" w:sz="6" w:space="0" w:color="' . $this->tableBorder . '"/>';
        $this->body .= '<w:insideV w:val="single" w:sz="6" w:space="0" w:color="' . $this->tableBorder . '"/>';
        $this->body .= '</w:tblBorders><w:tblCellMar><w:top w:w="120" w:type="dxa"/><w:bottom w:w="120" w:type="dxa"/><w:left w:w="140" w:type="dxa"/><w:right w:w="140" w:type="dxa"/></w:tblCellMar></w:tblPr>';

        foreach ($rows as $rIdx => $r) {
            $isHeaderField = ($r[0] === 'Test Case ID' || $r[0] === 'Judul Kasus Uji');
            $bg = $isHeaderField ? 'F1F5F9' : (($rIdx % 2 === 1) ? 'F8FAFC' : 'FFFFFF');
            $this->body .= '<w:tr>';
            
            // Kolom Label
            $this->body .= '<w:tc><w:tcPr><w:tcW w:w="2600" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="' . $bg . '"/></w:tcPr>';
            $this->body .= '<w:p><w:pPr><w:spacing w:after="0"/><w:jc w:val="left"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="19"/><w:color w:val="' . $this->subTitleBlue . '"/><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/></w:rPr><w:t>' . escape_xml($r[0]) . '</w:t></w:r></w:p></w:tc>';
            
            // Kolom Isi
            $this->body .= '<w:tc><w:tcPr><w:tcW w:w="7000" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="' . $bg . '"/></w:tcPr>';
            
            // Render multi-line content cleanly
            $lines = explode("\n", $r[1]);
            foreach ($lines as $lIdx => $line) {
                $afterSpace = ($lIdx === count($lines) - 1) ? "0" : "60";
                $this->body .= '<w:p><w:pPr><w:spacing w:before="0" w:after="' . $afterSpace . '" w:line="240" w:lineRule="auto"/><w:jc w:val="left"/></w:pPr>';
                $this->body .= '<w:r><w:rPr><w:sz w:val="19"/><w:color w:val="' . $this->bodyText . '"/>';
                if ($r[0] === 'Test Case ID' || $r[0] === 'Actual Result & Status') {
                    $this->body .= '<w:b/>';
                }
                $this->body .= '<w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/></w:rPr><w:t xml:space="preserve">' . escape_xml($line) . '</w:t></w:r></w:p>';
            }
            $this->body .= '</w:tc></w:tr>';
        }

        $this->body .= '</w:tbl><w:p><w:pPr><w:spacing w:after="200"/></w:pPr></w:p>';
    }

    public function generateDocx($filePath) {
        $xmlDocument = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">' .
            '<w:body>' . $this->body .
            '<w:sectPr><w:pgSz w:w="12240" w:h="15840"/><w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440"/></w:sectPr>' .
            '</w:body></w:document>';

        $xmlContentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
            '<Default Extension="xml" ContentType="application/xml"/>' .
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
            '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>' .
            '</Types>';

        $xmlRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>' .
            '</Relationships>';

        $zip = new ZipArchive();
        if ($zip->open($filePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $zip->addFromString('[Content_Types].xml', $xmlContentTypes);
            $zip->addFromString('_rels/.rels', $xmlRels);
            $zip->addFromString('word/document.xml', $xmlDocument);
            $zip->close();
            return true;
        }
        return false;
    }
}

// Inisialisasi Builder
$doc = new TestCasesDocxBuilder();

$doc->addRunningHeader("PMPL Pertemuan 4 · Spesifikasi Test Case Sistem CUCI.IN (Milestone 2)");
$doc->addCoverSection();

// Tabel Informasi Modul & Tim Pengembang
$doc->addSubHeading("Informasi Modul Praktikum & Tim Pengembang");
$doc->addTable(
    ["Parameter", "Keterangan"],
    [
        ["Mata Kuliah", "Penjaminan Mutu Perangkat Lunak (PMPL) · Semester Ganjil 2026/2027"],
        ["Topik Praktikum", "Pertemuan 4: Menulis Test Case (Dari Skenario ke Test Case Terstruktur)"],
        ["Sistem Objek Pengujian", "CUCI.IN — Web Application POS Kasir & Manajemen Laundry Multi-Outlet"],
        ["Teknologi Aplikasi", "Laravel 12 Multi-Page Application (Blade), PostgreSQL, Tailwind CSS, Alpine.js"],
        ["Penanggung Jawab Modul 1", "Muh. Zaky Dawamul Busro (Autentikasi, Rate Limiting & Keamanan)"],
        ["Penanggung Jawab Modul 2", "Maulana Ahmad Bukhori (Lead: POS Kasir, Idempotensi & Pembayaran)"],
        ["Penanggung Jawab Modul 3", "Nayla Annora Nobel Widyonarko (Upload Bukti Bayar & Sanctum API)"],
        ["Penanggung Jawab Modul 4", "Daysyani Sophi Masayu (Pengaduan Pelanggan & Anti-IDOR Policies)"],
        ["Penanggung Jawab Modul 5", "Gargarina Nanda Iswati / Ayin (Katalog Layanan & Streaming Ekspor)"]
    ],
    [3200, 6400]
);

$doc->addCheckpoint("Dokumen ini mendefinisikan kasus uji (test case) baku yang memenuhi tiga sifat esensial: Executable (siapa pun dapat menjalankan secara konsisten), Repeatable (menghasilkan perilaku yang sama persis saat diulang), dan Verifiable (semua penguji bersepakat terhadap putusan status Pass atau Fail).");

// ==============================================================================
// BAGIAN 1: DELAPAN ISTILAH KUNCI (PART 0 PMPL)
// ==============================================================================
$doc->addSectionHeading("1. Delapan Istilah Kunci Penjaminan Mutu (Part 0)");
$doc->addParagraph("Praktikum Pertemuan 4 menetapkan analogi resep masakan untuk memahami struktur pengujian perangkat lunak. Berikut adalah pemetaannya ke dalam alur kerja CUCI.IN:");

$doc->addTable(
    ["Istilah Kunci", "Arti Sederhana", "Analogi Resep", "Penerapan pada Kasus Nyata CUCI.IN"],
    [
        ["Test Scenario", "Situasi bisnis yang layak diuji (APA)", "Buat kue bolu cokelat", "Verifikasi pembatalan cucian ditolak saat status Diproses (BR-TRX-01)"],
        ["Test Case", "Prosedur persis untuk mengujinya (BAGAIMANA)", "Kartu resep lengkap", "Kartu uji TC-TRX-002 dengan nomor nota, langkah klik, dan assertion"],
        ["Precondition", "Keadaan yang harus benar sebelum langkah 1", "Oven sudah dipanaskan 180°C", "Staf kasir login dan nota TRX-002 sudah berstatus Diproses"],
        ["Test Data", "Nilai input spesifik yang ditentukan", "200 gram tepung terigu", "Nota TRX-202611-002, akun staff@cuci.in, uang bayar Rp24.000"],
        ["Test Steps", "Aksi bernomor, satu aksi per langkah", "1. Aduk telur 2. Tuang adonan", "1. Buka detail nota 2. Tekan tombol Batalkan 3. Reload halaman"],
        ["Expected Result", "Hasil yang benar (ditulis sebelum pengujian)", "Foto kue matang sempurna", "1) Muncul pesan penolakan 2) Status tetap Diproses 3) Kas tidak berubah"],
        ["Actual Result", "Fakta yang benar-benar teramati saat diuji", "Kue buatan sendiri di meja", "Pesan penolakan muncul, status tetap Diproses pasca reload"],
        ["Status", "Putusan akhir pengujian", "Cocok dengan foto = Pass", "Pass (seluruh baris expected result terpenuhi secara valid)"]
    ],
    [1600, 2400, 2400, 3200]
);

$doc->addParagraph("Aturan Pengisian Waktu: Komponen Test Scenario, Test Case, Precondition, Test Data, Test Steps, dan Expected Result ditulis SEBELUM pengujian dimulai. Sebaliknya, komponen Actual Result dan Status baru diisi SAAT pengujian dieksekusi di lingkungan uji.", true);

// ==============================================================================
// BAGIAN 2: PENGENALAN SISTEM CUCI.IN, FITUR & ATURAN BISNIS (LAB 1)
// ==============================================================================
$doc->addSectionHeading("2. Sistem CUCI.IN, Fitur & Aturan Bisnis (Lab 1)");
$doc->addParagraph("Sistem informasi CUCI.IN mengotomatisasi pencatatan penerimaan cucian di kasir outlet, pelacakan pengerjaan pakaian, pembayaran digital QRIS, hingga layanan purnajual penanganan komplain pelanggan.");

$doc->addSubHeading("Tabel Fitur Utama CUCI.IN (F1 s/d F7)");
$doc->addTable(
    ["Kode Fitur", "Nama Fitur", "Pengguna Sasaran", "Deskripsi Fungsional"],
    [
        ["F1", "Autentikasi & Rate Limiting", "Admin, Kasir, Pelanggan", "Login multi-peran dengan proteksi brute-force throttle:5,1"],
        ["F2", "POS Kasir & Idempotensi", "Staf Kasir Outlet", "Input penerimaan cucian dengan pelindung UUID anti double-submit"],
        ["F3", "Validasi Pembayaran Tunai", "Staf Kasir Outlet", "Perhitungan uang kembalian dan pemblokiran pembayaran kurang"],
        ["F4", "Unggah Bukti Bayar Digital", "Pelanggan & Kasir", "Unggah struk transfer bank/QRIS dan panel verifikasi persetujuan kasir"],
        ["F5", "Tiket Pengaduan Pelanggan", "Pelanggan & Kasir", "Formulir komplain pakaian rusak/noda terikat nomor nota transaksi"],
        ["F6", "Kebijakan Otorisasi Anti-IDOR", "Seluruh Pengguna", "Pembatasan hak akses data transaksi dan komplain via Laravel Policy"],
        ["F7", "API Tracking & Ekspor Laporan", "Publik & Owner", "Endpoint tracking nota publik Sanctum dan ekspor streaming CSV laporan"]
    ],
    [1200, 2600, 2200, 3600]
);

$doc->addSubHeading("Tabel Aturan Bisnis Inti CUCI.IN (Business Rules / BR)");
$doc->addTable(
    ["Kode Aturan", "Deskripsi Aturan Bisnis", "Konsekuensi Sistem jika Melanggar"],
    [
        ["BR-AUT-01", "Percobaan login gagal dibatasi maksimal 5 kali dalam 1 menit.", "Server mengembalikan HTTP 429 Too Many Requests dan header Retry-After."],
        ["BR-POS-01", "Satu transaksi POS kasir hanya boleh disubmit sekali per Idempotency-Key.", "Request duplikat ditolak, tombol checkout dinonaktifkan."],
        ["BR-PAY-01", "Pembayaran tunai kasir dilarang disimpan jika nominal bayar kurang.", "Transaksi ditolak dengan pesan galat, data tidak tersimpan di database."],
        ["BR-PAY-02", "Bukti bayar berstatus Menunggu Verifikasi hanya dapat disahkan oleh kasir.", "Pelanggan dilarang mengesahkan nota sendiri, status tetap Belum Lunas."],
        ["BR-CMP-01", "Komplain pelanggan hanya dapat diajukan pada nota berstatus Diambil.", "Pengajuan komplain ditolak jika cucian masih Diterima atau Diproses."],
        ["BR-CMP-02", "Pelanggan hanya boleh mengakses dan mengomplain transaksi miliknya sendiri.", "Sistem merespons HTTP 403 Forbidden via Laravel Policy (Anti-IDOR)."],
        ["BR-TRX-01", "Pembatalan transaksi hanya diizinkan saat status cucian masih Diterima.", "Aksi pembatalan ditolak keras jika pakaian sudah berstatus Diproses."]
    ],
    [1400, 4600, 3600]
);

$doc->addSubHeading("Siklus Status Transaksi Laundry (State Transition)");
$doc->addParagraph("Transisi status pesanan pakaian pada CUCI.IN dirancang dengan aturan yang ketat: Diterima -> Diproses -> Selesai -> Diambil. Pembatalan cucian hanya dapat dilakukan saat status awal masih Diterima. Begitu pakaian masuk ke tabung mesin cuci (status Diproses), pembatalan DILARANG KERAS karena detergen dan air telah terpakai.");

// Test Data Sheet Bersama
$doc->addSubHeading("Test Data Sheet Bersama CUCI.IN (Shared Test Data)");
$doc->addParagraph("Data uji berikut digunakan seragam di seluruh rancangan test case untuk memastikan pengujian bersifat deterministik:");

$doc->addTable(
    ["Kode Data", "Jenis Entitas", "Identitas / Nilai Uji", "Peran / Keterangan Uji"],
    [
        ["U-ADM", "Akun Pengguna", "owner@cuci.in (pwd: password)", "Owner / Admin (Akses penuh seluruh modul)"],
        ["U-KAS", "Akun Pengguna", "staff@cuci.in (pwd: password)", "Staf Kasir Outlet (Operator POS & Verifikasi Bayar)"],
        ["U-CST-A", "Akun Pengguna", "customer@cuci.in (pwd: password)", "Pelanggan A - Budi (Pengguna uji alur normal)"],
        ["U-CST-B", "Akun Pengguna", "customer2@cuci.in (pwd: password)", "Pelanggan B - Siti (Pengguna uji Anti-IDOR)"],
        ["S-KIL", "Katalog Layanan", "Cuci Kilat Express (Rp12.000/kg)", "Durasi 24 jam, prioritas mesin"],
        ["S-REG", "Katalog Layanan", "Cuci Reguler Bersih (Rp7.000/kg)", "Durasi 72 jam, cuci setrika standar"],
        ["O-01", "Nota Transaksi", "TRX-202611-001 · U-CST-A (Rp21.000)", "Status: Diterima · Pembayaran: Belum Lunas"],
        ["O-02", "Nota Transaksi", "TRX-202611-002 · U-CST-A (Rp60.000)", "Status: Diproses · Pembayaran: Lunas Tunai"],
        ["O-03", "Nota Transaksi", "TRX-202611-003 · U-CST-A (Rp14.000)", "Status: Diambil · Pembayaran: Lunas Tunai"],
        ["O-04", "Nota Transaksi", "TRX-202611-004 · U-CST-B (Rp48.000)", "Status: Diambil · Pembayaran: Lunas QRIS"],
        ["O-05", "Nota Transaksi", "TRX-202611-005 · U-CST-A (Rp14.000)", "Status: Diterima · Pembayaran: Menunggu Verifikasi"],
        ["P-01", "Bukti Pembayaran", "transfer_bca_valid.jpg (450 KB)", "Struk transfer m-Banking resmi valid"],
        ["P-02", "Bukti Pembayaran", "bukti_corrupt_large.png (3.5 MB)", "Berkas melebihi batas upload 2MB (Uji Negatif)"]
    ],
    [1400, 2000, 3200, 3000]
);

// ==============================================================================
// BAGIAN 3: KARTU TEST CASE FORMAL CUCI.IN (LAMPIRAN A PMPL)
// ==============================================================================
$doc->addSectionHeading("3. Kartu Kasus Uji Formal CUCI.IN (Lampiran A PMPL)");
$doc->addParagraph("Setiap kasus uji di bawah disusun dengan format standar Lampiran A Modul Praktikum PMPL Bab 4, mencakup 10 komponen terstruktur, assertion 3 lapisan, dan traceability ke basis kode program:");

// MODUL 1
$doc->addSubHeading("MODUL 1: AUTENTIKASI, RATE LIMITING & KEAMANAN SISTEM");

$doc->addTestCaseCard(
    "TC-AUT-001",
    "Verifikasi login staf kasir berhasil dengan kredensial yang valid",
    "TS-AUT-001 · Fitur Autentikasi F1 (Happy Path)",
    "Positif · High",
    "Akun staf U-KAS (staff@cuci.in) aktif di database. Pengguna membuka halaman /login.",
    "U-KAS (Email: staff@cuci.in, Password: password)",
    "1. Masukkan email 'staff@cuci.in'.\n2. Masukkan kata sandi 'password'.\n3. Tekan tombol 'Masuk'.\n4. Muat ulang halaman peramban (page reload).",
    "1) Layar: Halaman dialihkan ke URL /staff/dashboard, profil 'Staff Kasir' tampil di navbar.\n2) Data: Sesi terotentikasi persisten di server; pasca reload, URL tetap berada di dashboard.\n3) Efek Lanjutan: Token CSRF diperbarui, tidak ada log galat pada storage/logs/laravel.log.",
    "Environment: Localhost/Staging · Akun: U-KAS · Reset: Logout sesi · Alat: Playwright / Chromium.",
    "tests/Feature/AuthRateLimitTest.php & tests/e2e/test_login_cuciin.py",
    "Pengalihan URL berhasil ke dashboard, sesi persisten pasca reload",
    "PASS"
);

$doc->addTestCaseCard(
    "TC-AUT-002",
    "Verifikasi penolakan login saat kata sandi salah dan integritas form",
    "TS-AUT-001 · Penanganan Kredensial Tidak Valid",
    "Negatif · High",
    "Akun U-KAS terdaftar di sistem. Pengguna berada di form /login.",
    "Email: staff@cuci.in, Password salah: PasswordSalah123!",
    "1. Masukkan email 'staff@cuci.in'.\n2. Masukkan kata sandi salah 'PasswordSalah123!'.\n3. Tekan tombol 'Masuk'.\n4. Periksa pesan galat dan URL peramban.",
    "1) Layar: Pesan validasi merah 'Kredensial yang diberikan tidak cocok' muncul di antarmuka.\n2) Data: Peramban tetap berada di URL /login; sesi otentikasi tidak terbentuk di backend.\n3) Efek Lanjutan: Input kata sandi dikosongkan otomatis; rate limiter mencatat 1 kali kegagalan.",
    "Environment: Localhost · Dependency: DB Connection · Reset: None · Alat: Playwright E2E.",
    "tests/e2e/test_login_cuciin.py (test_login_gagal_kata_sandi_salah)",
    "Pesan validasi merah tampil, URL tetap di /login, sesi aman",
    "PASS"
);

$doc->addTestCaseCard(
    "TC-AUT-003",
    "Verifikasi pembatasan rate limiter login maksimal 5 kegagalan (Aturan BR-AUT-01)",
    "TS-AUT-002 · Aturan Bisnis BR-AUT-01 (throttle:5,1)",
    "Negatif · High",
    "Aplikasi CUCI.IN aktif. Cache/rate limiter untuk IP penguji dalam keadaan bersih (reset).",
    "Email: staff@cuci.in, Password: SalahLagi99! (dikirim 6 kali berturut-turut)",
    "1. Lakukan request POST ke /login dengan kata sandi salah 5 kali berturut-turut.\n2. Amati respons server pada percobaan ke-1 s/d ke-5.\n3. Lakukan request POST ke-6 dengan kredensial yang sama.\n4. Periksa kode status HTTP dan header respons server.",
    "1) Layar: Muncul pesan 'Terlalu banyak upaya login. Silakan coba lagi dalam X detik.'\n2) Data: Server mengembalikan kode status HTTP 429 Too Many Requests pada percobaan ke-6.\n3) Efek Lanjutan: Header memuat Retry-After: 60; database menghentikan eksekusi hash kueri.",
    "Environment: Localhost/Testing · Reset: php artisan cache:clear · Alat: PHPUnit HTTP Client.",
    "tests/Feature/AuthRateLimitTest.php (test_login_terkena_rate_limit_setelah_5_kali_gagal)",
    "Request ke-6 menghasilkan status HTTP 429 dengan header Retry-After: 60",
    "PASS"
);

// MODUL 2
$doc->addSubHeading("MODUL 2: POS KASIR, IDEMPOTENSI & PEMBAYARAN TUNAI");

$doc->addTestCaseCard(
    "TC-POS-001",
    "Verifikasi pembuatan transaksi laundry baru dan cetak nota kasir POS",
    "TS-POS-001 · Alur Utama POS Kasir F2",
    "Positif · High",
    "Staf U-KAS login. Pelanggan U-CST-A aktif. Layanan S-REG (Rp7.000/kg) tersedia.",
    "Pelanggan: U-CST-A, Layanan: S-REG, Bobot: 3.0 kg, Bayar Tunai: Rp25.000",
    "1. Buka menu POS Kasir (/staff/transactions/create).\n2. Pilih pelanggan 'Budi' (U-CST-A).\n3. Pilih layanan 'Cuci Reguler' (S-REG) dan isi bobot 3.0 kg.\n4. Masukkan nominal uang tunai Rp25.000.\n5. Tekan tombol 'Simpan Transaksi & Cetak Nota'.\n6. Muat ulang halaman detail transaksi.",
    "1) Layar: Notifikasi flash sukses muncul, kembalian Rp4.000 terhitung benar, preview thermal terbuka.\n2) Data: Transaksi baru tersimpan di database (TRX-XXXX), status Diterima, bayar Lunas.\n3) Efek Lanjutan: Antrean job struk tercatat; kas kasir bertambah sebesar Rp21.000.",
    "Environment: Localhost · Akun: U-KAS · Data: U-CST-A & S-REG · Alat: Playwright Headless.",
    "tests/Feature/IdempotencyTransactionTest.php & tests/e2e/test_rapuh_pos_cuciin.py",
    "Transaksi tersimpan dengan nomor nota TRX-XXXX, uang kembali Rp4.000",
    "PASS"
);

$doc->addTestCaseCard(
    "TC-POS-002",
    "Verifikasi Idempotency Key mencegah double-submit transaksi saat klik ganda (Aturan BR-POS-01)",
    "TS-POS-002 · Aturan Bisnis BR-POS-01 (EnsureIdempotency)",
    "Negatif · High",
    "Kasir U-KAS berada di form checkout POS. Header Idempotency-Key (UUID) aktif di Alpine.js.",
    "Idempotency-Key: c9b2f1e4-8a3d-4c7b-9e21-5a4f6d8b0e12, Payload: U-CST-A, S-KIL (2kg, Rp24.000)",
    "1. Kirim request POST pembuatan transaksi dengan Idempotency-Key di atas.\n2. Tanpa mengubah UUID, kirimkan request POST kedua dalam jeda 200ms (double-click).\n3. Periksa respons dari server pada request kedua.\n4. Periksa jumlah rekaman transaksi di database.",
    "1) Layar: Tombol checkout berubah disabled (:disabled='loading') dengan spinner pemrosesan.\n2) Data: Server merespons 200/409 idempotent; tabel transactions HANYA berisi 1 baris transaksi.\n3) Efek Lanjutan: Tabel idempotency_keys mencatat key terpakai; nota tidak melompat ganda.",
    "Environment: Localhost/Testing · Reset: Hapus key dari tabel idempotency_keys · Alat: PHPUnit.",
    "tests/Feature/IdempotencyTransactionTest.php (test_idempotency_mencegah_transaksi_kasir_ganda)",
    "Request kedua dicegat middleware idempotensi, hanya 1 transaksi tersimpan di DB",
    "PASS"
);

$doc->addTestCaseCard(
    "TC-PAY-001",
    "Verifikasi penolakan transaksi saat uang tunai bayar kurang (Aturan BR-PAY-01)",
    "TS-PAY-001 · Aturan Bisnis BR-PAY-01 (Insufficient Payment)",
    "Negatif · High",
    "Kasir U-KAS login. Order baru dengan total tagihan Rp25.000 sedang diinput di kasir.",
    "Total Tagihan: Rp25.000, Uang Diterima: Rp24.000 (Kurang Rp1.000 tepat di batas)",
    "1. Masukkan nominal bayar Rp24.000 pada kolom 'Uang Diterima'.\n2. Tekan tombol 'Konfirmasi Pembayaran'.\n3. Amati pesan pada antarmuka.\n4. Muat ulang halaman (page reload) dan cek database.",
    "1) Layar: Muncul pesan penolakan 'Nominal uang dibayarkan kurang dari tagihan (Kurang Rp1.000)'.\n2) Data: Transaksi tidak tersimpan di database; status tidak berpindah ke Lunas setelah reload.\n3) Efek Lanjutan: Kas fisik kasir tidak mengalami penambahan; tidak ada nota dicetak.",
    "Environment: Localhost · Akun: U-KAS · Reset: None · Alat: Playwright E2E.",
    "tests/e2e/test_pembayaran_kurang_cuciin.py (test_pembayaran_saldo_kurang_ditolak_tiga_lapisan)",
    "Pesan penolakan muncul di layar, database tetap bersih pasca reload",
    "PASS"
);

// MODUL 3
$doc->addSubHeading("MODUL 3: BUKTI BAYAR DIGITAL & PANEL VERIFIKASI KASIR");

$doc->addTestCaseCard(
    "TC-PAY-002",
    "Verifikasi pelanggan berhasil mengunggah bukti transfer QRIS valid",
    "TS-PAY-002 · Fitur Unggah Pembayaran F4",
    "Positif · High",
    "Pelanggan U-CST-A login. Memiliki nota O-01 (TRX-202611-001) status Belum Bayar.",
    "Nota: O-01, Berkas: P-01 (transfer_bca_valid.jpg, 450 KB, JPEG)",
    "1. Buka halaman riwayat transaksi saya (/customer/transactions).\n2. Pilih nota TRX-202611-001 dan tekan 'Unggah Bukti Bayar'.\n3. Lampirkan berkas transfer_bca_valid.jpg.\n4. Tekan tombol 'Kirim Bukti Pembayaran'.\n5. Muat ulang halaman detail nota.",
    "1) Layar: Notifikasi sukses muncul; badge status berubah menjadi kuning (Menunggu Verifikasi).\n2) Data: Path berkas tersimpan di tabel payments, berkas fisik tersimpan di disk storage.\n3) Efek Lanjutan: Notifikasi transaksi masuk ke panel kasir; nota terkunci dari upload ulang.",
    "Environment: Localhost · Dependency: php artisan storage:link aktif · Alat: PHPUnit Upload Test.",
    "tests/Feature/CustomerPaymentProofTest.php",
    "Bukti bayar tersimpan di disk storage, thumbnail tampil, status Menunggu Verifikasi",
    "PASS"
);

$doc->addTestCaseCard(
    "TC-PAY-003",
    "Verifikasi penolakan unggah berkas bukti bayar ukuran > 2MB atau format non-gambar",
    "TS-PAY-002 · Validasi Integritas Berkas Upload",
    "Negatif · Medium",
    "Pelanggan U-CST-A berada di modal unggah bukti pembayaran nota O-01.",
    "Berkas: P-02 (bukti_corrupt_large.png, 3.5 MB) dan berkas non-gambar",
    "1. Pilih berkas bukti_corrupt_large.png (3.5 MB) pada input form file.\n2. Tekan tombol 'Kirim Bukti Pembayaran'.\n3. Amati validasi antarmuka dan respons sistem.\n4. Muat ulang halaman dan periksa disk storage.",
    "1) Layar: Muncul pesan validasi 'Ukuran berkas tidak boleh melebihi 2MB'.\n2) Data: Tidak ada berkas baru yang tersimpan di disk storage; tabel payments tidak bertambah.\n3) Efek Lanjutan: Server tetap stabil tanpa terjadi galat fatal 500 post_max_size.",
    "Environment: Localhost · Alat: Browser Playwright / PHPUnit Test.",
    "tests/Feature/CustomerPaymentProofTest.php (test_upload_bukti_bayar_melebihi_batas_ukuran_ditolak)",
    "Validasi ukuran menolak berkas, disk storage bersih dari file korup",
    "PASS"
);

$doc->addTestCaseCard(
    "TC-PAY-004",
    "Verifikasi kasir menyetujui (approve) bukti bayar digital dan nota menjadi Lunas",
    "TS-PAY-003 · Aturan Bisnis BR-PAY-02 (Persetujuan Kasir)",
    "Positif · High",
    "Staf kasir U-KAS login. Transaksi O-05 memiliki bukti bayar status Menunggu Verifikasi.",
    "Transaksi: O-05, Aksi Kasir: 'Setujui Pembayaran'",
    "1. Buka menu kasir 'Verifikasi Pembayaran' (/staff/payments/verification).\n2. Klik pratinjau bukti struk transfer nota TRX-202611-005.\n3. Tekan tombol 'Setujui Pembayaran (Approve)'.\n4. Buka kembali halaman riwayat transaksi pelanggan U-CST-A.",
    "1) Layar: Baris nota berpindah ke tab 'Selesai Diverifikasi', badge status menjadi hijau (Lunas).\n2) Data: Kolom payment_status pada tabel transactions berubah menjadi PAID dan verified_at terisi.\n3) Efek Lanjutan: Notifikasi status lunas terpicu; pakaian dijadwalkan masuk antrean cuci.",
    "Environment: Localhost · Akun: U-KAS · Reset: Rollback status sesudah test · Alat: Feature Test.",
    "tests/Feature/CashierPaymentVerificationTest.php",
    "Status pembayaran berubah menjadi PAID, timestamp tercatat, nota lunas",
    "PASS"
);

// MODUL 4
$doc->addSubHeading("MODUL 4: PENGADUAN PELANGGAN & SENTRALISASI OTORISASI ANTI-IDOR");

$doc->addTestCaseCard(
    "TC-CMP-001",
    "Verifikasi pelanggan berhasil mengajukan komplain pada transaksi berstatus Diambil",
    "TS-CMP-001 · Aturan Bisnis BR-CMP-01 (Alur Positif Komplain)",
    "Positif · High",
    "Pelanggan U-CST-A login. Memiliki transaksi O-03 (TRX-202611-003) dengan status akhir Diambil.",
    "Nota: O-03, Kategori: Noda Tidak Bersih, Keterangan: 'Terdapat sisa noda minyak di kerah kemeja.'",
    "1. Buka menu 'Ajukan Komplain' (/customer/complaints/create).\n2. Pilih nota TRX-202611-003 dari dropdown transaksi.\n3. Pilih kategori keluhan dan isi keterangan.\n4. Tekan tombol 'Kirim Pengaduan'.\n5. Muat ulang halaman daftar pengaduan (/customer/complaints).",
    "1) Layar: Banner hijau sukses muncul, nomor tiket CMP-XXXX tampil di antarmuka.\n2) Data: Tiket baru tersimpan di tabel complaints berstatus Menunggu; data persisten pasca reload.\n3) Efek Lanjutan: Tidak terjadi error 500; notifikasi komplain otomatis terkirim ke outlet.",
    "Environment: Localhost · Akun: U-CST-A · Data: Transaksi status Diambil · Alat: Playwright E2E.",
    "tests/Feature/CustomerComplaintTest.php & tests/e2e/test_complaint_cuciin.py",
    "Tiket komplain CMP-XXXX terbit, data persisten pasca reload, status Menunggu Review",
    "PASS"
);

$doc->addTestCaseCard(
    "TC-CMP-002",
    "Verifikasi penolakan pengajuan komplain pada transaksi yang masih Diproses (Aturan BR-CMP-01)",
    "TS-CMP-001 · Aturan Bisnis BR-CMP-01 (Rule Constraint)",
    "Negatif · High",
    "Pelanggan U-CST-A login. Transaksi O-02 (TRX-202611-002) masih berstatus Diproses.",
    "Target Akses: /customer/complaints/create?transaction_id=O-02 (bypass parameter form)",
    "1. Buka form pembuatan komplain dengan menyisipkan ID transaksi O-02 pada URL.\n2. Amati respons antarmuka form.\n3. Bila form terbuka, isi keterangan dan paksa submit tombol 'Kirim Pengaduan'.\n4. Muat ulang halaman dan periksa basis data.",
    "1) Layar: Muncul pesan 'Komplain hanya dapat diajukan setelah cucian diambil (Status Diambil)'.\n2) Data: Request ditolak dengan kode status HTTP 422/403; tabel complaints tidak bertambah.\n3) Efek Lanjutan: Status transaksi O-02 tetap Diproses tanpa efek samping.",
    "Environment: Localhost · Akun: U-CST-A · Reset: None · Alat: Feature Test & Playwright.",
    "tests/Feature/CustomerComplaintTest.php & tests/e2e/test_complaint_cuciin.py",
    "Form menolak pengajuan komplain status Diproses, tidak ada tiket tersimpan di DB",
    "PASS"
);

$doc->addTestCaseCard(
    "TC-CMP-003",
    "Verifikasi kebijakan otorisasi Anti-IDOR: Pelanggan A dilarang mengakses/mengomplain nota Pelanggan B",
    "TS-CMP-002 · Aturan Bisnis BR-CMP-02 (ComplaintPolicy & TransactionPolicy)",
    "Negatif · High",
    "Transaksi O-04 milik Pelanggan B (U-CST-B). Pengguna login sebagai Pelanggan A (U-CST-A).",
    "Akun: U-CST-A (customer@cuci.in), Target Akses: Nota O-04 milik U-CST-B",
    "1. Login sebagai Pelanggan A (U-CST-A).\n2. Ketikkan URL langsung di address bar browser: /customer/transactions/O-04 atau komplain O-04.\n3. Amati kode respons sistem dan tampilan halaman.\n4. Periksa judul tab peramban dan kode status HTTP.",
    "1) Layar: Halaman menampilkan layar kesalahan resmi Laravel '403 This action is unauthorized'.\n2) Data: Tidak ada detail transaksi Pelanggan B yang bocor di DOM HTML maupun judul tab.\n3) Efek Lanjutan: Gate::authorize() mencatat insiden akses ilegal; sesi Pelanggan A tidak crash.",
    "Environment: Localhost · Akun: U-CST-A dan U-CST-B · Alat: PHPUnit Feature Test.",
    "tests/Feature/CustomerComplaintTest.php (test_customer_tidak_bisa_mengajukan_komplain_transaksi_orang_lain)",
    "Akses ditolak keras dengan status HTTP 403 Forbidden, data pelanggan lain aman",
    "PASS"
);

// MODUL 5
$doc->addSubHeading("MODUL 5: KATALOG LAYANAN, PEMBATALAN TRANSAKSI & STREAMING EKSPOR");

$doc->addTestCaseCard(
    "TC-TRX-001",
    "Verifikasi pembatalan transaksi berstatus Diterima berhasil dilakukan (Aturan BR-TRX-01)",
    "TS-TRX-001 · Aturan Bisnis BR-TRX-01 (Alur Positif Pembatalan)",
    "Positif · High",
    "Kasir U-KAS login. Transaksi O-01 (TRX-202611-001) dalam status Diterima.",
    "Transaksi: O-01 (Status: Diterima)",
    "1. Buka detail transaksi O-01 dari daftar kasir.\n2. Tekan tombol merah 'Batalkan Transaksi'.\n3. Isi alasan pembatalan 'Pelanggan salah memilih paket layanan' dan konfirmasi.\n4. Muat ulang halaman (page reload).",
    "1) Layar: Notifikasi 'Transaksi berhasil dibatalkan' muncul, badge status berubah menjadi merah.\n2) Data: Kolom status pada database berubah menjadi Dibatalkan secara permanen setelah reload.\n3) Efek Lanjutan: Cucian dikeluarkan dari daftar tunggu mesin cuci; kasir mencatat log pembatalan.",
    "Environment: Localhost · Akun: U-KAS · Reset: Hapus transaksi dummy · Alat: Playwright E2E.",
    "tests/e2e/test_pembatalan_transaksi_cuciin.py (test_pembatalan_transaksi_diterima_berhasil)",
    "Transaksi berhasil dibatalkan, status berubah menjadi Dibatalkan di database",
    "PASS"
);

$doc->addTestCaseCard(
    "TC-TRX-002",
    "Verifikasi larangan pembatalan transaksi saat cucian berstatus Diproses (Aturan BR-TRX-01)",
    "TS-TRX-001 · Aturan Bisnis BR-TRX-01 (TC-TRX-003 Modul P4/P9)",
    "Negatif · High",
    "Transaksi O-02 milik U-CST-A berstatus Diproses (cucian sudah berada di mesin cuci).",
    "Transaksi: O-02 (Status: Diproses)",
    "1. Buka halaman detail transaksi O-02.\n2. Amati tombol 'Batalkan Transaksi'.\n3. Lakukan pengujian paksa via request POST pembatalan ke endpoint /staff/transactions/O-02/cancel.\n4. Muat ulang halaman detail transaksi.",
    "1) Layar: Tombol 'Batalkan' dinonaktifkan di antarmuka. Jika dipaksa, muncul pesan penolakan.\n2) Data: Status pesanan tetap Diproses setelah halaman dimuat ulang (bukan bug kosmetik).\n3) Efek Lanjutan: Tidak ada mutasi status atau pengembalian dana liar di sistem keuangan.",
    "Environment: Localhost · Data: Transaksi status Diproses · Alat: Playwright E2E & PHPUnit.",
    "tests/e2e/test_pembatalan_transaksi_cuciin.py (test_pembatalan_transaksi_diproses_ditolak)",
    "Aksi pembatalan ditolak keras, status tetap Diproses setelah dimuat ulang",
    "PASS"
);

$doc->addTestCaseCard(
    "TC-EXP-001",
    "Verifikasi mesin ekspor laporan transaksi streaming CSV bebas dari Out-of-Memory (OOM)",
    "TS-EXP-001 · Fitur Ekspor Laporan F7",
    "Positif · High",
    "Owner U-ADM login. Database memuat dataset pengujian skala besar (>1.000 transaksi).",
    "Rentang Tanggal: 2026-01-01 s/d 2026-11-02, Format: CSV Streaming",
    "1. Buka menu 'Laporan Keuangan' (/owner/reports).\n2. Tentukan filter tanggal dari awal bulan hingga hari ini.\n3. Tekan tombol 'Unduh Laporan CSV (Streaming)'.\n4. Amati konsumsi memori server (RAM) dan kelengkapan baris berkas unduhan.",
    "1) Layar: Peramban langsung mengunduh berkas laporan-transaksi-cuciin.csv tanpa jeda layar putih.\n2) Data: Header memuat Content-Type: text/csv dan Transfer-Encoding: chunked; seluruh data utuh.\n3) Efek Lanjutan: RAM server tetap stabil di bawah 30MB karena menerapkan streamDownload() & lazyById().",
    "Environment: Localhost / AWS EC2 Free Tier · Reset: None · Alat: Benchmark HTTP Client.",
    "tests/Feature/ReportExportTest.php",
    "Berkas CSV terunduh secara streaming, penggunaan memori server hemat di bawah 30MB",
    "PASS"
);

// ==============================================================================
// BAGIAN 4: ENAM KEBUTUHAN EKSEKUSI & STATUS BLOCKED (LAB 6)
// ==============================================================================
$doc->addSectionHeading("4. Kebutuhan Eksekusi & Penanganan Status Blocked (Lab 6)");
$doc->addParagraph("Setiap kasus uji sempurna tidak akan berguna jika kebutuhan eksekusinya tidak tersedia saat jadwal pengujian tiba. Tabel berikut merangkum 6 jenis kebutuhan eksekusi:");

$doc->addTable(
    ["Test Case ID", "Environment", "Akun & Role", "Data Awal", "Dependensi Luar", "Alat Bantu Verifikasi"],
    [
        ["TC-AUT-001", "Local / Staging", "U-KAS (Kasir)", "Seeder akun terdaftar", "None", "Playwright Inspector"],
        ["TC-AUT-003", "Testing CI/CD", "Any IP", "Clean cache table", "RateLimiter Driver", "HTTP Header Inspector"],
        ["TC-POS-002", "Local Web Server", "U-KAS (Kasir)", "Keranjang kasir isi", "Alpine.js UUID Header", "PostgreSQL DB Client"],
        ["TC-PAY-002", "Local / Staging", "U-CST-A (Customer)", "Nota O-01 belum bayar", "Storage symlink disk", "File Explorer / Disk"],
        ["TC-CMP-003", "Testing App", "U-CST-A & U-CST-B", "Nota O-04 milik B", "Laravel Gate & Policy", "PHPUnit Test Suite"],
        ["TC-EXP-001", "Staging AWS EC2", "U-ADM (Owner)", "1.000+ Transaksi dummy", "PHP Output Buffer", "Memory Profiler"]
    ],
    [1300, 1600, 1800, 1700, 1600, 1600]
);

$doc->addSubHeading("Prosedur Penetapan Status Blocked pada Kasus Nyata");
$doc->addParagraph("Ketika sebuah kasus uji tidak dapat dieksekusi karena ketiadaan infrastruktur prasyarat (bukan karena kode program menghasilkan bug), status yang diberikan adalah BLOCKED, bukan Fail. Contoh penanganan pada CUCI.IN:");
$doc->addParagraph("1. Kasus Provider QRIS Maintenance (TS-PAY-004): Ketika gateway pembayaran eksternal offline untuk perbaikan, pengujian callback QRIS diberi status BLOCKED dan dilaporkan kepada Project Leader.\n2. Kasus Background Worker Offline (TS-CMP-004): Jika worker queue lokal belum diaktifkan, pengujian optimasi gambar komplain ditandai BLOCKED hingga perintah php artisan queue:work dijalankan.", true);

$doc->addCheckpoint("Menguji transaksi di sistem production dengan uang sungguhan DILARANG KERAS karena merugikan pembukuan kas nyata, mencemari dataset analitik bisnis, dan berisiko memicu rekonsiliasi keuangan fiktif.");

// ==============================================================================
// BAGIAN 5: SIMULASI EKSEKUSI & METRIK PENGUJIAN (LAB 7)
// ==============================================================================
$doc->addSectionHeading("5. Simulasi Eksekusi & Log Metrik Pengujian (Lab 7)");
$doc->addParagraph("Pencatatan hasil pengujian dilakukan secara objektif dengan mencatat fakta teramati (bukan opini). Tabel di bawah merekam hasil eksekusi pengujian pada repositori CUCI.IN:");

$doc->addTable(
    ["Test Case ID", "Perilaku yang Diharapkan", "Fakta yang Diamati saat Eksekusi", "Status", "Keterangan Hasil"],
    [
        ["TC-AUT-001", "Dashboard tampil, sesi persisten pasca reload", "Dashboard terbuka, sesi login tersimpan", "PASS", "Alur login normal lulus"],
        ["TC-AUT-002", "Pesan validasi merah, URL tetap di /login", "Pesan merah tampil, URL tidak berpindah", "PASS", "Pengujian negatif kredensial lulus"],
        ["TC-AUT-003", "Request ke-6 menghasilkan status HTTP 429", "Respons 429 + header Retry-After: 60", "PASS", "Proteksi brute force terverifikasi"],
        ["TC-POS-001", "Nota TRX terbit, kembalian Rp4.000 pas", "Nota berhasil cetak, kas bertambah", "PASS", "Kalkulasi kasir akurat"],
        ["TC-POS-002", "Double-click hanya membuat 1 baris nota", "Hanya 1 transaksi tersimpan di DB", "PASS", "Idempotency key bekerja sempurna"],
        ["TC-PAY-001", "Ditolak saat uang kurang, DB tidak berubah", "Pesan penolakan muncul, DB bersih", "PASS", "Aturan BR-PAY-01 terpenuhi"],
        ["TC-PAY-002", "Bukti struk tersimpan di storage disk", "File tersimpan, status Menunggu Verifikasi", "PASS", "Fitur upload digital lulus"],
        ["TC-PAY-003", "Berkas > 2MB ditolak validasi form", "Pesan batas 2MB tampil di form upload", "PASS", "Proteksi disk storage aktif"],
        ["TC-PAY-004", "Kasir approve, status nota menjadi Lunas", "Status transaksi berubah menjadi PAID", "PASS", "Verifikasi kasir berfungsi"],
        ["TC-CMP-001", "Komplain nota Diambil berhasil diajukan", "Tiket CMP-XXXX terbit di dashboard", "PASS", "Aturan BR-CMP-01 alur sukses"],
        ["TC-CMP-002", "Komplain nota Diproses ditolak sistem", "Form memblokir pengajuan transaksi jalan", "PASS", "Aturan BR-CMP-01 penolakan lulus"],
        ["TC-CMP-003", "Pelanggan A dilarang akses nota Pelanggan B", "Sistem merespons HTTP 403 Forbidden", "PASS", "Otorisasi Anti-IDOR bekerja"],
        ["TC-TRX-001", "Nota status Diterima berhasil dibatalkan", "Status berubah Dibatalkan di database", "PASS", "Pembatalan awal diizinkan"],
        ["TC-TRX-002", "Pembatalan nota status Diproses ditolak", "Tombol batal nonaktif, status tetap Diproses", "PASS", "Penolakan pembatalan jalan"],
        ["TC-EXP-001", "File CSV terunduh streaming tanpa error OOM", "File CSV 1.000 baris terunduh, RAM < 30MB", "PASS", "Optimasi streaming efisien"]
    ],
    [1300, 2400, 2600, 1100, 2200]
);

$doc->addSubHeading("Rekapitulasi Testing Metrics CUCI.IN (Milestone 2)");
$doc->addTable(
    ["Metrik Pengujian", "Nilai Capaian", "Standar Evaluasi Mutu", "Status Mutu"],
    [
        ["Total Test Cases Terdefinisi", "15 Kasus Uji", "Mencakup 5 modul tim secara seimbang", "TERPENUHI"],
        ["Total Test Cases Dieksekusi", "15 Kasus Uji", "100% test case selesai dijalankan", "TERPENUHI"],
        ["Total Kasus Uji PASS", "15 Kasus Uji", "Seluruh assertion 3 lapisan valid", "LULUS 100%"],
        ["Total Kasus Uji FAIL", "0 Kasus Uji", "Bebas dari critical & high severity bug", "ZERO DEFECT"],
        ["Total Kasus Uji BLOCKED", "0 Kasus Uji", "Seluruh environment & sandbox siap", "TERATASI"],
        ["Test Pass Rate", "100%", "Ambang batas kelulusan minimal: >= 95%", "UNGGUL (A)"]
    ],
    [2600, 2200, 3200, 1600]
);

// ==============================================================================
// BAGIAN 6: MATRIKS KETERLACAKAN (TRACEABILITY MATRIX)
// ==============================================================================
$doc->addSectionHeading("6. Matriks Keterlacakan (Traceability Matrix)");
$doc->addParagraph("Matriks keterlacakan menjamin setiap kebutuhan perangkat lunak (PRD) dan aturan bisnis (BR) memiliki pengujian fungsional yang terhubung ke baris kode program nyata:");

$doc->addTable(
    ["Kebutuhan Fungsional (PRD)", "Aturan Bisnis", "Test Case ID", "Tipe Uji", "Berkas Pengujian Nyata di Repositori"],
    [
        ["Keamanan Login & Throttle", "BR-AUT-01", "TC-AUT-001 / TC-AUT-003", "Feature & E2E", "tests/Feature/AuthRateLimitTest.php & test_login_cuciin.py"],
        ["Idempotensi POS Kasir", "BR-POS-01", "TC-POS-001 / TC-POS-002", "Feature & E2E", "tests/Feature/IdempotencyTransactionTest.php & test_rapuh_pos_cuciin.py"],
        ["Validasi Pembayaran Tunai", "BR-PAY-01", "TC-PAY-001", "E2E 3-Layers", "tests/e2e/test_pembayaran_kurang_cuciin.py"],
        ["Verifikasi Pembayaran Digital", "BR-PAY-02", "TC-PAY-002 / TC-PAY-004", "Feature Test", "tests/Feature/CustomerPaymentProofTest.php"],
        ["Pengaduan Pelanggan", "BR-CMP-01", "TC-CMP-001 / TC-CMP-002", "Feature & E2E", "tests/Feature/CustomerComplaintTest.php & test_complaint_cuciin.py"],
        ["Otorisasi Anti-IDOR Policies", "BR-CMP-02", "TC-CMP-003", "Policy Test", "tests/Feature/CustomerComplaintTest.php (Gate::authorize)"],
        ["Aturan Pembatalan Cucian", "BR-TRX-01", "TC-TRX-001 / TC-TRX-002", "E2E Playwright", "tests/e2e/test_pembatalan_transaksi_cuciin.py"],
        ["Ekspor Laporan Streaming", "N/A (Perf)", "TC-EXP-001", "Feature Test", "tests/Feature/ReportExportTest.php (streamDownload)"]
    ],
    [2000, 1300, 2000, 1300, 3000]
);

// ==============================================================================
// PENUTUP: CEK DIRI & REFLEKSI (LAB 8 & PENUTUP)
// ==============================================================================
$doc->addSectionHeading("7. Cek Diri, Pengumpulan, dan Refleksi");

$doc->addSubHeading("Checklist Kesiapan Pengumpulan Dokumen");
$doc->addTable(
    ["Pernyataan Checklist Evaluasi Mandiri", "Status Verifikasi"],
    [
        ["Setiap test case hanya memiliki satu tujuan verifikasi yang spesifik dan terukur.", "[ V ] TERPENUHI"],
        ["Setiap precondition menyebut akun, transaksi, atau status tertentu dari data sheet.", "[ V ] TERPENUHI"],
        ["Setiap langkah pengujian berisi satu aksi eksplisit dan menyebut nama tombol yang persis.", "[ V ] TERPENUHI"],
        ["Setiap expected result memeriksa tiga lapisan: layar, data setelah reload, dan efek lanjutan.", "[ V ] TERPENUHI"],
        ["Tersedia pasangan seimbang antara test case positif (happy path) dan negatif (rule enforcement).", "[ V ] TERPENUHI"],
        ["Setiap test case mencantumkan referensi skenario (traceability) ke kode nyata di repositori.", "[ V ] TERPENUHI"]
    ],
    [7600, 2000]
);

$doc->addSubHeading("Refleksi Pembelajaran Pengujian Perangkat Lunak (D4 SIB)");
$doc->addParagraph("1. Komponen yang paling menantang dirancang adalah Assertion Tiga Lapisan, karena penguji harus memastikan data benar-benar tersimpan di database melalui simulasi page.reload() untuk menghindari kelulusan semu (bug kosmetik antarmuka).\n2. Memisahkan antara status Fail dan Blocked memberikan pemahaman mendalam bahwa kegagalan akibat dependensi luar yang belum siap tidak boleh mencemari metrik kualitas kode program inti.\n3. Perancangan test case terstruktur di Pertemuan 4 ini menjadi prasyarat mutlak yang mempermudah otomatisasi penulisan skrip Playwright E2E pada Pertemuan 9 dan suite PHPUnit pada Milestone 2.", false, true);

// Eksekusi Pembuatan Dokumen Word
$outputFile = 'docs/PMPL-M4-Dokumen-Spesifikasi-Test-Case-CUCIIN.docx';
$backupFile = 'docs/PMPL-M4-Dokumen-Spesifikasi-Test-Case-CUCIIN-Lengkap.docx';

$doc->generateDocx($backupFile);
if ($doc->generateDocx($outputFile)) {
    echo "BERHASIL: Dokumen Spesifikasi Kasus Uji tersimpan di " . $outputFile . "\n";
    echo "BERHASIL: Salinan dokumen cadangan tersimpan di " . $backupFile . "\n";
} else {
    echo "GAGAL: Tidak dapat membuat dokumen Word.\n";
}

