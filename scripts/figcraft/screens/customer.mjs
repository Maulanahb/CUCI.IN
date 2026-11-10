import { COLORS, ICONS } from '../tokens.mjs';

export function buildCustomerScreens(b, publicAuthRefs = {}) {
  // --------------------------------------------------------
  // FRAME 06: Customer Dashboard (1:1 customer/dashboard.blade.php)
  // --------------------------------------------------------
  let btnCustLookup, btnCustViewNote, btnCustAllHistory, btnCustCompBanner, btnSecViewAll;

  const f06Shell = b.appShell({
    name: '06-Customer-Dashboard',
    x: 40,
    y: 1060,
    role: 'customer',
    activeLabel: 'Beranda',
    pageTitle: 'Dashboard Pelanggan',
    userName: 'Budi Santoso',
    returnRefs: true,
    actions: [
      { label: 'Semua Riwayat Transaksi', variant: 'primary', icon: 'receipt', width: 200 },
      { label: 'Cek Kode Nota Fisik', variant: 'secondary', icon: 'search', width: 180 },
    ],
    contentFn: (builder, main, { actionRefs }) => {
      btnCustAllHistory = actionRefs[0];
      btnCustLookup = actionRefs[1];

      // 1. Welcome Banner Card
      const banner = builder.frame({
        name: 'WelcomeBanner',
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

      const bText = builder.frame({ name: 'Txt', parent: banner, width: 750, height: 50, props: { layout: 'vertical', gap: 4 } });
      builder.text({ name: 'Title', parent: bText, text: 'Selamat Datang, Budi Santoso', props: { fontSize: 22, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.text({ name: 'Sub', parent: bText, text: 'Pantau status pengerjaan pakaian laundry Anda secara langsung serta cek riwayat transaksi dan rincian nota kapan saja.', props: { fontSize: 13, fill: COLORS.slate600 } });

      // 2. Monolithic Stat Strip (FR-27)
      builder.monolithicStatStrip({
        parent: main,
        x: 0,
        y: 106,
        width: 1136,
        height: 110,
        stats: [
          { label: 'Cucian berjalan', value: '1', unit: 'Nota Aktif', sub: 'Sedang diproses di workshop', tone: 'brand' },
          { label: 'Menunggu pelunasan', value: 'Rp 34.000', sub: 'Dapat dilunasi saat pengambilan', tone: 'warning' },
          { label: 'Total riwayat nota', value: '12', unit: 'Nota', sub: 'Riwayat sejak akun terdaftar', tone: 'slate' },
          { label: 'Pengaduan aktif', value: '1', unit: 'Tiket', sub: 'Sedang ditangani tim staf', tone: 'danger' },
        ],
      });

      // 3. Active Complaint Notification Banner (FR-27)
      const compBanner = builder.frame({
        name: 'ComplaintNotificationBanner',
        parent: main,
        width: 1136,
        height: 74,
        props: {
          fill: COLORS.rose50,
          stroke: COLORS.rose200,
          strokeWeight: 1,
          cornerRadius: 14,
          padding: [14, 20, 14, 20],
          layout: 'horizontal',
          mainAlign: 'spaceBetween',
          crossAlign: 'center',
        },
      });

      const compLeft = builder.frame({ name: 'CLeft', parent: compBanner, width: 840, height: 44, props: { layout: 'horizontal', crossAlign: 'center', gap: 12 } });
      builder.vector({ name: 'CIco', parent: compLeft, width: 22, height: 22, path: ICONS['alert-triangle'], props: { stroke: COLORS.rose700 } });
      const compText = builder.frame({ name: 'CTxt', parent: compLeft, width: 800, height: 44, props: { layout: 'vertical', gap: 2 } });
      builder.text({ name: 'CTitle', parent: compText, text: 'Anda Memiliki 1 Pengaduan Cucian yang Sedang Ditangani', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.rose900 } });
      builder.text({ name: 'CSub', parent: compText, text: 'Tiket terbaru: CMP-20261024-001 — Pakaian Kelunturan Warna Biru (Status: Sedang Ditangani oleh Daysyani)', props: { fontSize: 11, fill: COLORS.rose700 } });

      btnCustCompBanner = builder.button({ parent: compBanner, label: 'Lihat Respons Petugas', variant: 'destructive', icon: 'arrow-right', width: 195, height: 36 });

      // 4. Cucian Sedang Berjalan Section
      const activeSec = builder.frame({
        name: 'ActiveLaundriesSection',
        parent: main,
        width: 1136,
        height: 230,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 22, layout: 'vertical', gap: 14 },
      });

      const secHead = builder.frame({ name: 'SecHead', parent: activeSec, width: 1092, height: 32, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
      const secHeadLeft = builder.frame({ name: 'SHL', parent: secHead, width: 320, height: 32, props: { layout: 'horizontal', crossAlign: 'center', gap: 8 } });
      builder.vector({ name: 'ActIco', parent: secHeadLeft, width: 18, height: 18, path: ICONS.activity, props: { stroke: COLORS.brand700 } });
      builder.text({ name: 'SecTitle', parent: secHeadLeft, text: 'Cucian Sedang Berjalan', props: { fontSize: 16, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.badge({ parent: secHeadLeft, label: '1 Aktif', tone: 'brand' });
      btnSecViewAll = builder.text({ name: 'ViewAll', parent: secHead, text: 'Lihat Semua di Riwayat Transaksi ->', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.brand700 } });

      // Active Laundry Item Card
      const itemCard = builder.frame({
        name: 'ItemCard-TRX-20261024-0001',
        parent: activeSec,
        width: 1092,
        height: 136,
        props: { fill: COLORS.slate50, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 12, padding: 16, layout: 'vertical', gap: 10 },
      });

      const itemHead = builder.frame({ name: 'IH', parent: itemCard, width: 1060, height: 26, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
      builder.text({ name: 'NotaInfo', parent: itemHead, text: 'Nota #TRX-20261024-0001 • Cuci Kering Setrika Reguler (4.0 kg) • Total: Rp 34.000', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.badge({ parent: itemHead, label: 'PROSES CUCI AKTIF', tone: 'brand' });

      // Stepper Bar
      const itemStepper = builder.frame({
        name: 'ItemStepper',
        parent: itemCard,
        width: 1060,
        height: 36,
        props: { fill: COLORS.white, cornerRadius: 8, layout: 'horizontal', mainAlign: 'spaceAround', crossAlign: 'center' },
      });
      ['1. Diterima (Selesai)', '2. Sedang Dicuci (Aktif)', '3. Siap Diambil', '4. Selesai'].forEach((st, i) => {
        builder.text({ name: `St-${i}`, parent: itemStepper, text: st, props: { fontSize: 11, fontWeight: i === 1 ? 'bold' : 'normal', fill: i === 1 ? COLORS.brand700 : (i === 0 ? COLORS.emerald700 : COLORS.slate400) } });
      });

      const itemFoot = builder.frame({ name: 'IF', parent: itemCard, width: 1060, height: 32, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
      builder.text({ name: 'EstFoot', parent: itemFoot, text: 'Perkiraan Selesai: 25 Oktober 2026, 17:00 WIB (~12 Jam lagi)', props: { fontSize: 12, fill: COLORS.slate600 } });
      btnCustViewNote = builder.button({ parent: itemFoot, label: 'Lihat Rincian Nota', variant: 'secondary', icon: 'receipt', width: 160, height: 32 });
    },
  });
  const f06 = f06Shell.root;

  // --------------------------------------------------------
  // FRAME 07: Customer Transactions History
  // --------------------------------------------------------
  const f07Shell = b.appShell({
    name: '07-Customer-Transactions-History',
    x: 1560,
    y: 1060,
    role: 'customer',
    activeLabel: 'Riwayat Transaksi',
    pageTitle: 'Riwayat Transaksi Pelanggan',
    userName: 'Budi Santoso',
    returnRefs: true,
    actions: [{ label: 'Lacak Nota Lain', variant: 'secondary', icon: 'search' }],
    contentFn: (builder, main) => {
      const tblCard = builder.frame({
        name: 'TableCard',
        parent: main,
        width: 1136,
        height: 750,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 24, layout: 'vertical', gap: 16 },
      });

      const fRow = builder.frame({ name: 'FRow', parent: tblCard, width: 1088, height: 44, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
      builder.inputField({ parent: fRow, width: 360, height: 40, placeholder: 'Cari kode nota atau layanan...', icon: 'search' });
      builder.badge({ parent: fRow, label: 'Menampilkan 12 Riwayat Transaksi', tone: 'slate', width: 220 });

      const th = builder.frame({ name: 'TH', parent: tblCard, width: 1088, height: 40, props: { fill: COLORS.slate50, cornerRadius: 8, layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16] } });
      const cols = [{ name: 'Kode Nota', w: 180 }, { name: 'Tanggal', w: 160 }, { name: 'Paket Layanan', w: 260 }, { name: 'Berat', w: 100 }, { name: 'Total', w: 140 }, { name: 'Status Cucian', w: 130 }, { name: 'Pembayaran', w: 118 }];
      cols.forEach(c => builder.text({ name: c.name, parent: th, text: c.name, props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate600 }, width: c.w }));

      const rows = [
        { code: 'TRX-20261024-0001', date: '24 Okt 2026, 10:00', srv: 'Cuci Kering Setrika Reguler', w: '4.0 Kg', total: 'Rp 34.000', st: 'Diproses', pay: 'Belum Lunas' },
        { code: 'TRX-20261020-0012', date: '20 Okt 2026, 09:30', srv: 'Kilat Express 24 Jam', w: '3.5 Kg', total: 'Rp 35.000', st: 'Selesai', pay: 'Lunas' },
        { code: 'TRX-20261015-0005', date: '15 Okt 2026, 14:15', srv: 'Setrika Rapi Saja', w: '5.0 Kg', total: 'Rp 20.000', st: 'Selesai', pay: 'Lunas' },
      ];
      rows.forEach((r, idx) => {
        const tr = builder.frame({ name: `TR-${idx}`, parent: tblCard, width: 1088, height: 50, props: { stroke: COLORS.slate100, strokeWeight: 1, layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16] } });
        builder.text({ name: 'C', parent: tr, text: r.code, props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.brand700 }, width: 180 });
        builder.text({ name: 'D', parent: tr, text: r.date, props: { fontSize: 12, fill: COLORS.slate600 }, width: 160 });
        builder.text({ name: 'S', parent: tr, text: r.srv, props: { fontSize: 13, fill: COLORS.slate800 }, width: 260 });
        builder.text({ name: 'W', parent: tr, text: r.w, props: { fontSize: 12, fill: COLORS.slate600 }, width: 100 });
        builder.text({ name: 'T', parent: tr, text: r.total, props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate900 }, width: 140 });
        builder.badge({ parent: tr, label: r.st, tone: r.st === 'Selesai' ? 'success' : 'brand', width: 110 });
        builder.badge({ parent: tr, label: r.pay, tone: r.pay === 'Lunas' ? 'success' : 'warning', width: 90 });
      });
    },
  });
  const f07 = f07Shell.root;

  // --------------------------------------------------------
  // FRAME 08: Customer Complaints Index (1:1 customer/complaints/index.blade.php)
  // --------------------------------------------------------
  let btnCompIdxCreate, btnCompIdxDetail0;
  const f08Shell = b.appShell({
    name: '08-Customer-Complaints-Index',
    x: 3080,
    y: 1060,
    role: 'customer',
    activeLabel: 'Pengaduan',
    pageTitle: 'Daftar Pengaduan Pelanggan',
    userName: 'Budi Santoso',
    returnRefs: true,
    actions: [{ label: 'Ajukan Pengaduan', variant: 'primary', icon: 'plus', width: 170 }],
    contentFn: (builder, main, { actionRefs }) => {
      btnCompIdxCreate = actionRefs[0];

      // 3-Column Monolithic Stat Strip (Total, Sedang Ditangani, Terselesaikan)
      const stat3 = builder.frame({
        name: 'ComplaintStatStrip',
        parent: main,
        width: 1136,
        height: 106,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, layout: 'horizontal' },
      });
      const cStats = [
        { l: 'Total pengaduan', v: '3', s: 'Arsip riwayat seluruh laporan Anda', fill: COLORS.slate900 },
        { l: 'Sedang ditangani', v: '1', s: '0 Menunggu • 1 Diproses', fill: COLORS.amber800 },
        { l: 'Terselesaikan', v: '2', s: 'Pengaduan tuntas diselesaikan', fill: COLORS.emerald700 },
      ];
      cStats.forEach((st, i) => {
        const col = builder.frame({
          name: `Stat-${i}`,
          parent: stat3,
          width: 378,
          height: 106,
          props: { padding: 18, layout: 'vertical', gap: 6, stroke: i < 2 ? COLORS.slate200 : 'none', strokeWeight: i < 2 ? 1 : 0 },
        });
        builder.text({ name: 'L', parent: col, text: st.l, props: { fontSize: 13, fontWeight: 'medium', fill: COLORS.slate600 } });
        builder.text({ name: 'V', parent: col, text: st.v, props: { fontSize: 28, fontWeight: 'bold', fill: st.fill } });
        builder.text({ name: 'S', parent: col, text: st.s, props: { fontSize: 11, fill: COLORS.slate500 } });
      });

      // Filter & Search Bar
      const fltCard = builder.frame({
        name: 'FilterBar',
        parent: main,
        width: 1136,
        height: 64,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: [12, 16, 12, 16], layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' },
      });
      const tabs = builder.frame({ name: 'Tabs', parent: fltCard, width: 460, height: 36, props: { layout: 'horizontal', gap: 8, crossAlign: 'center' } });
      builder.badge({ parent: tabs, label: 'Semua (3)', tone: 'brand', width: 95, height: 34 });
      builder.badge({ parent: tabs, label: 'Menunggu (0)', tone: 'slate', width: 110, height: 34 });
      builder.badge({ parent: tabs, label: 'Diproses (1)', tone: 'slate', width: 105, height: 34 });
      builder.badge({ parent: tabs, label: 'Selesai (2)', tone: 'slate', width: 100, height: 34 });

      const sRow = builder.frame({ name: 'SearchRow', parent: fltCard, width: 400, height: 38, props: { layout: 'horizontal', gap: 8, crossAlign: 'center' } });
      builder.inputField({ parent: sRow, width: 310, height: 38, placeholder: 'Cari kode tiket, subjek, atau nota...', icon: 'search' });
      builder.button({ parent: sRow, label: 'Cari', variant: 'primary', width: 70, height: 36 });

      // Table Card
      const tbl = builder.frame({
        name: 'ComplaintsTable',
        parent: main,
        width: 1136,
        height: 560,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 20, layout: 'vertical', gap: 12 },
      });

      const th = builder.frame({ name: 'TH', parent: tbl, width: 1096, height: 38, props: { fill: COLORS.slate50, cornerRadius: 8, layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16] } });
      [
        { n: 'Tiket & Nota Transaksi', w: 220 },
        { n: 'Subjek & Uraian Keluhan', w: 410 },
        { n: 'Status', w: 130 },
        { n: 'Target SLA (Maks 2 Hari)', w: 196 },
        { n: 'Aksi', w: 108 },
      ].forEach(c => builder.text({ name: c.n, parent: th, text: c.n, props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate500 }, width: c.w }));

      const cRows = [
        {
          code: 'CMP-20261024-001',
          trx: 'TRX-20261024-0001 • 24 Okt 2026',
          subj: 'Pakaian Kelunturan Warna Biru pada Kerah Kemeja Putih',
          desc: 'Saat bungkusan dibuka, terdapat noda biru muda di bagian kerah kemeja putih kerja.',
          replied: true,
          status: 'Diproses',
          tone: 'brand',
          sla: '26 Okt 2026, 14:00 WIB',
          slaSub: 'Maks 1 hari lagi',
        },
        {
          code: 'CMP-20261012-004',
          trx: 'TRX-20261010-0018 • 12 Okt 2026',
          subj: 'Kancing Lengan Kemeja Batik Terlepas Satu Buah',
          desc: 'Kancing cadangan sudah dijahit kembali oleh tim penjahit workshop CUCI.IN.',
          replied: true,
          status: 'Selesai',
          tone: 'success',
          sla: '14 Okt 2026, 10:00 WIB',
          slaSub: 'Tuntas Selesai',
        },
        {
          code: 'CMP-20260928-002',
          trx: 'TRX-20260926-0009 • 28 Sep 2026',
          subj: 'Lipatan Celana Kain Kurang Presisi',
          desc: 'Sudah dilakukan setrika uap ulang gratis dan diantar kembali ke alamat.',
          replied: true,
          status: 'Selesai',
          tone: 'success',
          sla: '30 Sep 2026, 16:00 WIB',
          slaSub: 'Tuntas Selesai',
        },
      ];

      cRows.forEach((r, idx) => {
        const tr = builder.frame({
          name: `CompRow-${idx}`,
          parent: tbl,
          width: 1096,
          height: 84,
          props: { stroke: COLORS.slate100, strokeWeight: 1, layout: 'horizontal', crossAlign: 'center', padding: [12, 16, 12, 16] },
        });
        const col1 = builder.frame({ name: 'C1', parent: tr, width: 220, height: 56, props: { layout: 'vertical', gap: 4 } });
        builder.text({ name: 'Code', parent: col1, text: r.code, props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.brand700 } });
        builder.text({ name: 'Trx', parent: col1, text: r.trx, props: { fontSize: 11, fill: COLORS.slate500 } });

        const col2 = builder.frame({ name: 'C2', parent: tr, width: 410, height: 60, props: { layout: 'vertical', gap: 4 } });
        builder.text({ name: 'Subj', parent: col2, text: r.subj, props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate900 }, width: 390 });
        builder.text({ name: 'Desc', parent: col2, text: r.desc, props: { fontSize: 11, fill: COLORS.slate500 }, width: 390 });
        if (r.replied) builder.badge({ parent: col2, label: 'Telah ditanggapi staf', tone: 'success', width: 140, height: 20 });

        const col3 = builder.frame({ name: 'C3', parent: tr, width: 130, height: 40, props: { layout: 'horizontal', crossAlign: 'center' } });
        builder.badge({ parent: col3, label: r.status, tone: r.tone, width: 96, height: 26 });

        const col4 = builder.frame({ name: 'C4', parent: tr, width: 196, height: 44, props: { layout: 'vertical', gap: 3 } });
        builder.text({ name: 'SLA', parent: col4, text: r.sla, props: { fontSize: 12, fontWeight: 'medium', fill: COLORS.slate700 } });
        builder.text({ name: 'SLASub', parent: col4, text: r.slaSub, props: { fontSize: 11, fontWeight: 'bold', fill: r.status === 'Selesai' ? COLORS.emerald700 : COLORS.amber800 } });

        const btn = builder.button({ parent: tr, label: 'Rincian', variant: 'secondary', icon: 'arrow-right', width: 100, height: 32 });
        if (idx === 0) btnCompIdxDetail0 = btn;
      });
    },
  });
  const f08 = f08Shell.root;

  // --------------------------------------------------------
  // FRAME 08b: Customer Complaints Create (1:1 customer/complaints/create.blade.php)
  // --------------------------------------------------------
  let btnCompCreateBack, btnCompCreateCancel, btnCompCreateSubmit;
  const f08bShell = b.appShell({
    name: '08b-Customer-Complaints-Create',
    x: 4600,
    y: 1060,
    role: 'customer',
    activeLabel: 'Pengaduan',
    pageTitle: 'Formulir Pengaduan Pelanggan',
    userName: 'Budi Santoso',
    returnRefs: true,
    actions: [{ label: 'Kembali', variant: 'secondary', icon: 'arrow-left', width: 110 }],
    contentFn: (builder, main, { actionRefs }) => {
      btnCompCreateBack = actionRefs[0];

      // SLA Commitment Banner
      const slaBanner = builder.frame({
        name: 'SLABanner',
        parent: main,
        width: 1136,
        height: 68,
        props: { fill: COLORS.brand50, stroke: COLORS.brand200, strokeWeight: 1, cornerRadius: 14, padding: [12, 16, 12, 16], layout: 'horizontal', gap: 12, crossAlign: 'center' },
      });
      builder.vector({ name: 'Ico', parent: slaBanner, width: 22, height: 22, path: ICONS['shield-check'], props: { stroke: COLORS.brand700 } });
      const slaTxt = builder.frame({ name: 'Txt', parent: slaBanner, width: 1040, height: 44, props: { layout: 'vertical', gap: 2 } });
      builder.text({ name: 'T1', parent: slaTxt, text: 'Komitmen Layanan & Batas Waktu (SLA 2 Hari Kerja)', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.brand900 } });
      builder.text({
        name: 'T2',
        parent: slaTxt,
        text: 'Setiap laporan pengaduan wajib terhubung ke nomor nota transaksi yang sah dan dijamin tuntas diselesaikan maksimal dalam 2 hari kerja oleh tim operasional workshop CUCI.IN.',
        props: { fontSize: 11, fill: COLORS.brand800 },
      });

      // Form Card
      const formCard = builder.frame({
        name: 'FormCard',
        parent: main,
        width: 1136,
        height: 690,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 22, layout: 'vertical', gap: 12 },
      });

      builder.inputField({
        parent: formCard,
        width: 1080,
        height: 40,
        label: '1. Pilih Nota Transaksi *',
        value: 'TRX-20261024-0001 — 24 Okt 2026 • Diproses (Cuci Kering Setrika Reguler)',
        icon: 'receipt',
        mono: true,
      });

      builder.inputField({
        parent: formCard,
        width: 1080,
        height: 40,
        label: '2. Subjek / Judul Kendala * (Minimal 5 karakter)',
        value: 'Pakaian Kelunturan Warna Biru pada Kerah Kemeja Putih',
      });

      // Textarea Description
      const descGroup = builder.frame({ name: 'DescGroup', parent: formCard, width: 1080, height: 125, props: { layout: 'vertical', gap: 6 } });
      builder.text({ name: 'Lbl', parent: descGroup, text: '3. Rincian Kendala & Kronologi Lengkap *', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate700 } });
      const descBox = builder.frame({
        name: 'TextareaBox',
        parent: descGroup,
        width: 1080,
        height: 95,
        props: { fill: COLORS.white, stroke: COLORS.slate300, strokeWeight: 1, cornerRadius: 12, padding: 12, layout: 'vertical', gap: 4 },
      });
      builder.text({
        name: 'Val',
        parent: descBox,
        text: 'Saat bungkusan laundry saya periksa di rumah, terdapat bercak kelunturan warna biru muda pada kerah kemeja putih lengan panjang merk Alisan. Mohon dibantu pencucian ulang khusus penghilang noda luntur.',
        props: { fontSize: 12, fill: COLORS.slate800 },
        width: 1040,
      });

      // Photo Upload Dropzone & Live Preview (P9 Queue Compression Notice)
      const upGroup = builder.frame({ name: 'UploadGroup', parent: formCard, width: 1080, height: 170, props: { layout: 'vertical', gap: 6 } });
      builder.text({ name: 'ULbl', parent: upGroup, text: '4. Foto Bukti Pakaian Kendala / Noda (Opsional — JPG, PNG, WEBP maks. 5 MB)', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate700 } });

      const dropzone = builder.frame({
        name: 'DropzonePreview',
        parent: upGroup,
        width: 1080,
        height: 135,
        props: { fill: COLORS.slate50, stroke: COLORS.brand300, strokeWeight: 1, cornerRadius: 14, padding: 16, layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' },
      });
      const dzLeft = builder.frame({ name: 'DZLeft', parent: dropzone, width: 780, height: 100, props: { layout: 'horizontal', gap: 16, crossAlign: 'center' } });
      const thumb = builder.frame({
        name: 'PhotoThumb',
        parent: dzLeft,
        width: 100,
        height: 100,
        props: { fill: COLORS.brand100, stroke: COLORS.brand300, strokeWeight: 1, cornerRadius: 12, layout: 'vertical', mainAlign: 'center', crossAlign: 'center', gap: 4 },
      });
      builder.vector({ name: 'Ico', parent: thumb, width: 24, height: 24, path: ICONS.eye, props: { stroke: COLORS.brand700 } });
      builder.text({ name: 'T', parent: thumb, text: 'kemeja_noda.jpg', props: { fontSize: 10, fontWeight: 'bold', fill: COLORS.brand800 } });

      const fMeta = builder.frame({ name: 'FileMeta', parent: dzLeft, width: 620, height: 80, props: { layout: 'vertical', gap: 4 } });
      builder.text({ name: 'FN', parent: fMeta, text: 'bukti_kerah_kemeja_putih_luntur.jpg (1.84 MB)', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.text({ name: 'FS', parent: fMeta, text: 'Resolusi 1600x1200 px • Akan dikompresi otomatis di latar belakang via Queue Job (ProcessComplaintPhotoJob).', props: { fontSize: 11, fill: COLORS.slate600 } });
      builder.badge({ parent: fMeta, label: 'Foto Siap Diunggah', tone: 'success', width: 140, height: 24 });

      builder.button({ parent: dropzone, label: 'Ganti Foto', variant: 'secondary', width: 120, height: 36 });

      // Footer Actions
      const fFoot = builder.frame({
        name: 'FormFooter',
        parent: formCard,
        width: 1080,
        height: 48,
        props: { stroke: COLORS.slate100, strokeWeight: 1, layout: 'horizontal', mainAlign: 'max', crossAlign: 'center', gap: 12, padding: [8, 0, 0, 0] },
      });
      btnCompCreateCancel = builder.button({ parent: fFoot, label: 'Batal', variant: 'secondary', width: 110, height: 40 });
      btnCompCreateSubmit = builder.button({ parent: fFoot, label: 'Kirim Pengaduan', variant: 'primary', icon: 'check', width: 180, height: 40 });
    },
  });
  const f08b = f08bShell.root;

  // --------------------------------------------------------
  // FRAME 09: Customer Complaint Detail (1:1 customer/complaints/show.blade.php)
  // --------------------------------------------------------
  let btnCompShowBack, btnCompShowNota;
  const f09Shell = b.appShell({
    name: '09-Customer-Complaint-Detail',
    x: 6120,
    y: 1060,
    role: 'customer',
    activeLabel: 'Pengaduan',
    pageTitle: 'Rincian Pengaduan — CMP-20261024-001',
    userName: 'Budi Santoso',
    returnRefs: true,
    actions: [{ label: 'Kembali ke Daftar', variant: 'secondary', icon: 'arrow-left', width: 160 }],
    contentFn: (builder, main, { actionRefs }) => {
      btnCompShowBack = actionRefs[0];

      // 1. Signature 3-Step Complaint Progress Stepper Card
      const stepCard = builder.frame({
        name: 'ComplaintStepperCard',
        parent: main,
        width: 1136,
        height: 104,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 14, padding: [12, 20, 12, 20], layout: 'vertical', gap: 8 },
      });
      const stHead = builder.frame({ name: 'SH', parent: stepCard, width: 1096, height: 22, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
      builder.text({ name: 'ST', parent: stHead, text: 'TAHAPAN PENANGANAN TIKET KELUHAN (CMP-20261024-001)', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate500 } });
      builder.badge({ parent: stHead, label: 'Sedang Diproses', tone: 'brand', width: 130 });

      const stepRow = builder.frame({ name: 'StepRow', parent: stepCard, width: 1096, height: 56, props: { layout: 'horizontal', gap: 14 } });
      const steps = [
        { num: '1', title: 'Laporan Diajukan', sub: '24 Okt 2026, 14:10 WIB (Tuntas)', tone: COLORS.emerald50, border: COLORS.emerald500, txt: COLORS.emerald700 },
        { num: '2', title: 'Pemeriksaan Workshop', sub: 'Sedang ditangani oleh Daysyani', tone: COLORS.brand50, border: COLORS.brand600, txt: COLORS.brand800 },
        { num: '3', title: 'Solusi Diberikan', sub: 'Target: 26 Okt 2026, 14:10 WIB', tone: COLORS.slate50, border: COLORS.slate200, txt: COLORS.slate500 },
      ];
      steps.forEach((s, i) => {
        const sc = builder.frame({
          name: `Step-${i}`,
          parent: stepRow,
          width: 356,
          height: 54,
          props: { fill: s.tone, stroke: s.border, strokeWeight: i === 1 ? 2 : 1, cornerRadius: 10, padding: [8, 12, 8, 12], layout: 'horizontal', gap: 10, crossAlign: 'center' },
        });
        const badgeCircle = builder.frame({ name: 'Num', parent: sc, width: 28, height: 28, props: { fill: s.border, cornerRadius: 14, layout: 'horizontal', mainAlign: 'center', crossAlign: 'center' } });
        builder.text({ name: 'N', parent: badgeCircle, text: s.num, props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.white } });
        const meta = builder.frame({ name: 'M', parent: sc, width: 280, height: 36, props: { layout: 'vertical', gap: 1 } });
        builder.text({ name: 'T', parent: meta, text: s.title, props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate900 } });
        builder.text({ name: 'S', parent: meta, text: s.sub, props: { fontSize: 10, fill: s.txt } });
      });

      // 2. 2-Column Content Grid
      const grid = builder.frame({ name: 'DetailGrid', parent: main, width: 1136, height: 640, props: { layout: 'horizontal', gap: 16 } });

      // Left Column
      const leftCol = builder.frame({ name: 'LeftCol', parent: grid, width: 660, height: 640, props: { layout: 'vertical', gap: 14 } });

      const issueCard = builder.frame({
        name: 'IssueDetailCard',
        parent: leftCol,
        width: 660,
        height: 360,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 14, padding: 18, layout: 'vertical', gap: 10 },
      });
      builder.text({ name: 'H', parent: issueCard, text: 'Rincian Pengaduan Pelanggan', props: { fontSize: 15, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.text({ name: 'SLbl', parent: issueCard, text: 'SUBJEK KELUHAN', props: { fontSize: 10, fontWeight: 'bold', fill: COLORS.slate500 } });
      builder.text({ name: 'SVal', parent: issueCard, text: 'Pakaian Kelunturan Warna Biru pada Kerah Kemeja Putih', props: { fontSize: 14, fontWeight: 'bold', fill: COLORS.slate900 } });

      builder.text({ name: 'DLbl', parent: issueCard, text: 'URAIAN MASALAH / KENDALA', props: { fontSize: 10, fontWeight: 'bold', fill: COLORS.slate500 } });
      const descBox = builder.frame({
        name: 'DescBox',
        parent: issueCard,
        width: 624,
        height: 64,
        props: { fill: COLORS.slate50, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 10, padding: 10, layout: 'vertical' },
      });
      builder.text({
        name: 'DTxt',
        parent: descBox,
        text: 'Saat bungkusan laundry saya periksa di rumah, terdapat bercak kelunturan warna biru muda pada kerah kemeja putih lengan panjang merk Alisan. Mohon dibantu pencucian ulang khusus penghilang noda luntur.',
        props: { fontSize: 11, fill: COLORS.slate800 },
        width: 604,
      });

      builder.text({ name: 'PLbl', parent: issueCard, text: 'BUKTI FOTO KENDALA / PAKAIAN', props: { fontSize: 10, fontWeight: 'bold', fill: COLORS.slate500 } });
      const photoBox = builder.frame({
        name: 'PhotoAttachment',
        parent: issueCard,
        width: 624,
        height: 110,
        props: { fill: COLORS.slate100, stroke: COLORS.slate300, strokeWeight: 1, cornerRadius: 10, padding: 14, layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' },
      });
      const pInfo = builder.frame({ name: 'PI', parent: photoBox, width: 430, height: 75, props: { layout: 'vertical', gap: 4 } });
      builder.text({ name: 'PT', parent: pInfo, text: 'Foto Lampiran: bukti_kerah_kemeja_putih_luntur.webp', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.text({ name: 'PS', parent: pInfo, text: 'Terverifikasi oleh sistem • Resolusi dioptimalkan (420 KB)', props: { fontSize: 11, fill: COLORS.slate600 } });
      builder.badge({ parent: pInfo, label: 'Otorisasi Policy: ComplaintPolicy::view', tone: 'brand', width: 235 });
      builder.button({ parent: photoBox, label: 'Lihat Ukuran Penuh', variant: 'secondary', icon: 'eye', width: 145, height: 32 });

      // Staff Response Card
      const respCard = builder.frame({
        name: 'StaffResponseCard',
        parent: leftCol,
        width: 660,
        height: 264,
        props: { fill: COLORS.emerald50, stroke: COLORS.emerald500, strokeWeight: 1, cornerRadius: 14, padding: 18, layout: 'vertical', gap: 8 },
      });
      const rHead = builder.frame({ name: 'RH', parent: respCard, width: 624, height: 24, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
      builder.text({ name: 'RT', parent: rHead, text: 'Tanggapan Resmi Tim Staf CUCI.IN', props: { fontSize: 14, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.text({ name: 'RD', parent: rHead, text: 'Ditangani: 24 Okt 2026, 16:30 WIB', props: { fontSize: 11, fill: COLORS.emerald700, fontWeight: 'bold' } });

      const rBody = builder.frame({
        name: 'RBody',
        parent: respCard,
        width: 624,
        height: 120,
        props: { fill: COLORS.white, stroke: COLORS.emerald100, strokeWeight: 1, cornerRadius: 10, padding: 12, layout: 'vertical', gap: 4 },
      });
      builder.text({
        name: 'RText',
        parent: rBody,
        text: 'Halo Bapak Budi Santoso, mohon maaf atas ketidaknyamanan yang dialami. Kemeja putih Anda telah kami jemput dan saat ini sedang melalui proses spotting chemical khusus penghilang kelunturan warna di workshop. Pakaian akan diantar kembali besok pagi pukul 09:00 WIB tanpa biaya tambahan.',
        props: { fontSize: 11, fill: COLORS.slate800 },
        width: 600,
      });
      builder.text({ name: 'Handler', parent: respCard, text: 'Ditindaklanjuti oleh: Daysyani Sophi Masayu (Staf Operasional)', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.emerald700 } });

      // Right Column: SLA & Linked Transaction
      const rightCol = builder.frame({ name: 'RightCol', parent: grid, width: 456, height: 640, props: { layout: 'vertical', gap: 14 } });

      const slaCard = builder.frame({
        name: 'SLACommitmentCard',
        parent: rightCol,
        width: 456,
        height: 180,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 14, padding: 18, layout: 'vertical', gap: 8 },
      });
      builder.text({ name: 'SL1', parent: slaCard, text: 'WAKTU PENGAJUAN', props: { fontSize: 10, fontWeight: 'bold', fill: COLORS.slate500 } });
      builder.text({ name: 'SV1', parent: slaCard, text: '24 Oktober 2026, 14:10 WIB (2 jam yang lalu)', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.text({ name: 'SL2', parent: slaCard, text: 'TARGET SLA (MAKSIMAL 2 HARI KERJA)', props: { fontSize: 10, fontWeight: 'bold', fill: COLORS.slate500 } });
      builder.text({ name: 'SV2', parent: slaCard, text: '26 Oktober 2026, 14:10 WIB', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.badge({ parent: slaCard, label: 'Target SLA: Sisa 1 hari 21 jam', tone: 'warning', width: 220, height: 26 });

      const trxCard = builder.frame({
        name: 'LinkedTransactionCard',
        parent: rightCol,
        width: 456,
        height: 444,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 14, padding: 18, layout: 'vertical', gap: 10 },
      });
      const trxHead = builder.frame({ name: 'TH', parent: trxCard, width: 420, height: 38, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
      const trxTitleCol = builder.frame({ name: 'TTC', parent: trxHead, width: 240, height: 36, props: { layout: 'vertical', gap: 2 } });
      builder.text({ name: 'TL', parent: trxTitleCol, text: 'NOTA TRANSAKSI TERKAIT', props: { fontSize: 10, fontWeight: 'bold', fill: COLORS.slate500 } });
      builder.text({ name: 'TC', parent: trxTitleCol, text: 'TRX-20261024-0001', props: { fontSize: 15, fontWeight: 'bold', fill: COLORS.brand700 } });
      btnCompShowNota = builder.button({ parent: trxHead, label: 'Buka Nota', variant: 'secondary', icon: 'arrow-right', width: 110, height: 32 });

      const metaRows = [
        ['Tanggal Transaksi', '24 Okt 2026'],
        ['Status Cucian', 'Sedang Diproses'],
        ['Estimasi Selesai', '25 Okt 2026, 17:00 WIB'],
        ['Total Tagihan', 'Rp 34.000'],
      ];
      metaRows.forEach(([k, v], idx) => {
        const mr = builder.frame({ name: `MR-${idx}`, parent: trxCard, width: 420, height: 24, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
        builder.text({ name: 'K', parent: mr, text: k, props: { fontSize: 11, fill: COLORS.slate500 } });
        builder.text({ name: 'V', parent: mr, text: v, props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate900 } });
      });

      builder.text({ name: 'ILbl', parent: trxCard, text: 'ITEM LAYANAN DALAM NOTA', props: { fontSize: 10, fontWeight: 'bold', fill: COLORS.slate500 } });
      builder.text({ name: 'I1', parent: trxCard, text: '• Cuci Kering Setrika Reguler (4.0 kg) — Rp 28.000', props: { fontSize: 11, fill: COLORS.slate700 } });
      builder.text({ name: 'I2', parent: trxCard, text: '• Pewangi Premium Anti-Apek (1 paket) — Rp 6.000', props: { fontSize: 11, fill: COLORS.slate700 } });
    },
  });
  const f09 = f09Shell.root;

  // --------------------------------------------------------
  // PROTOTYPING WIRES: ALUR MANDIRI PELANGGAN (TERHUBUNG KE ALUR UTAMA)
  // --------------------------------------------------------
  if (publicAuthRefs.btnHeroCust) {
    b.wire({ id: publicAuthRefs.btnHeroCust, dest: f06 });
  }
  if (publicAuthRefs.btnLoginSubmit) {
    b.wire({ id: publicAuthRefs.btnLoginSubmit, dest: f06 });
  }
  if (publicAuthRefs.btnChipCustomer) {
    b.wire({ id: publicAuthRefs.btnChipCustomer, dest: f06 });
  }
  if (publicAuthRefs.f02) {
    if (btnCustLookup) b.wire({ id: btnCustLookup, dest: publicAuthRefs.f02 });
    if (btnCustViewNote) b.wire({ id: btnCustViewNote, dest: publicAuthRefs.f02 });
    if (f07Shell.actionRefs[0]) b.wire({ id: f07Shell.actionRefs[0], dest: publicAuthRefs.f02 });
  }

  // Dashboard (06) <-> History (07) <-> Complaints (08 -> 08b -> 09)
  if (btnCustAllHistory) b.wire({ id: btnCustAllHistory, dest: f07 });
  if (btnSecViewAll) b.wire({ id: btnSecViewAll, dest: f07 });
  if (btnCustCompBanner) b.wire({ id: btnCustCompBanner, dest: f09 });

  // Sidebar navigation across ALL Customer screens (06, 07, 08, 08b, 09)
  const custShells = [f06Shell, f07Shell, f08Shell, f08bShell, f09Shell];
  custShells.forEach((sh) => {
    if (sh.navRefs['Beranda']) b.wire({ id: sh.navRefs['Beranda'], dest: f06 });
    if (sh.navRefs['Riwayat Transaksi']) b.wire({ id: sh.navRefs['Riwayat Transaksi'], dest: f07 });
    if (sh.navRefs['Pengaduan']) b.wire({ id: sh.navRefs['Pengaduan'], dest: f08 });
    if (sh.navRefs['Lacak Nota Fisik'] && publicAuthRefs.f02) {
      b.wire({ id: sh.navRefs['Lacak Nota Fisik'], dest: publicAuthRefs.f02 });
    }
    if (sh.logoutRef && publicAuthRefs.f01) {
      b.wire({ id: sh.logoutRef, dest: publicAuthRefs.f01 });
    }
  });

  if (btnCompIdxCreate) b.wire({ id: btnCompIdxCreate, dest: f08b });
  if (btnCompIdxDetail0) b.wire({ id: btnCompIdxDetail0, dest: f09 });
  if (btnCompCreateBack) b.wire({ id: btnCompCreateBack, dest: f08 });
  if (btnCompCreateCancel) b.wire({ id: btnCompCreateCancel, dest: f08 });
  if (btnCompCreateSubmit) b.wire({ id: btnCompCreateSubmit, dest: f09 });
  if (btnCompShowBack) b.wire({ id: btnCompShowBack, dest: f08 });
  if (btnCompShowNota) b.wire({ id: btnCompShowNota, dest: f07 });

  return { f06, f07, f08, f08b, f09, btnCustLookup, btnCustViewNote };
}
