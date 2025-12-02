

# ✅ **README.md — Flash-Sale Checkout API (Laravel)**

## **Overview**

This project implements a high-concurrency **Flash Sale Checkout API** using **Laravel**, built to guarantee correctness under heavy parallel traffic.
It prevents **overselling**, supports **temporary holds**, **order creation**, and **idempotent payment webhooks**.

The API exposes:

* Accurate product stock endpoint
* Short-lived holds (reserved stock)
* Order creation using holds
* Safe, idempotent payment webhook
* Automatic release of expired holds

This implementation follows the requirements of:
**Laravel Interview Task — Flash Sale Checkout (Concurrency & Correctness)**.

---

## **Tech Stack**

* **Laravel** (v11/12 compatible)
* **MySQL (InnoDB)**
* **Laravel Cache** (Redis recommended but supports file/database)
* **Queues** (for automatic hold expiry)
* **Laravel Scheduler**

---

# **Core Concepts & Guarantees**

### ✅ 1. **Accurate Available Stock**

Stock visibility is always correct because:

* Holds reserve stock immediately.
* Holds are stored in DB + cache.
* Expired holds automatically release stock.
* All write operations use DB transactions and row-level locking.

### ✅ 2. **Short-Lived Holds (Reservations)**

`POST /api/holds`

* Creates a 2-minute reservation.
* Reduces available stock immediately.
* Stored in DB and cache.
* If time expires → a scheduled job auto-releases the stock.

### Hold Expiry Handling

Uses:

* `expire_at` timestamp
* Laravel Scheduled Command runs every minute to release expired holds
* Safe from double-processing (DB locking)

---

### ✅ 3. **Order Creation**

`POST /api/orders`

* Validates hold is active & unused.
* Locks the hold row using `SELECT … FOR UPDATE`.
* Creates order in `pending_payment` state.
* Stock is already reserved from the hold.

---

### ✅ 4. **Payment Webhook (Idempotent)**

`POST /api/payments/webhook`

Guarantees:

* Webhook may arrive **multiple times** → processed once.
* Webhook may arrive **before order creation** → waits & retries.
* Uses unique **idempotency_key** to dedupe.
* Safe concurrent processing via DB row locks.

Final states:

* If payment success → order marked `paid`
* If payment fails → order marked `cancelled` and stock released back

---

### ⚙️ Idempotency Implementation

Webhook handler stores:

* `idempotency_key`
* `payload_hash`
* `status`

Any repeated webhook with the same key returns the existing result.

---

# **Project Setup**

### **1. Clone the repository**

```bash
git clone <repo-url>
cd flash-sale
```

### **2. Install dependencies**

```bash
composer install
```

### **3. Copy environment file**

```bash
cp .env.example .env
php artisan key:generate
```

### **4. Configure .env**

Database:

```
DB_DATABASE=flashsale
DB_USERNAME=root
DB_PASSWORD=
```

Cache (recommended):

```
CACHE_DRIVER=redis
```

Queues:

```
QUEUE_CONNECTION=database
```

---

### **5. Run migrations and seed product**

```bash
php artisan migrate --seed
```

Seeder creates:

* Product `ID = 1`
* Price
* Finite stock (example: 50)

---

### **6. Start queue worker**

```bash
php artisan queue:work
```

### **7. Start scheduler**

(For auto-expire holds)

```
php artisan schedule:work
```

---

## **API Endpoints**

### **GET /api/products/{id}**

Returns:

* id
* name
* price
* total stock
* available stock
  (calculated as: total stock − active holds − paid orders)

---

### **POST /api/holds**

Body:

```json
{
  "product_id": 1,
  "qty": 1
}
```

Returns:

```json
{
  "hold_id": 14,
  "expires_at": "2025-01-12T10:45:20Z"
}
```

---

### **POST /api/orders**

Body:

```json
{
  "hold_id": 14
}
```

Returns:

```json
{
  "order_id": 99,
  "status": "pending_payment"
}
```

---

### **POST /api/payments/webhook**

Body example:

```json
{
  "idempotency_key": "pay_abc_123",
  "order_id": 99,
  "status": "success"
}
```

Possible webhook statuses:

* `success`
* `failed`
* `cancelled`

---

# **Testing the System**

### **1. Simulate High Concurrency (Parallel Holds)**

Use Postman Runner or artillery:

```
40 users
each tries: POST /api/holds qty=1
```

Expected:

* No overselling
* Holds succeed until real available stock runs out
* Remaining requests get error: “Insufficient stock”

---

### **2. Test Hold Expiry**

Steps:

1. Create hold
2. Wait 2 minutes
3. Check product availability → stock should return automatically

---

### **3. Test Webhook Idempotency**

Send same webhook multiple times:

```bash
repeat 5 times
POST /api/payments/webhook (same idempotency_key)
```

### **4. Test Out-of-Order Webhook**

1. Send webhook before creating order
2. Then create order normally
3. Webhook processor will detect and fix final state

---








