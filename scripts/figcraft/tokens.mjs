/**
 * CUCI.IN Design Tokens & Icons for FigCraft
 * Extracted from resources/css/app.css, layouts/app.blade.php, and Lucide Icons
 */

export const COLORS = {
  // Brand (Aqua Teal - Air, Kebersihan & Kesegaran Laundry)
  brand50: '#ecfeff',
  brand100: '#cffafe',
  brand200: '#a5f3fc',
  brand300: '#67e8f9',
  brand400: '#22d3ee',
  brand500: '#06b6d4',
  brand600: '#0891b2',
  brand700: '#0e7490',
  brand800: '#155e75',
  brand900: '#164e63',
  brand950: '#083344',

  // Slate Neutral
  slate50: '#f8fafc',
  slate100: '#f1f5f9',
  slate200: '#e2e8f0',
  slate300: '#cbd5e1',
  slate400: '#94a3b8',
  slate500: '#64748b',
  slate600: '#475569',
  slate700: '#334155',
  slate800: '#1e293b',
  slate900: '#0f172a',
  slate950: '#020617',

  // Semantic Status
  emerald50: '#ecfdf5',
  emerald100: '#d1fae5',
  emerald500: '#10b981',
  emerald600: '#059669',
  emerald700: '#047857',

  amber50: '#fffbeb',
  amber100: '#fef3c7',
  amber500: '#f59e0b',
  amber700: '#b45309',
  amber800: '#92400e',

  rose50: '#fff1f2',
  rose100: '#ffe4e6',
  rose500: '#f43f5e',
  rose600: '#e11d48',
  rose700: '#be123c',

  white: '#ffffff',
  black: '#000000',
  transparent: '#00000000',
};

export const SHADOWS = {
  card: [{ type: 'dropShadow', color: '#0f172a0d', radius: 8, offset: [0, 2] }],
  sm: [{ type: 'dropShadow', color: '#0f172a08', radius: 4, offset: [0, 1] }],
  md: [{ type: 'dropShadow', color: '#0f172a12', radius: 14, offset: [0, 4] }],
  lg: [{ type: 'dropShadow', color: '#0f172a18', radius: 24, offset: [0, 8] }],
};

export const NAV_ITEMS = {
  customer: [
    { label: 'Beranda', icon: 'home', active: true },
    { label: 'Riwayat Transaksi', icon: 'receipt', active: false },
    { label: 'Pengaduan', icon: 'message-square', active: false },
    { label: 'Lacak Nota Fisik', icon: 'search', active: false },
  ],
  staff: [
    { label: 'Dashboard', icon: 'layout-dashboard', active: true },
    { label: 'Kasir POS', icon: 'plus-circle', active: false },
    { label: 'Kelola Transaksi', icon: 'list', active: false },
    { label: 'Verifikasi Pembayaran', icon: 'shield-check', active: false },
    { label: 'Data Pelanggan', icon: 'users', active: false },
    { label: 'Katalog Layanan', icon: 'tag', active: false },
    { label: 'Pengaduan', icon: 'message-square', active: false },
  ],
  admin: [
    { label: 'Dashboard', icon: 'layout-dashboard', active: true },
    { label: 'Kasir POS', icon: 'plus-circle', active: false },
    { label: 'Kelola Transaksi', icon: 'list', active: false },
    { label: 'Verifikasi Pembayaran', icon: 'shield-check', active: false },
    { label: 'Data Pelanggan', icon: 'users', active: false },
    { label: 'Master Layanan', icon: 'tag', active: false },
    { label: 'Pengaduan', icon: 'message-square', active: false },
    { label: 'Laporan', icon: 'bar-chart-3', active: false },
    { label: 'Kelola Pengguna', icon: 'user-cog', active: false },
  ],
};

export const ICONS = {
  search: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
  receipt: 'M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1-2-1zm4 6h8m-8 4h8m-8 4h5',
  plus: 'M12 5v14m-7-7h14',
  'plus-circle': 'M12 8v8m-4-4h8m8 0a10 10 0 11-20 0 10 10 0 0120 0z',
  'arrow-left': 'M19 12H5m7 7l-7-7 7-7',
  'arrow-right': 'M5 12h14m-7 7l7-7-7-7',
  'arrow-up-right': 'M7 17L17 7m0 0H7m10 0v10',
  'check-circle': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
  'alert-triangle': 'M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0zM12 9v4m0 4h.01',
  'shield-check': 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
  users: 'M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M10 10a4 4 0 100-8 4 4 0 000 8zm11 10v-2a4 4 0 00-3-3.87M16 2.13a4 4 0 010 7.75',
  tag: 'M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82zM7 7h.01',
  'message-square': 'M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z',
  'layout-dashboard': 'M3 3h7v9H3zm11 0h7v5h-7zm0 9h7v9h-7zM3 16h7v5H3z',
  list: 'M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01',
  'bar-chart-3': 'M3 3v18h18M18 17V9m-5 8V5m-5 12v-5',
  'user-cog': 'M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2m19-2l-1.5-.5M19 19v3m-3-1.5l3-1.5M9 7a4 4 0 100-8 4 4 0 000 8z',
  'qr-code': 'M3 3h6v6H3zm12 0h6v6h-6zm0 12h6v6h-6zM3 15h6v6H3zm3-9h.01M18 6h.01M6 18h.01M18 18h.01',
  home: 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10',
  printer: 'M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-12 0v4h12v-4',
  download: 'M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4m4-5l5 5 5-5m-5 5V3',
  zap: 'M13 2L3 14h9l-1 8 10-12h-9l1-8z',
  clock: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
  mail: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
  lock: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
  eye: 'M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8zm11 3a3 3 0 100-6 3 3 0 000 6z',
  scale: 'M16 16l3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1zm-14 0l3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1zM7 21h10M12 3v18M3 7h18',
  activity: 'M22 12h-4l-3 9L9 3l-3 9H2',
  calculator: 'M4 2v20h16V2H4zm4 4h8v2H8V6zm0 6h2v2H8v-2zm6 0h2v2h-2v-2zm-6 6h2v2H8v-2zm6 0h2v2h-2v-2z',
  award: 'M12 15a7 7 0 100-14 7 7 0 000 14zm-4 4l4-2 4 2v-4.17A8.99 8.99 0 0112 17c-1.47 0-2.85-.35-4.08-.98V19z',
  route: 'M6 19a3 3 0 100-6 3 3 0 000 6zm12-10a3 3 0 100-6 3 3 0 000 6zm-6 1a3 3 0 100-6 3 3 0 000 6zm-3 8h6M9 9l6 6',
  sparkles: 'M12 3l1.91 5.86L20 10l-4.75 3.65L16.82 20 12 16.4 7.18 20l1.57-6.35L4 10l6.09-1.14L12 3z',
  copy: 'M8 4v12a2 2 0 002 2h8a2 2 0 002-2V4a2 2 0 00-2-2h-8a2 2 0 00-2 2zm-4 4v12a2 2 0 002 2h8',
  check: 'M20 6L9 17l-5-5',
  'log-in': 'M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3',
  'log-out': 'M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9',
  x: 'M18 6L6 18M6 6l12 12',
  menu: 'M4 6h16M4 12h16M4 18h16',
  'refresh-cw': 'M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15',
  'camera-off': 'M1 1l22 22M21 21H3a2 2 0 01-2-2V8a2 2 0 012-2h3m3-3h6l2 3h4a2 2 0 012 2v9.34M9 9a3 3 0 004.24 4.24',
};

