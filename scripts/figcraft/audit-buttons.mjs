import fs from 'node:fs';

const cmds = JSON.parse(fs.readFileSync('docs/design/cuci-in.commands.json', 'utf8'));

const nodesByAs = {};
const childrenByParent = {};

cmds.forEach((c) => {
  if (c.as) {
    nodesByAs[c.as] = c.params;
  }
  if (c.command === 'node.create' && c.params.parent) {
    const pId = typeof c.params.parent === 'object' ? c.params.parent.$ref : c.params.parent;
    if (pId) {
      if (!childrenByParent[pId]) childrenByParent[pId] = [];
      childrenByParent[pId].push(c.as);
    }
  }
});

console.log('=== AUDIT SELURUH TOMBOL & ELEMEN INTERAKTIF ===\n');

// 1. All Button nodes (type === 'frame' and name starts with 'Btn-')
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

console.log(`Total Tombol (Btn-*): ${buttons.length}`);

// 2. Button Heights & Inconsistencies
const heightsMap = {};
buttons.forEach((b) => {
  if (!heightsMap[b.height]) heightsMap[b.height] = [];
  heightsMap[b.height].push(b.name);
});

console.log('\n--- Variasi Tinggi Tombol (Height Distribution) ---');
Object.keys(heightsMap).sort((a, b) => Number(a) - Number(b)).forEach((h) => {
  console.log(`Height ${h}px (${heightsMap[h].length} tombol): contoh -> ${heightsMap[h].slice(0, 3).join(', ')}`);
});

// 3. Check for tight label widths (potential clipping)
console.log('\n--- Pemeriksaan Kerapatan Teks / Label Truncation ---');
const tightButtons = [];
buttons.forEach((b) => {
  // Segoe UI Bold 12px: char width is ~7.4px
  const estTextW = b.label.length * 7.5;
  const iconW = b.hasIcon ? 14 : 0;
  const kbdW = b.hasKbd ? 24 : 0;
  let gapCount = 0;
  if (b.hasIcon) gapCount++;
  if (b.hasKbd) gapCount++;
  const gapsW = gapCount * (b.props?.gap || 6);
  const contentW = estTextW + iconW + kbdW + gapsW;
  const sidePadding = (b.width - contentW) / 2;

  if (sidePadding < 12) {
    tightButtons.push({
      name: b.name,
      width: b.width,
      contentW: Math.round(contentW),
      sidePadding: Math.round(sidePadding),
      label: b.label,
      hasIcon: b.hasIcon,
      hasKbd: b.hasKbd,
    });
  }
});

if (tightButtons.length === 0) {
  console.log('Semua 113 tombol memiliki lebar dan padding samping yang lapang (>= 12px per sisi).');
} else {
  console.log(`Ditemukan ${tightButtons.length} tombol berpotensi sempit/mepet (side padding < 12px):`);
  tightButtons.forEach((t) => console.log(` - ${t.name}: width=${t.width}px, konten=${t.contentW}px, sisa padding sisi=${t.sidePadding}px (label: "${t.label}")`));
}

// 4. Check all Interactive Wires (proto.interaction.add)
console.log('\n--- Pemeriksaan Tautan Interaksi Prototipe (Interaction Wires) ---');
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

console.log(`Total Wires Terpasang: ${wires.length}`);
console.log(`Total Broken Wires: ${brokenWires.length}`);
console.log('Distribusi Jenis Trigger:', triggerTypes);

// 5. Check visual consistency of button corner radius, font size, and variants
console.log('\n--- Pemeriksaan Konsistensi Gaya Tombol (Style Tokens) ---');
const radii = {};
const variants = {};

buttons.forEach((b) => {
  const r = b.props?.cornerRadius || 0;
  radii[r] = (radii[r] || 0) + 1;

  let v = 'custom';
  if (b.props?.fill === '#0284c7') v = 'primary (brand600)';
  else if (b.props?.fill === '#ffffff' && b.props?.stroke === '#e2e8f0') v = 'secondary (white/slate200)';
  else if (b.props?.fill === 'none' && b.props?.stroke === 'none') v = 'ghost';
  else if (b.props?.fill === '#e11d48') v = 'destructive (rose600)';
  variants[v] = (variants[v] || 0) + 1;
});

// 6. Group Buttons by Parent Screen Frame
console.log('\n--- Daftar Tombol per Layar (Per-Screen Button Breakdown) ---');

function findTopFrame(nodeId) {
  let curr = nodesByAs[nodeId];
  let currId = nodeId;
  while (curr && curr.parent) {
    const pId = typeof curr.parent === 'object' ? curr.parent.$ref : curr.parent;
    if (nodesByAs[pId]?.type === 'frame' && !nodesByAs[pId]?.parent) {
      return { id: pId, name: nodesByAs[pId].name };
    }
    curr = nodesByAs[pId];
    currId = pId;
  }
  return { id: currId, name: curr?.name || 'Top' };
}

const buttonsByScreen = {};
buttons.forEach((b) => {
  const top = findTopFrame(b.id);
  const screenName = top.name || 'Unknown';
  if (!buttonsByScreen[screenName]) buttonsByScreen[screenName] = [];
  buttonsByScreen[screenName].push(b);
});

Object.keys(buttonsByScreen).sort().forEach((screen) => {
  console.log(`\nScreen: ${screen} (${buttonsByScreen[screen].length} tombol)`);
  buttonsByScreen[screen].forEach((b) => {
    let v = 'primary';
    if (b.props?.fill === '#ffffff') v = 'secondary';
    else if (b.props?.fill === 'none') v = 'ghost';
    else if (b.props?.fill === '#e11d48') v = 'destructive';
    console.log(`  - [${v}] "${b.label}" (w:${b.width}px, h:${b.height}px${b.hasIcon ? ', icon:' + b.iconName : ''}${b.hasKbd ? ', kbd:' + b.kbdText : ''})`);
  });
});

