---
name: auto-mode
description: Activate Superpowers automated skill scanning and Caveman zero-fluff execution mode
---

# Workflow: auto-mode

When invoked:
1. Scan prompt requirements against global, plugin, and project skills.
2. For HTML/CSS, Blade UI, or client-side JS tasks, activate `modern-web-guidance` FIRST (`search` -> `retrieve`) before writing UI code.
3. Load relevant `SKILL.md` using `read_file`.
4. Announce `[Skill: <name>]`.
5. Execute with Caveman principles: high density, code first, zero fluff.
6. Verify output (tests/lint/build) before completion.
