# test-manager-library-3145556-api

> **Technical assessment · ADSO cohort 3145556**
> Deadline: **Tuesday, October 13 2026, 5:00 p.m.** (17:00, Colombia time).

Backend for *Manager Library*, built with **PHP 8.3 + Laravel 11** on top of PostgreSQL 16.

Spec lives in [`test-manager-library-3145556-docs`](https://github.com/TheJhampierBoy/test-manager-library-3145556-docs).
The spec is written for Python and .NET but the business rules, API contract, and data model are
implemented here in Laravel without changes to scope.

## Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.3 |
| Framework | Laravel 11 |
| Database | PostgreSQL 16 — schema `library` |
| Auth | JWT via `tymon/jwt-auth` (8 h TTL, HS256) |
| Image | `intervention/image` 3.x |
| Container | Docker (php:8.3-cli base image) |

## Quick start

```bash
cp .env.example .env
# Set APP_KEY, JWT_SECRET, ADMIN_USERNAME, ADMIN_PASSWORD in .env

docker compose up --build
# Verify:
curl http://localhost:8000/api/v1/health
# → {"status":"ok"}
```

## Environment variables

| Variable | Required | Purpose |
|---|---|---|
| `DATABASE_URL` | yes | PostgreSQL connection string (parsed with `parse_url()`) |
| `JWT_SECRET` | yes | HS256 signing key — never committed |
| `ADMIN_USERNAME` / `ADMIN_PASSWORD` | yes | Initial admin account credentials |
| `APP_KEY` | yes | Laravel encryption key |
| `CORS_ORIGIN` | yes | Allowed frontend origin (e.g. `http://localhost:5173`) |
| `COVERS_DIR` | no | Cover storage path (default: `storage/app/public/covers`) |

## API contract

Base URL: `/api/v1`

All endpoints except `POST /auth/login` and `GET /health` require
`Authorization: Bearer <token>`.

See full contract in [`docs/api-contract.md`](docs/api-contract.md).

## Task progress

| Task | Description | Status |
|---|---|---|
| T-01 | Project skeleton + `GET /health` | ✅ done |
| T-02 | Initial migration: 6 tables, FK, unique indexes, category seed | ✅ done |
| T-03 | Domain value objects: `Isbn`, `Quantity`, `DateRange` | ⬜ pending |
| T-04 | Domain: `Book` (Withdraw, Restock, soft-delete) | ⬜ pending |
| T-05 | Domain: `Loan` (AddItem, ReturnItem, calculated status, 14 days, max 3) | ⬜ pending |
| T-06 | Domain: `Member` and `User` (name normalisation, valid role) | ⬜ pending |
| T-07 | Use cases: authentication (`LoginService`, `CreateLibrarianService`) | ⬜ pending |
| T-08 | Use cases: books (create, search, update, soft-delete, cover) | ⬜ pending |
| T-09 | Use case: lend (`LendBooksService`) | ⬜ pending |
| T-10 | Use case: return and overdue | ⬜ pending |
| T-11 | Use case: most-borrowed report | ⬜ pending |
| T-12 | Persistence adapter: book, member, user repositories | ⬜ pending |
| T-13 | Persistence adapter: loan repository + optimistic lock | ⬜ pending |
| T-14 | Security adapter: password hashing + JWT | ⬜ pending |
| T-15 | Cover adapter (disk) | ⬜ pending |
| T-16 | HTTP adapter: routes, unified error body, role auth | ⬜ pending |
| T-17 | Composition and bootstrap (admin from env) | ⬜ pending |
| T-18 | Push domain invariants to DB: `ck_book_total_positive`, `ck_user_role` | ⬜ pending |
| T-19 | Architecture tests (Arch. I, II, III) | ⬜ pending |
| T-20 | `infra`: `docker compose up` starts empty engine, API and frontend | ⬜ pending |
| T-21 | `tool`: demo seeder via API (≥40 books, 10 members, 15 loans, 3 overdue) | ⬜ pending |
| T-22 | `app`: login, catalogue, members, lend, return, overdue, report | ⬜ pending |
| T-23 | `page`: static public presentation site | ⬜ pending |

## Repository links

| Repository | Purpose |
|---|---|
| [`docs`](https://github.com/TheJhampierBoy/test-manager-library-3145556-docs) | Spec |
| [`api`](https://github.com/TheJhampierBoy/test-manager-library-3145556-api) | This repo — Laravel backend |
| [`app`](https://github.com/TheJhampierBoy/test-manager-library-3145556-app) | React frontend |
| [`page`](https://github.com/TheJhampierBoy/test-manager-library-3145556-page) | Static public site |
| [`infra`](https://github.com/TheJhampierBoy/test-manager-library-3145556-infra) | Containers and empty database engine |
| [`tool`](https://github.com/TheJhampierBoy/test-manager-library-3145556-tool) | Demo data seeder |
