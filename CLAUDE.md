# FleetCo — Claude Code Instructions

## Project Overview
FleetCo is a PHP 8.1+ fleet management web application backed by MySQL 8.0.
Source lives under `fleetco/` (the web root). Infrastructure files (Docker, Composer) are at the repo root.

## Stack
- **Language:** PHP 8.1+
- **Database:** MySQL 8.0 (connection: `fleetco/connections/ConnectionManager.php`)
- **Frontend:** HTML/JS with XTemplate engine (`fleetco/include/xtempl.php`)
- **PDF:** dompdf
- **Spreadsheet:** PHPSpreadsheet
- **Auth:** Session-based, user table `carrierusers`, passwords currently plaintext

## Key Directories
| Path | Purpose |
|---|---|
| `fleetco/classes/` | Core page/control classes (runnerpage, listpage, editpage, etc.) |
| `fleetco/include/` | Settings, variables, helper functions per table |
| `fleetco/connections/` | DB connection classes and ConnectionManager |
| `fleetco/classes/controls/` | Field/control rendering classes |
| `fleetco/plugins/` | Third-party libraries (dompdf, PHPExcel) |
| `fleetco/templates_c/` | Compiled template cache (gitignored) |
| `fleetco/files/` | User uploads (gitignored) |
| `docker/` | Apache, PHP, MySQL, Xdebug config for Docker |

## Development Workflow
```bash
make dev        # Start dev stack (PHP + MySQL + Xdebug)
make lint       # PHP syntax check all files
make db-shell   # Open MySQL shell
make logs       # Tail app logs
```

## Database Connection
Edit `fleetco/connections/ConnectionManager.php` lines 254–258 to change DB credentials.
Default DB: `fleetco`, user: `fleetco`, password: `_RFnEicsTYeD_P6P`, host: `localhost:3306`.

## Coding Conventions
- PHP 8.1+ — always use `?? ''` / `?? []` instead of `@` error suppression
- Cast values before passing to `strlen()`, `trim()`, `rawurlencode()`, `count()` when source may be null
- No `count(null)` — use `count($var ?? [])`
- No `strlen(null)` — use `strlen((string)($var ?? ''))`
- Passwords: plaintext currently — **TODO: migrate to `password_hash()` / `password_verify()`**
- Do not commit `vendor/`, `fleetco/templates_c/`, or `fleetco/files/`

## Git Branches
- `master` — upstream/original source
- `dev` — active development branch, push here

## Open TODOs
- [ ] Hash passwords with bcrypt (`password_hash` / `password_verify`) in `loginpage.php`
- [ ] Add brute-force / rate-limiting protection on login
- [ ] Add CSRF token validation on forms
- [ ] Enforce HTTPS-only session cookies
- [ ] Create `fleetcov96_settings` table in MySQL (see `fleetco/classes/paramsLogger.php`)
