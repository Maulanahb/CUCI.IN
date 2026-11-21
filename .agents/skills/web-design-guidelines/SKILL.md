---
name: web-design-guidelines
description: >-
  Core web design standards, UI/UX conventions, accessibility (WCAG), performance optimization, and layout consistency. Use when building or auditing web pages for usability, semantic structure, accessibility, and responsive UX.
---

# Web Design Guidelines Skill

This skill enforces established web standards, UX best practices, accessibility (a11y), responsive design, and performance optimizations across web applications.

---

## 1. Semantic HTML & Document Structure

- **Page Structure**: Always use semantic tags (`<header>`, `<nav>`, `<main>`, `<section>`, `<article>`, `<aside>`, `<footer>`).
- **Heading Hierarchy**: Exactly one `<h1>` per page. Subheadings must follow logical sequential order (`<h2>` -> `<h3>` -> `<h4>`) without skipping levels.
- **Navigation**: Wrap main navigational links in `<nav>` with clear link text (avoid vague labels like "click here").
- **Landmarks & ARIA**: Use native semantic HTML elements first. Only supplement with ARIA attributes (`aria-label`, `aria-expanded`, `aria-live`) when native HTML cannot convey the state or role.

---

## 2. Accessibility (WCAG 2.1 AA Compliance)

- **Color Contrast**: Ensure at least `4.5:1` contrast ratio for normal text and `3:1` for large text against background colors.
- **Keyboard Navigation**:
  - All interactive elements must be reachable via `Tab` and operable via `Enter` or `Space`.
  - Provide visible `:focus-visible` outline indicators (never use `outline: none` without an accessible alternative).
- **Images & Media**: Always provide meaningful `alt` text for informational images, or empty `alt=""` for purely decorative images.
- **Forms**: Every form input must have an associated `<label for="...">` or `aria-labelledby`.

---

## 3. Responsive Layout & Breakpoints

Follow a mobile-first approach with fluid layouts:

| Breakpoint | Target Devices | Behavior |
| :--- | :--- | :--- |
| `< 640px` (`sm`) | Mobile phones | Single-column layouts, touch-friendly targets (min 44x44px), collapsible nav. |
| `640px - 1024px` (`md`) | Tablets / Large phones | Multi-column grids where appropriate, adjusted typography scale. |
| `1024px - 1280px` (`lg`) | Laptops / Desktops | Full navigation bar, sidebars, multi-column dashboard layouts. |
| `> 1280px` (`xl/2xl`) | Large displays | Maximum container widths (`max-w-7xl` or similar) to prevent line lengths exceeding readable limits (~65-75 characters). |

---

## 4. User Experience (UX) Patterns

- **Form Usability**:
  - Inline validation with clear error messages located next to the relevant field.
  - Clear distinction between required and optional fields.
  - Disable submit button during asynchronous submission to prevent double submission, and display a spinner or loading text.
- **Feedback & States**:
  - Loading states (skeletons or spinners) for asynchronous operations.
  - Empty states with informative copy and a clear call-to-action (CTA).
  - Feedback toasts or alerts for successes, warnings, and errors.

---

## 5. Web Performance & Core Web Vitals

- **Images**: Use modern formats (WebP, AVIF) with explicit `width` and `height` attributes to prevent Cumulative Layout Shift (CLS).
- **Lazy Loading**: Enable native `loading="lazy"` on below-the-fold images and iframes.
- **Fonts**: Preload critical fonts and use `font-display: swap` to avoid Flash of Invisible Text (FOIT).
- **Asset Size**: Minify CSS and JS bundles, and avoid unnecessary external libraries when native CSS/JS suffices.
