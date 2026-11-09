import fs from 'node:fs';
import path from 'node:path';
import { execFileSync } from 'node:child_process';
import { FigBuilder } from './primitives.mjs';
import { buildPublicAuthScreens } from './screens/public-auth.mjs';
import { buildCustomerScreens } from './screens/customer.mjs';
import { buildStaffScreens } from './screens/staff.mjs';
import { buildAdminScreens } from './screens/admin.mjs';

const FIGCRAFT_CLI = 'E:\\Figcraft\\FigCraft\\figcraft-cli.exe';
const ROOT_DIR = 'C:\\laragon\\www\\cuci-in';
const DESIGN_DIR = path.join(ROOT_DIR, 'docs', 'design');
const PREVIEWS_DIR = path.join(DESIGN_DIR, 'previews');
const OUT_FILE = path.join(DESIGN_DIR, 'cuci-in.figcraft');
const SCRIPT_JSON = path.join(DESIGN_DIR, 'cuci-in.commands.json');

async function main() {
  console.log('[FigCraft] Mengompilasi kode sumber Blade & CSS menjadi Scene Graph FigCraft...');
  const startTime = Date.now();

  fs.mkdirSync(DESIGN_DIR, { recursive: true });
  fs.mkdirSync(PREVIEWS_DIR, { recursive: true });

  const builder = new FigBuilder();

  // 1. Compile all screen modules
  console.log(' - Membangun 00 - Design System & 01 - Public & Auth...');
  const publicAuthRefs = buildPublicAuthScreens(builder);

  console.log(' - Membangun 02 - Customer Portal...');
  const customerRefs = buildCustomerScreens(builder, publicAuthRefs);

  console.log(' - Membangun 03 - Staff & Kasir Operasional (POS & Thermal)...');
  const staffRefs = buildStaffScreens(builder, publicAuthRefs);

  console.log(' - Membangun 04 - Admin & Executive (Reports & Roles)...');
  buildAdminScreens(builder, publicAuthRefs, staffRefs);

  console.log(`[FigCraft] Total perintah yang digenerate: ${builder.commands.length} langkah.`);

  // 2. Write commands script
  fs.writeFileSync(SCRIPT_JSON, JSON.stringify(builder.commands, null, 2), 'utf-8');
  console.log(`[FigCraft] Berkas script tersimpan di: ${SCRIPT_JSON}`);

  // 3. Run headless compilation via figcraft-cli
  console.log('[FigCraft] Mengeksekusi kompilasi via figcraft-cli run...');
  const runOutput = execFileSync(
    FIGCRAFT_CLI,
    ['run', SCRIPT_JSON, '--out', OUT_FILE],
    { encoding: 'utf-8', maxBuffer: 50 * 1024 * 1024 }
  );
  console.log(`[FigCraft] Selesai menulis: ${OUT_FILE}`);

  // 4. Inspect file info
  const infoOutput = execFileSync(FIGCRAFT_CLI, ['info', OUT_FILE], { encoding: 'utf-8' });
  console.log('\n--- Metadata Dokumen FigCraft ---');
  console.log(infoOutput.trim());
  console.log('--------------------------------\n');

  // 5. Export PNG previews for all pages
  console.log('[FigCraft] Mengekspor pratinjau PNG seluruh halaman ke docs/design/previews/...');
  const pages = [
    '00 - Design System',
    '01 - Public & Auth',
    '02 - Customer Portal',
    '03 - Staff & Kasir Operasional',
    '04 - Admin & Executive',
  ];

  for (const p of pages) {
    try {
      execFileSync(
        FIGCRAFT_CLI,
        ['export', OUT_FILE, '--page', p, '--out', PREVIEWS_DIR, '--format', 'png'],
        { encoding: 'utf-8' }
      );
    } catch (e) {
      console.warn(`[Warning] Gagal mengekspor halaman ${p}:`, e.message);
    }
  }

  const duration = ((Date.now() - startTime) / 1000).toFixed(2);
  console.log(`[FigCraft] Build selesai sukses dalam ${duration} detik.`);
}

main().catch((err) => {
  console.error('[FigCraft Error]', err);
  process.exit(1);
});

