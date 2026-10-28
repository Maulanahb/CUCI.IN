---
name: frontend-design
description: >-
  Guidelines and best practices for creating visually stunning, modern, responsive frontend user interfaces with distinctive aesthetics, typography, animations, and avoiding generic designs. Use when designing or styling UI components, pages, or design systems.
---

# Frontend Design Skill

This skill guides the design and implementation of distinctive, polished, and production-grade frontend interfaces. It prioritizes craft, intentionality, and visual hierarchy over generic, cookie-cutter templates.

---

## Core Philosophy

1. **Avoid Generic Aesthetics**: Move beyond default framework templates (default Tailwind/Bootstrap vibes). Every design should feel custom-crafted with deliberate visual choices.
2. **Intentional Hierarchy**: Establish clear relationships between primary, secondary, and tertiary elements through scale, weight, and contrast.
3. **Harmonious Color Systems**: Use curated palettes, semantic tokenization, and subtle gradients rather than harsh primary colors.
4. **Delightful Micro-Interactions**: Provide immediate, tactile feedback for user interactions (hover, focus, active, loading states) with smooth transitions (150ms–300ms).

---

## Design System Tokens

When building components, structure styling using design tokens:

### 1. Typography
- **Headings**: Modern sans-serif or expressive display faces (e.g., *Outfit*, *Plus Jakarta Sans*, *Inter*, *Cal Sans*).
- **Body**: Highly readable sans-serif (e.g., *Inter*, system sans-serif font stack).
- **Code / Technical**: Clean monospace (e.g., *JetBrains Mono*, *Fira Code*).
- **Scale**: Strict type scale (`text-xs`, `text-sm`, `text-base`, `text-lg`, `text-xl`, `text-2xl`, `text-3xl`, `text-4xl`).

### 2. Color Palette & Theming
- **Primary / Brand**: Specific brand accent with defined shades (50 through 950).
- **Neutrals**: Slightly tinted grays (e.g., slate, zinc, cool gray) instead of pure `#000000` or `#ffffff`.
- **Accents & States**: Distinct colors for success, warning, danger, and info with matching accessible background tints.
- **Glassmorphism / Elevation**: Subtle backdrop blurs (`backdrop-blur-md`), translucent border strokes (`border-white/10` or `border-black/5`), and soft multi-layered drop shadows.

### 3. Spacing & Layout
- Use consistent spacing increments (4px, 8px, 12px, 16px, 24px, 32px, 48px, 64px).
- Give elements room to breathe with intentional whitespace (padding & margins).
- Use CSS Grid and Flexbox for predictable, fluid layouts that adapt smoothly to mobile, tablet, and desktop viewports.

---

## Component Checklist

When crafting any frontend component:
- [ ] **Interactive States**: Hover, active, focus-visible, and disabled states are fully styled.
- [ ] **Smooth Transitions**: CSS transitions added for color/transform changes (`transition-all duration-200 ease-in-out`).
- [ ] **Accessibility**: High contrast ratios, accessible touch targets (minimum 44x44px on mobile), and ARIA attributes where needed.
- [ ] **Empty & Error States**: Components gracefully handle empty arrays, missing data, and error boundaries.
- [ ] **Responsive Design**: Looks natural and functional on screens from 320px to 4K.
