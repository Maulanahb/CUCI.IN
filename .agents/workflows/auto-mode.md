---
name: auto-mode
description: Activate Superpowers automated skill scanning and Caveman zero-fluff execution mode
---

# Workflow: auto-mode

When invoked:
1. Scan prompt requirements against global, plugin, and project skills.
2. For HTML/CSS, Blade UI, or client-side JS tasks, activate `modern-web-guidance` FIRST (`search` -> `retrieve`) before writing UI code.
3. For diagrams (architecture, ERD/PDM, sequence, activity, flowchart, state, `.drawio`/Mermaid/PlantUML redraw), activate `diagram-design` FIRST (check `.agents/rules/diagram-design.md` and `SKILL.md` §0).
4. For web scraping, documentation reading, or JS-rendered page extraction without heavy Chromium overhead, use `lightpanda` (check `.agents/rules/lightpanda.md`).
5. Load relevant `SKILL.md` using `read_file`.
6. Announce `[Skill: <name>]`.
7. Execute with Caveman principles: high density, code first, zero fluff.
8. Verify output (tests/lint/build/self_check) before completion.
