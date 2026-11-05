# LoudIsland .NET Backend

This folder contains a parallel ASP.NET Core backend for the existing LoudIsland project. It does not replace the Laravel app; it gives you a separate `.NET` API with the same base prefix, `"/api/v1"`, so the existing frontend can be pointed at it incrementally.

## What is implemented

- JWT auth endpoints: `register`, `login`, `refresh`, `me`, `logout`
- CRUD-style admin endpoints for `users`, `roles`, `permissions`, `events`, `tikets`, `transactions`, `participants`, `promo-codes`
- Operational endpoints used by the frontend such as `racepacks`, `staffs`, `nomor-punggung`, `withdrawals`, `referral-codes`, `payment/{invoice}`, and basic dashboard totals
- Swagger/OpenAPI at `/openapi/v1.json` in development
- In-memory seeded data so the API is runnable without a database

## What is not ported yet

- Laravel-specific integrations such as Midtrans, media uploads, WhatsApp/email blast jobs, file exports, queue workers, and database persistence
- Full parity for every route in `routes/api.php`

## Run

```bash
cd /Volumes/BISMILLAH/GitHub/loudisland/dotnet-backend
dotnet run
```

The default seeded login is:

- `admin@loudisland.test` / `password`
- `staff@loudisland.test` / `password`

## Frontend

If you want to test the React frontend against this backend, point `VITE_APP_API_URL` to the .NET server, for example:

```bash
VITE_APP_API_URL=http://localhost:5148/api/v1
```
