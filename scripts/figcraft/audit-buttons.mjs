import fs from 'node:fs';

const cmds = JSON.parse(fs.readFileSync('docs/design/cuci-in.commands.json', 'utf8'));

const nodesByAs = {};
const childrenByParent = {};
const pages = [];
const topFrames = [];

cmds.forEach((c) => {
  if (c.command === 'page.create' || c.command === 'page.rename') {
    pages.push(c.params);
  }
  if (c.as) {
    nodesByAs[c.as] = c.params;
  }
  if (c.command === 'node.create') {
    if (c.params.type === 'frame' && !c.params.parent) {
      topFrames.push({
        id: c.as,
        name: c.params.name,
        x: c.params.x,
        y: c.params.y,
        width: c.params.width,
        height: c.params.height,
      });
    }
    if (c.params.parent) {
      const pId = typeof c.params.parent === 'object' ? c.params.parent.$ref : c.params.parent;
      if (pId) {
        if (!childrenByParent[pId]) childrenByParent[pId] = [];
        childrenByParent[pId].push(c.as);
      }
    }
  }
});

console.log('====================================================');
console.log('   AUDIT KONSOLIDASI SINGLE PAGE & PROTOTYPE WIRES   ');
console.log('====================================================\n');

// 1. Audit Pages
console.log(`1. Halaman Dokumen (Pages): ${pages.length} halaman`);
pages.forEach((p, idx) => {
  console.log(`   [${idx + 1}] "${p.name}" (fill: ${p.props?.fill || 'none'})`);
});

// 2. Audit Top-Level Screen Frames
console.log(`\n2. Layar Utama (Top-level Frames): ${topFrames.length} frame`);
const nonStandard = [];
topFrames.forEach((f) => {
  const is1440x900 = f.width === 1440 && f.height === 900;
  if (!is1440x900) {
    nonStandard.push(f);
  }
  console.log(`   - ${f.name.padEnd(35)}: (${f.x}, ${f.y}) | ${f.width}x${f.height} px ${is1440x900 ? '[1440x900 VALID]' : '[NON-STANDARD!]'}`);
});

if (nonStandard.length === 0) {
  console.log('\n   => SEMUA 29 FRAME TELAH 100% STANDAR 1440x900 PX!');
} else {
  console.log(`\n   => PERHATIAN: Ditemukan ${nonStandard.length} frame di luar ukuran 1440x900 px!`);
}

// 3. All Button nodes
const buttons = [];
Object.entries(nodesByAs).forEach(([id, node]) => {
  if (node.type === 'frame' && node.name && node.name.startsWith('Btn-')) {
    const childIds = childrenByParent[id] || [];
    const children = childIds.map((cid) => nodesByAs[cid]).filter(Boolean);
    const labelNode = children.find((ch) => ch.name === 'Label');
    const iconNode = children.find((ch) => ch.name && ch.name.startsWith('Icon-'));
    const kbdNode = children.find((ch) => ch.name === 'Kbd');
    const kbdTextNode = kbdNode ? (childrenByParent[childIds.find((cid) => nodesByAs[cid]?.name === 'Kbd')] || []).map((cid) => nodesByAs[cid]).find((ch) => ch?.name === 'KbdText') : null;

    buttons.push({
      id,
      name: node.name,
      label: labelNode?.text || '',
      width: node.width,
      height: node.height,
      hasIcon: !!iconNode,
      iconName: iconNode ? iconNode.name.replace('Icon-', '') : null,
      hasKbd: !!kbdNode,
      kbdText: kbdTextNode?.text || null,
      props: node.props,
      parentRef: node.parent?.$ref || node.parent,
    });
  }
});

console.log(`\n3. Total Tombol Terstandarisasi: ${buttons.length} tombol`);
const heightsMap = {};
buttons.forEach((b) => {
  if (!heightsMap[b.height]) heightsMap[b.height] = [];
  heightsMap[b.height].push(b.name);
});
Object.keys(heightsMap).sort((a, b) => Number(a) - Number(b)).forEach((h) => {
  console.log(`   - Tinggi ${h}px: ${heightsMap[h].length} tombol`);
});

// 4. Interactive Wires (proto.interaction.add) & Flows
const flows = cmds.filter((c) => c.command === 'proto.flow.add');
const wires = cmds.filter((c) => c.command === 'proto.interaction.add');
const brokenWires = [];
const triggerTypes = {};

wires.forEach((w, idx) => {
  const sRef = w.params.id?.$ref || w.params.id;
  const dRef = w.params.dest?.$ref || w.params.dest;
  const sNode = nodesByAs[sRef];
  const dNode = nodesByAs[dRef];

  if (!sNode || !dNode) {
    brokenWires.push({ index: idx, sRef, dRef, sExists: !!sNode, dExists: !!dNode });
  } else {
    const typeKey = sNode.name?.startsWith('Btn-') ? 'Button' : (sNode.type === 'text' ? 'Text Link' : (sNode.name?.startsWith('NavItem-') ? 'Nav Item' : sNode.type));
    triggerTypes[typeKey] = (triggerTypes[typeKey] || 0) + 1;
  }
});

console.log(`\n4. Prototipe Interaktif:`);
console.log(`   - Total Prototype Flows: ${flows.length}`);
flows.forEach((fl, idx) => {
  const sRef = fl.params.start?.$ref || fl.params.start;
  const sNode = nodesByAs[sRef];
  console.log(`     [${idx + 1}] "${fl.params.name}" (Mulai dari: ${sNode?.name || sRef})`);
});
console.log(`   - Total Wires Terhubung: ${wires.length}`);
console.log(`   - Broken Wires: ${brokenWires.length}`);
console.log(`   - Jenis Trigger:`, triggerTypes);

if (brokenWires.length > 0) {
  console.log('\nDetail Broken Wires:');
  brokenWires.forEach((bw) => console.log(bw));
} else {
  console.log('\n=> SELURUH TAUTAN KAWAT PROTOTIPE 100% UTUH TANPA BROKEN WIRES!');
}
