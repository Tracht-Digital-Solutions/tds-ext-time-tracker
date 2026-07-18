# AGENTS.md — ext-time-tracker

The first TDS panel **extension** and the reference for `panel-contract`. Read
`panel-contract`'s AGENTS.md first — this repo just implements that contract.

## Shape

- `src/index.ts` — `defineExtension({...})` default export (the manifest).
- `pages/*.astro` — pages injected via the manifest's `routes` slot.
- `widgets/*.astro` — dashboard widget shells (server component + embedded
  hydrated React island). Referenced by the `widgets` slot's `island`.
- `islands/*` — React islands + settings shells.
- `php/src/TimeTrackerModule.php` — the backend `Module`.
- `php/db/migrations/*` — Phinx migrations, class names **prefixed `TimeTracker`**.

## Gotchas

- **`island` / `entrypoint` are package subpaths, not local paths.** They must be
  exposed in `package.json` `exports` (`./pages/*`, `./widgets/*`, `./islands/*`)
  so the host's Astro/Vite resolves them from `node_modules`.
- **The manifest is built (tsup) to `dist/`.** The host imports plain JS from
  `.`; `defineExtension` is `external` (resolved from the host's panel-contract).
- **Widgets can't be hydrated by string.** A widget is an `.astro` shell that
  internally renders its React island with `client:load`; the host renders the
  shell in a loop (see `panel-contract` astro.ts).
- **Migration class names must be globally unique** across all modules — always
  prefix with `TimeTracker`.
- Depends on the **published** `tds-panel-contract` (`^0.2.0`): npm from GitHub
  Packages (`.npmrc` + `NPM_TOKEN`), Composer from the public VCS repo. **No local
  path repo** — Composer fatals on a missing path repo in CI. Same dual pipeline as
  `tds-ext-template` (annotated release tag; `npm install --no-package-lock`).

## Checkpoint status

- **CP1 (reference smoke):** manifest with all six slots + placeholder
  `/time/summary` proved end-to-end composition.
- **CP2 (real time tracking):** `Domain\TimeEntryRepository` + a real module —
  scoped to the authenticated user (`app_user_id` = JWT `userId`, via the core
  `UserContext`; data via the core PDO). A single running timer (`POST /time/start`
  / `/time/stop`, one open `ended_at IS NULL` row per user), manual entries
  (`POST /time/entries`, validated `ended_at > started_at`), a recent list
  (`GET /time/entries`, SQL-computed duration), delete, and the widget's real
  weekly total (`GET /time/summary` → `weekHours` + running state, current ISO
  week Mon→now). New `time:write` permission (viewing stays `time:read`). Frontend:
  the `WeekSummary` widget fetches the real summary; the `/time` page hosts the full
  `TimeTracker` island (timer + manual form + list). phpunit 4/4 (RBAC/validation
  short-circuit before the repo; DB-backed paths skip without a DB). Added `php-di`
  dev dep for the test container.

## Commands

```bash
npm run build && npm run type-check
composer install && composer test   # (no PHP tests yet)
```

## After a change

Bump `version` in `package.json` + `composer.json` (lockstep), update this file +
README, commit together.
