import { COLORS, ICONS } from '../tokens.mjs';

export function buildAdminScreens(b, publicAuthRefs = {}, staffRefs = {}) {
  let btnAdminPos, btnAdminServices, btnAdminCustomers;
  let btnRepPdf, btnUserAdd, btnUserEdit0;

  // --- Frame 17: Admin Dashboard ---
  const f17Shell = b.appShell({
    name: '17-Admin-Dashboard',
    x: 40,
    y: 3100,
    role: 'admin',
    activeLabel: 'Dashboard',
    pageTitle: 'Dashboard Administrator & Pemilik Laundry',
    userName: 'Muh. Zaky Dawamul',
    returnRefs: true,
    actions: [
      { label: 'Transaksi Baru', variant: 'primary', icon: 'plus-circle', kbd: 'F2', width: 165 },
      { label: 'Kelola Layanan', variant: 'secondary', icon: 'tag', width: 145 },
      { label: 'Data Pelanggan', variant: 'secondary', icon: 'users', width: 145 },
    ],
    contentFn: (builder, main, { actionRefs }) => {
      btnAdminPos = actionRefs[0];
      btnAdminServices = actionRefs[1];
      btnAdminCustomers = actionRefs[2];
      // 1. Welcome Banner
      const banner = builder.frame({
        name: 'Banner',
        parent: main,
        width: 1136,
        height: 90,
        props: {
          fill: COLORS.white,
          stroke: COLORS.slate200,
          strokeWeight: 1,
          cornerRadius: 16,
          padding: [20, 24, 20, 24],
          layout: 'horizontal',
          mainAlign: 'spaceBetween',
          crossAlign: 'center',
        },
      });
      const bCol = builder.frame({ name: 'BC', parent: banner, width: 800, height: 50, props: { layout: 'vertical', gap: 4 } });
      builder.text({ name: 'H1', parent: bCol, text: 'Selamat Datang, Muh. Zaky Dawamul!', props: { fontSize: 22, fontWeight: 'bold' } });
      builder.text({ name: 'H2', parent: bCol, text: 'Pantau kesehatan finansial, antrean pengerjaan cucian, dan kelola seluruh master data sistem dari sini.', props: { fontSize: 13, fill: COLORS.slate600 } });

      // 2. Master Data 4-Grid
      const grid = builder.frame({
        name: 'MasterDataGrid',
        parent: main,
        x: 0,
        y: 106,
        width: 1136,
        height: 96,
        props: { layout: 'horizontal', gap: 16 },
      });
      const mCards = [
        { label: 'Total Pengguna', val: '4', sub: 'Admin & Kasir', icon: 'shield-check' },
        { label: 'Total Pelanggan', val: '158', sub: 'Pelanggan Terdaftar', icon: 'users' },
        { label: 'Layanan Aktif', val: '12', sub: 'Katalog Paket', icon: 'tag' },
        { label: 'Total Transaksi', val: '1.420', sub: 'Riwayat Nota', icon: 'receipt' },
      ];
      mCards.forEach(c => {
        const mc = builder.frame({
          name: `MC-${c.label}`,
          parent: grid,
          width: 272,
          height: 96,
          props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 14, padding: 16, layout: 'vertical', gap: 6 },
        });
        const mHead = builder.frame({ name: 'MH', parent: mc, width: 240, height: 24, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
        builder.text({ name: 'L', parent: mHead, text: c.label, props: { fontSize: 12, fill: COLORS.slate600, fontWeight: 'medium' } });
        builder.vector({ name: 'I', parent: mHead, width: 16, height: 16, path: ICONS[c.icon], props: { stroke: COLORS.brand600 } });
        builder.text({ name: 'V', parent: mc, text: c.val, props: { fontSize: 22, fontWeight: 'bold', fill: COLORS.slate900 } });
        builder.text({ name: 'S', parent: mc, text: c.sub, props: { fontSize: 11, fill: COLORS.slate500 } });
      });

      // 3. Omzet & Finansial Big Cards
      const finRow = builder.frame({
        name: 'FinRow',
        parent: main,
        x: 0,
        y: 218,
        width: 1136,
        height: 120,
        props: { layout: 'horizontal', gap: 20 },
      });

      // Omzet card
      const omzetCard = builder.frame({
        name: 'OmzetCard',
        parent: finRow,
        width: 558,
        height: 120,
        props: { fill: COLORS.emerald50, stroke: COLORS.emerald100, strokeWeight: 1, cornerRadius: 16, padding: 20, layout: 'vertical', gap: 6 },
      });
      builder.text({ name: 'OLbl', parent: omzetCard, text: 'OMZET MASUK BULAN INI (TERVERIFIKASI)', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.emerald700 } });
      builder.text({ name: 'OVal', parent: omzetCard, text: 'Rp 28.450.000', props: { fontSize: 32, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.text({ name: 'OSub', parent: omzetCard, text: 'Naik +14.2% dibandingkan periode bulan sebelumnya', props: { fontSize: 12, fill: COLORS.emerald700 } });

      // Piutang card
      const piutangCard = builder.frame({
        name: 'PiutangCard',
        parent: finRow,
        width: 558,
        height: 120,
        props: { fill: COLORS.amber50, stroke: COLORS.amber100, strokeWeight: 1, cornerRadius: 16, padding: 20, layout: 'vertical', gap: 6 },
      });
      builder.text({ name: 'PLbl', parent: piutangCard, text: 'PIUTANG BELUM TERTAGIH (BELUM LUNAS)', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.amber800 } });
      builder.text({ name: 'PVal', parent: piutangCard, text: 'Rp 1.240.000', props: { fontSize: 32, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.text({ name: 'PSub', parent: piutangCard, text: 'Dari total 18 nota yang cuciannya siap diambil', props: { fontSize: 12, fill: COLORS.amber800 } });

      // 4. Activity & Recent Orders Table Card
      const actCard = builder.frame({
        name: 'ActivityCard',
        parent: main,
        x: 0,
        y: 354,
        width: 1136,
        height: 420,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 24, layout: 'vertical', gap: 14 },
      });
      builder.text({ name: 'ATitle', parent: actCard, text: 'Aktivitas Transaksi Terbaru di Seluruh Meja Kasir', props: { fontSize: 16, fontWeight: 'bold' } });

      const ath = builder.frame({ name: 'ATH', parent: actCard, width: 1088, height: 38, props: { fill: COLORS.slate50, cornerRadius: 8, layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16] } });
      [{ n: 'Nota', w: 180 }, { n: 'Pelanggan', w: 200 }, { n: 'Petugas Kasir', w: 180 }, { n: 'Total Tagihan', w: 160 }, { n: 'Status Pembayaran', w: 160 }, { n: 'Status Cucian', w: 180 }].forEach(c => {
        builder.text({ name: c.n, parent: ath, text: c.n, props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate600 }, width: c.w });
      });

      const arows = [
        { c: 'TRX-20261109-0042', p: 'Budi Santoso', k: 'Maulana Ahmad', t: 'Rp 45.000', pay: 'Menunggu Verif', st: 'Sedang Dicuci' },
        { c: 'TRX-20261109-0041', p: 'Siti Aminah', k: 'Maulana Ahmad', t: 'Rp 24.000', pay: 'Lunas', st: 'Siap Diambil' },
        { c: 'TRX-20261109-0040', p: 'Ahmad Fauzi', k: 'Nora Nobel', t: 'Rp 24.000', pay: 'Lunas', st: 'Diterima' },
        { c: 'TRX-20261108-0039', p: 'Hendro Wijaya', k: 'Daysyani Sophi', t: 'Rp 35.000', pay: 'Lunas', st: 'Selesai' },
      ];
      arows.forEach((r, idx) => {
        const tr = builder.frame({ name: `R-${idx}`, parent: actCard, width: 1088, height: 48, props: { stroke: COLORS.slate100, strokeWeight: 1, layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16] } });
        builder.text({ name: 'C', parent: tr, text: r.c, props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.brand700 }, width: 180 });
        builder.text({ name: 'P', parent: tr, text: r.p, props: { fontSize: 13 }, width: 200 });
        builder.text({ name: 'K', parent: tr, text: r.k, props: { fontSize: 13, fill: COLORS.slate600 }, width: 180 });
        builder.text({ name: 'T', parent: tr, text: r.t, props: { fontSize: 13, fontWeight: 'bold' }, width: 160 });
        builder.badge({ parent: tr, label: r.pay, tone: r.pay === 'Lunas' ? 'success' : 'warning', width: 140 });
        builder.badge({ parent: tr, label: r.st, tone: r.st === 'Selesai' ? 'success' : 'brand', width: 130 });
      });
    },
  });
  const f17 = f17Shell.root;

  // --- Frame 18: Admin Reports & Analytics ---
  const f18Shell = b.appShell({
    name: '18-Admin-Reports-Analytics',
    x: 1560,
    y: 3100,
    role: 'admin',
    activeLabel: 'Laporan',
    pageTitle: 'Laporan Operasional & Keuangan Laundry',
    userName: 'Muh. Zaky Dawamul',
    returnRefs: true,
    actions: [
      { label: 'Cetak PDF', variant: 'secondary', icon: 'printer', width: 120 },
      { label: 'Ekspor Excel (.xls)', variant: 'secondary', icon: 'download', width: 155 },
      { label: 'CSV Streaming (P9)', variant: 'primary', icon: 'download', width: 165 },
    ],
    contentFn: (builder, main, { actionRefs }) => {
      btnRepPdf = actionRefs[0];
      // 4 Aggregation Cards
      builder.monolithicStatStrip({
        parent: main,
        x: 0,
        y: 10,
        width: 1136,
        height: 110,
        stats: [
          { label: 'Omzet masuk', value: 'Rp 28.450.000', sub: 'Pembayaran lunas & terverifikasi', tone: 'success' },
          { label: 'Nilai tagihan bruto', value: 'Rp 29.690.000', sub: 'Total nilai transaksi dibuat', tone: 'brand' },
          { label: 'Total cucian selesai', value: '2.340', unit: 'Kg', sub: 'Volume cucian bersih' },
          { label: 'Rata-rata durasi', value: '14.2', unit: 'Jam', sub: 'Kecepatan pengerjaan workshop' },
        ],
      });

      // Filter Range & Table Card
      const reportCard = builder.frame({
        name: 'ReportCard',
        parent: main,
        x: 0,
        y: 136,
        width: 1136,
        height: 640,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 24, layout: 'vertical', gap: 16 },
      });

      // Filter Row
      const fltRow = builder.frame({ name: 'FltRow', parent: reportCard, width: 1088, height: 44, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
      const dInputs = builder.frame({ name: 'DInputs', parent: fltRow, width: 600, height: 44, props: { layout: 'horizontal', gap: 10, crossAlign: 'center' } });
      builder.inputField({ parent: dInputs, width: 220, height: 38, label: null, value: '01 Nov 2026' });
      builder.text({ name: 'To', parent: dInputs, text: 'sampai', props: { fontSize: 12, fill: COLORS.slate500 } });
      builder.inputField({ parent: dInputs, width: 220, height: 38, label: null, value: '09 Nov 2026' });
      builder.button({ parent: dInputs, label: 'Terapkan Filter', variant: 'primary', width: 130, height: 36 });
      builder.badge({ parent: fltRow, label: 'Periode 9 Hari Terpilih', tone: 'slate', width: 160 });

      // Daily Breakdown Table
      const rth = builder.frame({ name: 'RTH', parent: reportCard, width: 1088, height: 38, props: { fill: COLORS.slate50, cornerRadius: 8, layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16] } });
      [{ n: 'Tanggal', w: 180 }, { n: 'Nota Masuk', w: 160 }, { n: 'Berat Total', w: 180 }, { n: 'Omzet Tunai', w: 180 }, { n: 'Omzet Digital (QRIS/Bank)', w: 220 }, { n: 'Total Omzet', w: 168 }].forEach(c => {
        builder.text({ name: c.n, parent: rth, text: c.n, props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate600 }, width: c.w });
      });

      const days = [
        { d: '09 Nov 2026 (Hari Ini)', n: '18 Nota', w: '84.5 Kg', cash: 'Rp 420.000', dig: 'Rp 1.120.000', tot: 'Rp 1.540.000' },
        { d: '08 Nov 2026', n: '24 Nota', w: '112.0 Kg', cash: 'Rp 650.000', dig: 'Rp 1.480.000', tot: 'Rp 2.130.000' },
        { d: '07 Nov 2026', n: '21 Nota', w: '96.2 Kg', cash: 'Rp 510.000', dig: 'Rp 1.340.000', tot: 'Rp 1.850.000' },
        { d: '06 Nov 2026', n: '29 Nota', w: '140.0 Kg', cash: 'Rp 820.000', dig: 'Rp 1.950.000', tot: 'Rp 2.770.000' },
      ];
      days.forEach((day, idx) => {
        const tr = builder.frame({ name: `DR-${idx}`, parent: reportCard, width: 1088, height: 48, props: { stroke: COLORS.slate100, strokeWeight: 1, layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16] } });
        builder.text({ name: 'D', parent: tr, text: day.d, props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate900 }, width: 180 });
        builder.text({ name: 'N', parent: tr, text: day.n, props: { fontSize: 13 }, width: 160 });
        builder.text({ name: 'W', parent: tr, text: day.w, props: { fontSize: 13, fill: COLORS.slate700 }, width: 180 });
        builder.text({ name: 'C', parent: tr, text: day.cash, props: { fontSize: 13, fill: COLORS.slate800 }, width: 180 });
        builder.text({ name: 'G', parent: tr, text: day.dig, props: { fontSize: 13, fill: COLORS.brand700 }, width: 220 });
        builder.text({ name: 'T', parent: tr, text: day.tot, props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.emerald700 }, width: 168 });
      });
    },
  });
  const f18 = f18Shell.root;

  // --- Frame 18b: Admin Report PDF Preview (1:1 admin/reports/pdf.blade.php) ---
  let btnPdfBack;
  const f18b = b.frame({
    name: '18b-Admin-Report-PDF-Preview',
    x: 3080,
    y: 3100,
    width: 1440,
    height: 900,
    props: { fill: COLORS.slate200, layout: 'vertical', crossAlign: 'center', padding: [16, 0, 20, 0], gap: 14 },
  });

  // Top PDF Viewer Bar
  const pdfBar = b.frame({
    name: 'PDFViewerToolbar',
    parent: f18b,
    width: 860,
    height: 52,
    props: { fill: COLORS.slate900, cornerRadius: 10, padding: [8, 16, 8, 16], layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' },
  });
  b.text({ name: 'DocName', parent: pdfBar, text: 'laporan-operasional-cuciin-20261101-20261109.pdf (DomPDF A4 Portrait)', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.white } });
  const pdfBtns = b.frame({ name: 'Btns', parent: pdfBar, width: 280, height: 36, props: { layout: 'horizontal', gap: 10, mainAlign: 'max', crossAlign: 'center' } });
  btnPdfBack = b.button({ parent: pdfBtns, label: 'Kembali ke Laporan', variant: 'secondary', icon: 'arrow-left', width: 155, height: 36 });
  b.button({ parent: pdfBtns, label: 'Unduh PDF', variant: 'primary', icon: 'download', width: 115, height: 36 });

  // A4 Document Paper Sheet
  const a4 = b.frame({
    name: 'A4PortraitPaper',
    parent: f18b,
    width: 860,
    height: 760,
    props: { fill: COLORS.white, stroke: COLORS.slate300, strokeWeight: 1, cornerRadius: 4, padding: [22, 36, 22, 36], layout: 'vertical', gap: 10 },
  });

  // Kop Surat Header
  const kop = b.frame({
    name: 'KopHeader',
    parent: a4,
    width: 772,
    height: 68,
    props: { stroke: COLORS.brand700, strokeWeight: 2, layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center', padding: [0, 0, 12, 0] },
  });
  const kopLeft = b.frame({ name: 'KL', parent: kop, width: 420, height: 54, props: { layout: 'vertical', gap: 2 } });
  b.text({ name: 'Brand', parent: kopLeft, text: 'CUCI.IN', props: { fontSize: 22, fontWeight: 'bold', fill: COLORS.brand700 } });
  b.text({ name: 'Sub', parent: kopLeft, text: 'Sistem Manajemen Operasional & Rekapitulasi Kasir Laundry • Jl. Transaksi Bersih No. 12, Malang', props: { fontSize: 10, fill: COLORS.slate500 } });

  const kopRight = b.frame({ name: 'KR', parent: kop, width: 320, height: 54, props: { layout: 'vertical', crossAlign: 'max', gap: 2 } });
  b.text({ name: 'DocT', parent: kopRight, text: 'LAPORAN OPERASIONAL', props: { fontSize: 16, fontWeight: 'bold', fill: COLORS.slate900 } });
  b.text({ name: 'Per', parent: kopRight, text: 'Periode: 01 November 2026 — 09 November 2026', props: { fontSize: 10, fontWeight: 'bold', fill: COLORS.slate600 } });
  b.text({ name: 'Gen', parent: kopRight, text: 'Dicetak: 09 Nov 2026, 21:00 WIB oleh Gargarina Nanda Iswati', props: { fontSize: 9, fill: COLORS.slate500 } });

  // 4 Executive KPI Cards inside PDF
  const pdfKpi = b.frame({ name: 'KPIGrid', parent: a4, width: 772, height: 68, props: { layout: 'horizontal', gap: 10 } });
  [
    { l: 'TOTAL TRANSAKSI', v: '192 Pesanan', c: COLORS.slate900 },
    { l: 'OMZET MASUK (LUNAS)', v: 'Rp 28.450.000', c: COLORS.emerald700 },
    { l: 'NILAI TAGIHAN BRUTO', v: 'Rp 29.690.000', c: COLORS.slate900 },
    { l: 'PIUTANG BERJALAN', v: 'Rp 1.240.000', c: COLORS.amber800 },
  ].forEach((k, i) => {
    const kc = b.frame({ name: `K-${i}`, parent: pdfKpi, width: 185, height: 68, props: { fill: COLORS.slate50, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 6, padding: 10, layout: 'vertical', gap: 4 } });
    b.text({ name: 'L', parent: kc, text: k.l, props: { fontSize: 9, fontWeight: 'bold', fill: COLORS.slate500 } });
    b.text({ name: 'V', parent: kc, text: k.v, props: { fontSize: 14, fontWeight: 'bold', fill: k.c } });
  });

  b.text({
    name: 'PayComposition',
    parent: a4,
    text: 'Komposisi Pembayaran: Tunai: Rp 11.200.000 | QRIS: Rp 12.850.000 | Transfer: Rp 4.400.000',
    props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate700 },
  });

  // PDF Data Table
  const pdfTbl = b.frame({ name: 'DataTable', parent: a4, width: 772, height: 480, props: { stroke: COLORS.slate200, strokeWeight: 1, layout: 'vertical' } });
  const pth = b.frame({ name: 'TH', parent: pdfTbl, width: 772, height: 34, props: { fill: COLORS.brand700, layout: 'horizontal', crossAlign: 'center', padding: [0, 10, 0, 10] } });
  [
    { n: 'NO', w: 36 },
    { n: 'NO. NOTA', w: 135 },
    { n: 'TANGGAL', w: 105 },
    { n: 'PELANGGAN', w: 125 },
    { n: 'LAYANAN', w: 175 },
    { n: 'STATUS', w: 70 },
    { n: 'BAYAR', w: 55 },
    { n: 'TAGIHAN', w: 71 },
  ].forEach(c => b.text({ name: c.n, parent: pth, text: c.n, props: { fontSize: 9, fontWeight: 'bold', fill: COLORS.white }, width: c.w }));

  const pdfRows = [
    ['1', 'TRX-20261109-0042', '09/11/2026 10:00', 'Budi Santoso', 'Cuci Komplit Kilat (5 kg)', 'Diproses', 'DP', 'Rp 45.000'],
    ['2', 'TRX-20261109-0041', '09/11/2026 09:15', 'Siti Aminah', 'Cuci Lipat Reguler (4 kg)', 'Selesai', 'Lunas', 'Rp 24.000'],
    ['3', 'TRX-20261109-0040', '09/11/2026 08:40', 'Ahmad Fauzi', 'Cuci Setrika Express (3 kg)', 'Diterima', 'Lunas', 'Rp 24.000'],
    ['4', 'TRX-20261108-0039', '08/11/2026 16:20', 'Hendro Wijaya', 'Bedcover Jumbo (1 pcs)', 'Diambil', 'Lunas', 'Rp 35.000'],
    ['5', 'TRX-20261108-0038', '08/11/2026 14:10', 'Dewi Lestari', 'Cuci Setrika Express (4 kg)', 'Selesai', 'Lunas', 'Rp 32.000'],
    ['6', 'TRX-20261108-0037', '08/11/2026 11:05', 'Rina Maryati', 'Cuci Komplit Kilat (4 kg)', 'Diambil', 'Lunas', 'Rp 36.000'],
    ['7', 'TRX-20261107-0035', '07/11/2026 15:30', 'Joko Prabowo', 'Sepatu Deep Clean (2 psg)', 'Diambil', 'Lunas', 'Rp 70.000'],
  ];
  pdfRows.forEach((r, idx) => {
    const row = b.frame({
      name: `PRow-${idx}`,
      parent: pdfTbl,
      width: 772,
      height: 36,
      props: { fill: idx % 2 === 1 ? COLORS.slate50 : COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, layout: 'horizontal', crossAlign: 'center', padding: [0, 10, 0, 10] },
    });
    const widths = [36, 135, 105, 125, 175, 70, 55, 71];
    r.forEach((cell, ci) => {
      b.text({
        name: `C-${ci}`,
        parent: row,
        text: cell,
        props: { fontSize: 10, fontWeight: ci === 1 || ci === 7 ? 'bold' : 'normal', fill: COLORS.slate800 },
        width: widths[ci],
      });
    });
  });

  // Signature Footer inside A4
  const sigRow = b.frame({ name: 'SignatureSection', parent: a4, width: 772, height: 130, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center', padding: [16, 24, 0, 24] } });
  const sigL = b.frame({ name: 'SigKasir', parent: sigRow, width: 260, height: 110, props: { layout: 'vertical', gap: 6 } });
  b.text({ name: 'S1', parent: sigL, text: 'Diverifikasi oleh,', props: { fontSize: 10, fill: COLORS.slate600 } });
  b.text({ name: 'S2', parent: sigL, text: 'Kasir Penanggung Jawab', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate900 } });
  b.text({ name: 'S3', parent: sigL, text: '( Maulana Ahmad Bukhori )', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate900 } });

  const sigR = b.frame({ name: 'SigOwner', parent: sigRow, width: 260, height: 110, props: { layout: 'vertical', crossAlign: 'max', gap: 6 } });
  b.text({ name: 'O1', parent: sigR, text: 'Mengetahui,', props: { fontSize: 10, fill: COLORS.slate600 } });
  b.text({ name: 'O2', parent: sigR, text: 'Manajer Outlet / Owner', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate900 } });
  b.text({ name: 'O3', parent: sigR, text: '( Gargarina Nanda Iswati )', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate900 } });

  b.text({
    name: 'DocFootNote',
    parent: a4,
    text: 'Dokumen ini diterbitkan secara otomatis oleh Sistem CUCI.IN dan merupakan bukti sah pelaporan operasional outlet.',
    props: { fontSize: 9, fill: COLORS.slate400 },
  });

  // --- Frame 19: Admin User Role Management ---
  const f19Shell = b.appShell({
    name: '19-Admin-User-Role-Management',
    x: 4600,
    y: 3100,
    role: 'admin',
    activeLabel: 'Kelola Pengguna',
    pageTitle: 'Manajemen Pengguna & Otorisasi Peran (Role Authorization)',
    userName: 'Muh. Zaky Dawamul',
    returnRefs: true,
    actions: [{ label: '+ Tambah Pengguna', variant: 'primary', icon: 'plus', width: 165 }],
    contentFn: (builder, main, { actionRefs }) => {
      btnUserAdd = actionRefs[0];
      const card = builder.frame({
        name: 'UserCard',
        parent: main,
        width: 1136,
        height: 750,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 24, layout: 'vertical', gap: 16 },
      });

      // Role Tabs
      const rTabs = builder.frame({ name: 'RTabs', parent: card, width: 1088, height: 40, props: { layout: 'horizontal', gap: 10 } });
      builder.badge({ parent: rTabs, label: 'Semua Peran (5)', tone: 'brand', width: 130 });
      builder.badge({ parent: rTabs, label: 'Administrator (1)', tone: 'slate', width: 140 });
      builder.badge({ parent: rTabs, label: 'Staf / Kasir (3)', tone: 'slate', width: 130 });
      builder.badge({ parent: rTabs, label: 'Pelanggan (1)', tone: 'slate', width: 120 });

      // Table Header
      const uth = builder.frame({ name: 'UTH', parent: card, width: 1088, height: 38, props: { fill: COLORS.slate50, cornerRadius: 8, layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16] } });
      [{ n: 'Nama Lengkap', w: 220 }, { n: 'Alamat Email', w: 240 }, { n: 'Peran Akun', w: 160 }, { n: 'Status Akun', w: 140 }, { n: 'Terdaftar Sejak', w: 160 }, { n: 'Otorisasi', w: 168 }].forEach(c => {
        builder.text({ name: c.n, parent: uth, text: c.n, props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate600 }, width: c.w });
      });

      const users = [
        { name: 'Muh. Zaky Dawamul', email: 'zakydb091005@gmail.com', role: 'admin', st: 'Aktif', date: '01 Okt 2026' },
        { name: 'Maulana Ahmad Bukhori', email: 'maulanahmad2287@gmail.com', role: 'staff', st: 'Aktif', date: '01 Okt 2026' },
        { name: 'Daysyani Sophi Masayu', email: 'syaniday@gmail.com', role: 'staff', st: 'Aktif', date: '05 Okt 2026' },
        { name: 'Nayla Annora Nobel', email: 'naylaannora@gmail.com', role: 'staff', st: 'Aktif', date: '05 Okt 2026' },
        { name: 'Budi Santoso', email: 'budi.santoso@example.com', role: 'customer', st: 'Aktif', date: '12 Okt 2026' },
      ];
      users.forEach((u, idx) => {
        const tr = builder.frame({ name: `UR-${idx}`, parent: card, width: 1088, height: 52, props: { stroke: COLORS.slate100, strokeWeight: 1, layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16] } });
        builder.text({ name: 'N', parent: tr, text: u.name, props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate900 }, width: 220 });
        builder.text({ name: 'E', parent: tr, text: u.email, props: { fontSize: 13, fill: COLORS.slate600 }, width: 240 });
        builder.badge({ parent: tr, label: u.role.toUpperCase(), tone: u.role === 'admin' ? 'danger' : (u.role === 'staff' ? 'brand' : 'slate'), width: 120 });
        builder.badge({ parent: tr, label: u.st, tone: 'success', width: 90 });
        builder.text({ name: 'D', parent: tr, text: u.date, props: { fontSize: 12, fill: COLORS.slate500 }, width: 160 });
        const bEd = builder.button({ parent: tr, label: 'Edit Hak Akses', variant: 'secondary', width: 130, height: 32 });
        if (idx === 0) btnUserEdit0 = bEd;
      });
    },
  });
  const f19 = f19Shell.root;

  // --- Frame 19b: Admin User Create (1:1 admin/users/create.blade.php) ---
  let btnUserCreateBack, btnUserCreateCancel, btnUserCreateSave;
  const f19bShell = b.appShell({
    name: '19b-Admin-User-Create',
    x: 6120,
    y: 3100,
    role: 'admin',
    activeLabel: 'Kelola Pengguna',
    pageTitle: 'Tambah Pengguna Baru',
    userName: 'Muh. Zaky Dawamul',
    returnRefs: true,
    actions: [{ label: 'Kembali ke Daftar Pengguna', variant: 'secondary', icon: 'arrow-left', width: 210 }],
    contentFn: (builder, main, { actionRefs }) => {
      btnUserCreateBack = actionRefs[0];

      const card = builder.frame({
        name: 'CreateUserCard',
        parent: main,
        width: 780,
        height: 680,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 28, layout: 'vertical', gap: 14 },
      });

      const head = builder.frame({ name: 'H', parent: card, width: 716, height: 50, props: { layout: 'vertical', gap: 4 } });
      builder.text({ name: 'T1', parent: head, text: 'Tambah Akun Pengguna Baru', props: { fontSize: 20, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.text({ name: 'T2', parent: head, text: 'Buat akun staf operasional, kasir, atau administrator baru untuk sistem CUCI.IN.', props: { fontSize: 13, fill: COLORS.slate500 } });

      builder.inputField({ parent: card, width: 716, height: 40, label: 'Nama Lengkap *', placeholder: 'Contoh: Rian Anggara', value: 'Gargarina Nanda Iswati' });
      builder.inputField({ parent: card, width: 716, height: 40, label: 'Alamat Email *', placeholder: 'nama@cuci.in', value: 'ayiniswati22@gmail.com', icon: 'mail' });
      builder.inputField({ parent: card, width: 716, height: 40, label: 'Peran & Hak Akses *', value: 'Staf / Kasir (Akses POS & Operasional)', icon: 'shield-check' });

      const pwRow = builder.frame({ name: 'PasswordRow', parent: card, width: 716, height: 64, props: { layout: 'horizontal', gap: 16 } });
      builder.inputField({ parent: pwRow, width: 350, height: 40, label: 'Kata Sandi * (Minimal 8 karakter)', value: '••••••••••••', icon: 'lock' });
      builder.inputField({ parent: pwRow, width: 350, height: 40, label: 'Konfirmasi Kata Sandi *', value: '••••••••••••', icon: 'lock' });

      // Toggle Active Account
      const toggleBox = builder.frame({
        name: 'ActiveToggleBox',
        parent: card,
        width: 716,
        height: 60,
        props: { fill: COLORS.emerald50, stroke: COLORS.emerald100, strokeWeight: 1, cornerRadius: 12, padding: [10, 16, 10, 16], layout: 'horizontal', gap: 14, crossAlign: 'center' },
      });
      builder.badge({ parent: toggleBox, label: 'AKTIF', tone: 'success', width: 70, height: 28 });
      const tgTxt = builder.frame({ name: 'Txt', parent: toggleBox, width: 580, height: 38, props: { layout: 'vertical', gap: 2 } });
      builder.text({ name: 'L1', parent: tgTxt, text: 'Aktifkan Akun Langsung', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.text({ name: 'L2', parent: tgTxt, text: 'Jika dinonaktifkan, akun ini tidak dapat digunakan untuk masuk ke sistem.', props: { fontSize: 11, fill: COLORS.slate600 } });

      const foot = builder.frame({ name: 'Footer', parent: card, width: 716, height: 48, props: { layout: 'horizontal', mainAlign: 'max', crossAlign: 'center', gap: 12 } });
      btnUserCreateCancel = builder.button({ parent: foot, label: 'Batal', variant: 'secondary', width: 110, height: 40 });
      btnUserCreateSave = builder.button({ parent: foot, label: 'Simpan Pengguna Baru', variant: 'primary', icon: 'check', width: 195, height: 40 });
    },
  });
  const f19b = f19bShell.root;

  // --- Frame 19c: Admin User Edit (1:1 admin/users/edit.blade.php) ---
  let btnUserEditBack, btnUserEditCancel, btnUserEditSave;
  const f19cShell = b.appShell({
    name: '19c-Admin-User-Edit',
    x: 7640,
    y: 3100,
    role: 'admin',
    activeLabel: 'Kelola Pengguna',
    pageTitle: 'Edit Data Pengguna — Muh. Zaky Dawamul',
    userName: 'Muh. Zaky Dawamul',
    returnRefs: true,
    actions: [{ label: 'Kembali ke Daftar Pengguna', variant: 'secondary', icon: 'arrow-left', width: 210 }],
    contentFn: (builder, main, { actionRefs }) => {
      btnUserEditBack = actionRefs[0];

      // Self-Account Guard Banner ($isSelf)
      const selfAlert = builder.frame({
        name: 'SelfAccountGuardAlert',
        parent: main,
        width: 780,
        height: 64,
        props: { fill: COLORS.brand50, stroke: COLORS.brand200, strokeWeight: 1, cornerRadius: 14, padding: [10, 16, 10, 16], layout: 'horizontal', gap: 12, crossAlign: 'center' },
      });
      builder.vector({ name: 'Ico', parent: selfAlert, width: 20, height: 20, path: ICONS['shield-check'], props: { stroke: COLORS.brand700 } });
      const saTxt = builder.frame({ name: 'Txt', parent: selfAlert, width: 700, height: 40, props: { layout: 'vertical', gap: 2 } });
      builder.text({ name: 'T1', parent: saTxt, text: 'Anda sedang mengedit akun Anda sendiri (Self-Lockout Guard Aktif)', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.brand900 } });
      builder.text({ name: 'T2', parent: saTxt, text: 'Untuk menjaga keamanan operasional, hak akses peran Administrator dan status aktif tidak dapat dicabut dari akun sendiri.', props: { fontSize: 11, fill: COLORS.brand800 } });

      // Edit Form Card
      const card = builder.frame({
        name: 'EditUserCard',
        parent: main,
        width: 780,
        height: 680,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 26, layout: 'vertical', gap: 12 },
      });

      const head = builder.frame({ name: 'H', parent: card, width: 716, height: 46, props: { layout: 'vertical', gap: 2 } });
      builder.text({ name: 'T1', parent: head, text: 'Edit Data Pengguna', props: { fontSize: 20, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.text({ name: 'T2', parent: head, text: 'Perbarui profil, hak akses peran, atau setel ulang kata sandi pengguna Muh. Zaky Dawamul.', props: { fontSize: 12, fill: COLORS.slate500 } });

      builder.inputField({ parent: card, width: 716, height: 40, label: 'Nama Lengkap *', value: 'Muh. Zaky Dawamul Busro' });
      builder.inputField({ parent: card, width: 716, height: 40, label: 'Alamat Email *', value: 'zakydb091005@gmail.com', icon: 'mail' });

      // Locked Role Field
      const roleLocked = builder.frame({
        name: 'LockedRoleBox',
        parent: card,
        width: 716,
        height: 56,
        props: { fill: COLORS.slate50, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 12, padding: [8, 16, 8, 16], layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' },
      });
      const rlLeft = builder.frame({ name: 'RLL', parent: roleLocked, width: 460, height: 38, props: { layout: 'vertical', gap: 2 } });
      builder.text({ name: 'L', parent: rlLeft, text: 'Peran & Hak Akses *', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate700 } });
      builder.text({ name: 'S', parent: rlLeft, text: 'Administrator (Terkunci untuk akun sendiri • Akun berstatus Aktif)', props: { fontSize: 11, fill: COLORS.emerald700, fontWeight: 'medium' } });
      builder.badge({ parent: roleLocked, label: 'ADMINISTRATOR', tone: 'brand', width: 140 });

      // Optional Password Change Section
      const pwSec = builder.frame({
        name: 'OptionalPasswordSection',
        parent: card,
        width: 716,
        height: 130,
        props: { fill: COLORS.slate50, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 14, padding: 14, layout: 'vertical', gap: 8 },
      });
      builder.text({ name: 'PT', parent: pwSec, text: 'Ubah Kata Sandi (Opsional) — Biarkan kosong jika tidak ingin mengubah password lama', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate800 } });
      const pwGrid = builder.frame({ name: 'PGrid', parent: pwSec, width: 680, height: 60, props: { layout: 'horizontal', gap: 16 } });
      builder.inputField({ parent: pwGrid, width: 332, height: 38, label: 'Kata Sandi Baru', placeholder: 'Kosongkan jika tidak diubah', icon: 'lock' });
      builder.inputField({ parent: pwGrid, width: 332, height: 38, label: 'Konfirmasi Kata Sandi Baru', placeholder: 'Ulangi kata sandi baru', icon: 'lock' });

      const foot = builder.frame({ name: 'Footer', parent: card, width: 716, height: 48, props: { layout: 'horizontal', mainAlign: 'max', crossAlign: 'center', gap: 12 } });
      btnUserEditCancel = builder.button({ parent: foot, label: 'Batal', variant: 'secondary', width: 110, height: 40 });
      btnUserEditSave = builder.button({ parent: foot, label: 'Simpan Perubahan', variant: 'primary', icon: 'check', width: 175, height: 40 });
    },
  });
  const f19c = f19cShell.root;

  // ========================================================
  // PROTOTYPING WIRES: ALUR ADMINISTRATOR (TERHUBUNG KE ALUR UTAMA)
  // ========================================================
  if (publicAuthRefs.btnChipAdmin) {
    b.wire({ id: publicAuthRefs.btnChipAdmin, dest: f17 });
  }

  const adminShells = [f17Shell, f18Shell, f19Shell, f19bShell, f19cShell];
  adminShells.forEach((sh) => {
    if (sh.navRefs['Dashboard']) b.wire({ id: sh.navRefs['Dashboard'], dest: f17 });
    if (sh.navRefs['Laporan']) b.wire({ id: sh.navRefs['Laporan'], dest: f18 });
    if (sh.navRefs['Kelola Pengguna']) b.wire({ id: sh.navRefs['Kelola Pengguna'], dest: f19 });
    if (staffRefs.f11 && sh.navRefs['Kasir POS']) b.wire({ id: sh.navRefs['Kasir POS'], dest: staffRefs.f11 });
    if (staffRefs.f12 && sh.navRefs['Kelola Transaksi']) b.wire({ id: sh.navRefs['Kelola Transaksi'], dest: staffRefs.f12 });
    if (staffRefs.f14 && sh.navRefs['Verifikasi Pembayaran']) b.wire({ id: sh.navRefs['Verifikasi Pembayaran'], dest: staffRefs.f14 });
    if (staffRefs.f15 && sh.navRefs['Data Pelanggan']) b.wire({ id: sh.navRefs['Data Pelanggan'], dest: staffRefs.f15 });
    if (staffRefs.f15 && sh.navRefs['Master Layanan']) b.wire({ id: sh.navRefs['Master Layanan'], dest: staffRefs.f15 });
    if (staffRefs.f16 && sh.navRefs['Pengaduan']) b.wire({ id: sh.navRefs['Pengaduan'], dest: staffRefs.f16 });
    if (sh.logoutRef && publicAuthRefs.f01) {
      b.wire({ id: sh.logoutRef, dest: publicAuthRefs.f01 });
    }
  });

  if (staffRefs.f11 && btnAdminPos) b.wire({ id: btnAdminPos, dest: staffRefs.f11 });
  if (staffRefs.f15 && btnAdminServices) b.wire({ id: btnAdminServices, dest: staffRefs.f15 });
  if (staffRefs.f15 && btnAdminCustomers) b.wire({ id: btnAdminCustomers, dest: staffRefs.f15 });

  // Reports (18 <-> 18b PDF Preview)
  if (btnRepPdf) b.wire({ id: btnRepPdf, dest: f18b });
  if (btnPdfBack) b.wire({ id: btnPdfBack, dest: f18 });

  // User Management (19 <-> 19b Create & 19c Edit)
  if (btnUserAdd) b.wire({ id: btnUserAdd, dest: f19b });
  if (btnUserEdit0) b.wire({ id: btnUserEdit0, dest: f19c });

  if (btnUserCreateBack) b.wire({ id: btnUserCreateBack, dest: f19 });
  if (btnUserCreateCancel) b.wire({ id: btnUserCreateCancel, dest: f19 });
  if (btnUserCreateSave) b.wire({ id: btnUserCreateSave, dest: f19 });

  if (btnUserEditBack) b.wire({ id: btnUserEditBack, dest: f19 });
  if (btnUserEditCancel) b.wire({ id: btnUserEditCancel, dest: f19 });
  if (btnUserEditSave) b.wire({ id: btnUserEditSave, dest: f19 });

  return { f17, f18, f18b, f19, f19b, f19c };
}

