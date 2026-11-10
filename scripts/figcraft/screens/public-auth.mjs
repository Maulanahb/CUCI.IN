import { COLORS, ICONS } from '../tokens.mjs';

export function buildPublicAuthScreens(b) {
  // ========================================================
  // SINGLE MASTER PAGE DECLARATION
  // ========================================================
  b.page('01 - CUCI.IN Master Prototype', COLORS.slate100);

  // ========================================================
  // FRAME 01: Welcome Landing Page (1440x900 Fold View)
  // ========================================================
  const f01 = b.frame({
    name: '01-Welcome-Landing-Page',
    x: 40,
    y: 40,
    width: 1440,
    height: 900,
    props: { fill: COLORS.slate50, layout: 'vertical', gap: 0 },
  });

  // 1. Sticky Header (height: 64)
  const header = b.frame({
    name: 'Header',
    parent: f01,
    width: 1440,
    height: 64,
    props: {
      fill: COLORS.white,
      stroke: COLORS.slate200,
      strokeWeight: 1,
      layout: 'horizontal',
      mainAlign: 'spaceBetween',
      crossAlign: 'center',
      padding: [0, 60, 0, 60],
    },
  });

  const logoGroup = b.frame({
    name: 'LogoBrand',
    parent: header,
    width: 150,
    height: 36,
    props: { layout: 'horizontal', crossAlign: 'center', gap: 8 },
  });
  b.rect({ name: 'LogoIco', parent: logoGroup, width: 32, height: 32, props: { fill: COLORS.brand600, cornerRadius: 8 } });
  b.text({ name: 'LogoTxt', parent: logoGroup, text: 'CUCI.IN', props: { fontSize: 20, fontWeight: 'bold', fill: COLORS.brand700 } });

  const navLinks = b.frame({
    name: 'NavLinks',
    parent: header,
    width: 480,
    height: 36,
    props: { layout: 'horizontal', crossAlign: 'center', gap: 28 },
  });
  ['Fitur Layanan', 'Kalkulator Tarif', 'Keunggulan', 'Cara Kerja'].forEach((link) => {
    b.text({ name: `Nav-${link}`, parent: navLinks, text: link, props: { fontSize: 13, fontWeight: 'medium', fill: COLORS.slate600 } });
  });

  const headerActions = b.frame({
    name: 'HeaderActions',
    parent: header,
    width: 320,
    height: 36,
    props: { layout: 'horizontal', crossAlign: 'center', gap: 10, mainAlign: 'max' },
  });
  const btnHeaderTrack = b.button({ parent: headerActions, label: 'Lacak Cucian', variant: 'secondary', icon: 'search', width: 120, height: 36 });
  const btnHeaderLogin = b.button({ parent: headerActions, label: 'Masuk', variant: 'secondary', icon: 'log-in', width: 90, height: 36 });
  const btnHeaderRegister = b.button({ parent: headerActions, label: 'Daftar', variant: 'primary', width: 85, height: 36 });

  // 2. Hero Section (height: 420)
  const heroSection = b.frame({
    name: 'HeroSection',
    parent: f01,
    width: 1440,
    height: 420,
    props: {
      fill: COLORS.brand50,
      layout: 'vertical',
      crossAlign: 'center',
      padding: [28, 60, 24, 60],
      gap: 14,
    },
  });

  b.text({
    name: 'HeroTitle1',
    parent: heroSection,
    text: 'Laundry Bersih, Higienis & Terpantau Real-Time',
    props: { fontSize: 32, fontWeight: 'bold', fill: COLORS.slate900, textAlign: 'center' },
  });

  b.text({
    name: 'HeroSub',
    parent: heroSection,
    text: 'Nikmati kemudahan mencuci tanpa rasa cemas. Timbangan presisi desimal, pakaian tidak dicampur,\ndan pantau status cucian langsung dari ponsel menggunakan kode nota fisik Anda.',
    props: { fontSize: 13, fill: COLORS.slate600, textAlign: 'center' },
  });

  // Quick Tracking Box Form
  const quickTrackBox = b.frame({
    name: 'QuickTrackFormCard',
    parent: heroSection,
    width: 720,
    height: 110,
    props: {
      fill: COLORS.white,
      stroke: COLORS.slate200,
      strokeWeight: 1,
      cornerRadius: 16,
      padding: [14, 20, 14, 20],
      layout: 'vertical',
      gap: 8,
    },
  });

  const trackInputRow = b.frame({
    name: 'InputRow',
    parent: quickTrackBox,
    width: 680,
    height: 44,
    props: { layout: 'horizontal', gap: 10, crossAlign: 'center' },
  });

  const trackField = b.frame({
    name: 'FieldBox',
    parent: trackInputRow,
    width: 480,
    height: 44,
    props: {
      fill: COLORS.slate50,
      stroke: COLORS.slate200,
      strokeWeight: 1,
      cornerRadius: 10,
      layout: 'horizontal',
      crossAlign: 'center',
      gap: 10,
      padding: [0, 16, 0, 16],
    },
  });
  b.vector({ name: 'ReceiptIco', parent: trackField, width: 18, height: 18, path: ICONS.receipt, props: { stroke: COLORS.slate400 } });
  b.text({ name: 'FieldValue', parent: trackField, text: 'TRX-20261024-0001', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate900 } });

  const btnHeroTrack = b.button({
    parent: trackInputRow,
    label: 'Lacak Sekarang',
    variant: 'primary',
    icon: 'arrow-right',
    width: 190,
    height: 44,
  });

  b.text({
    name: 'HelpText',
    parent: quickTrackBox,
    text: 'Tanpa perlu login. Cukup ketik kode transaksi yang tercetak pada struk thermal Anda.',
    props: { fontSize: 11, fill: COLORS.slate500, textAlign: 'center' },
  });

  // Trust Badges Row
  const trustRow = b.frame({
    name: 'TrustBadgesRow',
    parent: heroSection,
    width: 820,
    height: 36,
    props: { layout: 'horizontal', mainAlign: 'center', gap: 12, crossAlign: 'center' },
  });
  const trustList = [
    { label: '1 Mesin 1 Pelanggan', icon: 'shield-check', color: COLORS.emerald600 },
    { label: 'Timbangan Akurat Desimal', icon: 'scale', color: COLORS.brand600 },
    { label: 'Estimasi Selesai Tepat', icon: 'clock', color: COLORS.brand600 },
    { label: 'Struk Thermal Resmi', icon: 'printer', color: COLORS.slate600 },
  ];
  trustList.forEach((t) => {
    const pill = b.frame({
      name: `Trust-${t.label}`,
      parent: trustRow,
      width: 190,
      height: 32,
      props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, layout: 'horizontal', crossAlign: 'center', mainAlign: 'center', gap: 6, padding: [0, 10, 0, 10] },
    });
    b.vector({ name: 'Ico', parent: pill, width: 14, height: 14, path: ICONS[t.icon], props: { stroke: t.color } });
    b.text({ name: 'Lbl', parent: pill, text: t.label, props: { fontSize: 11, fontWeight: 'medium', fill: COLORS.slate700 } });
  });

  // 3. Keunggulan Bento Grid Section (height: 260)
  const keunggulanSection = b.frame({
    name: 'KeunggulanSection',
    parent: f01,
    width: 1440,
    height: 260,
    props: { fill: COLORS.slate50, layout: 'vertical', crossAlign: 'center', padding: [16, 60, 16, 60], gap: 10 },
  });

  b.text({ name: 'Head', parent: keunggulanSection, text: 'Keunggulan Standar Layanan Operasional CUCI.IN', props: { fontSize: 18, fontWeight: 'bold', fill: COLORS.slate900 } });

  const bentoRow = b.frame({ name: 'BentoRow', parent: keunggulanSection, width: 1320, height: 185, props: { layout: 'horizontal', gap: 16 } });
  const bentoItems = [
    { title: '1 Mesin 1 Pelanggan', desc: 'Pakaian tidak pernah dicampur dengan pakaian pelanggan lain untuk higienitas maksimal.', icon: 'shield-check', bg: COLORS.brand50, ic: COLORS.brand700 },
    { title: 'Timbangan Presisi Desimal', desc: 'Penimbangan digital akurat hingga desimal 0,1 kg tanpa pembulatan sepihak yang merugikan.', icon: 'scale', bg: COLORS.emerald50, ic: COLORS.emerald700 },
    { title: 'Pelacakan Real-Time', desc: 'Ketahui status cucian: Diterima, Diproses, Selesai, atau Diambil langsung lewat kode nota.', icon: 'activity', bg: COLORS.brand50, ic: COLORS.brand700 },
    { title: 'Struk Thermal & QRIS', desc: 'Pembayaran fleksibel (Tunai, QRIS, Bank Transfer) dengan bukti struk fisik sah dan riwayat digital.', icon: 'printer', bg: COLORS.amber50, ic: COLORS.amber700 },
  ];
  bentoItems.forEach((item) => {
    const card = b.frame({
      name: `Card-${item.title}`,
      parent: bentoRow,
      width: 318,
      height: 180,
      props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 14, padding: 16, layout: 'vertical', gap: 8 },
    });
    const icBox = b.frame({ name: 'IcBox', parent: card, width: 36, height: 36, props: { fill: item.bg, cornerRadius: 8, layout: 'horizontal', mainAlign: 'center', crossAlign: 'center' } });
    b.vector({ name: 'Ico', parent: icBox, width: 18, height: 18, path: ICONS[item.icon], props: { stroke: item.ic } });
    b.text({ name: 'T', parent: card, text: item.title, props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate900 } });
    b.text({ name: 'D', parent: card, width: 286, text: item.desc, props: { fontSize: 11, fill: COLORS.slate600, textAutoResize: 'height' } });
  });

  // 4. CTA Banner & Micro Footer (height: 156)
  const ctaContainer = b.frame({
    name: 'CTAContainer',
    parent: f01,
    width: 1440,
    height: 156,
    props: { fill: COLORS.slate100, layout: 'vertical', crossAlign: 'center', padding: [14, 60, 10, 60], gap: 10 },
  });

  const ctaCard = b.frame({
    name: 'CTABannerCard',
    parent: ctaContainer,
    width: 1320,
    height: 84,
    props: {
      fill: COLORS.brand700,
      cornerRadius: 14,
      padding: [14, 28, 14, 28],
      layout: 'horizontal',
      mainAlign: 'spaceBetween',
      crossAlign: 'center',
    },
  });

  const ctaLeft = b.frame({ name: 'CtaLeft', parent: ctaCard, width: 800, height: 56, props: { layout: 'vertical', gap: 4 } });
  b.text({ name: 'CtaHead', parent: ctaLeft, text: 'Sudah Punya Nota Cucian? Cek Status Pakaian Anda Sekarang!', props: { fontSize: 16, fontWeight: 'bold', fill: COLORS.white } });
  b.text({ name: 'CtaSub', parent: ctaLeft, text: 'Cukup masukkan nomor transaksi dari struk thermal Anda untuk pembaruan status pengerjaan secara instan.', props: { fontSize: 12, fill: COLORS.brand100 } });

  const ctaBtns = b.frame({ name: 'CtaBtns', parent: ctaCard, width: 340, height: 44, props: { layout: 'horizontal', gap: 10, mainAlign: 'max', crossAlign: 'center' } });
  const btnCtaTrack = b.button({ parent: ctaBtns, label: 'Lacak Nota', variant: 'secondary', icon: 'search', width: 140, height: 36 });
  const btnCtaLogin = b.button({ parent: ctaBtns, label: 'Masuk Akun', variant: 'secondary', icon: 'arrow-right', width: 140, height: 36 });

  b.text({
    name: 'MiniFooter',
    parent: ctaContainer,
    text: '© 2026 CUCI.IN Laundry Management System • Seluruh Hak Cipta Dilindungi • D4 Sistem Informasi Bisnis (Milestone 2)',
    props: { fontSize: 11, fill: COLORS.slate500, textAlign: 'center' },
  });

  // ========================================================
  // FRAME 02: Public Track Lookup (1440x900 2-Column View)
  // ========================================================
  const f02 = b.frame({
    name: '02-Public-Track-Lookup',
    x: 1560,
    y: 40,
    width: 1440,
    height: 900,
    props: { fill: COLORS.slate50, layout: 'vertical', gap: 0 },
  });

  // Header Bar
  const f02Header = b.frame({
    name: 'Header',
    parent: f02,
    width: 1440,
    height: 64,
    props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center', padding: [0, 48, 0, 48] },
  });
  b.text({ name: 'Logo', parent: f02Header, text: 'CUCI.IN — Portal Lacak Status Nota', props: { fontSize: 18, fontWeight: 'bold', fill: COLORS.brand700 } });
  const f02HeadActs = b.frame({ name: 'Acts', parent: f02Header, width: 320, height: 36, props: { layout: 'horizontal', gap: 10, mainAlign: 'max', crossAlign: 'center' } });
  const btnLookupBack = b.button({ parent: f02HeadActs, label: 'Kembali ke Beranda', variant: 'secondary', icon: 'arrow-left', width: 165, height: 36 });
  const btnLookupLogin = b.button({ parent: f02HeadActs, label: 'Masuk Sistem', variant: 'primary', icon: 'log-in', width: 130, height: 36 });

  // Main Container centered (836px)
  const f02Main = b.frame({
    name: 'MainTrackContainer',
    parent: f02,
    width: 1440,
    height: 836,
    props: { layout: 'vertical', crossAlign: 'center', gap: 14, padding: [16, 48, 16, 48] },
  });

  // Row 1: Search Card + Status Stepper Card
  const f02Row1 = b.frame({
    name: 'Row1Top',
    parent: f02Main,
    width: 1344,
    height: 136,
    props: { layout: 'horizontal', gap: 16 },
  });

  // Search Card (width: 580)
  const f02SearchCard = b.frame({
    name: 'SearchFormCard',
    parent: f02Row1,
    width: 580,
    height: 136,
    props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 14, padding: 16, layout: 'vertical', gap: 8 },
  });
  b.text({ name: 'Lbl', parent: f02SearchCard, text: 'Masukkan Kode Transaksi / Nomor Nota', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate700 } });
  const f02InputRow = b.frame({ name: 'Row', parent: f02SearchCard, width: 548, height: 42, props: { layout: 'horizontal', gap: 8 } });
  b.inputField({ parent: f02InputRow, width: 288, height: 42, value: 'TRX-20261024-0001', icon: 'receipt', mono: true });
  const btnScanQr = b.button({ parent: f02InputRow, label: 'Pindai QR', variant: 'secondary', icon: 'qr-code', width: 110, height: 42 });
  const btnCheckTrx = b.button({ parent: f02InputRow, label: 'Cek Nota', variant: 'primary', icon: 'search', width: 134, height: 42 });

  const recentChipsRow = b.frame({ name: 'Recent', parent: f02SearchCard, width: 548, height: 26, props: { layout: 'horizontal', crossAlign: 'center', gap: 8 } });
  b.text({ name: 'RLbl', parent: recentChipsRow, text: 'Riwayat Pencarian:', props: { fontSize: 11, fill: COLORS.slate500 } });
  b.badge({ parent: recentChipsRow, label: 'TRX-20261024-0001', tone: 'brand' });
  b.badge({ parent: recentChipsRow, label: 'TRX-20261020-0012', tone: 'slate' });

  // Stepper Card (width: 748)
  const f02StepperCard = b.frame({
    name: 'StepperCard',
    parent: f02Row1,
    width: 748,
    height: 136,
    props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 14, padding: [14, 20, 14, 20], layout: 'vertical', gap: 10 },
  });

  const f02StepHead = b.frame({ name: 'Head', parent: f02StepperCard, width: 708, height: 38, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
  const f02StepLeft = b.frame({ name: 'Left', parent: f02StepHead, width: 430, height: 38, props: { layout: 'vertical', gap: 2 } });
  b.text({ name: 'StLbl', parent: f02StepLeft, text: 'Status Cucian: DIPROSES', props: { fontSize: 15, fontWeight: 'bold', fill: COLORS.brand700 } });
  b.text({ name: 'StSub', parent: f02StepLeft, text: 'Pakaian sedang dicuci, dikeringkan, atau disetrika rapi di workshop.', props: { fontSize: 11, fill: COLORS.slate500 } });

  const f02StepRight = b.frame({ name: 'Right', parent: f02StepHead, width: 260, height: 38, props: { layout: 'vertical', crossAlign: 'max', gap: 2 } });
  b.text({ name: 'EstL', parent: f02StepRight, text: 'Perkiraan Selesai:', props: { fontSize: 10, fill: COLORS.slate500 } });
  b.text({ name: 'EstV', parent: f02StepRight, text: '25 Oktober 2026, 17:00 WIB', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate800 } });

  const stepperBar = b.frame({
    name: 'StepperBar',
    parent: f02StepperCard,
    width: 708,
    height: 48,
    props: { fill: COLORS.slate50, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 10, layout: 'horizontal', mainAlign: 'spaceAround', crossAlign: 'center', padding: [0, 16, 0, 16] },
  });
  const stepsList = [
    { label: '1. Diterima', state: 'done', color: COLORS.emerald700, icon: 'check-circle' },
    { label: '2. Diproses (Aktif)', state: 'active', color: COLORS.brand700, icon: 'activity' },
    { label: '3. Selesai', state: 'pending', color: COLORS.slate400, icon: 'clock' },
    { label: '4. Diambil', state: 'pending', color: COLORS.slate400, icon: 'receipt' },
  ];
  stepsList.forEach((st) => {
    const sNode = b.frame({ name: `Step-${st.label}`, parent: stepperBar, width: 150, height: 30, props: { layout: 'horizontal', crossAlign: 'center', gap: 6 } });
    b.vector({ name: 'I', parent: sNode, width: 14, height: 14, path: ICONS[st.icon], props: { stroke: st.color } });
    b.text({ name: 'T', parent: sNode, text: st.label, props: { fontSize: 11, fontWeight: st.state === 'active' ? 'bold' : 'medium', fill: st.color } });
  });

  // Row 2: Detail Nota Card (Left) + Upload Bukti Bayar (Right)
  const f02Row2 = b.frame({
    name: 'Row2Bottom',
    parent: f02Main,
    width: 1344,
    height: 650,
    props: { layout: 'horizontal', gap: 16 },
  });

  // Detail Nota Card (width: 780)
  const f02DetailCard = b.frame({
    name: 'DetailNotaCard',
    parent: f02Row2,
    width: 780,
    height: 650,
    props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 14, padding: 22, layout: 'vertical', gap: 14 },
  });

  const f02TrxInfo = b.frame({ name: 'TrxInfo', parent: f02DetailCard, width: 736, height: 42, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
  const f02TrxLeft = b.frame({ name: 'TL', parent: f02TrxInfo, width: 400, height: 42, props: { layout: 'vertical', gap: 2 } });
  b.text({ name: 'TrxCodeLbl', parent: f02TrxLeft, text: 'NOMOR NOTA TRANSAKSI', props: { fontSize: 10, fontWeight: 'bold', fill: COLORS.slate500 } });
  const trxCodeRow = b.frame({ name: 'TCRow', parent: f02TrxLeft, width: 350, height: 26, props: { layout: 'horizontal', crossAlign: 'center', gap: 8 } });
  b.text({ name: 'Code', parent: trxCodeRow, text: 'TRX-20261024-0001', props: { fontSize: 15, fontWeight: 'bold', fill: COLORS.slate900 } });
  b.button({ parent: trxCodeRow, label: 'Salin Tautan', variant: 'secondary', icon: 'copy', width: 115, height: 28 });

  const f02TrxRight = b.frame({ name: 'TR', parent: f02TrxInfo, width: 300, height: 42, props: { layout: 'vertical', crossAlign: 'max', gap: 2 } });
  b.text({ name: 'CustLbl', parent: f02TrxRight, text: 'NAMA PELANGGAN', props: { fontSize: 10, fontWeight: 'bold', fill: COLORS.slate500 } });
  b.text({ name: 'CustVal', parent: f02TrxRight, text: 'B*** S****** (Disamarkan)', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate800 } });

  b.text({ name: 'TblTitle', parent: f02DetailCard, text: 'RINCIAN LAYANAN & CUCIAN', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate500 } });
  const table = b.frame({
    name: 'ItemsTable',
    parent: f02DetailCard,
    width: 736,
    height: 120,
    props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 10, layout: 'vertical' },
  });

  const thead = b.frame({ name: 'THead', parent: table, width: 736, height: 36, props: { fill: COLORS.slate50, layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16] } });
  b.text({ name: 'Th1', parent: thead, width: 340, text: 'Layanan', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate600 } });
  b.text({ name: 'Th2', parent: thead, width: 120, text: 'Berat / Jumlah', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate600 } });
  b.text({ name: 'Th3', parent: thead, width: 130, text: 'Tarif', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate600 } });
  b.text({ name: 'Th4', parent: thead, width: 114, text: 'Subtotal', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate600, textAlign: 'right' } });

  const tr1 = b.frame({ name: 'Tr1', parent: table, width: 736, height: 40, props: { layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16], stroke: COLORS.slate100, strokeWeight: 1 } });
  b.text({ name: 'Td1', parent: tr1, width: 340, text: 'Cuci Kering Setrika Reguler', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate900 } });
  b.text({ name: 'Td2', parent: tr1, width: 120, text: '4.0 kg', props: { fontSize: 12, fill: COLORS.slate700 } });
  b.text({ name: 'Td3', parent: tr1, width: 130, text: 'Rp 6.000 /kg', props: { fontSize: 12, fill: COLORS.slate600 } });
  b.text({ name: 'Td4', parent: tr1, width: 114, text: 'Rp 24.000', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate900, textAlign: 'right' } });

  const tr2 = b.frame({ name: 'Tr2', parent: table, width: 736, height: 40, props: { layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16] } });
  b.text({ name: 'Td1', parent: tr2, width: 340, text: 'Setrika Kemeja Kerja Satuan', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate900 } });
  b.text({ name: 'Td2', parent: tr2, width: 120, text: '2 pcs', props: { fontSize: 12, fill: COLORS.slate700 } });
  b.text({ name: 'Td3', parent: tr2, width: 130, text: 'Rp 5.000 /pcs', props: { fontSize: 12, fill: COLORS.slate600 } });
  b.text({ name: 'Td4', parent: tr2, width: 114, text: 'Rp 10.000', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate900, textAlign: 'right' } });

  const paySummary = b.frame({
    name: 'PaySummary',
    parent: f02DetailCard,
    width: 736,
    height: 48,
    props: { fill: COLORS.slate50, cornerRadius: 10, padding: [0, 16, 0, 16], layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' },
  });
  const payLeft = b.frame({ name: 'PL', parent: paySummary, width: 350, height: 32, props: { layout: 'horizontal', crossAlign: 'center', gap: 10 } });
  b.text({ name: 'TotLbl', parent: payLeft, text: 'Total Tagihan:', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate700 } });
  b.text({ name: 'TotVal', parent: payLeft, text: 'Rp 34.000', props: { fontSize: 16, fontWeight: 'bold', fill: COLORS.slate900 } });
  b.badge({ parent: paySummary, label: 'BELUM LUNAS', tone: 'warning' });

  // Right Column: Upload Bukti Pembayaran Digital (width: 548)
  const uploadBox = b.frame({
    name: 'UploadBox',
    parent: f02Row2,
    width: 548,
    height: 650,
    props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 14, padding: 22, layout: 'vertical', gap: 14 },
  });
  b.text({ name: 'UpTitle', parent: uploadBox, text: 'Unggah Bukti Pembayaran Digital (Nora P10)', props: { fontSize: 14, fontWeight: 'bold', fill: COLORS.slate900 } });
  b.text({ name: 'UpSub', parent: uploadBox, text: 'Bagi pelanggan yang memilih pembayaran non-tunai (QRIS / Transfer Bank), unggah foto bukti struk untuk diverifikasi kasir.', props: { fontSize: 12, fill: COLORS.slate600 } });

  const dropzone = b.frame({
    name: 'Dropzone',
    parent: uploadBox,
    width: 504,
    height: 180,
    props: { fill: COLORS.brand50, stroke: COLORS.brand400, strokeWeight: 1, cornerRadius: 12, layout: 'vertical', mainAlign: 'center', crossAlign: 'center', gap: 8 },
  });
  b.vector({ name: 'UpIco', parent: dropzone, width: 28, height: 28, path: ICONS.download, props: { stroke: COLORS.brand600 } });
  b.text({ name: 'DropText1', parent: dropzone, text: 'Klik untuk memilih foto atau seret berkas ke sini', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.brand700 } });
  b.text({ name: 'DropText2', parent: dropzone, text: 'Format JPG, PNG, atau WEBP maksimal 2 MB', props: { fontSize: 11, fill: COLORS.slate500 } });

  b.button({ parent: uploadBox, label: 'Kirim Bukti Pembayaran', variant: 'primary', icon: 'check-circle', width: 504, height: 44 });

  const infoNotice = b.frame({
    name: 'InfoNotice',
    parent: uploadBox,
    width: 504,
    height: 90,
    props: { fill: COLORS.slate50, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 10, padding: 14, layout: 'vertical', gap: 4 },
  });
  b.text({ name: 'NT1', parent: infoNotice, text: 'Catatan Konfirmasi Otomatis:', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate700 } });
  b.text({ name: 'NT2', parent: infoNotice, text: 'Setelah bukti bayar diunggah, tim kasir akan melakukan verifikasi di panel operasional dalam waktu maksimal 15 menit.', props: { fontSize: 11, fill: COLORS.slate600 } });

  // ========================================================
  // FRAME 03: Public QR Scanner Modal (Overlay Modal)
  // ========================================================
  const f03 = b.frame({
    name: '03-Public-QR-Scanner-Modal',
    x: 3080,
    y: 40,
    width: 1440,
    height: 900,
    props: { fill: COLORS.slate800, layout: 'horizontal', mainAlign: 'center', crossAlign: 'center' },
  });

  const modal = b.frame({
    name: 'QRScannerModal',
    parent: f03,
    width: 480,
    height: 520,
    props: { fill: COLORS.white, cornerRadius: 20, padding: 24, layout: 'vertical', gap: 16 },
  });

  const mHeader = b.frame({ name: 'MHeader', parent: modal, width: 432, height: 36, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
  b.text({ name: 'MTitle', parent: mHeader, text: 'Pindai QR Code Nota Fisik', props: { fontSize: 16, fontWeight: 'bold', fill: COLORS.slate900 } });
  const btnCloseModal = b.button({ parent: mHeader, label: 'Tutup', variant: 'ghost', icon: 'x', width: 80, height: 32 });

  const camBox = b.frame({
    name: 'CameraViewport',
    parent: modal,
    width: 432,
    height: 340,
    props: { fill: COLORS.slate950, cornerRadius: 14, layout: 'vertical', mainAlign: 'center', crossAlign: 'center', gap: 12 },
  });
  b.vector({ name: 'QRIco', parent: camBox, width: 72, height: 72, path: ICONS['qr-code'], props: { stroke: COLORS.brand400 } });
  b.text({ name: 'CamTxt', parent: camBox, text: 'Arahkan kamera ke QR Code di nota fisik laundry', props: { fontSize: 12, fill: COLORS.slate300 } });

  const modalFooter = b.frame({ name: 'MFooter', parent: modal, width: 432, height: 40, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
  b.button({ parent: modalFooter, label: 'Ganti Kamera', variant: 'secondary', icon: 'refresh-cw', width: 140, height: 36 });
  b.button({ parent: modalFooter, label: 'Tutup Pemindai', variant: 'secondary', width: 130, height: 36 });

  // ========================================================
  // FRAME 04: Auth Login (1:1 login.blade.php & guest.blade.php)
  // ========================================================
  let btnLoginBack, btnLoginSubmit, btnChipAdmin, btnChipStaff, btnChipCustomer, btnLinkRegister, btnLinkTrack, btnForgotPassword;

  const f04Shell = b.guestShell({
    name: '04-Auth-Login',
    x: 4600,
    y: 40,
    title: 'Masuk ke CUCI.IN',
    subtitle: 'Silakan masuk untuk melanjutkan ke portal operasional laundry.',
    maxWidth: 460,
    contentFn: (builder, card) => {
      builder.inputField({
        parent: card,
        width: 396,
        height: 42,
        label: 'Alamat Email',
        placeholder: 'nama@domain.com',
        value: 'budi@gmail.com',
        icon: 'mail',
      });

      builder.inputField({
        parent: card,
        width: 396,
        height: 42,
        label: 'Kata Sandi',
        placeholder: 'Masukkan kata sandi',
        value: '••••••••',
        icon: 'lock',
      });

      const optRow = builder.frame({
        name: 'OptRow',
        parent: card,
        width: 396,
        height: 24,
        props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' },
      });
      builder.text({ name: 'Remember', parent: optRow, text: 'Ingat saya di perangkat ini', props: { fontSize: 11, fill: COLORS.slate600 } });
      btnForgotPassword = builder.text({ name: 'Forgot', parent: optRow, text: 'Lupa Password?', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.brand700 } });

      btnLoginSubmit = builder.button({
        parent: card,
        label: 'Masuk',
        variant: 'primary',
        icon: 'log-in',
        width: 396,
        height: 44,
      });

      // Demo Quick Login Box (Pilih Role Masuk untuk Uji Prototipe 1 Alur)
      const demoBox = builder.frame({
        name: 'DemoBox',
        parent: card,
        width: 396,
        height: 68,
        props: { fill: COLORS.slate50, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 8, padding: 8, layout: 'vertical', gap: 6 },
      });
      builder.text({ name: 'DT', parent: demoBox, text: 'Pilih Role Masuk (Uji Prototipe 1 Alur):', props: { fontSize: 10, fontWeight: 'bold', fill: COLORS.slate600 } });
      const chips = builder.frame({ name: 'Chips', parent: demoBox, width: 380, height: 32, props: { layout: 'horizontal', gap: 6 } });
      btnChipCustomer = builder.button({ parent: chips, label: 'Customer', variant: 'secondary', width: 120, height: 32 });
      btnChipStaff = builder.button({ parent: chips, label: 'Kasir POS', variant: 'secondary', width: 120, height: 32 });
      btnChipAdmin = builder.button({ parent: chips, label: 'Admin/Owner', variant: 'secondary', width: 120, height: 32 });

      btnLinkRegister = builder.text({
        name: 'LinkRegister',
        parent: card,
        text: 'Belum memiliki akun? Daftar Pelanggan',
        props: { fontSize: 12, fill: COLORS.brand700, fontWeight: 'bold', textAlign: 'center' },
      });

      btnLinkTrack = builder.text({
        name: 'LinkTrack',
        parent: card,
        text: 'Ingin lacak pesanan tanpa login? Cek Transaksi',
        props: { fontSize: 12, fill: COLORS.slate600, textAlign: 'center' },
      });
    },
  });

  const f04 = f04Shell.root;
  btnLoginBack = f04Shell.backBtn;

  // ========================================================
  // FRAME 05: Auth Register (1:1 register.blade.php)
  // ========================================================
  let btnRegisterBack, btnRegisterSubmit, btnLinkLogin;

  const f05Shell = b.guestShell({
    name: '05-Auth-Register',
    x: 6120,
    y: 40,
    title: 'Daftar Akun Baru Pelanggan',
    subtitle: 'Buat akun untuk memantau cucian mandiri dan riwayat komplain.',
    maxWidth: 460,
    contentFn: (builder, card) => {
      builder.inputField({ parent: card, width: 396, height: 38, label: 'Nama Lengkap', value: 'Budi Santoso' });
      builder.inputField({ parent: card, width: 396, height: 38, label: 'Nomor WhatsApp', value: '081299887766' });
      builder.inputField({ parent: card, width: 396, height: 38, label: 'Alamat Pengantaran', value: 'Jl. Rungkut Madya No. 10' });
      builder.inputField({ parent: card, width: 396, height: 38, label: 'Alamat Email', value: 'budi@gmail.com', icon: 'mail' });
      builder.inputField({ parent: card, width: 396, height: 38, label: 'Kata Sandi', value: '••••••••', icon: 'lock' });

      btnRegisterSubmit = builder.button({
        parent: card,
        label: 'Daftar Akun Pelanggan',
        variant: 'primary',
        width: 396,
        height: 44,
      });

      btnLinkLogin = builder.text({
        name: 'LinkLogin',
        parent: card,
        text: 'Sudah memiliki akun? Masuk ke Akun',
        props: { fontSize: 12, fill: COLORS.brand700, fontWeight: 'bold', textAlign: 'center' },
      });
    },
  });

  const f05 = f05Shell.root;
  btnRegisterBack = f05Shell.backBtn;

  // ========================================================
  // FRAME 20: Auth Forgot Password (1:1 auth/forgot-password.blade.php)
  // ========================================================
  let btnForgotBack, btnForgotSubmit, btnForgotLinkLogin;

  const f20Shell = b.guestShell({
    name: '20-Auth-Forgot-Password',
    x: 7640,
    y: 40,
    title: 'Lupa Kata Sandi?',
    subtitle: 'Masukkan alamat email yang terdaftar. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi Anda.',
    maxWidth: 460,
    contentFn: (builder, card) => {
      const statusAlert = builder.frame({
        name: 'StatusAlert',
        parent: card,
        width: 396,
        height: 52,
        props: {
          fill: COLORS.emerald50,
          stroke: COLORS.emerald100,
          strokeWeight: 1,
          cornerRadius: 12,
          padding: [10, 14, 10, 14],
          layout: 'horizontal',
          crossAlign: 'center',
          gap: 10,
        },
      });
      builder.vector({ name: 'ChkIco', parent: statusAlert, width: 16, height: 16, path: ICONS['check-circle'], props: { stroke: COLORS.emerald700 } });
      builder.text({
        name: 'StatusTxt',
        parent: statusAlert,
        text: 'Tautan reset kata sandi telah dikirim ke email Anda.',
        props: { fontSize: 11, fontWeight: 'medium', fill: COLORS.emerald700 },
        width: 340,
      });

      builder.inputField({
        parent: card,
        width: 396,
        height: 42,
        label: 'Email Terdaftar',
        placeholder: 'nama@email.com',
        value: 'budi.santoso@example.com',
        icon: 'mail',
      });

      btnForgotSubmit = builder.button({
        parent: card,
        label: 'Kirim Tautan Reset Password',
        variant: 'primary',
        icon: 'arrow-right',
        width: 396,
        height: 44,
      });

      btnForgotLinkLogin = builder.text({
        name: 'LinkBackLogin',
        parent: card,
        text: 'Ingat kata sandi Anda? Kembali ke Halaman Masuk',
        props: { fontSize: 12, fill: COLORS.brand700, fontWeight: 'bold', textAlign: 'center' },
      });
    },
  });

  const f20 = f20Shell.root;
  btnForgotBack = f20Shell.backBtn;

  // ========================================================
  // FRAME 21: Auth Reset Password (1:1 auth/reset-password.blade.php)
  // ========================================================
  let btnResetBack, btnResetSubmit, btnResetLinkLogin;

  const f21Shell = b.guestShell({
    name: '21-Auth-Reset-Password',
    x: 9160,
    y: 40,
    title: 'Atur Ulang Kata Sandi',
    subtitle: 'Silakan masukkan kata sandi baru yang aman untuk akun Anda.',
    maxWidth: 460,
    contentFn: (builder, card) => {
      builder.inputField({
        parent: card,
        width: 396,
        height: 40,
        label: 'Email Akun',
        value: 'budi.santoso@example.com',
        icon: 'mail',
      });

      builder.inputField({
        parent: card,
        width: 396,
        height: 40,
        label: 'Password Baru',
        placeholder: 'Minimal 8 karakter',
        value: '••••••••••••',
        icon: 'lock',
      });

      builder.inputField({
        parent: card,
        width: 396,
        height: 40,
        label: 'Ulangi Password Baru',
        placeholder: 'Ulangi password baru',
        value: '••••••••••••',
        icon: 'shield-check',
      });

      btnResetSubmit = builder.button({
        parent: card,
        label: 'Simpan Kata Sandi Baru',
        variant: 'primary',
        icon: 'check',
        width: 396,
        height: 44,
      });

      btnResetLinkLogin = builder.text({
        name: 'LinkCancelReset',
        parent: card,
        text: 'Batal atur ulang sandi? Kembali ke Halaman Masuk',
        props: { fontSize: 12, fill: COLORS.brand700, fontWeight: 'bold', textAlign: 'center' },
      });
    },
  });

  const f21 = f21Shell.root;
  btnResetBack = f21Shell.backBtn;

  // ========================================================
  // FRAME 00: Design Tokens & UI Kit (1440x900)
  // ========================================================
  const dsFrame = b.frame({
    name: '00-Design-Tokens-And-UI-Kit',
    x: 10680,
    y: 40,
    width: 1440,
    height: 900,
    props: {
      fill: COLORS.white,
      cornerRadius: 16,
      stroke: COLORS.slate200,
      strokeWeight: 1,
      padding: 32,
      layout: 'vertical',
      gap: 24,
    },
  });

  b.text({
    name: 'DS-Title',
    parent: dsFrame,
    text: 'CUCI.IN — Design Tokens & Reusable UI Primitives',
    props: { fontSize: 26, fontWeight: 'bold', fill: COLORS.slate900 },
  });

  const colorSection = b.frame({
    name: 'Color-Palette',
    parent: dsFrame,
    width: 1376,
    height: 120,
    props: { layout: 'vertical', gap: 10 },
  });
  b.text({ name: 'Heading', parent: colorSection, text: 'Palet Warna Brand & Slate Neutral (Tailwind CSS v4)', props: { fontSize: 15, fontWeight: 'bold' } });

  const swatchesRow = b.frame({ name: 'Swatches', parent: colorSection, width: 1376, height: 80, props: { layout: 'horizontal', gap: 12 } });
  const brandScale = [
    { name: 'brand-50', hex: COLORS.brand50 },
    { name: 'brand-100', hex: COLORS.brand100 },
    { name: 'brand-300', hex: COLORS.brand300 },
    { name: 'brand-500', hex: COLORS.brand500 },
    { name: 'brand-600', hex: COLORS.brand600 },
    { name: 'brand-700', hex: COLORS.brand700 },
    { name: 'brand-900', hex: COLORS.brand900 },
    { name: 'emerald-600', hex: COLORS.emerald600 },
    { name: 'amber-500', hex: COLORS.amber500 },
    { name: 'rose-600', hex: COLORS.rose600 },
  ];
  brandScale.forEach((c) => {
    const sw = b.frame({ name: c.name, parent: swatchesRow, width: 116, height: 75, props: { fill: c.hex, cornerRadius: 8, padding: 8, layout: 'vertical', mainAlign: 'max' } });
    b.text({ name: 'Lbl', parent: sw, text: c.name, props: { fontSize: 10, fill: c.name.includes('50') || c.name.includes('100') ? COLORS.slate800 : COLORS.white, fontWeight: 'bold' } });
  });

  const compSection = b.frame({ name: 'Components-Preview', parent: dsFrame, width: 1376, height: 350, props: { layout: 'vertical', gap: 20 } });
  b.text({ name: 'Heading', parent: compSection, text: 'Varian Tombol Standar Desain Token & Status Badges', props: { fontSize: 15, fontWeight: 'bold' } });

  const btnRow = b.frame({ name: 'BtnRow', parent: compSection, width: 1376, height: 48, props: { layout: 'horizontal', gap: 12, crossAlign: 'center' } });
  b.button({ parent: btnRow, label: 'Primary POS [F2]', variant: 'primary', icon: 'plus-circle', kbd: 'F2' });
  b.button({ parent: btnRow, label: 'Secondary Button', variant: 'secondary', icon: 'search' });
  b.button({ parent: btnRow, label: 'Ghost Action', variant: 'ghost' });
  b.button({ parent: btnRow, label: 'Tolak Pembayaran', variant: 'destructive', icon: 'alert-triangle' });

  const badgeRow = b.frame({ name: 'BadgeRow', parent: compSection, width: 1376, height: 40, props: { layout: 'horizontal', gap: 12, crossAlign: 'center' } });
  b.badge({ parent: badgeRow, label: 'Diterima (Aktif)', tone: 'brand' });
  b.badge({ parent: badgeRow, label: 'Sedang Dicuci', tone: 'brand' });
  b.badge({ parent: badgeRow, label: 'Selesai / Lunas', tone: 'success' });
  b.badge({ parent: badgeRow, label: 'Menunggu Verifikasi', tone: 'warning' });
  b.badge({ parent: badgeRow, label: 'Terlambat / Ditolak', tone: 'danger' });

  // ========================================================
  // FIGCRAFT PROTOTYPING WIRES (Native Single Closed-Loop Flow)
  // ========================================================
  b.flow({ start: f01, name: 'Alur Utama CUCI.IN (Master Prototype)' });

  // 1. From 01-Welcome-Landing-Page
  b.wire({ id: btnHeaderTrack, dest: f02 });
  b.wire({ id: btnHeroTrack, dest: f02 });
  b.wire({ id: btnCtaTrack, dest: f02 });

  b.wire({ id: btnHeaderLogin, dest: f04 });
  b.wire({ id: btnCtaLogin, dest: f04 });

  b.wire({ id: btnHeaderRegister, dest: f05 });

  // 2. From 02-Public-Track-Lookup
  b.wire({ id: btnLookupBack, dest: f01 });
  b.wire({ id: btnLookupLogin, dest: f04 });
  b.wire({ id: btnScanQr, dest: f03 });
  b.wire({ id: btnCloseModal, dest: f02 });

  // 3. From 04-Auth-Login
  b.wire({ id: btnLoginBack, dest: f01 });
  b.wire({ id: btnLinkRegister, dest: f05 });
  b.wire({ id: btnLinkTrack, dest: f02 });
  b.wire({ id: btnForgotPassword, dest: f20 });

  // 4. From 05-Auth-Register
  b.wire({ id: btnRegisterBack, dest: f01 });
  b.wire({ id: btnLinkLogin, dest: f04 });
  b.wire({ id: btnRegisterSubmit, dest: f04 });

  // 5. From 20-Auth-Forgot-Password & 21-Auth-Reset-Password
  b.wire({ id: btnForgotBack, dest: f01 });
  b.wire({ id: btnForgotLinkLogin, dest: f04 });
  b.wire({ id: btnForgotSubmit, dest: f21 });

  b.wire({ id: btnResetBack, dest: f01 });
  b.wire({ id: btnResetLinkLogin, dest: f04 });
  b.wire({ id: btnResetSubmit, dest: f04 });

  return { f01, f02, f03, f04, f05, f20, f21, btnLoginSubmit, btnChipAdmin, btnChipStaff, btnChipCustomer };
}
