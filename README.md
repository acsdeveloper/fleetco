# FleetCo - Fleet Management System

A web-based fleet management system for tracking vehicles, fuel, maintenance, inventory, accidents, and renewals.

## Features

- Vehicle master records and reporting
- Fuel record maintenance and efficiency reports
- Maintenance records (regular service, general repair, accident repair)
- Inventory module with GRN (Goods Received Note) workflows
- Accident reporting
- Insurance management (claims, payments, companies)
- Renewal tracking (vehicle renewals, other renewals)
- Vehicle expense reports
- User management with role-based access control

## Requirements

- PHP 8.1+
- MySQL 8.0+
- Composer

## Quick Start with Docker

The easiest way to run FleetCo is with Docker Compose.

**1. Clone the repository**
```bash
git clone https://github.com/acsdeveloper/fleetco.git
cd fleetco
```

**2. Start the stack**
```bash
make up
```

The app will be available at [http://localhost:8080](http://localhost:8080).

**3. Import the database**
```bash
make db-restore < fleetco/mysql-dump-with\ dummy-data.sql
```

Or import manually via the MySQL shell:
```bash
make db-shell
```

### Docker environment variables

| Variable | Default | Description |
|---|---|---|
| `APP_PORT` | `8080` | Host port to expose the app on |
| `DB_NAME` | `fleetco` | MySQL database name |
| `DB_USER` | `fleetco` | MySQL user |
| `DB_PASSWORD` | `fleetco` | MySQL user password |
| `DB_ROOT_PASSWORD` | `rootpassword` | MySQL root password |

### Available make commands

```bash
make help         # List all commands
make build        # Build production image
make up           # Start production stack
make dev          # Start development stack (with Xdebug)
make down         # Stop containers
make logs         # Tail app logs
make shell        # Open shell in app container
make db-shell     # Open MySQL shell
make db-dump      # Dump database to backup.sql
make db-restore   # Restore database from backup.sql
make lint         # Check PHP syntax on all files
```

## Manual Installation (without Docker)

**1. Install PHP dependencies**
```bash
composer install --no-dev --optimize-autoloader
```

**2. Create a MySQL database and import the dump**
```sql
CREATE DATABASE fleetco;
```
```bash
mysql -u root -p fleetco < fleetco/mysql-dump-with\ dummy-data.sql
```

**3. Configure the database connection**

Edit `fleetco/connections/ConnectionManager.php` lines 254–258:
```php
$data["connInfo"][0] = "localhost";       // host
$data["connInfo"][1] = "db_username";     // username
$data["connInfo"][2] = "db_password";     // password
$data["connInfo"][3] = "3306";            // port
$data["connInfo"][4] = "fleetco";         // database name
```

**4. Point your web server document root** to `/path/to/fleetco/fleetco/`

**5. Visit the app** and log in with the default admin credentials:

> **Username:** `admin`
> **Password:** `AdminF123`

**Change the default password immediately after first login.**

## Development

Start the dev stack with Xdebug enabled:
```bash
make dev
```

The source directory is mounted into the container, so changes are reflected instantly without rebuilding.

## License

GPLv3 — Originally created by Vishan Fernando.
