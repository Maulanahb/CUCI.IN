import { COLORS, ICONS } from '../tokens.mjs';

export function buildPublicAuthScreens(b) {
  // ========================================================
  // PAGE 0: DESIGN SYSTEM & UI KIT
  // ========================================================
  b.page('00 - Design System', COLORS.slate100);

  const dsFrame = b.frame({
    name: '00-Design-Tokens-And-UI-Kit',
    x: 40,
    y: 40,
    width: 1440,
    height: 1200,
    props: {
      fill: COLORS.white,
      cornerRadius: 16,
      stroke: COLORS.slate200,
      strokeWeight: 1,
      padding: 40,
      layout: 'vertical',
      gap: 32,
    },
  });

  // Title
  b.text({
    name: 'DS-Title',
    parent: dsFrame,
    text: 'CUCI.IN — Design Tokens & Reusable UI Primitives',
    props: { fontSize: 28, fontWeight: 'bold', fill: COLORS.slate900 },
  });

  // Color Palette Section
  const colorSection = b.frame({
    name: 'Color-Palette',
    parent: dsFrame,
    width: 1360,
    height: 130,
    props: { layout: 'vertical', gap: 12 },
  });
  b.text({ name: 'Heading', parent: colorSection, text: 'Palet Warna Brand & Slate Neutral (Tailwind CSS v4)', props: { fontSize: 16, fontWeight: 'bold' } });

  const swatchesRow = b.frame({ name: 'Swatches', parent: colorSection, width: 1360, height: 80, props: { layout: 'horizontal', gap: 12 } });
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
  brandScale.forEach(c => {
    const sw = b.frame({ name: c.name, parent: swatchesRow, width: 110, height: 75, props: { fill: c.hex, cornerRadius: 8, padding: 8, layout: 'vertical', mainAlign: 'max' } });
    b.text({ name: 'Lbl', parent: sw, text: c.name, props: { fontSize: 10, fill: c.name.includes('50') || c.name.includes('100') ? COLORS.slate800 : COLORS.white, fontWeight: 'bold' } });
  });

  // UI Components Section
  const compSection = b.frame({ name: 'Components-Preview', parent: dsFrame, width: 1360, height: 350, props: { layout: 'vertical', gap: 20 } });
  b.text({ name: 'Heading', parent: compSection, text: 'Varian Tombol & Status Badges', props: { fontSize: 16, fontWeight: 'bold' } });

  const btnRow = b.frame({ name: 'BtnRow', parent: compSection, width: 1360, height: 48, props: { layout: 'horizontal', gap: 12, crossAlign: 'center' } });
  b.button({ parent: btnRow, label: 'Primary POS [F2]', variant: 'primary', icon: 'plus-circle', kbd: 'F2' });
  b.button({ parent: btnRow, label: 'Secondary Button', variant: 'secondary', icon: 'search' });
  b.button({ parent: btnRow, label: 'Ghost Action', variant: 'ghost' });
  b.button({ parent: btnRow, label: 'Tolak Pembayaran', variant: 'destructive', icon: 'alert-triangle' });

  const badgeRow = b.frame({ name: 'BadgeRow', parent: compSection, width: 1360, height: 40, props: { layout: 'horizontal', gap: 12, crossAlign: 'center' } });
  b.badge({ parent: badgeRow, label: 'Diterima (Aktif)', tone: 'brand' });
  b.badge({ parent: badgeRow, label: 'Sedang Dicuci', tone: 'brand' });
  b.badge({ parent: badgeRow, label: 'Selesai / Lunas', tone: 'success' });
  b.badge({ parent: badgeRow, label: 'Menunggu Verifikasi', tone: 'warning' });
  b.badge({ parent: badgeRow, label: 'Terlambat / Ditolak', tone: 'danger' });

  // ========================================================
  // PAGE 1: PUBLIC & AUTHENTICATION
  // ========================================================
  b.page('01 - Public & Auth', COLORS.slate50);

  // --------------------------------------------------------
  // FRAME 01: Welcome Landing Page (1:1 welcome.blade.php)
  // --------------------------------------------------------
  const f01 = b.frame({
    name: '01-Welcome-Landing-Page',
    x: 40,
    y: 40,
    width: 1440,
    height: 2600,
    props: { fill: COLORS.slate50, layout: 'vertical', gap: 0 },
  });

  // 1. Sticky Glass Header (height: 64)
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
  ['Fitur Layanan', 'Kalkulator Tarif', 'Keunggulan', 'Cara Kerja'].forEach(link => {
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

  // 2. Hero Section (height: 520, bg: brand50/40)
  const heroSection = b.frame({
    name: 'HeroSection',
    parent: f01,
    width: 1440,
    height: 520,
    props: {
      fill: COLORS.brand50,
      layout: 'vertical',
      crossAlign: 'center',
      padding: [48, 60, 48, 60],
      gap: 16,
    },
  });

  b.text({
    name: 'HeroTitle1',
    parent: heroSection,
    text: 'Laundry Bersih, Higienis & Terpantau Real-Time',
    props: { fontSize: 40, fontWeight: 'bold', fill: COLORS.slate900, textAlign: 'center' },
  });

  b.text({
    name: 'HeroSub',
    parent: heroSection,
    text: 'Nikmati kemudahan mencuci tanpa rasa cemas. Timbangan presisi, pakaian tidak pernah dicampur,\ndan Anda dapat memantau status cucian langsung dari ponsel menggunakan kode nota.',
    props: { fontSize: 15, fill: COLORS.slate600, textAlign: 'center' },
  });

  // Quick Tracking Box Form
  const quickTrackBox = b.frame({
    name: 'QuickTrackFormCard',
    parent: heroSection,
    width: 720,
    height: 120,
    props: {
      fill: COLORS.white,
      stroke: COLORS.slate200,
      strokeWeight: 1,
      cornerRadius: 16,
      padding: [16, 20, 16, 20],
      layout: 'vertical',
      gap: 10,
    },
  });

  const trackInputRow = b.frame({
    name: 'InputRow',
    parent: quickTrackBox,
    width: 680,
    height: 48,
    props: { layout: 'horizontal', gap: 10, crossAlign: 'center' },
  });

  const trackField = b.frame({
    name: 'FieldBox',
    parent: trackInputRow,
    width: 480,
    height: 48,
    props: {
      fill: COLORS.slate50,
      stroke: COLORS.slate200,
      strokeWeight: 1,
      cornerRadius: 12,
      layout: 'horizontal',
      crossAlign: 'center',
      gap: 10,
      padding: [0, 16, 0, 16],
    },
  });
  b.vector({ name: 'ReceiptIco', parent: trackField, width: 18, height: 18, path: ICONS.receipt, props: { stroke: COLORS.slate400 } });
  b.text({ name: 'FieldValue', parent: trackField, text: 'TRX-20261024-0001', props: { fontSize: 14, fontWeight: 'bold', fill: COLORS.slate900 } });

  const btnHeroTrack = b.button({
    parent: trackInputRow,
    label: 'Lacak Sekarang',
    variant: 'primary',
    icon: 'arrow-right',
    width: 190,
    height: 48,
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
    height: 40,
    props: { layout: 'horizontal', mainAlign: 'center', gap: 12, crossAlign: 'center' },
  });
  const trustList = [
    { label: '1 Mesin 1 Pelanggan', icon: 'shield-check', color: COLORS.emerald600 },
    { label: 'Timbangan Akurat Desimal', icon: 'scale', color: COLORS.brand600 },
    { label: 'Estimasi Selesai Tepat', icon: 'clock', color: COLORS.brand600 },
    { label: 'Struk Thermal Resmi', icon: 'printer', color: COLORS.slate600 },
  ];
  trustList.forEach(t => {
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

  // 3. Calculator Section (height: 640)
  const calcSection = b.frame({
    name: 'CalculatorSection',
    parent: f01,
    width: 1440,
    height: 640,
    props: {
      fill: COLORS.white,
      stroke: COLORS.slate200,
      strokeWeight: 1,
      layout: 'vertical',
      crossAlign: 'center',
      padding: [48, 60, 48, 60],
      gap: 14,
    },
  });

  b.badge({ parent: calcSection, label: 'Simulasi Biaya & Durasi', tone: 'brand', width: 170 });
  b.text({ name: 'CalcHead', parent: calcSection, text: 'Kalkulator Estimasi Biaya Laundry', props: { fontSize: 26, fontWeight: 'bold', fill: COLORS.slate900 } });
  b.text({ name: 'CalcSub', parent: calcSection, text: 'Hitung perkiraan total tagihan dan waktu pengerjaan secara transparan sebelum mengantar pakaian Anda.', props: { fontSize: 13, fill: COLORS.slate600 } });

  // Calculator Card Container
  const calcCard = b.frame({
    name: 'CalculatorCard',
    parent: calcSection,
    width: 820,
    height: 440,
    props: {
      fill: COLORS.slate50,
      stroke: COLORS.slate200,
      strokeWeight: 1,
      cornerRadius: 18,
      padding: 24,
      layout: 'vertical',
      gap: 16,
    },
  });

  b.text({ name: 'Step1', parent: calcCard, text: '1. PILIH PAKET LAYANAN', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate900 } });

  // 4 Service Buttons (2x2 Grid)
  const gridRow1 = b.frame({ name: 'GridRow1', parent: calcCard, width: 772, height: 60, props: { layout: 'horizontal', gap: 12 } });
  const gridRow2 = b.frame({ name: 'GridRow2', parent: calcCard, width: 772, height: 60, props: { layout: 'horizontal', gap: 12 } });

  // Service 1 (Selected Active)
  const s1 = b.frame({
    name: 'Service-Reguler-Active',
    parent: gridRow1,
    width: 380,
    height: 60,
    props: { fill: COLORS.white, stroke: COLORS.brand500, strokeWeight: 2, cornerRadius: 12, layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center', padding: [0, 16, 0, 16] },
  });
  const s1Txt = b.frame({ name: 'Txt', parent: s1, width: 220, height: 38, props: { layout: 'vertical', gap: 2 } });
  b.text({ name: 'T', parent: s1Txt, text: 'Cuci Kering Setrika', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate900 } });
  b.text({ name: 'D', parent: s1Txt, text: 'Estimasi 48 Jam (2 Hari)', props: { fontSize: 11, fill: COLORS.slate500 } });
  b.text({ name: 'P', parent: s1, text: 'Rp 6.000 /kg', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.brand700 } });

  // Service 2
  const s2 = b.frame({
    name: 'Service-Express',
    parent: gridRow1,
    width: 380,
    height: 60,
    props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 12, layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center', padding: [0, 16, 0, 16] },
  });
  const s2Txt = b.frame({ name: 'Txt', parent: s2, width: 220, height: 38, props: { layout: 'vertical', gap: 2 } });
  b.text({ name: 'T', parent: s2Txt, text: 'Kilat Express 24 Jam', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate900 } });
  b.text({ name: 'D', parent: s2Txt, text: 'Estimasi 1 Hari', props: { fontSize: 11, fill: COLORS.slate500 } });
  b.text({ name: 'P', parent: s2, text: 'Rp 10.000 /kg', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.brand700 } });

  // Service 3
  const s3 = b.frame({
    name: 'Service-Super',
    parent: gridRow2,
    width: 380,
    height: 60,
    props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 12, layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center', padding: [0, 16, 0, 16] },
  });
  const s3Txt = b.frame({ name: 'Txt', parent: s3, width: 220, height: 38, props: { layout: 'vertical', gap: 2 } });
  b.text({ name: 'T', parent: s3Txt, text: 'Super Express 8 Jam', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate900 } });
  b.text({ name: 'D', parent: s3Txt, text: 'Selesai di Hari yang Sama', props: { fontSize: 11, fill: COLORS.slate500 } });
  b.text({ name: 'P', parent: s3, text: 'Rp 15.000 /kg', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.brand700 } });

  // Service 4
  const s4 = b.frame({
    name: 'Service-Setrika',
    parent: gridRow2,
    width: 380,
    height: 60,
    props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 12, layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center', padding: [0, 16, 0, 16] },
  });
  const s4Txt = b.frame({ name: 'Txt', parent: s4, width: 220, height: 38, props: { layout: 'vertical', gap: 2 } });
  b.text({ name: 'T', parent: s4Txt, text: 'Setrika Rapi Saja', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate900 } });
  b.text({ name: 'D', parent: s4Txt, text: 'Estimasi 24 Jam', props: { fontSize: 11, fill: COLORS.slate500 } });
  b.text({ name: 'P', parent: s4, text: 'Rp 4.000 /kg', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.brand700 } });

  // Step 2: Slider Simulation
  const sliderHead = b.frame({ name: 'SliderHead', parent: calcCard, width: 772, height: 24, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
  b.text({ name: 'Step2', parent: sliderHead, text: '2. PERKIRAAN BERAT PAKAIAN', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate900 } });
  b.text({ name: 'WeightVal', parent: sliderHead, text: '3.0 Kg', props: { fontSize: 15, fontWeight: 'bold', fill: COLORS.brand700 } });

  const sliderBar = b.frame({ name: 'SliderBar', parent: calcCard, width: 772, height: 12, props: { layout: 'horizontal', crossAlign: 'center' } });
  b.rect({ name: 'TrackBg', parent: sliderBar, width: 772, height: 8, props: { fill: COLORS.slate200, cornerRadius: 4 } });

  const sliderLabels = b.frame({ name: 'SliderLabels', parent: calcCard, width: 772, height: 16, props: { layout: 'horizontal', mainAlign: 'spaceBetween' } });
  ['1 Kg (3-4 potong)', '5 Kg (1 minggu)', '10 Kg', '20 Kg'].forEach(lbl => {
    b.text({ name: 'L', parent: sliderLabels, text: lbl, props: { fontSize: 10, fill: COLORS.slate500 } });
  });

  // Output Box
  const outputBox = b.frame({
    name: 'OutputBox',
    parent: calcCard,
    width: 772,
    height: 100,
    props: {
      fill: COLORS.brand50,
      stroke: COLORS.brand200,
      strokeWeight: 1,
      cornerRadius: 14,
      padding: [16, 20, 16, 20],
      layout: 'horizontal',
      mainAlign: 'spaceBetween',
      crossAlign: 'center',
    },
  });

  const outLeft = b.frame({ name: 'OutLeft', parent: outputBox, width: 500, height: 68, props: { layout: 'vertical', gap: 4 } });
  b.text({ name: 'SName', parent: outLeft, text: 'Cuci Kering Setrika Reguler', props: { fontSize: 12, fontWeight: 'medium', fill: COLORS.slate600 } });
  const totalRow = b.frame({ name: 'TotalRow', parent: outLeft, width: 500, height: 30, props: { layout: 'horizontal', crossAlign: 'baseline', gap: 8 } });
  b.text({ name: 'Price', parent: totalRow, text: 'Rp 18.000', props: { fontSize: 24, fontWeight: 'bold', fill: COLORS.slate900 } });
  b.text({ name: 'Formula', parent: totalRow, text: '(3.0 kg × Rp 6.000)', props: { fontSize: 12, fill: COLORS.slate500 } });
  b.text({ name: 'Note', parent: outLeft, text: 'Pakaian dicuci wangi, disetrika uap rapi, dan dikemas plastik higienis.', props: { fontSize: 11, fill: COLORS.slate600 } });

  const outRight = b.frame({ name: 'OutRight', parent: outputBox, width: 180, height: 50, props: { layout: 'vertical', crossAlign: 'max', gap: 4 } });
  b.text({ name: 'EstLbl', parent: outRight, text: 'Estimasi Selesai:', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.brand700 } });
  b.text({ name: 'EstVal', parent: outRight, text: '48 Jam', props: { fontSize: 18, fontWeight: 'bold', fill: COLORS.slate900 } });

  // 4. Keunggulan Section (height: 400)
  const keunggulanSection = b.frame({
    name: 'KeunggulanSection',
    parent: f01,
    width: 1440,
    height: 400,
    props: { fill: COLORS.slate50, layout: 'vertical', crossAlign: 'center', padding: [48, 60, 48, 60], gap: 14 },
  });

  b.badge({ parent: keunggulanSection, label: 'Jaminan Kepuasan', tone: 'brand', width: 140 });
  b.text({ name: 'Head', parent: keunggulanSection, text: 'Mengapa Memilih Layanan CUCI.IN?', props: { fontSize: 26, fontWeight: 'bold', fill: COLORS.slate900 } });
  b.text({ name: 'Sub', parent: keunggulanSection, text: 'Standar operasional higienis yang dirancang khusus untuk kenyamanan dan ketenangan Anda.', props: { fontSize: 13, fill: COLORS.slate600 } });

  const bentoRow = b.frame({ name: 'BentoRow', parent: keunggulanSection, width: 1180, height: 200, props: { layout: 'horizontal', gap: 20 } });
  const bentoItems = [
    { title: '1 Mesin 1 Pelanggan', desc: 'Pakaian tidak pernah dicampur dengan pakaian orang lain untuk higienitas dan cegah tertukar.', icon: 'shield-check', bg: COLORS.brand50, ic: COLORS.brand700 },
    { title: 'Timbangan Presisi Desimal', desc: 'Penimbangan digital akurat hingga desimal 0,1 kg tanpa pembulatan sepihak yang merugikan.', icon: 'scale', bg: COLORS.emerald50, ic: COLORS.emerald700 },
    { title: 'Pelacakan Real-Time', desc: 'Ketahui status cucian Anda: Diterima, Diproses, Selesai, atau Diambil langsung lewat kode nota.', icon: 'activity', bg: COLORS.brand50, ic: COLORS.brand700 },
    { title: 'Struk Thermal & QRIS', desc: 'Pembayaran fleksibel (Tunai, QRIS, Bank Transfer) dengan bukti struk fisik sah dan riwayat digital.', icon: 'printer', bg: COLORS.amber50, ic: COLORS.amber700 },
  ];
  bentoItems.forEach(item => {
    const card = b.frame({
      name: `Card-${item.title}`,
      parent: bentoRow,
      width: 280,
      height: 190,
      props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 20, layout: 'vertical', gap: 10 },
    });
    const icBox = b.frame({ name: 'IcBox', parent: card, width: 40, height: 40, props: { fill: item.bg, cornerRadius: 10, layout: 'horizontal', mainAlign: 'center', crossAlign: 'center' } });
    b.vector({ name: 'Ico', parent: icBox, width: 20, height: 20, path: ICONS[item.icon], props: { stroke: item.ic } });
    b.text({ name: 'T', parent: card, text: item.title, props: { fontSize: 14, fontWeight: 'bold', fill: COLORS.slate900 } });
    b.text({ name: 'D', parent: card, width: 240, text: item.desc, props: { fontSize: 12, fill: COLORS.slate600, textAutoResize: 'height' } });
  });

  // 5. Cara Kerja Section (height: 380)
  const alurSection = b.frame({
    name: 'AlurSection',
    parent: f01,
    width: 1440,
    height: 380,
    props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, layout: 'vertical', crossAlign: 'center', padding: [48, 60, 48, 60], gap: 14 },
  });

  b.badge({ parent: alurSection, label: 'Proses Cepat & Praktis', tone: 'brand', width: 160 });
  b.text({ name: 'AlurHead', parent: alurSection, text: '3 Langkah Mudah Menggunakan CUCI.IN', props: { fontSize: 26, fontWeight: 'bold', fill: COLORS.slate900 } });
  b.text({ name: 'AlurSub', parent: alurSection, text: 'Pengalaman mencuci tanpa ribet dari awal penyerahan hingga pakaian kembali wangi ke tangan Anda.', props: { fontSize: 13, fill: COLORS.slate600 } });

  const alurRow = b.frame({ name: 'AlurRow', parent: alurSection, width: 1180, height: 180, props: { layout: 'horizontal', gap: 24 } });
  const alurSteps = [
    { num: '1', title: 'Antar Pakaian ke Kasir', desc: 'Kasir menimbang pakaian dengan timbangan digital, menginput ke sistem POS, dan cetak struk nota resmi.' },
    { num: '2', title: 'Pantau Progres dari Rumah', desc: 'Masukkan kode transaksi pada menu Lacak Cucian di website untuk melihat tahapan cuci secara langsung.' },
    { num: '3', title: 'Ambil & Pakaian Siap Pakai', desc: 'Begitu status berubah menjadi Selesai, ambil cucian Anda yang sudah wangi, bersih, dan rapi terbungkus plastik.' },
  ];
  alurSteps.forEach(st => {
    const card = b.frame({
      name: `Step-${st.num}`,
      parent: alurRow,
      width: 377,
      height: 170,
      props: { fill: COLORS.slate50, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 20, layout: 'vertical', crossAlign: 'center', gap: 8 },
    });
    const numBadge = b.frame({ name: 'Num', parent: card, width: 36, height: 36, props: { fill: COLORS.brand600, cornerRadius: 10, layout: 'horizontal', mainAlign: 'center', crossAlign: 'center' } });
    b.text({ name: 'N', parent: numBadge, text: st.num, props: { fontSize: 16, fontWeight: 'bold', fill: COLORS.white } });
    b.text({ name: 'T', parent: card, text: st.title, props: { fontSize: 14, fontWeight: 'bold', fill: COLORS.slate900 } });
    b.text({ name: 'D', parent: card, width: 335, text: st.desc, props: { fontSize: 12, fill: COLORS.slate600, textAlign: 'center', textAutoResize: 'height' } });
  });

  // 6. CTA Banner (height: 240)
  const ctaContainer = b.frame({
    name: 'CTAContainer',
    parent: f01,
    width: 1440,
    height: 240,
    props: { fill: COLORS.slate50, layout: 'horizontal', mainAlign: 'center', crossAlign: 'center', padding: [30, 60, 30, 60] },
  });

  const ctaCard = b.frame({
    name: 'CTABannerCard',
    parent: ctaContainer,
    width: 1180,
    height: 160,
    props: {
      fill: COLORS.brand700,
      cornerRadius: 24,
      padding: [24, 40, 24, 40],
      layout: 'horizontal',
      mainAlign: 'spaceBetween',
      crossAlign: 'center',
    },
  });

  const ctaLeft = b.frame({ name: 'CtaLeft', parent: ctaCard, width: 720, height: 110, props: { layout: 'vertical', gap: 6 } });
  b.text({ name: 'Tag', parent: ctaLeft, text: 'Outlet Laundry Terpercaya', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.brand200 } });
  b.text({ name: 'CtaHead', parent: ctaLeft, text: 'Sudah Punya Nota Cucian? Cek Status Pakaian Anda Sekarang!', props: { fontSize: 22, fontWeight: 'bold', fill: COLORS.white } });
  b.text({ name: 'CtaSub', parent: ctaLeft, text: 'Cukup masukkan nomor transaksi dan dapatkan pembaruan status pengerjaan secara instan.', props: { fontSize: 13, fill: COLORS.brand100 } });

  const ctaBtns = b.frame({ name: 'CtaBtns', parent: ctaCard, width: 360, height: 48, props: { layout: 'horizontal', gap: 12, mainAlign: 'max', crossAlign: 'center' } });
  const btnCtaTrack = b.button({ parent: ctaBtns, label: 'Lacak Nota', variant: 'secondary', icon: 'search', width: 140, height: 44 });
  const btnCtaLogin = b.button({ parent: ctaBtns, label: 'Masuk ke Akun', variant: 'secondary', icon: 'arrow-right', width: 140, height: 44 });

  // 7. Full Footer (height: 280)
  const footer = b.frame({
    name: 'Footer',
    parent: f01,
    width: 1440,
    height: 280,
    props: {
      fill: COLORS.white,
      stroke: COLORS.slate200,
      strokeWeight: 1,
      padding: [40, 60, 20, 60],
      layout: 'vertical',
      gap: 24,
    },
  });

  const footerCols = b.frame({ name: 'Cols', parent: footer, width: 1320, height: 160, props: { layout: 'horizontal', gap: 40 } });

  const col1 = b.frame({ name: 'Col1', parent: footerCols, width: 500, height: 150, props: { layout: 'vertical', gap: 8 } });
  b.text({ name: 'FLogo', parent: col1, text: 'CUCI.IN', props: { fontSize: 18, fontWeight: 'bold', fill: COLORS.brand700 } });
  b.text({ name: 'FBio', parent: col1, text: 'Aplikasi sistem manajemen laundry modern berbasis web. Membantu kasir mencatat penerimaan cucian, timbangan akurat, cetak struk thermal, dan memudahkan pelanggan melacak status cucian secara transparan.', props: { fontSize: 12, fill: COLORS.slate500 } });
  b.text({ name: 'FHours', parent: col1, text: '• Buka Setiap Hari: 07.00 - 21.00 WIB', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.emerald600 } });

  const col2 = b.frame({ name: 'Col2', parent: footerCols, width: 240, height: 150, props: { layout: 'vertical', gap: 8 } });
  b.text({ name: 'H2', parent: col2, text: 'NAVIGASI', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate900 } });
  ['Fitur Layanan', 'Kalkulator Tarif', 'Keunggulan Kami', 'Cara Kerja'].forEach(item => {
    b.text({ name: `L-${item}`, parent: col2, text: item, props: { fontSize: 12, fill: COLORS.slate600 } });
  });

  const col3 = b.frame({ name: 'Col3', parent: footerCols, width: 240, height: 150, props: { layout: 'vertical', gap: 8 } });
  b.text({ name: 'H3', parent: col3, text: 'AKSES SISTEM', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate900 } });
  ['Lacak Status Nota', 'Masuk Akun (Login)', 'Pendaftaran Pelanggan'].forEach(item => {
    b.text({ name: `L-${item}`, parent: col3, text: item, props: { fontSize: 12, fill: COLORS.slate600 } });
  });

  const col4 = b.frame({ name: 'Col4', parent: footerCols, width: 240, height: 150, props: { layout: 'vertical', gap: 8 } });
  b.text({ name: 'H4', parent: col4, text: 'LAYANAN UTAMA', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate900 } });
  ['Cuci Kering Setrika Reguler', 'Kilat Express 24 Jam', 'Super Express 8 Jam', 'Setrika Saja'].forEach(item => {
    b.text({ name: `L-${item}`, parent: col4, text: item, props: { fontSize: 12, fill: COLORS.slate600 } });
  });

  b.text({
    name: 'CopyRight',
    parent: footer,
    text: '© 2026 CUCI.IN Laundry System. Seluruh Hak Cipta Dilindungi. — D4 Sistem Informasi Bisnis (Milestone 2)',
    props: { fontSize: 11, fill: COLORS.slate400, textAlign: 'center' },
  });

  // --------------------------------------------------------
  // FRAME 02: Public Track Lookup (1:1 lookup/_content.blade.php)
  // --------------------------------------------------------
  const f02 = b.frame({
    name: '02-Public-Track-Lookup',
    x: 1520,
    y: 40,
    width: 1440,
    height: 1350,
    props: { fill: COLORS.slate50, layout: 'vertical', gap: 20 },
  });

  // Header Bar
  const f02Header = b.frame({
    name: 'Header',
    parent: f02,
    width: 1440,
    height: 64,
    props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center', padding: [0, 60, 0, 60] },
  });
  b.text({ name: 'Logo', parent: f02Header, text: 'CUCI.IN — Portal Lacak Status Nota', props: { fontSize: 18, fontWeight: 'bold', fill: COLORS.brand700 } });
  const f02HeadActs = b.frame({ name: 'Acts', parent: f02Header, width: 300, height: 36, props: { layout: 'horizontal', gap: 10, mainAlign: 'max', crossAlign: 'center' } });
  const btnLookupBack = b.button({ parent: f02HeadActs, label: 'Kembali ke Beranda', variant: 'secondary', icon: 'arrow-left', width: 160 });
  const btnLookupLogin = b.button({ parent: f02HeadActs, label: 'Masuk Sistem', variant: 'primary', icon: 'log-in', width: 120 });

  // Main Container centered
  const f02Main = b.frame({
    name: 'MainTrackContainer',
    parent: f02,
    width: 1440,
    height: 1220,
    props: { layout: 'vertical', crossAlign: 'center', gap: 20, padding: [20, 60, 40, 60] },
  });

  // Search Card (max-w-2xl)
  const f02SearchCard = b.frame({
    name: 'SearchFormCard',
    parent: f02Main,
    width: 800,
    height: 140,
    props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 20, layout: 'vertical', gap: 10 },
  });
  b.text({ name: 'Lbl', parent: f02SearchCard, text: 'Masukkan Kode Transaksi / Nomor Nota', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate700 } });
  const f02InputRow = b.frame({ name: 'Row', parent: f02SearchCard, width: 760, height: 44, props: { layout: 'horizontal', gap: 10 } });
  b.inputField({ parent: f02InputRow, width: 460, height: 44, value: 'TRX-20261024-0001', icon: 'receipt', mono: true });
  const btnScanQr = b.button({ parent: f02InputRow, label: 'Pindai QR', variant: 'secondary', icon: 'qr-code', width: 130, height: 44 });
  const btnCheckTrx = b.button({ parent: f02InputRow, label: 'Cek Transaksi', variant: 'primary', icon: 'search', width: 150, height: 44 });

  const recentChipsRow = b.frame({ name: 'Recent', parent: f02SearchCard, width: 760, height: 26, props: { layout: 'horizontal', crossAlign: 'center', gap: 8 } });
  b.text({ name: 'RLbl', parent: recentChipsRow, text: 'Pencarian Terakhir:', props: { fontSize: 11, fill: COLORS.slate500 } });
  b.badge({ parent: recentChipsRow, label: 'TRX-20261024-0001', tone: 'brand' });
  b.badge({ parent: recentChipsRow, label: 'TRX-20261020-0012', tone: 'slate' });

  // 1. Stepper Card
  const f02StepperCard = b.frame({
    name: 'StepperCard',
    parent: f02Main,
    width: 800,
    height: 170,
    props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 24, layout: 'vertical', gap: 16 },
  });

  const f02StepHead = b.frame({ name: 'Head', parent: f02StepperCard, width: 752, height: 44, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
  const f02StepLeft = b.frame({ name: 'Left', parent: f02StepHead, width: 450, height: 44, props: { layout: 'vertical', gap: 2 } });
  b.text({ name: 'StLbl', parent: f02StepLeft, text: 'Status Cucian: DIPROSES', props: { fontSize: 16, fontWeight: 'bold', fill: COLORS.brand700 } });
  b.text({ name: 'StSub', parent: f02StepLeft, text: 'Pakaianmu sedang dicuci, dikeringkan, atau disetrika rapi.', props: { fontSize: 12, fill: COLORS.slate500 } });

  const f02StepRight = b.frame({ name: 'Right', parent: f02StepHead, width: 280, height: 44, props: { layout: 'vertical', crossAlign: 'max', gap: 2 } });
  b.text({ name: 'EstL', parent: f02StepRight, text: 'Perkiraan Selesai:', props: { fontSize: 11, fill: COLORS.slate500 } });
  b.text({ name: 'EstV', parent: f02StepRight, text: '25 Oktober 2026, 17:00 WIB', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate800 } });

  // Stepper Visual 4 Steps
  const stepperBar = b.frame({
    name: 'StepperBar',
    parent: f02StepperCard,
    width: 752,
    height: 56,
    props: { fill: COLORS.slate50, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 12, layout: 'horizontal', mainAlign: 'spaceAround', crossAlign: 'center', padding: [0, 20, 0, 20] },
  });
  const stepsList = [
    { label: '1. Diterima', state: 'done', color: COLORS.emerald700, icon: 'check-circle' },
    { label: '2. Diproses (Aktif)', state: 'active', color: COLORS.brand700, icon: 'activity' },
    { label: '3. Selesai', state: 'pending', color: COLORS.slate400, icon: 'clock' },
    { label: '4. Diambil', state: 'pending', color: COLORS.slate400, icon: 'receipt' },
  ];
  stepsList.forEach(st => {
    const sNode = b.frame({ name: `Step-${st.label}`, parent: stepperBar, width: 160, height: 32, props: { layout: 'horizontal', crossAlign: 'center', gap: 6 } });
    b.vector({ name: 'I', parent: sNode, width: 16, height: 16, path: ICONS[st.icon], props: { stroke: st.color } });
    b.text({ name: 'T', parent: sNode, text: st.label, props: { fontSize: 12, fontWeight: st.state === 'active' ? 'bold' : 'medium', fill: st.color } });
  });

  // 2. Detail Rincian Nota & Pelanggan Card
  const f02DetailCard = b.frame({
    name: 'DetailNotaCard',
    parent: f02Main,
    width: 800,
    height: 640,
    props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 16, padding: 24, layout: 'vertical', gap: 16 },
  });

  // Info Transaksi Header
  const f02TrxInfo = b.frame({ name: 'TrxInfo', parent: f02DetailCard, width: 752, height: 44, props: { layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' } });
  const f02TrxLeft = b.frame({ name: 'TL', parent: f02TrxInfo, width: 400, height: 44, props: { layout: 'vertical', gap: 2 } });
  b.text({ name: 'TrxCodeLbl', parent: f02TrxLeft, text: 'NOMOR NOTA TRANSAKSI', props: { fontSize: 10, fontWeight: 'bold', fill: COLORS.slate500 } });
  const trxCodeRow = b.frame({ name: 'TCRow', parent: f02TrxLeft, width: 350, height: 26, props: { layout: 'horizontal', crossAlign: 'center', gap: 8 } });
  b.text({ name: 'Code', parent: trxCodeRow, text: 'TRX-20261024-0001', props: { fontSize: 16, fontWeight: 'bold', fill: COLORS.slate900 } });
  b.button({ parent: trxCodeRow, label: 'Salin Tautan', variant: 'secondary', icon: 'copy', width: 110, height: 26 });

  const f02TrxRight = b.frame({ name: 'TR', parent: f02TrxInfo, width: 300, height: 44, props: { layout: 'vertical', crossAlign: 'max', gap: 2 } });
  b.text({ name: 'CustLbl', parent: f02TrxRight, text: 'NAMA PELANGGAN', props: { fontSize: 10, fontWeight: 'bold', fill: COLORS.slate500 } });
  b.text({ name: 'CustVal', parent: f02TrxRight, text: 'B*** S****** (Disamarkan)', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate800 } });

  // Tabel Rincian Layanan & Cucian
  b.text({ name: 'TblTitle', parent: f02DetailCard, text: 'RINCIAN LAYANAN & CUCIAN', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate500 } });
  const table = b.frame({
    name: 'ItemsTable',
    parent: f02DetailCard,
    width: 752,
    height: 120,
    props: { fill: COLORS.white, stroke: COLORS.slate200, strokeWeight: 1, cornerRadius: 10, layout: 'vertical' },
  });

  const thead = b.frame({ name: 'THead', parent: table, width: 752, height: 36, props: { fill: COLORS.slate50, layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16] } });
  b.text({ name: 'Th1', parent: thead, width: 350, text: 'Layanan', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate600 } });
  b.text({ name: 'Th2', parent: thead, width: 120, text: 'Berat / Jumlah', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate600 } });
  b.text({ name: 'Th3', parent: thead, width: 130, text: 'Tarif', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate600 } });
  b.text({ name: 'Th4', parent: thead, width: 120, text: 'Subtotal', props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate600, textAlign: 'right' } });

  const tr1 = b.frame({ name: 'Tr1', parent: table, width: 752, height: 40, props: { layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16], stroke: COLORS.slate100, strokeWeight: 1 } });
  b.text({ name: 'Td1', parent: tr1, width: 350, text: 'Cuci Kering Setrika Reguler', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate900 } });
  b.text({ name: 'Td2', parent: tr1, width: 120, text: '4.0 kg', props: { fontSize: 12, fill: COLORS.slate700 } });
  b.text({ name: 'Td3', parent: tr1, width: 130, text: 'Rp 6.000 /kg', props: { fontSize: 12, fill: COLORS.slate600 } });
  b.text({ name: 'Td4', parent: tr1, width: 120, text: 'Rp 24.000', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate900, textAlign: 'right' } });

  const tr2 = b.frame({ name: 'Tr2', parent: table, width: 752, height: 40, props: { layout: 'horizontal', crossAlign: 'center', padding: [0, 16, 0, 16] } });
  b.text({ name: 'Td1', parent: tr2, width: 350, text: 'Setrika Kemeja Kerja Satuan', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate900 } });
  b.text({ name: 'Td2', parent: tr2, width: 120, text: '2 pcs', props: { fontSize: 12, fill: COLORS.slate700 } });
  b.text({ name: 'Td3', parent: tr2, width: 130, text: 'Rp 5.000 /pcs', props: { fontSize: 12, fill: COLORS.slate600 } });
  b.text({ name: 'Td4', parent: tr2, width: 120, text: 'Rp 10.000', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate900, textAlign: 'right' } });

  // Ringkasan Pembayaran Row
  const paySummary = b.frame({
    name: 'PaySummary',
    parent: f02DetailCard,
    width: 752,
    height: 48,
    props: { fill: COLORS.slate50, cornerRadius: 10, padding: [0, 16, 0, 16], layout: 'horizontal', mainAlign: 'spaceBetween', crossAlign: 'center' },
  });
  const payLeft = b.frame({ name: 'PL', parent: paySummary, width: 350, height: 32, props: { layout: 'horizontal', crossAlign: 'center', gap: 10 } });
  b.text({ name: 'TotLbl', parent: payLeft, text: 'Total Tagihan:', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate700 } });
  b.text({ name: 'TotVal', parent: payLeft, text: 'Rp 34.000', props: { fontSize: 16, fontWeight: 'bold', fill: COLORS.slate900 } });
  b.badge({ parent: paySummary, label: 'BELUM LUNAS', tone: 'warning' });

  // Kotak Unggah Bukti Pembayaran Digital (Nora P10)
  const uploadBox = b.frame({
    name: 'UploadBox',
    parent: f02DetailCard,
    width: 752,
    height: 230,
    props: { fill: COLORS.brand50, stroke: COLORS.brand200, strokeWeight: 1, cornerRadius: 14, padding: 18, layout: 'vertical', gap: 10 },
  });
  b.text({ name: 'UpTitle', parent: uploadBox, text: 'Unggah Bukti Pembayaran Digital (QRIS / Transfer Bank)', props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.slate900 } });
  b.text({ name: 'UpSub', parent: uploadBox, text: 'Bagi pelanggan yang memilih pembayaran non-tunai, unggah foto bukti transfer untuk diverifikasi oleh kasir.', props: { fontSize: 11, fill: COLORS.slate600 } });

  const dropzone = b.frame({
    name: 'Dropzone',
    parent: uploadBox,
    width: 716,
    height: 90,
    props: { fill: COLORS.white, stroke: COLORS.brand400, strokeWeight: 1, cornerRadius: 10, layout: 'vertical', mainAlign: 'center', crossAlign: 'center', gap: 6 },
  });
  b.vector({ name: 'UpIco', parent: dropzone, width: 22, height: 22, path: ICONS.download, props: { stroke: COLORS.brand600 } });
  b.text({ name: 'DropText1', parent: dropzone, text: 'Klik untuk pilih foto bukti struk atau seret file ke sini', props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.brand700 } });
  b.text({ name: 'DropText2', parent: dropzone, text: 'Format JPG, PNG, atau WEBP maksimal 2 MB', props: { fontSize: 10, fill: COLORS.slate500 } });

  b.button({ parent: uploadBox, label: 'Kirim Bukti Pembayaran', variant: 'primary', icon: 'check-circle', width: 220, height: 38 });

  // --------------------------------------------------------
  // FRAME 03: Public QR Scanner Modal (Overlay Modal)
  // --------------------------------------------------------
  const f03 = b.frame({
    name: '03-Public-QR-Scanner-Modal',
    x: 3000,
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
  const btnCloseModal = b.button({ parent: mHeader, label: 'Tutup', variant: 'ghost', icon: 'x', width: 80 });

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
  b.button({ parent: modalFooter, label: 'Ganti Kamera', variant: 'secondary', icon: 'refresh-cw', width: 140 });
  b.button({ parent: modalFooter, label: 'Tutup Pemindai', variant: 'secondary', width: 130 });

  // --------------------------------------------------------
  // FRAME 04: Auth Login (1:1 login.blade.php & guest.blade.php)
  // --------------------------------------------------------
  let btnLoginBack, btnLoginSubmit, btnChipAdmin, btnChipStaff, btnChipCustomer, btnLinkRegister, btnLinkTrack, btnForgotPassword;

  const f04Shell = b.guestShell({
    name: '04-Auth-Login',
    x: 4480,
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

      // Demo Quick Login Box
      const demoBox = builder.frame({
        name: 'DemoBox',
        parent: card,
        width: 396,
        height: 64,
        props: { fill: COLORS.slate50, cornerRadius: 10, padding: 8, layout: 'vertical', gap: 6 },
      });
      builder.text({ name: 'DT', parent: demoBox, text: 'Akun Cepat Pengujian (Role):', props: { fontSize: 10, fill: COLORS.slate500 } });
      const chips = builder.frame({ name: 'Chips', parent: demoBox, width: 380, height: 26, props: { layout: 'horizontal', gap: 6 } });
      btnChipAdmin = builder.badge({ parent: chips, label: 'Admin (Dawamul)', tone: 'brand', width: 120 });
      btnChipStaff = builder.badge({ parent: chips, label: 'Kasir (Maulana)', tone: 'brand', width: 120 });
      btnChipCustomer = builder.badge({ parent: chips, label: 'Customer (Budi)', tone: 'brand', width: 120 });

      // Links below card
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

  // --------------------------------------------------------
  // FRAME 05: Auth Register (1:1 register.blade.php)
  // --------------------------------------------------------
  let btnRegisterBack, btnRegisterSubmit, btnLinkLogin;

  const f05Shell = b.guestShell({
    name: '05-Auth-Register',
    x: 5960,
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

  // --------------------------------------------------------
  // FRAME 20: Auth Forgot Password (1:1 auth/forgot-password.blade.php)
  // --------------------------------------------------------
  let btnForgotBack, btnForgotSubmit, btnForgotLinkLogin;

  const f20Shell = b.guestShell({
    name: '20-Auth-Forgot-Password',
    x: 7440,
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

  // --------------------------------------------------------
  // FRAME 21: Auth Reset Password (1:1 auth/reset-password.blade.php)
  // --------------------------------------------------------
  let btnResetBack, btnResetSubmit, btnResetLinkLogin;

  const f21Shell = b.guestShell({
    name: '21-Auth-Reset-Password',
    x: 8920,
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
  // FIGCRAFT PROTOTYPING WIRES (Native Interactions)
  // ========================================================
  // Flow 1: Alur Pengunjung & Pelanggan Publik
  b.flow({ start: f01, name: 'Alur Pelanggan & Pengunjung' });

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
