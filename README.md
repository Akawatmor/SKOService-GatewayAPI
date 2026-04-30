# GatewayAPI

GatewayAPI is a PHP 8.2 hybrid API hub for SKOService. It combines API catalog discovery, bilingual documentation, JWT and API Key authentication, access-request workflows, proxy forwarding, rate limiting, try-it-out tooling, and SQLite snapshot delivery.

## Features

- Public homepage and searchable API catalog
- API detail pages with collapsible schema viewer, health checks, and interactive try-it-out
- Hybrid authentication: JWT browser session plus API Key programmatic access
- Access request and grant workflow for private APIs
- Admin and developer workspaces for APIs, endpoints, users, access requests, rate limits, and logs
- Proxy mode for upstream REST, GraphQL, SOAP-like HTTP targets
- File snapshot generation for services of type `File`
- Thai and English UI toggle, Anuphan typography, dark mode, mobile-ready layout, and PWA install support

## Project Structure

- `public/` front controller, static assets
- `src/` application core, controllers, middleware, services, models
- `templates/` PHP views and reusable components
- `database/` SQLite file, migrations, demo seed
- `storage/` schemas, generated snapshots, logs

## Run Locally

1. Use PHP 8.2 or newer.
1. Start the built-in server from the project root:

```bash
php -S 127.0.0.1:8000 -t public
```

1. Open `http://127.0.0.1:8000`.

The SQLite database is created automatically at first boot and seeded with demo data.

## Demo Accounts

- Admin: `admin@gatewayapi.local` / `Admin123#`
- Developer: `dev@gatewayapi.local` / `Dev12345#`

## Important Routes

- `/` homepage
- `/search` API catalog and filters
- `/api/{slug}` API detail
- `/dashboard` developer workspace
- `/admin` admin overview
- `/proxy/{slug}/{path}` authenticated proxy forwarding

## Notes

- Tailwind CDN has been replaced with local utility styles in `public/assets/css/utilities.css` for safer production deployment.
- The PWA service worker caches the app shell and serves `public/offline.html` as a navigation fallback when the network is unavailable.
- Schema rendering uses sample files in `storage/schemas/`.
- Snapshot generation clones the current SQLite database into `storage/snapshots/`.
- Built-in PHP sessions are used for form flashes and browser auth context; JWT and API key resolution still apply to request handling.
- Upstream auth secret storage and WebSocket/gRPC proxying remain future-phase items.
