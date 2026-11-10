import { COLORS, ICONS } from '../tokens.mjs';

export function buildStaffScreens(b, publicAuthRefs = {}) {
  let btnDashPos, btnDashStatus0, btnPosCheckout, btnPosList;
  let btnManagePos, btnManageDetail0, btnVerifApprove, btnVerifReject, btnCustCreatePos0, btnCompReply0;

  // --- Frame 10: Staff Dashboard ---
  const f10Shell = b.appShell({
    name: '10-Staff-Dashboard',
    x: 40,
    y: 2080,
    role: 'staff',
    activeLabel: 'Dashboard',
    pageTitle: 'Dashboard Kasir & Operasional',
    userName: 'Maulana Ahmad',
    returnRefs: true,
    actions: [
      { label: 'Cek Nota', variant: 'secondary', icon: 'search' },
      { label: 'Buka Kasir POS', variant: 'primary', icon: 'plus-circle', kbd: 'F2', width: 170 },
    ],
    contentFn: (builder, main, { actionRefs }) => {
      btnDashPos = actionRefs[1];
      // 1. Header Banner
      const bnr = builder.frame({
        name: 'MejaKasirHeader',
        parent: main,
        width: 1136,
        height: 64,
        props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' },
      });
      const tCol = builder.frame({ name: 'TCol', parent: bnr, width: 600, height: 50, props: { layout: 'vertical', gap: 4 } });
      builder.text({ name: 'H1', parent: tCol, text: 'Meja kasir & Antrean Cucian Aktif', props: { fontSize: 24, fontWeight: 'bold' } });
      builder.text({ name: 'H2', parent: tCol, text: 'Senin, 09 November 2026 / Maulana Ahmad (Staf Kasir Shift 1)', props: { fontSize: 13, fill: COLORS.slate600 } });

      // 2. Monolithic Stat Strip
      builder.monolithicStatStrip({
        parent: main,
        x: 0,
        y: 80,
        width: 1136,
        height: 110,
        stats: [
          { label: 'Nota masuk hari ini', value: '18', unit: 'Nota', sub: 'Transaksi baru hari ini' },
          { label: 'Perlu dikerjakan', value: '7', unit: 'Nota', sub: 'Diterima & diproses', tone: 'brand' },
          { label: 'Siap diambil', value: '5', unit: 'Nota', sub: 'Menunggu pelanggan', tone: 'success' },
          { label: 'Terlambat', value: '2', unit: 'Nota', sub: 'Dahulukan pengerjaannya', tone: 'danger' },
        ],
      });

      // 3. Queue Card
      const qCard = builder.frame({
        name: 'QueueCard',
        parent: main,
        x: 0,
        y: 206,
        width: 1136,
        height: 580,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 24, layout: 'vertical', gap: 16 },
      });

      const qHead = builder.frame({ name: 'QHead', parent: qCard, width: 1088, height: 36, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
      builder.text({ name: 'QTitle', parent: qHead, text: 'Antrean Prioritas Pengerjaan (Dahulukan Estimasi Mendekati/Terlewat)', props: { fontSize: 16, fontWeight: 'bold' } });
      builder.badge({ parent: qHead, label: '2 Cucian Terlambat', tone: 'danger', width: 140 });

      // Queue Table Headers
      const th = builder.frame({ name: 'TH', parent: qCard, width: 1088, height: 36, props: { fill: COLORS.slate50, cornerRadius: 8, layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16] } });
      [{ n: 'Nota', w: 160 }, { n: 'Pelanggan', w: 180 }, { n: 'Layanan', w: 260 }, { n: 'Estimasi Selesai', w: 200 }, { n: 'Status Cucian', w: 140 }, { n: 'Aksi Cepat', w: 148 }].forEach(c => {
        builder.text({ name: c.n, parent: th, text: c.n, props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate600 }, width: c.w });
      });

      // Sample Queue Rows
      const qRows = [
        { code: 'TRX-20261109-0038', name: 'Dewi Lestari', srv: 'Cuci Setrika Express (24 Jam)', est: 'Hari ini, 10:00 (Terlewat)', st: 'Diterima', late: true },
        { code: 'TRX-20261109-0042', name: 'Budi Santoso', srv: 'Cuci Komplit Kilat (6 Jam)', est: 'Hari ini, 18:00 WIB', st: 'Diproses', late: false },
        { code: 'TRX-20261109-0040', name: 'Ahmad Fauzi', srv: 'Cuci Lipat Reguler (48 Jam)', est: '11 Nov 2026, 12:00 WIB', st: 'Diterima', late: false },
        { code: 'TRX-20261108-0031', name: 'Siti Aminah', srv: 'Bedcover Besar (Satuan)', est: 'Hari ini, 14:00 (Siap Ambil)', st: 'Selesai', late: false },
      ];
      qRows.forEach((r, idx) => {
        const tr = builder.frame({ name: `QRow-${idx}`, parent: qCard, width: 1088, height: 48, props: { stroke: COLORS.slate100, strokeWeight: 1, layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16] } });
        builder.text({ name: 'C', parent: tr, text: r.code, props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.brand700 }, width: 160 });
        builder.text({ name: 'N', parent: tr, text: r.name, props: { fontSize: 13, fill: COLORS.slate900 }, width: 180 });
        builder.text({ name: 'S', parent: tr, text: r.srv, props: { fontSize: 13, fill: COLORS.slate800 }, width: 260 });
        builder.text({ name: 'E', parent: tr, text: r.est, props: { fontSize: 12, fill: r.late ? COLORS.rose700 : COLORS.slate600, fontWeight: r.late ? 'bold' : 'normal' }, width: 200 });
        builder.badge({ parent: tr, label: r.st, tone: r.st === 'Selesai' ? 'success' : 'brand', width: 120 });
        const bAct = builder.button({ parent: tr, label: 'Ubah Status', variant: 'secondary', width: 110, height: 32 });
        if (idx === 0) btnDashStatus0 = bAct;
      });
    },
  });
  const f10 = f10Shell.root;

  // --- Frame 11: Staff POS Create ---
  const f11Shell = b.appShell({
    name: '11-Staff-POS-Create',
    x: 1560,
    y: 2080,
    role: 'staff',
    activeLabel: 'Kasir POS',
    pageTitle: 'Kasir Point of Sale (POS) — Transaksi Baru (Protected by EnsureIdempotency UUID)',
    userName: 'Maulana Ahmad',
    returnRefs: true,
    actions: [{ label: 'Daftar Transaksi', variant: 'secondary', icon: 'list', width: 150 }],
    contentFn: (builder, main, { actionRefs }) => {
      btnPosList = actionRefs[0];
      // Shortcut bar
      const sc = builder.frame({
        name: 'ShortcutBar',
        parent: main,
        width: 1136,
        height: 40,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 10, layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16], gap: 16 },
      });
      builder.text({ name: 'STitle', parent: sc, text: 'Pintasan POS:', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.text({ name: 'F1', parent: sc, text: '[F1] / Cari Layanan', props: { fontSize: 12, fill: COLORS.slate600 } });
      builder.text({ name: 'F2', parent: sc, text: '[F2] Pelanggan Baru', props: { fontSize: 12, fill: COLORS.slate600 } });
      builder.text({ name: 'F4', parent: sc, text: '[F4] Tambah Baris', props: { fontSize: 12, fill: COLORS.slate600 } });
      builder.text({ name: 'F9', parent: sc, text: '[F9] Simpan Nota Transaksi', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.brand700 } });

      // Split Screen POS
      const split = builder.frame({
        name: 'SplitPOS',
        parent: main,
        x: 0,
        y: 52,
        width: 1136,
        height: 730,
        props: { layout: 'horizontal', gap: 20 },
      });

      // Left Column (7/12: Catalog)
      const leftCol = builder.frame({
        name: 'CatalogCol',
        parent: split,
        width: 680,
        height: 730,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 20, layout: 'vertical', gap: 14 },
      });
      builder.text({ name: 'CatTitle', parent: leftCol, text: 'Katalog Layanan Cepat (Reguler & Paket Kilat)', props: { fontSize: 16, fontWeight: 'bold' } });
      builder.inputField({ parent: leftCol, width: 640, height: 40, placeholder: 'Ketik nama layanan laundry...', icon: 'search' });

      // Service Cards
      const services = [
        { name: 'Cuci Komplit Kilat (6 Jam)', price: 'Rp 9.000 / Kg', est: '6 Jam', fast: true, selected: true },
        { name: 'Cuci Lipat Reguler (48 Jam)', price: 'Rp 6.000 / Kg', est: '48 Jam', fast: false },
        { name: 'Cuci Setrika Express (24 Jam)', price: 'Rp 8.000 / Kg', est: '24 Jam', fast: false },
        { name: 'Bedcover Sedang (Satuan)', price: 'Rp 25.000 / Pcs', est: '48 Jam', fast: false },
        { name: 'Sepatu Sneaker Deep Clean', price: 'Rp 35.000 / Psg', est: '72 Jam', fast: false },
      ];
      services.forEach((s) => {
        const sCard = builder.frame({
          name: `Srv-${s.name}`,
          parent: leftCol,
          width: 640,
          height: 80,
          props: {
            fill: s.selected ? COLORS.brand50 : COLORS.slate50,
            stroke: s.selected ? COLORS.brand500 : COLORS.slate200,
            strokeWeight: s.selected ? 2 : 1,
            cornerRadius: 12,
            padding: 14,
            layout: 'horizontal',
            mainAlign: 'spaceBetween',
            crossAlign: 'center',
          },
        });
        const sMeta = builder.frame({ name: 'Meta', parent: sCard, width: 440, height: 50, props: { layout: 'vertical', gap: 4 } });
        const sNameRow = builder.frame({ name: 'NR', parent: sMeta, width: 400, height: 22, props: { layout: 'horizontal', gap: 8, crossAlign: 'center' } });
        builder.text({ name: 'Name', parent: sNameRow, text: s.name, props: { fontSize: 14, fontWeight: 'bold' } });
        if (s.fast) builder.badge({ parent: sNameRow, label: 'PAKET KILAT', tone: 'brand' });
        builder.text({ name: 'Price', parent: sMeta, text: `${s.price} • Estimasi ${s.est}`, props: { fontSize: 12, fill: COLORS.slate600 } });

        if (s.selected) {
          const weightBox = builder.frame({ name: 'WBox', parent: sCard, width: 150, height: 44, props: { layout: 'horizontal', crossAlign: 'center', gap: 6 } });
          builder.text({ name: 'WLbl', parent: weightBox, text: 'Berat:', props: { fontSize: 12, fontWeight: 'bold' } });
          builder.badge({ parent: weightBox, label: '5.0 Kg', tone: 'brand', width: 60 });
        } else {
          builder.button({ parent: sCard, label: '+ Tambah', variant: 'secondary', width: 90, height: 32 });
        }
      });

      // Right Column (5/12: Summary & Checkout)
      const rightCol = builder.frame({
        name: 'SummaryCol',
        parent: split,
        width: 436,
        height: 730,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 24, layout: 'vertical', gap: 16 },
      });
      builder.text({ name: 'STitle', parent: rightCol, text: 'Ringkasan Nota & Pembayaran', props: { fontSize: 16, fontWeight: 'bold' } });

      // Customer Directory selector
      builder.inputField({ parent: rightCol, width: 388, height: 40, label: '1. Pilih Data Pelanggan', value: 'Budi Santoso — 0812-9988-7766' });

      // Items list box
      const itemBox = builder.frame({
        name: 'ItemBox',
        parent: rightCol,
        width: 388,
        height: 120,
        props: { fill: COLORS.slate50, cornerRadius: 10, padding: 12, layout: 'vertical', gap: 6 },
      });
      builder.text({ name: 'I1', parent: itemBox, text: '• Cuci Komplit Kilat (5.0 Kg x Rp 9.000) = Rp 45.000', props: { fontSize: 13, fontWeight: 'bold' } });
      builder.text({ name: 'I2', parent: itemBox, text: 'Estimasi Selesai Otomatis: 09 Nov 2026, 18:00 WIB', props: { fontSize: 11, fill: COLORS.brand700 } });

      // Payment Method Chips
      builder.text({ name: 'PMethod', parent: rightCol, text: '2. Metode Pembayaran', props: { fontSize: 12, fontWeight: 'bold' } });
      const pRow = builder.frame({ name: 'PRow', parent: rightCol, width: 388, height: 36, props: { layout: 'horizontal', gap: 8 } });
      builder.badge({ parent: pRow, label: 'Tunai / Cash', tone: 'brand', width: 110 });
      builder.badge({ parent: pRow, label: 'QRIS Statis/Dinamis', tone: 'slate', width: 140 });
      builder.badge({ parent: pRow, label: 'Transfer Bank', tone: 'slate', width: 110 });

      // Total Tagihan Big Box
      const totalBox = builder.frame({
        name: 'TotalBox',
        parent: rightCol,
        width: 388,
        height: 80,
        props: { fill: COLORS.brand50, stroke: COLORS.brand200, strokeWeight: 1, cornerRadius: 12, padding: 16, layout: 'vertical', gap: 4 },
      });
      builder.text({ name: 'TLbl', parent: totalBox, text: 'TOTAL TAGIHAN NOTA', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.brand800 } });
      builder.text({ name: 'TVal', parent: totalBox, text: 'Rp 45.000', props: { fontSize: 26, fontWeight: 'bold', fill: COLORS.brand900 } });

      // Checkout Button
      btnPosCheckout = builder.button({
        parent: rightCol,
        label: 'Simpan Transaksi & Cetak Struk [F9]',
        variant: 'primary',
        icon: 'printer',
        kbd: 'F9',
        width: 388,
        height: 48,
      });
    },
  });
  const f11 = f11Shell.root;

  // --- Frame 12: Staff Transactions Index ---
  const f12Shell = b.appShell({
    name: '12-Staff-Transactions-Manage',
    x: 3080,
    y: 2080,
    role: 'staff',
    activeLabel: 'Kelola Transaksi',
    pageTitle: 'Kelola Status Transaksi & Cetak Struk Thermal',
    userName: 'Maulana Ahmad',
    returnRefs: true,
    actions: [{ label: 'Buka Kasir POS', variant: 'primary', icon: 'plus-circle', kbd: 'F2', width: 170 }],
    contentFn: (builder, main, { actionRefs }) => {
      btnManagePos = actionRefs[0];
      const card = builder.frame({
        name: 'Card',
        parent: main,
        width: 1136,
        height: 750,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 24, layout: 'vertical', gap: 16 },
      });
      builder.inputField({ parent: card, width: 400, height: 40, placeholder: 'Cari kode nota TRX-XXXX atau nama...', icon: 'search' });

      // Table Header
      const th = builder.frame({ name: 'TH', parent: card, width: 1088, height: 38, props: { fill: COLORS.slate50, cornerRadius: 8, layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16] } });
      [{ n: 'Nota', w: 160 }, { n: 'Pelanggan', w: 180 }, { n: 'Layanan', w: 240 }, { n: 'Tagihan', w: 130 }, { n: 'Status Cucian', w: 130 }, { n: 'Pembayaran', w: 110 }, { n: 'Aksi', w: 138 }].forEach(c => {
        builder.text({ name: c.n, parent: th, text: c.n, props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate600 }, width: c.w });
      });

      const rows = [
        { c: 'TRX-20261109-0042', p: 'Budi Santoso', s: 'Cuci Komplit Kilat (5 Kg)', t: 'Rp 45.000', st: 'Sedang Dicuci', pay: 'Belum Lunas' },
        { c: 'TRX-20261109-0041', p: 'Siti Aminah', s: 'Cuci Lipat Reguler (4 Kg)', t: 'Rp 24.000', st: 'Siap Diambil', pay: 'Lunas' },
        { c: 'TRX-20261109-0040', p: 'Ahmad Fauzi', s: 'Cuci Setrika Express (3 Kg)', t: 'Rp 24.000', st: 'Diterima', pay: 'Lunas' },
        { c: 'TRX-20261108-0039', p: 'Hendro Wijaya', s: 'Bedcover Jumbo (Satuan)', t: 'Rp 35.000', st: 'Selesai', pay: 'Lunas' },
      ];
      rows.forEach((r, idx) => {
        const tr = builder.frame({ name: `R-${idx}`, parent: card, width: 1088, height: 50, props: { stroke: COLORS.slate100, strokeWeight: 1, layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16] } });
        builder.text({ name: 'C', parent: tr, text: r.c, props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.brand700 }, width: 160 });
        builder.text({ name: 'P', parent: tr, text: r.p, props: { fontSize: 13, fill: COLORS.slate900 }, width: 180 });
        builder.text({ name: 'S', parent: tr, text: r.s, props: { fontSize: 13, fill: COLORS.slate800 }, width: 240 });
        builder.text({ name: 'T', parent: tr, text: r.t, props: { fontSize: 13, fontWeight: 'bold' }, width: 130 });
        builder.badge({ parent: tr, label: r.st, tone: r.st === 'Selesai' ? 'success' : 'brand', width: 110 });
        builder.badge({ parent: tr, label: r.pay, tone: r.pay === 'Lunas' ? 'success' : 'warning', width: 90 });
        const bDet = builder.button({ parent: tr, label: 'Detail & Struk', variant: 'secondary', width: 110, height: 32 });
        if (idx === 0) btnManageDetail0 = bDet;
      });
    },
  });
  const f12 = f12Shell.root;

  // --- Frame 13: Staff Transaction Detail (1:1 staff/transactions/show.blade.php) ---
  let btnTrxDetailPrint, btnTrxDetailBack, btnTrxDetailReviewProof;
  const f13Shell = b.appShell({
    name: '13-Staff-Transaction-Detail',
    x: 4600,
    y: 2080,
    role: 'staff',
    activeLabel: 'Kelola Transaksi',
    pageTitle: 'Detail Transaksi — TRX-20261109-0042',
    userName: 'Maulana Ahmad',
    returnRefs: true,
    actions: [
      { label: 'Cetak Nota Thermal', variant: 'primary', icon: 'printer', width: 176 },
      { label: 'Kembali', variant: 'secondary', icon: 'arrow-left', width: 110 },
    ],
    contentFn: (builder, main, { actionRefs }) => {
      btnTrxDetailPrint = actionRefs[0];
      btnTrxDetailBack = actionRefs[1];

      // 1. Identity & Main Action Card
      const idCard = builder.frame({
        name: 'IdentityCard',
        parent: main,
        width: 1136,
        height: 164,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 24, layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' },
      });

      const idLeft = builder.frame({ name: 'Left', parent: idCard, width: 700, height: 116, props: { layout: 'vertical', gap: 8 } });
      const bRow = builder.frame({ name: 'Badges', parent: idLeft, width: 420, height: 26, props: { layout: 'horizontal', gap: 8, crossAlign: 'center' } });
      builder.badge({ parent: bRow, label: 'Diproses', tone: 'brand', width: 90 });
      builder.badge({ parent: bRow, label: 'Menunggu Verifikasi QRIS', tone: 'warning', width: 180 });
      builder.text({ name: 'Created', parent: bRow, text: 'Dibuat 15 menit yang lalu', props: { fontSize: 12, fill: COLORS.slate500 } });

      builder.text({ name: 'Code', parent: idLeft, text: 'TRX-20261109-0042', props: { fontSize: 24, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.text({ name: 'Cust', parent: idLeft, text: 'Budi Santoso • 0812-9988-7766 • Estimasi selesai: 09 Nov 2026, 18:00 WIB', props: { fontSize: 13, fontWeight: 'medium', fill: COLORS.slate700 } });

      const idRight = builder.frame({ name: 'Right', parent: idCard, width: 360, height: 116, props: { layout: 'vertical', mainAlign: 'spaceBetween', crossAlign: 'max' } });
      const remCol = builder.frame({ name: 'Rem', parent: idRight, width: 220, height: 52, props: { layout: 'vertical', gap: 2, crossAlign: 'max' } });
      builder.text({ name: 'RL', parent: remCol, text: 'Sisa Tagihan', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate500 } });
      builder.text({ name: 'RV', parent: remCol, text: 'Rp 45.000', props: { fontSize: 26, fontWeight: 'bold', fill: COLORS.rose700 } });

      const actBtns = builder.frame({ name: 'ActBtns', parent: idRight, width: 350, height: 38, props: { layout: 'horizontal', gap: 8, mainAlign: 'max' } });
      builder.button({ parent: actBtns, label: 'Catat Pembayaran', variant: 'primary', width: 165, height: 36 });
      builder.button({ parent: actBtns, label: 'Kirim WhatsApp', variant: 'secondary', width: 145, height: 36 });

      // 2. Detail Layanan Table Card
      const srvCard = builder.frame({
        name: 'ServicesCard',
        parent: main,
        width: 1136,
        height: 220,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 22, layout: 'vertical', gap: 12 },
      });
      const srvHead = builder.frame({ name: 'SH', parent: srvCard, width: 1092, height: 32, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
      builder.text({ name: 'ST', parent: srvHead, text: 'Detail Layanan Laundry', props: { fontSize: 16, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.badge({ parent: srvHead, label: '1 layanan • 5,00 kg', tone: 'slate', width: 150 });

      const sth = builder.frame({ name: 'STH', parent: srvCard, width: 1092, height: 36, props: { fill: COLORS.slate50, cornerRadius: 8, layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16] } });
      [{ n: 'Layanan', w: 460 }, { n: 'Berat / Kuantitas', w: 200 }, { n: 'Harga / Unit', w: 200 }, { n: 'Subtotal', w: 200 }].forEach(c => {
        builder.text({ name: c.n, parent: sth, text: c.n, props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate600 }, width: c.w });
      });

      const str = builder.frame({ name: 'Row1', parent: srvCard, width: 1092, height: 44, props: { stroke: COLORS.slate100, strokeWeight: 1, layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16] } });
      builder.text({ name: 'L', parent: str, text: 'Cuci Komplit Kilat (6 Jam)', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate900 }, width: 460 });
      builder.text({ name: 'B', parent: str, text: '5,00 kg', props: { fontSize: 13, fill: COLORS.slate700 }, width: 200 });
      builder.text({ name: 'H', parent: str, text: 'Rp 9.000', props: { fontSize: 13, fill: COLORS.slate700 }, width: 200 });
      builder.text({ name: 'S', parent: str, text: 'Rp 45.000', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate900 }, width: 200 });

      const sfoot = builder.frame({ name: 'Foot', parent: srvCard, width: 1092, height: 38, props: { fill: COLORS.slate50, cornerRadius: 8, layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center', padding: [0, 16, 0, 16] } });
      builder.text({ name: 'FL', parent: sfoot, text: 'Total 5,00 kg', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate700 } });
      builder.text({ name: 'FV', parent: sfoot, text: 'Rp 45.000', props: { fontSize: 14, fontWeight: 'bold', fill: COLORS.slate900 } });

      // 3. Informasi & Riwayat Pembayaran (MVP-07)
      const payCard = builder.frame({
        name: 'PaymentHistoryCard',
        parent: main,
        width: 1136,
        height: 290,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 22, layout: 'vertical', gap: 14 },
      });
      const pHead = builder.frame({ name: 'PH', parent: payCard, width: 1092, height: 32, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
      builder.text({ name: 'PT', parent: pHead, text: 'Informasi & Riwayat Pembayaran', props: { fontSize: 16, fontWeight: 'bold', fill: COLORS.slate900 } });
      btnTrxDetailReviewProof = builder.button({ parent: pHead, label: 'Tinjau Bukti QRIS', variant: 'secondary', icon: 'shield-check', width: 165, height: 36 });

      const pGrid = builder.frame({ name: 'PGrid', parent: payCard, width: 1092, height: 82, props: { layout: 'horizontal', gap: 16 } });
      [
        { l: 'Total Tagihan', v: 'Rp 45.000', bg: COLORS.slate50, brd: COLORS.slate200, txt: COLORS.slate900 },
        { l: 'Sudah Dibayar (Valid)', v: 'Rp 0', bg: COLORS.emerald50, brd: COLORS.emerald100, txt: COLORS.emerald700 },
        { l: 'Sisa Tagihan', v: 'Rp 45.000', bg: COLORS.rose50, brd: COLORS.rose100, txt: COLORS.rose700 },
      ].forEach((b, i) => {
        const box = builder.frame({ name: `PB-${i}`, parent: pGrid, width: 353, height: 82, props: { fill: b.bg, stroke: b.brd, strokeWeight: 1, cornerRadius: 12, padding: 14, layout: 'vertical', gap: 6 } });
        builder.text({ name: 'L', parent: box, text: b.l, props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate600 } });
        builder.text({ name: 'V', parent: box, text: b.v, props: { fontSize: 22, fontWeight: 'bold', fill: b.txt } });
      });

      const ptr = builder.frame({ name: 'PayRow', parent: payCard, width: 1092, height: 48, props: { fill: COLORS.slate50, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 10, layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center', padding: [0, 16, 0, 16] } });
      builder.text({ name: 'PInfo', parent: ptr, text: '09 Nov 2026, 10:15 • Pelunasan QRIS • Kasir: Maulana Ahmad', props: { fontSize: 12, fontWeight: 'medium', fill: COLORS.slate700 } });
      builder.badge({ parent: ptr, label: 'Menunggu Verifikasi', tone: 'warning', width: 150 });
      builder.text({ name: 'PAmt', parent: ptr, text: 'Rp 45.000', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.emerald700 } });

      // 4. Visual Laundry Pipeline Stepper & Rollback Guard
      const pipeCard = builder.frame({
        name: 'OperationalPipelineCard',
        parent: main,
        width: 1136,
        height: 310,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 22, layout: 'vertical', gap: 14 },
      });
      const opHead = builder.frame({ name: 'OH', parent: pipeCard, width: 1092, height: 32, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
      builder.text({ name: 'OT', parent: opHead, text: 'Status Operasional Cucian (Tahap 2 dari 4 — Dilindungi Rollback Guard)', props: { fontSize: 16, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.button({ parent: opHead, label: 'Majukan ke Selesai ->', variant: 'primary', icon: 'zap', width: 190, height: 36 });

      const step4 = builder.frame({ name: 'Step4Grid', parent: pipeCard, width: 1092, height: 86, props: { layout: 'horizontal', gap: 12 } });
      [
        { n: '1. Diterima', d: 'Antrean Kasir (Tuntas)', bg: COLORS.emerald50, brd: COLORS.emerald500 },
        { n: '2. Diproses', d: 'Cuci & Setrika (Aktif)', bg: COLORS.brand50, brd: COLORS.brand600 },
        { n: '3. Selesai', d: 'Siap Diambil Pelanggan', bg: COLORS.slate50, brd: COLORS.slate200 },
        { n: '4. Diambil', d: 'Diserahkan & Ditutup', bg: COLORS.slate50, brd: COLORS.slate200 },
      ].forEach((s, i) => {
        const sc = builder.frame({ name: `St-${i}`, parent: step4, width: 264, height: 86, props: { fill: s.bg, stroke: s.brd, strokeWeight: i === 1 ? 2 : 1, cornerRadius: 12, padding: 14, layout: 'vertical', gap: 6 } });
        builder.text({ name: 'T', parent: sc, text: s.n, props: { fontSize: 14, fontWeight: 'bold', fill: COLORS.slate900 } });
        builder.text({ name: 'D', parent: sc, text: s.d, props: { fontSize: 12, fill: COLORS.slate600 } });
      });

      const auditBar = builder.frame({ name: 'AuditTrailSummary', parent: pipeCard, width: 1092, height: 54, props: { fill: COLORS.slate50, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 12, padding: [0, 16, 0, 16], layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
      builder.text({ name: 'AT', parent: auditBar, text: 'Riwayat Pembaruan Status (Audit Trail): 09 Nov 2026 10:05 — Diubah ke Diproses oleh Maulana Ahmad', props: { fontSize: 12, fontWeight: 'medium', fill: COLORS.slate700 } });
      builder.badge({ parent: auditBar, label: '2 catatan riwayat', tone: 'slate', width: 130 });
    },
  });
  const f13 = f13Shell.root;

  // --- Frame 13b: Staff Thermal Receipt Print (1:1 staff/transactions/print.blade.php) ---
  let btnPrintBack;
  const f13b = b.frame({
    name: '13b-Staff-Thermal-Receipt-Print',
    x: 6120,
    y: 2080,
    width: 1440,
    height: 900,
    props: { fill: COLORS.slate100, layout: 'vertical', crossAlign: 'center', padding: [16, 0, 20, 0], gap: 14 },
  });

  // Top Action Bar (Kasir Toolbar .actions-bar)
  const actBar = b.frame({
    name: 'KasirActionToolbar',
    parent: f13b,
    width: 800,
    height: 56,
    props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 12, padding: [10, 18, 10, 18], layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' },
  });
  const abLeft = b.frame({ name: 'ABLeft', parent: actBar, width: 280, height: 38, props: { layout: 'horizontal', gap: 10, crossAlign: 'center' } });
  b.vector({ name: 'Ico', parent: abLeft, width: 20, height: 20, path: ICONS.receipt, props: { stroke: COLORS.brand700 } });
  const abMeta = b.frame({ name: 'Meta', parent: abLeft, width: 240, height: 36, props: { layout: 'vertical', gap: 2 } });
  b.text({ name: 'T1', parent: abMeta, text: 'Struk Kasir Thermal', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate900 } });
  b.text({ name: 'T2', parent: abMeta, text: 'TRX-20261109-0042 • Budi Santoso', props: { fontSize: 11, fill: COLORS.slate500 } });

  const abRight = b.frame({ name: 'ABRight', parent: actBar, width: 440, height: 38, props: { layout: 'horizontal', gap: 10, crossAlign: 'center', mainAlign: 'max' } });
  b.badge({ parent: abRight, label: '58mm [1] | 80mm [2]', tone: 'brand', width: 145, height: 34 });
  btnPrintBack = b.button({ parent: abRight, label: 'Kembali', variant: 'secondary', icon: 'arrow-left', width: 100, height: 36 });
  b.button({ parent: abRight, label: 'Print Struk', variant: 'primary', icon: 'printer', kbd: 'Ctrl+P', width: 155, height: 36 });

  // 80mm Thermal Receipt Paper Container
  const paper = b.frame({
    name: 'ReceiptPaper80mm',
    parent: f13b,
    width: 380,
    height: 750,
    props: { fill: COLORS.white, stroke: COLORS.slate300, strokeWeight: 1, cornerRadius: 4, padding: [18, 24, 20, 24], layout: 'vertical', crossAlign: 'center', gap: 8 },
  });
  b.text({ name: 'StoreTitle', parent: paper, text: 'CUCI.IN LAUNDRY', props: { fontSize: 18, fontWeight: 'bold', fill: COLORS.black } });
  b.text({ name: 'StoreSub1', parent: paper, text: 'Layanan Laundry Bersih, Cepat & Terpercaya', props: { fontSize: 11, fill: COLORS.slate700 } });
  b.text({ name: 'StoreSub2', parent: paper, text: 'Telp/WA: 0812-3456-7890', props: { fontSize: 11, fill: COLORS.slate700 } });
  b.text({ name: 'Div1', parent: paper, text: '------------------------------------------------', props: { fontSize: 11, fill: COLORS.slate400 } });

  // Barcode Code128 Mock
  const bcBox = b.frame({ name: 'BarcodeSection', parent: paper, width: 332, height: 52, props: { fill: COLORS.slate50, cornerRadius: 4, layout: 'vertical', mainAlign: 'center', crossAlign: 'center', gap: 4 } });
  b.text({ name: 'Bars', parent: bcBox, text: '||| || |||| | ||| |||| || ||| |||| | ||| ||||', props: { fontSize: 16, fontWeight: 'bold', fill: COLORS.black } });
  b.text({ name: 'CodeTxt', parent: bcBox, text: 'TRX-20261109-0042', props: { fontSize: 10, fontWeight: 'bold', fill: COLORS.slate700 } });

  b.text({ name: 'Div2', parent: paper, text: '------------------------------------------------', props: { fontSize: 11, fill: COLORS.slate400 } });

  const rMeta = [
    ['No. Nota:', 'TRX-20261109-0042'],
    ['Tanggal:', '09/11/2026 10:00'],
    ['Kasir:', 'Maulana Ahmad'],
    ['Pelanggan:', 'Budi Santoso'],
    ['No. Telepon:', '0812-9988-7766'],
  ];
  rMeta.forEach(([k, v], i) => {
    const mr = b.frame({ name: `RM-${i}`, parent: paper, width: 332, height: 18, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
    b.text({ name: 'K', parent: mr, text: k, props: { fontSize: 11, fill: COLORS.slate700 } });
    b.text({ name: 'V', parent: mr, text: v, props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.black } });
  });

  b.text({ name: 'Div3', parent: paper, text: '------------------------------------------------', props: { fontSize: 11, fill: COLORS.slate400 } });

  const itemLine = b.frame({ name: 'Item1', parent: paper, width: 332, height: 38, props: { layout: 'vertical', gap: 2 } });
  const ilTop = b.frame({ name: 'Top', parent: itemLine, width: 332, height: 18, props: { layout: 'horizontal', mainAlign: 'spaceBetween' } });
  b.text({ name: 'IN', parent: ilTop, text: 'Cuci Komplit Kilat (6 Jam)', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.black } });
  b.text({ name: 'IS', parent: ilTop, text: 'Rp 45.000', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.black } });
  b.text({ name: 'ID', parent: itemLine, text: '5,00 kg x Rp 9.000', props: { fontSize: 10, fill: COLORS.slate600 } });

  b.text({ name: 'Div4', parent: paper, text: '------------------------------------------------', props: { fontSize: 11, fill: COLORS.slate400 } });

  const totals = [
    ['Total Tagihan:', 'Rp 45.000'],
    ['Sudah Dibayar:', 'Rp 45.000'],
    ['Pelunasan • QRIS:', 'Rp 45.000'],
    ['Sisa Tagihan:', 'Rp 0'],
    ['Status Bayar:', 'LUNAS'],
    ['Status Cucian:', 'DIPROSES'],
    ['Estimasi Selesai:', '09/11/2026 18:00'],
  ];
  totals.forEach(([k, v], i) => {
    const tr = b.frame({ name: `RT-${i}`, parent: paper, width: 332, height: 18, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
    b.text({ name: 'K', parent: tr, text: k, props: { fontSize: 11, fill: COLORS.slate800 } });
    b.text({ name: 'V', parent: tr, text: v, props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.black } });
  });

  // QR Code Section
  const qrBox = b.frame({ name: 'QRSection', parent: paper, width: 332, height: 112, props: { layout: 'vertical', mainAlign: 'center', crossAlign: 'center', gap: 6, padding: [6, 0, 6, 0] } });
  const qrSq = b.frame({ name: 'QRSquare', parent: qrBox, width: 76, height: 76, props: { fill: COLORS.slate50, stroke: COLORS.black, strokeWeight: 2, cornerRadius: 6, layout: 'horizontal', mainAlign: 'center', crossAlign: 'center' } });
  b.vector({ name: 'QRIco', parent: qrSq, width: 52, height: 52, path: ICONS['qr-code'], props: { stroke: COLORS.black } });
  b.text({ name: 'QRLbl', parent: qrBox, text: 'PINDAI QR LACAK STATUS ONLINE', props: { fontSize: 9, fontWeight: 'bold', fill: COLORS.black } });

  b.text({ name: 'Foot1', parent: paper, text: 'portal-cuciin.web.id/lacak • TERIMA KASIH', props: { fontSize: 10, fontWeight: 'bold', fill: COLORS.slate700 } });

  // --- Frame 14: Staff Payment Verification ---
  const f14Shell = b.appShell({
    name: '14-Staff-Payment-Verification',
    x: 7640,
    y: 2080,
    role: 'staff',
    activeLabel: 'Verifikasi Pembayaran',
    pageTitle: 'Verifikasi Bukti Pembayaran Digital (QRIS & Transfer Bank)',
    userName: 'Nayla Annora Nobel',
    returnRefs: true,
    actions: [{ label: 'Segarkan Antrean', variant: 'secondary', icon: 'zap', width: 155 }],
    contentFn: (builder, main) => {
      // Tab Bar
      const tabRow = builder.frame({ name: 'Tabs', parent: main, width: 1136, height: 40, props: { layout: 'horizontal', gap: 10 } });
      builder.badge({ parent: tabRow, label: 'Menunggu Verifikasi (3)', tone: 'warning', width: 180 });
      builder.badge({ parent: tabRow, label: 'Terverifikasi (28)', tone: 'success', width: 140 });
      builder.badge({ parent: tabRow, label: 'Ditolak (1)', tone: 'danger', width: 110 });
      builder.badge({ parent: tabRow, label: 'Semua Bukti (32)', tone: 'slate', width: 130 });

      // Split View Verification
      const split = builder.frame({ name: 'Split', parent: main, x: 0, y: 52, width: 1136, height: 730, props: { layout: 'horizontal', gap: 20 } });

      // Left: List of pending proofs
      const listCol = builder.frame({ name: 'ListCol', parent: split, width: 540, height: 730, props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 20, layout: 'vertical', gap: 12 } });
      builder.text({ name: 'LTitle', parent: listCol, text: 'Antrean Bukti Bayar Masuk', props: { fontSize: 15, fontWeight: 'bold' } });

      const pItems = [
        { code: 'TRX-20261109-0042', cust: 'Budi Santoso', amt: 'Rp 45.000', m: 'QRIS', time: '10 menit lalu', sel: true },
        { code: 'TRX-20261109-0037', cust: 'Rina Maryati', amt: 'Rp 36.000', m: 'Transfer BCA', time: '25 menit lalu' },
        { code: 'TRX-20261109-0035', cust: 'Joko Prabowo', amt: 'Rp 50.000', m: 'Transfer Mandiri', time: '1 jam lalu' },
      ];
      pItems.forEach(p => {
        const item = builder.frame({
          name: `P-${p.code}`,
          parent: listCol,
          width: 500,
          height: 80,
          props: {
            fill: p.sel ? COLORS.brand50 : COLORS.slate50,
            stroke: p.sel ? COLORS.brand500 : COLORS.slate200,
            strokeWeight: p.sel ? 2 : 1,
            cornerRadius: 12,
            padding: 12,
            layout: 'horizontal',
            mainAlign: 'spaceBetween',
            crossAlign: 'center',
          },
        });
        const pMeta = builder.frame({ name: 'M', parent: item, width: 340, height: 50, props: { layout: 'vertical', gap: 4 } });
        builder.text({ name: 'C', parent: pMeta, text: `${p.code} — ${p.cust}`, props: { fontSize: 13, fontWeight: 'bold' } });
        builder.text({ name: 'A', parent: pMeta, text: `Tagihan: ${p.amt} (${p.m}) • ${p.time}`, props: { fontSize: 12, fill: COLORS.slate600 } });
        builder.badge({ parent: item, label: 'Menunggu', tone: 'warning' });
      });

      // Right: Proof Image & Approval Form
      const previewCol = builder.frame({ name: 'PreviewCol', parent: split, width: 576, height: 730, props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 24, layout: 'vertical', gap: 16 } });
      builder.text({ name: 'PHead', parent: previewCol, text: 'Verifikasi Struk #TRX-20261109-0042 (Budi Santoso)', props: { fontSize: 16, fontWeight: 'bold' } });

      // Receipt mockup box
      const receiptImg = builder.frame({ name: 'ReceiptImg', parent: previewCol, width: 528, height: 380, props: { fill: COLORS.slate100, stroke: COLORS.slate300, strokeWeight: 1, cornerRadius: 12, layout: 'vertical', mainAlign: 'center', crossAlign: 'center', gap: 10 } });
      builder.vector({ name: 'RIco', parent: receiptImg, width: 48, height: 48, path: ICONS.receipt, props: { stroke: COLORS.brand600 } });
      builder.text({ name: 'RText', parent: receiptImg, text: 'Tangkapan Layar Bukti QRIS Rp 45.000 Terlampir', props: { fontSize: 14, fontWeight: 'bold' } });
      builder.text({ name: 'RSub', parent: receiptImg, text: 'Pengirim: Budi Santoso (081299887766) ke Rekening CUCI.IN', props: { fontSize: 12, fill: COLORS.slate600 } });

      // Verification Action Buttons
      const actRow = builder.frame({ name: 'ActRow', parent: previewCol, width: 528, height: 48, props: { layout: 'horizontal', gap: 12 } });
      btnVerifReject = builder.button({ parent: actRow, label: 'Tinjau Detail / Tolak', variant: 'destructive', icon: 'alert-triangle', width: 220, height: 44 });
      btnVerifApprove = builder.button({ parent: actRow, label: 'Setujui & Tandai Lunas', variant: 'primary', icon: 'check-circle', width: 296, height: 44 });
    },
  });
  const f14 = f14Shell.root;

  // --- Frame 14b: Staff Payment Proof Detail (1:1 staff/payments/show.blade.php) ---
  let btnPayShowNota, btnPayShowBack, btnPayShowApprove;
  const f14bShell = b.appShell({
    name: '14b-Staff-Payment-Proof-Detail',
    x: 9160,
    y: 2080,
    role: 'staff',
    activeLabel: 'Verifikasi Pembayaran',
    pageTitle: 'Tinjau Pembayaran — TRX-20261109-0042',
    userName: 'Nayla Annora Nobel',
    returnRefs: true,
    actions: [
      { label: 'Lihat Nota', variant: 'secondary', icon: 'receipt', width: 125 },
      { label: 'Kembali', variant: 'secondary', icon: 'arrow-left', width: 110 },
    ],
    contentFn: (builder, main, { actionRefs }) => {
      btnPayShowNota = actionRefs[0];
      btnPayShowBack = actionRefs[1];

      const grid = builder.frame({ name: 'Grid5Col', parent: main, width: 1136, height: 770, props: { layout: 'horizontal', gap: 20 } });

      // Left (3/5 = 668px): Pratinjau Bukti Pembayaran
      const previewCard = builder.frame({
        name: 'ProofPreviewCard',
        parent: grid,
        width: 668,
        height: 770,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 22, layout: 'vertical', gap: 12 },
      });
      const pvHead = builder.frame({ name: 'PVH', parent: previewCard, width: 624, height: 28, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
      builder.text({ name: 'T', parent: pvHead, text: 'Pratinjau Bukti Pembayaran Digital (QRIS)', props: { fontSize: 16, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.text({ name: 'Zoom', parent: pvHead, text: 'Buka ukuran penuh ->', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.brand700 } });

      const imgMock = builder.frame({
        name: 'QRISReceiptImage',
        parent: previewCard,
        width: 624,
        height: 680,
        props: { fill: COLORS.slate100, stroke: COLORS.slate300, strokeWeight: 1, cornerRadius: 14, padding: 20, layout: 'vertical', mainAlign: 'center', crossAlign: 'center', gap: 10 },
      });
      const qrisPaper = builder.frame({
        name: 'BankSlipMock',
        parent: imgMock,
        width: 380,
        height: 590,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 22, layout: 'vertical', gap: 10 },
      });
      builder.badge({ parent: qrisPaper, label: 'PEMBAYARAN QRIS BERHASIL', tone: 'success', width: 220, height: 28 });
      builder.text({ name: 'Amt', parent: qrisPaper, text: 'Rp 45.000', props: { fontSize: 28, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.text({ name: 'Merchant', parent: qrisPaper, text: 'Merchant: CUCI.IN LAUNDRY OFFICIAL MALANG', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.brand700 } });
      builder.text({ name: 'TrxNota', parent: qrisPaper, text: 'No. Nota Terkait: #TRX-20261109-0042', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.text({ name: 'Layanan', parent: qrisPaper, text: 'Layanan: Cuci Komplit Kilat 6 Jam (5.0 Kg)', props: { fontSize: 11, fill: COLORS.slate700 } });
      builder.text({ name: 'Sender', parent: qrisPaper, text: 'Pengirim: BUDI SANTOSO (0812-9988-7766 • BCA Mobile)', props: { fontSize: 11, fontWeight: 'medium', fill: COLORS.slate800 } });
      builder.text({ name: 'Ref', parent: qrisPaper, text: 'No. Referensi RRN: 631409882716 • 09 Nov 2026, 10:14:22 WIB', props: { fontSize: 10, fill: COLORS.slate600 } });
      builder.text({ name: 'MimeInfo', parent: qrisPaper, text: 'Status Berkas: image/webp • 312 KB • Lolos Pemeriksaan MIME & Ukuran', props: { fontSize: 10, fontWeight: 'bold', fill: COLORS.emerald700 } });

      // Right (2/5 = 448px): Ringkasan, Keputusan Verifikasi & Form Alasan Penolakan
      const rightCol = builder.frame({ name: 'RightCol', parent: grid, width: 448, height: 770, props: { layout: 'vertical', gap: 14 } });

      const sumCard = builder.frame({
        name: 'SummaryCard',
        parent: rightCol,
        width: 448,
        height: 220,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 18, layout: 'vertical', gap: 8 },
      });
      const smTop = builder.frame({ name: 'SMT', parent: sumCard, width: 412, height: 56, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
      const smAmt = builder.frame({ name: 'SMA', parent: smTop, width: 250, height: 50, props: { layout: 'vertical', gap: 2 } });
      builder.text({ name: 'L', parent: smAmt, text: 'Nominal Diajukan', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate500 } });
      builder.text({ name: 'V', parent: smAmt, text: 'Rp 45.000', props: { fontSize: 24, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.badge({ parent: smTop, label: 'Menunggu Verifikasi', tone: 'warning', width: 145 });

      [
        ['Metode & Jenis', 'QRIS • Pelunasan'],
        ['Total Tagihan Nota', 'Rp 45.000'],
        ['Sudah Dibayar (Valid)', 'Rp 0'],
        ['Sisa Tagihan Saat Ini', 'Rp 45.000'],
      ].forEach(([k, v], idx) => {
        const r = builder.frame({ name: `SR-${idx}`, parent: sumCard, width: 412, height: 22, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
        builder.text({ name: 'K', parent: r, text: k, props: { fontSize: 12, fill: COLORS.slate600 } });
        builder.text({ name: 'V', parent: r, text: v, props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate900 } });
      });

      // Keputusan Verifikasi Card
      const decCard = builder.frame({
        name: 'DecisionCard',
        parent: rightCol,
        width: 448,
        height: 350,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 20, layout: 'vertical', gap: 10 },
      });
      builder.text({ name: 'DT', parent: decCard, text: 'Keputusan Verifikasi Kasir', props: { fontSize: 15, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.text({ name: 'DS', parent: decCard, text: 'Cocokkan nominal, tanggal, dan rekening/merchant tujuan pada bukti sebelum menyetujui.', props: { fontSize: 11, fill: COLORS.slate600 }, width: 408 });

      const btnPair = builder.frame({ name: 'BtnPair', parent: decCard, width: 408, height: 44, props: { layout: 'horizontal', gap: 10 } });
      btnPayShowApprove = builder.button({ parent: btnPair, label: 'Setujui Pembayaran', variant: 'primary', icon: 'check', width: 199, height: 44 });
      builder.button({ parent: btnPair, label: 'Tolak Bukti', variant: 'destructive', icon: 'x', width: 199, height: 44 });

      builder.text({ name: 'RLbl', parent: decCard, text: 'Alasan Penolakan * (Wajib diisi jika menolak bukti)', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate700 } });
      const rBox = builder.frame({
        name: 'RejectNotesBox',
        parent: decCard,
        width: 408,
        height: 84,
        props: { fill: COLORS.slate50, stroke: COLORS.slate300, strokeWeight: 1, cornerRadius: 10, padding: 10, layout: 'vertical' },
      });
      builder.text({
        name: 'Placeholder',
        parent: rBox,
        text: 'Contoh: Nominal pada bukti tidak sesuai, gambar buram, atau dana belum masuk ke rekening.',
        props: { fontSize: 11, fill: COLORS.slate500 },
        width: 388,
      });

      // Other payments on this note
      const othCard = builder.frame({
        name: 'OtherPaymentsCard',
        parent: rightCol,
        width: 448,
        height: 160,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 18, layout: 'vertical', gap: 8 },
      });
      builder.text({ name: 'OT', parent: othCard, text: 'Pembayaran pada Nota Ini', props: { fontSize: 14, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.text({ name: 'OI', parent: othCard, text: '• Rp 45.000 — 09 Nov 2026, 10:15 • QRIS (Sedang Ditinjau)', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.brand700 } });
    },
  });
  const f14b = f14bShell.root;

  // --- Frame 15: Staff Customers & Services (Direktori Pelanggan & Katalog Layanan) ---
  const f15Shell = b.appShell({
    name: '15-Staff-Customers-Services',
    x: 10680,
    y: 2080,
    role: 'staff',
    activeLabel: 'Data Pelanggan',
    pageTitle: 'Direktori Pelanggan & Katalog Layanan',
    userName: 'Maulana Ahmad',
    returnRefs: true,
    actions: [{ label: '+ Pelanggan Baru', variant: 'primary', icon: 'plus', width: 155 }],
    contentFn: (builder, main) => {
      // 1. Direktori Pelanggan Card
      const card = builder.frame({
        name: 'CustCard',
        parent: main,
        width: 1136,
        height: 340,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 20, layout: 'vertical', gap: 12 },
      });

      const custHead = builder.frame({ name: 'CH', parent: card, width: 1096, height: 38, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
      builder.text({ name: 'H', parent: custHead, text: 'Direktori Pelanggan Terdaftar', props: { fontSize: 16, fontWeight: 'bold' } });
      builder.inputField({ parent: custHead, width: 340, height: 36, placeholder: 'Cari nama atau no. telepon...', icon: 'search' });

      const th = builder.frame({ name: 'TH', parent: card, width: 1096, height: 36, props: { fill: COLORS.slate50, cornerRadius: 8, layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16] } });
      [{ n: 'Nama Pelanggan', w: 220 }, { n: 'Nomor WhatsApp', w: 200 }, { n: 'Alamat Domisili', w: 340 }, { n: 'Total Nota', w: 120 }, { n: 'Aksi POS', w: 168 }].forEach(c => {
        builder.text({ name: c.n, parent: th, text: c.n, props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate600 }, width: c.w });
      });

      const custs = [
        { name: 'Budi Santoso', phone: '0812-9988-7766', addr: 'Jl. Rungkut Madya No. 10', total: '14 Nota' },
        { name: 'Siti Aminah', phone: '0813-4455-6677', addr: 'Perum Gunung Anyar Harapan B-4', total: '8 Nota' },
        { name: 'Ahmad Fauzi', phone: '0857-1122-3344', addr: 'Jl. Tenggilis Mejoyo Selatan No. 15', total: '21 Nota' },
      ];
      custs.forEach((c, idx) => {
        const tr = builder.frame({ name: `R-${idx}`, parent: card, width: 1096, height: 46, props: { stroke: COLORS.slate100, strokeWeight: 1, layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16] } });
        builder.text({ name: 'N', parent: tr, text: c.name, props: { fontSize: 13, fontWeight: 'bold' }, width: 220 });
        builder.text({ name: 'P', parent: tr, text: c.phone, props: { fontSize: 13, fill: COLORS.slate600 }, width: 200 });
        builder.text({ name: 'A', parent: tr, text: c.addr, props: { fontSize: 13, fill: COLORS.slate800 }, width: 340 });
        builder.badge({ parent: tr, label: c.total, tone: 'brand', width: 90 });
        const bPos = builder.button({ parent: tr, label: 'Buat Nota POS', variant: 'primary', width: 140, height: 32 });
        if (idx === 0) btnCustCreatePos0 = bPos;
      });

      // 2. Katalog Master Layanan Card (Modul Ayin P10 PWL)
      const srvCard = builder.frame({
        name: 'ServicesCard',
        parent: main,
        width: 1136,
        height: 410,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 20, layout: 'vertical', gap: 12 },
      });

      const srvHead = builder.frame({ name: 'SH', parent: srvCard, width: 1096, height: 40, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
      const shLeft = builder.frame({ name: 'SHL', parent: srvHead, width: 750, height: 40, props: { layout: 'vertical', gap: 2 } });
      builder.text({ name: 'STitle', parent: shLeft, text: 'Katalog Master Layanan Laundry & Tarif Dinamis (P10 PWL)', props: { fontSize: 16, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.text({ name: 'SSub', parent: shLeft, text: 'Dikelola oleh Gargarina Nanda Iswati (Ayin) • Tarif dan durasi terintegrasi otomatis ke Kasir POS & Nota Publik', props: { fontSize: 11, fill: COLORS.slate500 } });
      builder.badge({ parent: srvHead, label: '5 Layanan Aktif', tone: 'brand', width: 125 });

      const sth = builder.frame({ name: 'STH', parent: srvCard, width: 1096, height: 36, props: { fill: COLORS.slate50, cornerRadius: 8, layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16] } });
      [{ n: 'Nama Paket Layanan', w: 260 }, { n: 'Satuan', w: 140 }, { n: 'Tarif / Unit', w: 160 }, { n: 'Estimasi Durasi Selesai', w: 200 }, { n: 'Status', w: 120 }, { n: 'Aksi Kasir', w: 168 }].forEach(c => {
        builder.text({ name: c.n, parent: sth, text: c.n, props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate600 }, width: c.w });
      });

      const srvList = [
        { name: 'Cuci Komplit Kilat', unit: 'Kiloan (Kg)', price: 'Rp 9.000 / Kg', est: '6 Jam (Paket Kilat)', fast: true },
        { name: 'Cuci Lipat Reguler', unit: 'Kiloan (Kg)', price: 'Rp 6.000 / Kg', est: '48 Jam (2 Hari)', fast: false },
        { name: 'Cuci Setrika Express', unit: 'Kiloan (Kg)', price: 'Rp 8.000 / Kg', est: '24 Jam (1 Hari)', fast: false },
        { name: 'Bedcover Ukuran Jumbo', unit: 'Satuan (Pcs)', price: 'Rp 35.000 / Pcs', est: '48 Jam (2 Hari)', fast: false },
        { name: 'Sepatu Sneaker Deep Clean', unit: 'Satuan (Pasang)', price: 'Rp 35.000 / Psg', est: '72 Jam (3 Hari)', fast: false },
      ];
      srvList.forEach((s, idx) => {
        const str = builder.frame({ name: `SR-${idx}`, parent: srvCard, width: 1096, height: 46, props: { stroke: COLORS.slate100, strokeWeight: 1, layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16] } });
        const nameCol = builder.frame({ name: 'NC', parent: str, width: 260, height: 32, props: { layout: 'horizontal', gap: 6, crossAlign: 'center' } });
        builder.text({ name: 'SN', parent: nameCol, text: s.name, props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate900 } });
        if (s.fast) builder.badge({ parent: nameCol, label: 'KILAT', tone: 'brand', width: 60, height: 20 });
        builder.text({ name: 'SU', parent: str, text: s.unit, props: { fontSize: 13, fill: COLORS.slate600 }, width: 140 });
        builder.text({ name: 'SP', parent: str, text: s.price, props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.emerald700 }, width: 160 });
        builder.text({ name: 'SE', parent: str, text: s.est, props: { fontSize: 12, fill: s.fast ? COLORS.brand700 : COLORS.slate700, fontWeight: s.fast ? 'bold' : 'normal' }, width: 200 });
        builder.badge({ parent: str, label: 'AKTIF', tone: 'success', width: 70 });
        builder.button({ parent: str, label: '+ Pilih di POS', variant: 'secondary', width: 120, height: 32 });
      });
    },
  });
  const f15 = f15Shell.root;

  // --- Frame 16: Staff Complaints Resolution ---
  const f16Shell = b.appShell({
    name: '16-Staff-Complaints-Resolution',
    x: 12200,
    y: 2080,
    role: 'staff',
    activeLabel: 'Pengaduan',
    pageTitle: 'Penanganan Tiket Pengaduan Pelanggan (Daysyani)',
    userName: 'Daysyani Sophi',
    returnRefs: true,
    actions: [{ label: 'Segarkan Tiket', variant: 'secondary', icon: 'zap', width: 145 }],
    contentFn: (builder, main) => {
      const card = builder.frame({
        name: 'Card',
        parent: main,
        width: 1136,
        height: 750,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 24, layout: 'vertical', gap: 16 },
      });
      builder.text({ name: 'H', parent: card, text: 'Daftar Tiket Keluhan Pelanggan yang Membutuhkan Tindakan', props: { fontSize: 16, fontWeight: 'bold' } });

      const th = builder.frame({ name: 'TH', parent: card, width: 1088, height: 38, props: { fill: COLORS.slate50, cornerRadius: 8, layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16] } });
      [{ n: 'Kode Tiket', w: 180 }, { n: 'Nota', w: 160 }, { n: 'Pelanggan', w: 180 }, { n: 'Subjek Keluhan', w: 260 }, { n: 'Status', w: 130 }, { n: 'Aksi Kasir', w: 148 }].forEach(c => {
        builder.text({ name: c.n, parent: th, text: c.n, props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate600 }, width: c.w });
      });

      const cList = [
        { c: 'CMP-20261109-001', trx: 'TRX-20261109-0042', cust: 'Budi Santoso', s: 'Noda kerah kemeja putih', st: 'Diproses' },
        { c: 'CMP-20261105-002', trx: 'TRX-20261104-0021', cust: 'Dewi Lestari', s: 'Kancing jaket terlepas', st: 'Selesai' },
      ];
      cList.forEach((cl, idx) => {
        const tr = builder.frame({ name: `R-${idx}`, parent: card, width: 1088, height: 50, props: { stroke: COLORS.slate100, strokeWeight: 1, layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16] } });
        builder.text({ name: 'C', parent: tr, text: cl.c, props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.rose700 }, width: 180 });
        builder.text({ name: 'T', parent: tr, text: cl.trx, props: { fontSize: 12, fill: COLORS.brand700 }, width: 160 });
        builder.text({ name: 'CU', parent: tr, text: cl.cust, props: { fontSize: 13 }, width: 180 });
        builder.text({ name: 'S', parent: tr, text: cl.s, props: { fontSize: 13, fill: COLORS.slate800 }, width: 260 });
        builder.badge({ parent: tr, label: cl.st, tone: cl.st === 'Selesai' ? 'success' : 'brand', width: 110 });
        const bRep = builder.button({ parent: tr, label: 'Beri Tanggapan', variant: 'primary', width: 130, height: 32 });
        if (idx === 0) btnCompReply0 = bRep;
      });
    },
  });
  const f16 = f16Shell.root;

  // --- Frame 16b: Staff Complaint Detail & Resolution Form (1:1 staff/complaints/show.blade.php) ---
  let btnStaffCompBack, btnStaffCompOpenPos, btnStaffCompSave;
  const f16bShell = b.appShell({
    name: '16b-Staff-Complaint-Detail',
    x: 13720,
    y: 2080,
    role: 'staff',
    activeLabel: 'Pengaduan',
    pageTitle: 'Detail Pengaduan — CMP-20261109-001',
    userName: 'Daysyani Sophi',
    returnRefs: true,
    actions: [{ label: 'Kembali ke Daftar', variant: 'secondary', icon: 'arrow-left', width: 160 }],
    contentFn: (builder, main, { actionRefs }) => {
      btnStaffCompBack = actionRefs[0];

      const grid = builder.frame({ name: 'CompGrid', parent: main, width: 1136, height: 730, props: { layout: 'horizontal', gap: 20 } });

      // Left Column (668px): Keluhan, Bukti Foto & Nota Terkait
      const leftCol = builder.frame({ name: 'LeftCol', parent: grid, width: 668, height: 730, props: { layout: 'vertical', gap: 14 } });

      const detailCard = builder.frame({
        name: 'ComplaintDetailCard',
        parent: leftCol,
        width: 668,
        height: 410,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 20, layout: 'vertical', gap: 10 },
      });
      const dcHead = builder.frame({ name: 'DCH', parent: detailCard, width: 620, height: 32, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
      builder.text({ name: 'H', parent: dcHead, text: 'Rincian Pengaduan Pelanggan', props: { fontSize: 16, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.badge({ parent: dcHead, label: 'Sedang Diproses', tone: 'brand', width: 130 });

      builder.text({ name: 'SL', parent: detailCard, text: 'Subjek Keluhan', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate500 } });
      builder.text({ name: 'SV', parent: detailCard, text: 'Noda kerah kemeja putih belum hilang sepenuhnya', props: { fontSize: 14, fontWeight: 'bold', fill: COLORS.slate900 } });

      builder.text({ name: 'UL', parent: detailCard, text: 'Uraian Masalah / Kendala', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate500 } });
      builder.text({
        name: 'UV',
        parent: detailCard,
        text: 'Pada paket Cuci Komplit Kilat 5 Kg, ada 1 potong kemeja kerja warna putih yang bagian kerahnya masih tampak bercak kebiruan. Mohon dibantu cuci ulang sebelum saya ambil sore ini.',
        props: { fontSize: 12, fill: COLORS.slate700 },
        width: 620,
      });

      builder.text({ name: 'PL', parent: detailCard, text: 'Bukti Foto Kendala / Pakaian', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate500 } });
      const photoPreview = builder.frame({
        name: 'PhotoBox',
        parent: detailCard,
        width: 620,
        height: 120,
        props: { fill: COLORS.slate100, stroke: COLORS.slate300, strokeWeight: 1, cornerRadius: 12, padding: 14, layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' },
      });
      const phLeft = builder.frame({ name: 'PHL', parent: photoPreview, width: 420, height: 80, props: { layout: 'vertical', gap: 4 } });
      builder.text({ name: 'P1', parent: phLeft, text: 'Lampiran Foto: bukti_kerah_kemeja_putih.webp', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.text({ name: 'P2', parent: phLeft, text: 'Diunggah oleh Budi Santoso • 09 Nov 2026, 11:20 WIB', props: { fontSize: 11, fill: COLORS.slate600 } });
      builder.button({ parent: photoPreview, label: 'Lihat Ukuran Penuh', variant: 'secondary', icon: 'eye', width: 155, height: 36 });

      // Linked POS Transaction Card
      const posCard = builder.frame({
        name: 'LinkedPOSCard',
        parent: leftCol,
        width: 668,
        height: 304,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 20, layout: 'vertical', gap: 10 },
      });
      const pcHead = builder.frame({ name: 'PCH', parent: posCard, width: 620, height: 32, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
      builder.text({ name: 'PCT', parent: pcHead, text: 'Informasi Nota Transaksi Terkait', props: { fontSize: 15, fontWeight: 'bold', fill: COLORS.slate900 } });
      btnStaffCompOpenPos = builder.button({ parent: pcHead, label: 'Buka Nota POS', variant: 'secondary', icon: 'arrow-up-right', width: 140, height: 32 });

      [
        ['Nomor Nota Transaksi', 'TRX-20261109-0042'],
        ['Status Transaksi Cucian', 'Sedang Diproses'],
        ['Nama Pelanggan', 'Budi Santoso'],
        ['Nomor Telepon / WhatsApp', '0812-9988-7766'],
        ['Item Layanan', 'Cuci Komplit Kilat (6 Jam) — 5,00 kg'],
      ].forEach(([k, v], i) => {
        const r = builder.frame({ name: `PR-${i}`, parent: posCard, width: 620, height: 26, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
        builder.text({ name: 'K', parent: r, text: k, props: { fontSize: 11, fill: COLORS.slate500 } });
        builder.text({ name: 'V', parent: r, text: v, props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate900 } });
      });

      // Right Column (448px): Form Tindak Lanjut & Target SLA
      const rightCol = builder.frame({ name: 'RightCol', parent: grid, width: 448, height: 730, props: { layout: 'vertical', gap: 14 } });

      const formCard = builder.frame({
        name: 'FollowUpFormCard',
        parent: rightCol,
        width: 448,
        height: 520,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 20, layout: 'vertical', gap: 10 },
      });
      builder.text({ name: 'FT', parent: formCard, text: 'Tindak Lanjut & Tanggapan Staf', props: { fontSize: 16, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.text({ name: 'FS', parent: formCard, text: 'Perbarui tahapan penanganan dan tulis pesan solusi untuk pelanggan.', props: { fontSize: 12, fill: COLORS.slate500 }, width: 400 });

      builder.inputField({
        parent: formCard,
        width: 400,
        height: 40,
        label: 'Status Penanganan *',
        value: 'Sedang Diproses',
      });

      builder.text({ name: 'RLbl', parent: formCard, text: 'Tanggapan / Solusi untuk Pelanggan', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate700 } });
      const respBox = builder.frame({
        name: 'ResponseTextarea',
        parent: formCard,
        width: 400,
        height: 140,
        props: { fill: COLORS.slate50, stroke: COLORS.slate300, strokeWeight: 1, cornerRadius: 12, padding: 12, layout: 'vertical' },
      });
      builder.text({
        name: 'RTxt',
        parent: respBox,
        text: 'Kami mohon maaf atas ketidaknyamanannya. Pakaian kemeja putih telah kami lakukan spotting chemical ulang dan noda luntur sudah bersih. Pakaian siap diambil sore ini pukul 17:00 WIB.',
        props: { fontSize: 11, fill: COLORS.slate800 },
        width: 372,
      });

      btnStaffCompSave = builder.button({ parent: formCard, label: 'Simpan Pembaruan', variant: 'primary', icon: 'check', width: 400, height: 44 });
      builder.text({ name: 'HandlerInfo', parent: formCard, text: 'Ditangani Terakhir Oleh: Daysyani Sophi Masayu (staff) • 09 Nov 2026, 11:45 WIB', props: { fontSize: 11, fill: COLORS.slate500 } });

      // SLA Target Card
      const slaCard = builder.frame({
        name: 'SLACard',
        parent: rightCol,
        width: 448,
        height: 194,
        props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 20, layout: 'vertical', gap: 8 },
      });
      builder.badge({ parent: slaCard, label: 'TARGET SLA (2 HARI KERJA)', tone: 'brand', width: 200 });
      builder.text({ name: 'DL', parent: slaCard, text: 'Batas Waktu Penanganan: Rabu, 11 November 2026', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate900 } });
      builder.text({ name: 'Rem', parent: slaCard, text: 'Sisa waktu penanganan: 1 hari 22 jam lagi.', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.emerald700 } });
    },
  });
  const f16b = f16bShell.root;

  // ========================================================
  // PROTOTYPING WIRES: ALUR KASIR & OPERASIONAL (TERHUBUNG KE ALUR UTAMA)
  // ========================================================
  if (publicAuthRefs.btnChipStaff) {
    b.wire({ id: publicAuthRefs.btnChipStaff, dest: f10 });
  }

  // Sidebar navigation across all Staff screens
  const staffShells = [f10Shell, f11Shell, f12Shell, f13Shell, f14Shell, f14bShell, f15Shell, f16Shell, f16bShell];
  staffShells.forEach((sh) => {
    if (sh.navRefs['Dashboard']) b.wire({ id: sh.navRefs['Dashboard'], dest: f10 });
    if (sh.navRefs['Kasir POS']) b.wire({ id: sh.navRefs['Kasir POS'], dest: f11 });
    if (sh.navRefs['Kelola Transaksi']) b.wire({ id: sh.navRefs['Kelola Transaksi'], dest: f12 });
    if (sh.navRefs['Verifikasi Pembayaran']) b.wire({ id: sh.navRefs['Verifikasi Pembayaran'], dest: f14 });
    if (sh.navRefs['Data Pelanggan']) b.wire({ id: sh.navRefs['Data Pelanggan'], dest: f15 });
    if (sh.navRefs['Katalog Layanan']) b.wire({ id: sh.navRefs['Katalog Layanan'], dest: f15 });
    if (sh.navRefs['Pengaduan']) b.wire({ id: sh.navRefs['Pengaduan'], dest: f16 });
    if (sh.logoutRef && publicAuthRefs.f01) {
      b.wire({ id: sh.logoutRef, dest: publicAuthRefs.f01 });
    }
  });

  // Action wires between Staff operational views
  if (btnDashPos) b.wire({ id: btnDashPos, dest: f11 });
  if (btnDashStatus0) b.wire({ id: btnDashStatus0, dest: f13 });
  if (btnPosList) b.wire({ id: btnPosList, dest: f12 });
  if (btnPosCheckout) b.wire({ id: btnPosCheckout, dest: f13b });
  if (btnManagePos) b.wire({ id: btnManagePos, dest: f11 });
  if (btnManageDetail0) b.wire({ id: btnManageDetail0, dest: f13 });

  // Detail Transaction (13) <-> Print Receipt (13b) & Proof Detail (14b)
  if (btnTrxDetailPrint) b.wire({ id: btnTrxDetailPrint, dest: f13b });
  if (btnTrxDetailBack) b.wire({ id: btnTrxDetailBack, dest: f12 });
  if (btnTrxDetailReviewProof) b.wire({ id: btnTrxDetailReviewProof, dest: f14b });
  if (btnPrintBack) b.wire({ id: btnPrintBack, dest: f13 });

  // Payment Verification (14 <-> 14b)
  if (btnVerifReject) b.wire({ id: btnVerifReject, dest: f14b });
  if (btnVerifApprove) b.wire({ id: btnVerifApprove, dest: f13 });
  if (btnPayShowNota) b.wire({ id: btnPayShowNota, dest: f13 });
  if (btnPayShowBack) b.wire({ id: btnPayShowBack, dest: f14 });
  if (btnPayShowApprove) b.wire({ id: btnPayShowApprove, dest: f14 });

  // Customers (15) -> POS (11)
  if (btnCustCreatePos0) b.wire({ id: btnCustCreatePos0, dest: f11 });

  // Complaints (16 <-> 16b -> 13)
  if (btnCompReply0) b.wire({ id: btnCompReply0, dest: f16b });
  if (btnStaffCompBack) b.wire({ id: btnStaffCompBack, dest: f16 });
  if (btnStaffCompSave) b.wire({ id: btnStaffCompSave, dest: f16 });
  if (btnStaffCompOpenPos) b.wire({ id: btnStaffCompOpenPos, dest: f13 });

  return { f10, f11, f12, f13, f13b, f14, f14b, f15, f16, f16b };
}

