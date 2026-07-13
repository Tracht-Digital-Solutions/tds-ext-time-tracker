# ext-time-tracker

Time-tracker **extension** for the TDS panel platform, and the **reference
implementation** of `panel-contract`. Two halves in one repo, like the contract:

- **Frontend** (`@tracht-digital-solutions/ext-time-tracker`, GitHub Packages) —
  a default-exported `ExtensionManifest` (`src/index.ts`) plus the `.astro` pages
  / widgets / settings and the React islands they hydrate (`pages/`, `widgets/`,
  `islands/`). Contributes: the `/time` page, the "Diese Woche" dashboard widget,
  a nav entry, the `time:read` permission, a settings section, DE/EN i18n.
- **Backend** (`tracht-digital-solutions/ext-time-tracker`, Composer) — a
  `TimeTrackerModule` (`php/src/`) mounting `/time/*` (incl. the widget's
  `/time/summary` dataEndpoint) + its Phinx migrations (`php/db/migrations`,
  class names prefixed `TimeTracker*`).

## How it plugs in

The product host enables it in `astro.config.mjs`:

```ts
import { panelHost } from "@tracht-digital-solutions/panel-contract/astro";
import timeTracker from "@tracht-digital-solutions/ext-time-tracker";
export default defineConfig({ integrations: [react(), panelHost({ extensions: [timeTracker] })] });
```

The base API adds `new TimeTrackerModule()` to its `ModuleRegistry`.

## Develop

```bash
npm install        # file: dep on ../panel-contract during local dev
npm run build      # tsup → dist/ (the manifest the host imports)
npm run type-check
composer install   # path repo → ../panel-contract
```

The manifest's `island` / route `entrypoint` values are package subpaths
(exposed via `exports`), which the host's Astro/Vite resolves — not local files.

## Status

Minimal but real (the `/time` UI + summary payload are placeholders). Built to
validate the contract end-to-end; the full tracker UI is ported from
`tds-admin`'s `TimeTracker.tsx` next.
