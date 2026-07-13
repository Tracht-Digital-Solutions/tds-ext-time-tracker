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
- During local dev, both halves point at `../panel-contract` (npm `file:` /
  Composer `path` repo). CI/consumers use the published package versions.

## Commands

```bash
npm run build && npm run type-check
composer install && composer test   # (no PHP tests yet)
```

## After a change

Bump `version` in `package.json` + `composer.json` (lockstep), update this file +
README, commit together.
