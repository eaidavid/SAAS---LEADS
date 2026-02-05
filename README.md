# SaaS Leads

Base structure for the SaaS Leads platform.

## Quick start

1) Copy env file:

```
copy .env.example .env
```

2) Install dependencies:

```
composer install
```

3) Run with PHP built-in server:

```
php -S localhost:8080 -t public
```

## Notes

- Tailwind is loaded via CDN for now.
- Add your Google Maps API key in `.env` when ready.
- Database schema is in `database/schema.sql`.
- Google Maps search uses `curl` in PHP (enable `ext-curl`).
- Phase 1 screens: `/leads`, `/leads/new`, `/leads/show?id=1`, `/leads/edit?id=1`.
- CSV export: `/leads/export` (respects filters).
- Google Maps search: `/leads/search` (requires `GOOGLE_MAPS_API_KEY`).
- Projects & imports: `/projects`, `/projects/new`, `/projects/show?id=1`, `/imports/new?project_id=1`.
- CSV import columns used (0-based): B(1) Maps URL, F(5) Address, I(8) Phone, K(10) Mobile, O(14) Category, Q(16) Comments, R(17) Rating, S(18) Website.
