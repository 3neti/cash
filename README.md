# 3neti/cash

A lightweight Laravel package for representing **monetary value as a first-class model**, with support for:

- precise money handling (via Brick\Money)
- wallet integration (via Bavix Wallet)
- status lifecycle (via Spatie Model Status)
- tagging (via Spatie Tags)
- metadata and expiration handling

This package is designed as a **supporting domain layer** for the x-change ecosystem, particularly for voucher issuance, redemption, and disbursement workflows.

---

## ✨ Core Concept

`Cash` is a **value container**:

- it represents an amount of money
- it can be tagged, status-tracked, and expired
- it can be linked to any model via `reference`
- it integrates with wallet systems for transfer and settlement

It is **not a ledger**  
It is **not a wallet**  

It is a **portable monetary unit** used across workflows.

---

## 📦 Installation

```bash
composer require 3neti/cash
```

---

## ⚙️ Configuration

Publish config (optional):

```bash
php artisan vendor:publish --tag=config
```

---

## 🧱 Database Migrations

This package uses:

```php
loadMigrationsFrom()
```

So migrations are **auto-loaded**.

Run:

```bash
php artisan migrate
```

---

## ⚠️ Migration Prerequisites

This package depends on the following schema:

- `spatie/laravel-model-status` → `statuses` table
- `spatie/laravel-tags` → `tags` tables
- `3neti/wallet` / `bavix/laravel-wallet` → wallet tables

👉 These must be installed and migrated by the host application.

This package **does NOT publish or manage those migrations**.

---

## 🧠 Usage

### Creating Cash

```php
use LBHurtado\Cash\Models\Cash;

$cash = Cash::create([
    'amount' => 1500.00,
    'currency' => 'PHP',
    'meta' => ['note' => 'Transport support'],
]);
```

---

### Using Money Object

```php
use Brick\Money\Money;

$cash = Cash::create([
    'amount' => Money::of(1500, 'PHP'),
    'currency' => 'PHP',
]);
```

Stored internally as **minor units**.

---

### Accessing Amount

```php
$cash->amount; // Brick\Money\Money instance
$cash->amount->getAmount()->toFloat(); // 1500.00
$cash->amount->getMinorAmount()->toInt(); // 150000
```

---

### Metadata

```php
$cash->meta->note;
$cash->meta['note'];
```

---

### Expiration

```php
$cash->expired = true;
$cash->save();

$cash->expired; // true
```

---

### Status Management

```php
use LBHurtado\Cash\Enums\CashStatus;

$cash->setStatus(CashStatus::DISBURSED);

$cash->hasStatus(CashStatus::DISBURSED);
$cash->getCurrentStatus();
```

---

### Secret Protection

```php
$cash->secret = '1234';
$cash->save();

$cash->verifySecret('1234'); // true
```

---

### Redemption Check

```php
$cash->canRedeem('1234');
```

---

### Tags

```php
$cash->attachTag('transport');
$cash->tags;
```

---

### Wallet Integration

```php
$cash->depositFloat(1000);
$cash->withdrawFloat(500);
```

---

## 🧾 Data Transformation

Use `CashData` for API responses:

```php
use LBHurtado\Cash\Data\CashData;

CashData::fromModel($cash);
```

---

## 🧱 Schema

```text
cash
- id
- amount (minor units)
- currency
- reference_type
- reference_id
- meta (json)
- secret (hashed)
- expires_on
- timestamps
```

---

## 🧩 Relationships

- morphTo: `reference`
- morphOne: `withdrawTransaction`
- statuses (Spatie)
- tags (Spatie)
- wallet (Bavix)

---

## 🧪 Testing

This package uses:

- Testbench
- in-memory SQLite
- test-only migrations under `tests/database/migrations`

No vendor migrations are required for tests.

---

## 🧭 Architecture Role

In the **x-change ecosystem**:

- `cash` = monetary payload
- `voucher` = contract/instruction
- `wallet` = balance + ledger
- `x-change` = orchestration

---

## 🔒 Design Principles

- Money is always stored in **minor units**
- Currency is explicit
- Status is first-class
- Expiration is built-in
- Metadata is flexible
- Secrets are hashed

---

## 🚀 Future Enhancements

- reconciliation support
- audit integration
- stricter immutability enforcement
- event hooks

---

## 🧾 License

Proprietary
