import { COLORS, ICONS, NAV_ITEMS, SHADOWS } from './tokens.mjs';

export class FigBuilder {
  constructor() {
    this.commands = [];
    this.stepIndex = 0;
    this.hasFirstPageRenamed = false;
  }

  nextRef(prefix = 'node') {
    return `${prefix}_${++this.stepIndex}`;
  }

  add(command, params, as = null) {
    const entry = { command, params };
    if (as) entry.as = as;
    this.commands.push(entry);
    return as ? { $ref: as } : null;
  }

  // --- Prototyping Helpers ---
  flow({ start, name }) {
    return this.add('proto.flow.add', { start, name });
  }

  wire({ id, dest }) {
    return this.add('proto.interaction.add', { id, dest });
  }

  // --- Page Operations ---
  page(name, bgColor = COLORS.slate50) {
    if (!this.hasFirstPageRenamed) {
      this.hasFirstPageRenamed = true;
      this.add('page.rename', { id: 1, name });
      this.add('page.setBackground', { id: 1, color: bgColor });
      return 1;
    }
    const as = this.nextRef('page');
    this.add('page.add', { name }, as);
    this.add('page.setBackground', { color: bgColor });
    return { $ref: as };
  }

  // --- Node Primitives ---
  frame({ name, parent, x = 0, y = 0, width = 100, height = 100, props = {} }) {
    const as = this.nextRef('frame');
    const normalizedProps = { ...props };

    // Default fill: transparent ('none') for inner frames, slate50 for top-level page frames
    if (!('fill' in normalizedProps)) {
      normalizedProps.fill = parent ? 'none' : COLORS.slate50;
    }

    if (normalizedProps.mainAlign) {
      const map = {
        flexend: 'max',
        end: 'max',
        flexstart: 'min',
        start: 'min',
        spacebetween: 'spaceBetween',
        spacearound: 'spaceAround',
        spaceevenly: 'spaceEvenly',
        center: 'center',
      };
      const val = map[String(normalizedProps.mainAlign).toLowerCase()];
      if (val) normalizedProps.mainAlign = val;
    }
    if (normalizedProps.crossAlign) {
      const map = {
        flexend: 'max',
        end: 'max',
        flexstart: 'min',
        start: 'min',
        center: 'center',
        baseline: 'baseline',
      };
      const val = map[String(normalizedProps.crossAlign).toLowerCase()];
      if (val) normalizedProps.crossAlign = val;
    }

    this.add('node.create', {
      type: 'frame',
      name,
      parent,
      x,
      y,
      width,
      height,
      props: normalizedProps,
    }, as);
    return { $ref: as };
  }

  rect({ name, parent, x = 0, y = 0, width = 100, height = 100, props = {} }) {
    const as = this.nextRef('rect');
    this.add('node.create', {
      type: 'rectangle',
      name,
      parent,
      x,
      y,
      width,
      height,
      props,
    }, as);
    return { $ref: as };
  }

  text({ name, parent, x = 0, y = 0, width, height, text = '', props = {} }) {
    const as = this.nextRef('text');
    const normalizedProps = {
      fontFamily: 'Segoe UI',
      fill: COLORS.slate900,
      fontSize: 14,
      ...props,
    };

    if (normalizedProps.fontWeight) {
      if (typeof normalizedProps.fontWeight === 'string') {
        const map = {
          bold: 700,
          semibold: 600,
          medium: 500,
          normal: 400,
          regular: 400,
          light: 300,
        };
        const val = map[normalizedProps.fontWeight.toLowerCase()];
        if (val) {
          normalizedProps.fontWeight = val;
        } else {
          delete normalizedProps.fontWeight;
        }
      }
    }

    if (width && normalizedProps.textAutoResize === undefined) {
      normalizedProps.textAutoResize = 'height';
    }

    const params = {
      type: 'text',
      name: name || text.slice(0, 24),
      parent,
      x,
      y,
      text,
      props: normalizedProps,
    };
    if (width) params.width = width;
    if (height) params.height = height;
    this.add('node.create', params, as);
    return { $ref: as };
  }

  vector({ name, parent, x = 0, y = 0, width = 16, height = 16, path, props = {} }) {
    const as = this.nextRef('icon');
    this.add('node.create', {
      type: 'vector',
      name: name || 'Icon',
      parent,
      x,
      y,
      width,
      height,
      path,
      props: {
        stroke: COLORS.slate600,
        strokeWeight: 1.8,
        fill: 'none',
        ...props,
      },
    }, as);
    return { $ref: as };
  }

  // --- UI Components ---
  button({ parent, x = 0, y = 0, width = 0, height = 36, label = '', variant = 'primary', icon, kbd }) {
    const isPrimary = variant === 'primary';
    const isSecondary = variant === 'secondary';
    const isGhost = variant === 'ghost';
    const isDestructive = variant === 'destructive';

    let fill = COLORS.brand600;
    let stroke = 'none';
    let textColor = COLORS.white;
    let effects = SHADOWS.sm;

    if (isSecondary) {
      fill = COLORS.white;
      stroke = COLORS.slate200;
      textColor = COLORS.slate700;
      effects = SHADOWS.sm;
    } else if (isGhost) {
      fill = 'none';
      stroke = 'none';
      textColor = COLORS.slate600;
      effects = [];
    } else if (isDestructive) {
      fill = COLORS.rose600;
      textColor = COLORS.white;
      effects = SHADOWS.sm;
    }

    const calculatedWidth = Math.ceil(label.length * 8.5 + (icon ? 32 : 18) + (kbd ? 36 : 0) + 24);
    const finalWidth = Math.max(width || 0, calculatedWidth);

    const btn = this.frame({
      name: `Btn-${label}`,
      parent,
      x,
      y,
      width: finalWidth,
      height,
      props: {
        fill,
        stroke,
        strokeWeight: stroke !== 'none' ? 1 : 0,
        cornerRadius: 10,
        layout: 'horizontal',
        mainAlign: 'center',
        crossAlign: 'center',
        gap: 6,
        padding: [8, 14, 8, 14],
        effects,
      },
    });

    if (icon && ICONS[icon]) {
      this.vector({
        name: `Icon-${icon}`,
        parent: btn,
        width: 14,
        height: 14,
        path: ICONS[icon],
        props: { stroke: textColor },
      });
    }

    this.text({
      name: 'Label',
      parent: btn,
      text: label,
      props: {
        fontSize: 12,
        fontWeight: 'bold',
        fill: textColor,
      },
    });

    if (kbd) {
      const kbdFrame = this.frame({
        name: 'Kbd',
        parent: btn,
        width: 24,
        height: 18,
        props: {
          fill: isPrimary ? COLORS.brand800 : COLORS.slate100,
          cornerRadius: 4,
          padding: [2, 4, 2, 4],
          layout: 'horizontal',
          mainAlign: 'center',
          crossAlign: 'center',
        },
      });
      this.text({
        name: 'KbdText',
        parent: kbdFrame,
        text: kbd,
        props: {
          fontSize: 10,
          fontWeight: 'bold',
          fill: isPrimary ? COLORS.white : COLORS.slate700,
        },
      });
    }

    return btn;
  }

  badge({ parent, x = 0, y = 0, width = 0, height = 24, label = '', tone = 'brand' }) {
    let fill = COLORS.brand50;
    let textColor = COLORS.brand700;
    let stroke = COLORS.brand200;

    if (tone === 'success' || tone === 'emerald') {
      fill = COLORS.emerald50;
      textColor = COLORS.emerald700;
      stroke = COLORS.emerald100;
    } else if (tone === 'warning' || tone === 'amber') {
      fill = COLORS.amber50;
      textColor = COLORS.amber800;
      stroke = COLORS.amber100;
    } else if (tone === 'danger' || tone === 'rose') {
      fill = COLORS.rose50;
      textColor = COLORS.rose700;
      stroke = COLORS.rose100;
    } else if (tone === 'slate') {
      fill = COLORS.slate100;
      textColor = COLORS.slate700;
      stroke = COLORS.slate200;
    }

    const calculatedWidth = Math.ceil(label.length * 7.5 + 20);
    const finalWidth = Math.max(width || 0, calculatedWidth);

    const badgeFrame = this.frame({
      name: `Badge-${label}`,
      parent,
      x,
      y,
      width: finalWidth,
      height,
      props: {
        fill,
        stroke,
        strokeWeight: 1,
        cornerRadius: 6,
        layout: 'horizontal',
        mainAlign: 'center',
        crossAlign: 'center',
        gap: 4,
        padding: [2, 8, 2, 8],
      },
    });

    this.text({
      name: 'Text',
      parent: badgeFrame,
      text: label,
      props: {
        fontSize: 11,
        fontWeight: 'bold',
        fill: textColor,
      },
    });

    return badgeFrame;
  }

  inputField({ parent, x = 0, y = 0, width = 320, height = 40, label, placeholder, value, icon, mono = false }) {
    const group = this.frame({
      name: `Field-${label || 'Input'}`,
      parent,
      x,
      y,
      width,
      height: label ? height + 24 : height,
      props: {
        fill: 'none',
        layout: 'vertical',
        gap: 6,
      },
    });

    if (label) {
      this.text({
        name: 'Label',
        parent: group,
        text: label,
        props: {
          fontSize: 12,
          fontWeight: 'bold',
          fill: COLORS.slate700,
        },
      });
    }

    const box = this.frame({
      name: 'InputBox',
      parent: group,
      width,
      height,
      props: {
        fill: COLORS.white,
        stroke: COLORS.slate300,
        strokeWeight: 1,
        cornerRadius: 12,
        layout: 'horizontal',
        crossAlign: 'center',
        gap: 8,
        padding: [8, 12, 8, 12],
        effects: SHADOWS.sm,
      },
    });

    if (icon && ICONS[icon]) {
      this.vector({
        name: 'Icon',
        parent: box,
        width: 16,
        height: 16,
        path: ICONS[icon],
        props: { stroke: COLORS.slate400 },
      });
    }

    this.text({
      name: 'TextValue',
      parent: box,
      text: value || placeholder || '',
      props: {
        fontSize: 13,
        fontWeight: mono ? 'bold' : 'normal',
        fill: value ? COLORS.slate900 : COLORS.slate400,
      },
    });

    return group;
  }

  // --- Monolithic Stat Strip (FR-27 4-Column Layout) ---
  monolithicStatStrip({ parent, x = 24, y = 24, width = 1136, height = 110, stats = [] }) {
    const strip = this.frame({
      name: 'Monolithic-Stat-Strip',
      parent,
      x,
      y,
      width,
      height,
      props: {
        fill: COLORS.white,
        stroke: COLORS.slate200,
        strokeWeight: 1,
        cornerRadius: 16,
        layout: 'horizontal',
      },
    });

    const colW = Math.floor(width / stats.length);
    stats.forEach((s, idx) => {
      const col = this.frame({
        name: `Stat-${s.label}`,
        parent: strip,
        width: colW,
        height,
        props: {
          padding: 16,
          layout: 'vertical',
          gap: 6,
          stroke: idx < stats.length - 1 ? COLORS.slate200 : 'none',
          strokeWeight: idx < stats.length - 1 ? 1 : 0,
        },
      });

      this.text({
        name: 'Label',
        parent: col,
        text: s.label,
        props: { fontSize: 13, fill: COLORS.slate600, fontWeight: 'medium' },
      });

      const valRow = this.frame({
        name: 'ValRow',
        parent: col,
        width: colW - 32,
        height: 36,
        props: { layout: 'horizontal', crossAlign: 'baseline', gap: 6 },
      });

      this.text({
        name: 'Value',
        parent: valRow,
        text: s.value,
        props: {
          fontSize: 26,
          fontWeight: 'bold',
          fill: s.tone === 'danger' ? COLORS.rose700 : (s.tone === 'success' ? COLORS.emerald700 : COLORS.slate900),
        },
      });

      if (s.unit) {
        this.text({
          name: 'Unit',
          parent: valRow,
          text: s.unit,
          props: { fontSize: 12, fill: COLORS.slate500 },
        });
      }

      this.text({
        name: 'Sub',
        parent: col,
        text: s.sub || '',
        props: { fontSize: 11, fill: s.tone === 'danger' ? COLORS.rose600 : COLORS.slate500 },
      });
    });

    return strip;
  }

  // --- App Shell (Sidebar + Topbar + Content Container) ---
  appShell({ parent, name, x = 0, y = 0, height = 900, role = 'staff', activeLabel = 'Dashboard', pageTitle, userName = 'Petugas Kasir', actions = [], contentFn, returnRefs = false }) {
    const root = this.frame({
      name,
      parent,
      x,
      y,
      width: 1440,
      height,
      props: { fill: COLORS.slate50 },
    });

    const navRefs = {};
    const actionRefs = [];

    // 1. Sidebar (width 256, height)
    const sidebar = this.frame({
      name: 'Sidebar',
      parent: root,
      x: 0,
      y: 0,
      width: 256,
      height,
      props: {
        fill: COLORS.white,
        stroke: COLORS.slate200,
        strokeWeight: 1,
        layout: 'vertical',
      },
    });

    // Sidebar Logo Header
    const logoRow = this.frame({
      name: 'LogoHeader',
      parent: sidebar,
      width: 256,
      height: 64,
      props: {
        layout: 'horizontal',
        crossAlign: 'center',
        padding: [0, 20, 0, 20],
        gap: 8,
        stroke: COLORS.slate100,
        strokeWeight: 1,
      },
    });

    this.rect({
      name: 'LogoIcon',
      parent: logoRow,
      width: 32,
      height: 32,
      props: { fill: COLORS.brand600, cornerRadius: 8 },
    });

    this.text({
      name: 'LogoText',
      parent: logoRow,
      text: 'CUCI.IN',
      props: { fontSize: 18, fontWeight: 'bold', fill: COLORS.brand700 },
    });

    // Navigation Menu List
    const navList = this.frame({
      name: 'NavList',
      parent: sidebar,
      width: 256,
      height: height - 180,
      props: {
        layout: 'vertical',
        padding: [16, 12, 16, 12],
        gap: 4,
      },
    });

    const items = NAV_ITEMS[role] || NAV_ITEMS.staff;
    items.forEach((item) => {
      const isActive = item.label === activeLabel;
      const navItem = this.frame({
        name: `NavItem-${item.label}`,
        parent: navList,
        width: 232,
        height: 40,
        props: {
          fill: isActive ? COLORS.brand700 : 'none',
          cornerRadius: 8,
          layout: 'horizontal',
          crossAlign: 'center',
          gap: 12,
          padding: [0, 12, 0, 12],
        },
      });

      navRefs[item.label] = navItem;

      if (ICONS[item.icon]) {
        this.vector({
          name: 'Icon',
          parent: navItem,
          width: 18,
          height: 18,
          path: ICONS[item.icon],
          props: { stroke: isActive ? COLORS.white : COLORS.slate600 },
        });
      }

      this.text({
        name: 'Label',
        parent: navItem,
        text: item.label,
        props: {
          fontSize: 13,
          fontWeight: isActive ? 'bold' : 'medium',
          fill: isActive ? COLORS.white : COLORS.slate700,
        },
      });
    });

    // Sidebar User Profile Footer
    const userFooter = this.frame({
      name: 'UserFooter',
      parent: sidebar,
      width: 256,
      height: 116,
      props: {
        layout: 'vertical',
        padding: [12, 16, 12, 16],
        gap: 8,
        stroke: COLORS.slate100,
        strokeWeight: 1,
      },
    });

    const userProfileRow = this.frame({
      name: 'ProfileRow',
      parent: userFooter,
      width: 224,
      height: 36,
      props: { layout: 'horizontal', crossAlign: 'center', gap: 10 },
    });

    const avatar = this.frame({
      name: 'Avatar',
      parent: userProfileRow,
      width: 34,
      height: 34,
      props: {
        fill: COLORS.brand100,
        cornerRadius: 17,
        layout: 'horizontal',
        mainAlign: 'center',
        crossAlign: 'center',
      },
    });

    this.text({
      name: 'Initial',
      parent: avatar,
      text: (userName || 'U').charAt(0).toUpperCase(),
      props: { fontSize: 13, fontWeight: 'bold', fill: COLORS.brand700 },
    });

    const userMeta = this.frame({
      name: 'UserMeta',
      parent: userProfileRow,
      width: 170,
      height: 34,
      props: { layout: 'vertical', gap: 2 },
    });

    this.text({
      name: 'UserName',
      parent: userMeta,
      text: userName,
      props: { fontSize: 12, fontWeight: 'bold', fill: COLORS.slate900 },
    });

    this.text({
      name: 'UserRole',
      parent: userMeta,
      text: role.toUpperCase(),
      props: { fontSize: 10, fill: COLORS.slate500 },
    });

    // 2. Topbar Header (x: 256, y: 0, width: 1184, height: 64)
    const header = this.frame({
      name: 'Header',
      parent: root,
      x: 256,
      y: 0,
      width: 1184,
      height: 64,
      props: {
        fill: COLORS.white,
        stroke: COLORS.slate200,
        strokeWeight: 1,
        layout: 'horizontal',
        mainAlign: 'spaceBetween',
        crossAlign: 'center',
        padding: [0, 24, 0, 24],
      },
    });

    this.text({
      name: 'PageTitle',
      parent: header,
      text: pageTitle || activeLabel,
      props: { fontSize: 18, fontWeight: 'bold', fill: COLORS.slate900 },
    });

    if (actions.length > 0) {
      const actWidth = actions.reduce((sum, act) => {
        const w = act.width || Math.ceil(act.label.length * 8.5 + (act.icon ? 32 : 18) + (act.kbd ? 36 : 0) + 24);
        return sum + w;
      }, 0) + (actions.length - 1) * 8;

      const actRow = this.frame({
        name: 'Actions',
        parent: header,
        width: actWidth,
        height: 40,
        props: { layout: 'horizontal', gap: 8, crossAlign: 'center', mainAlign: 'max' },
      });
      actions.forEach((act) => {
        const btn = this.button({
          parent: actRow,
          label: act.label,
          variant: act.variant || 'primary',
          icon: act.icon,
          kbd: act.kbd,
          width: act.width || 0,
        });
        actionRefs.push(btn);
      });
    }

    // 3. Main Content Container (x: 256, y: 64, width: 1184, height: height - 64)
    const content = this.frame({
      name: 'MainContent',
      parent: root,
      x: 256,
      y: 64,
      width: 1184,
      height: height - 64,
      props: {
        fill: COLORS.slate50,
        layout: 'vertical',
        padding: 24,
        gap: 16,
      },
    });

    if (typeof contentFn === 'function') {
      contentFn(this, content, { navRefs, actionRefs });
    }

    return returnRefs ? { root, navRefs, actionRefs } : root;
  }

  // --- Guest Shell (Public Landing & Auth matching guest.blade.php) ---
  guestShell({ parent, name, x = 0, y = 0, title, subtitle, maxWidth = 460, contentFn }) {
    const root = this.frame({
      name,
      parent,
      x,
      y,
      width: 1440,
      height: 900,
      props: { fill: COLORS.slate50 },
    });

    const cardX = Math.floor((1440 - maxWidth) / 2);

    // 1. Top Pill Navigation Bar (aligned with card width)
    const topBar = this.frame({
      name: 'TopPillRow',
      parent: root,
      x: cardX,
      y: 50,
      width: maxWidth,
      height: 40,
      props: {
        fill: 'none',
        layout: 'horizontal',
        mainAlign: 'spaceBetween',
        crossAlign: 'center',
      },
    });

    const backBtn = this.button({
      parent: topBar,
      label: 'Kembali ke Beranda',
      variant: 'secondary',
      icon: 'arrow-left',
      width: 175,
      height: 36,
    });

    this.text({
      name: 'PortalTag',
      parent: topBar,
      text: 'CUCI.IN PORTAL',
      props: { fontSize: 11, fontWeight: 'bold', fill: COLORS.slate500 },
    });

    // 2. Centered Content Card
    const card = this.frame({
      name: 'CardContainer',
      parent: root,
      x: cardX,
      y: 104,
      width: maxWidth,
      height: 680,
      props: {
        fill: COLORS.white,
        stroke: COLORS.slate200,
        strokeWeight: 1,
        cornerRadius: 18,
        padding: 32,
        layout: 'vertical',
        gap: 14,
        effects: SHADOWS.card,
      },
    });

    // Centered Brand Header inside card
    const cardHeader = this.frame({
      name: 'CardBrandHeader',
      parent: card,
      width: maxWidth - 64,
      height: 74,
      props: {
        fill: 'none',
        layout: 'vertical',
        crossAlign: 'center',
        gap: 4,
      },
    });

    const logoRow = this.frame({
      name: 'LogoRow',
      parent: cardHeader,
      width: 140,
      height: 32,
      props: { layout: 'horizontal', mainAlign: 'center', crossAlign: 'center', gap: 8 },
    });

    this.rect({
      name: 'LogoIcon',
      parent: logoRow,
      width: 26,
      height: 26,
      props: { fill: COLORS.brand600, cornerRadius: 8 },
    });

    this.text({
      name: 'LogoText',
      parent: logoRow,
      text: 'CUCI.IN',
      props: { fontSize: 20, fontWeight: 'bold', fill: COLORS.brand700 },
    });

    this.text({
      name: 'SystemSub',
      parent: cardHeader,
      text: 'Sistem Manajemen Operasional Laundry',
      props: { fontSize: 12, fill: COLORS.slate500, fontWeight: 'medium' },
    });

    if (title) {
      this.text({
        name: 'CardTitle',
        parent: card,
        text: title,
        props: { fontSize: 20, fontWeight: 'bold', fill: COLORS.slate900 },
      });
    }

    if (subtitle) {
      this.text({
        name: 'CardSubtitle',
        parent: card,
        text: subtitle,
        props: { fontSize: 13, fill: COLORS.slate500 },
      });
    }

    if (typeof contentFn === 'function') {
      contentFn(this, card);
    }

    // 3. Footer
    this.text({
      name: 'Footer',
      parent: root,
      x: 0,
      y: 810,
      width: 1440,
      text: '© 2026 CUCI.IN · Sistem Manajemen Operasional Laundry',
      props: { fontSize: 12, fill: COLORS.slate500, textAlign: 'center' },
    });

    return { root, backBtn, card };
  }
}
