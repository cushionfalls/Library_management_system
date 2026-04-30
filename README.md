# Library Management System (PHP + MySQL)

GitHub: `https://github.com/cushionfalls/Library_management_system`

## Overview
This is a **PHP (mysqli) + MySQL** library management system built to run easily on **XAMPP**. It includes:

- **Authentication** (register/login) with **email OTP verification**
- **User roles**: `ADMIN`, `LIBRARIAN`, `USER`
- **Books** + rentals/transactions + fines (project modules)
- **Wallet system** with **OTP-based top up**, transaction history, and admin refund tools
- **Membership system** where users can buy plans using **wallet balance**

## Membership plans (wallet purchase)
Membership is available at `index.php?page=membership` and is protected (login required).

- **1 month**: ₹399
- **6 months**: ₹699
- **12 months**: ₹1999

When a plan is purchased:
- Wallet balance is debited and a `WalletTransactions` row is created with reason **`MEMBERSHIP`**
- Membership becomes **ACTIVE** immediately (or extends the end date if already active)
- The membership page shows **Active plan + valid until**, otherwise **Not Activated**

## Project structure
This repo uses a simple MVC-style layout:

- **`index.php`**: single entrypoint → loads `public/index.php`
- **`public/index.php`**: front controller / router using query param `?page=...`
- **`views/`**: page templates (`dashboard.php`, `wallet.php`, `membership.php`, etc.)
- **`controllers/`**: JSON APIs (ex: `controllers/wallet.php`, `controllers/membership.php`)
- **`classes/`**: business logic + DB access (`Database.php`, `Session.php`, `Wallet.php`, `Membership.php`, etc.)
- **`config/config.php`**: app + DB configuration

## Requirements
- **XAMPP** (Apache + MySQL)
- PHP available via Apache (this project uses `mysqli`)

## Setup (local)
1. Clone:

```bash
git clone https://github.com/cushionfalls/Library_management_system.git
cd Library_management_system
```

2. Update database/app settings in `config/config.php`:
- `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`, `DB_PORT`
- `APP_URL` should match your local path (default: `http://localhost/library_management_system`)

3. Create DB + tables:
- Open the setup script once in browser:
  - `http://localhost/library_management_system/setup.php`
- This reads `db.sql`, creates tables, and seeds defaults.

4. Login with the seeded admin user (created by `setup.php`):
- **Email**: `admin@librarymanagement.com`
- **Password**: `admin123`

## Key pages (router)
Pages are loaded via:

- `http://localhost/library_management_system/index.php?page=home`
- `http://localhost/library_management_system/index.php?page=login`
- `http://localhost/library_management_system/index.php?page=dashboard` (protected)
- `http://localhost/library_management_system/index.php?page=books` (protected)
- `http://localhost/library_management_system/index.php?page=wallet` (protected)
- `http://localhost/library_management_system/index.php?page=membership` (protected)

## Key JSON APIs
These live under `controllers/` and are called by `public/js/*.js`.

### Wallet API (`controllers/wallet.php`)
- `GET  ?action=getBalance`
- `GET  ?action=getTransactions&limit=20&offset=0`
- `POST ?action=request-topup-otp` (amount, method) → sends OTP to email
- `POST ?action=verify-topup-otp` (otp) → credits wallet + logs transaction
- `GET  ?action=downloadStatement` (CSV)

### Membership API (`controllers/membership.php`)
- `GET  ?action=getPlans`
- `GET  ?action=getStatus`
- `GET  ?action=getHistory`
- `POST ?action=purchase` (plan_id) → debits wallet + activates/extends membership

## Database tables (high level)
Core:
- `Users`, `Books`, `Authors`, `BookTransactions`, `Fines`, `WalletTransactions`, `OTP`

Membership:
- `MembershipPlans` (seeded in `db.sql`)
- `UserMemberships`
- `MembershipPurchases`

## Notes / security
- **Do not commit real email credentials**. `config/config.php` contains mail settings; keep production secrets out of git.
- Wallet top-up is **OTP-protected** (email).

## Troubleshooting
- **Blank page / routing issues**: confirm `APP_URL` in `config/config.php` matches your folder name under `htdocs`.
- **Database errors**: make sure MySQL is running and `DB_NAME` exists (or run `setup.php`).
