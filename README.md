## 🏦 DFCU Payments Gateway – Deployment Guide

This guide walks you through deploying the DFCU Payments Gateway API and Client on a production-ready environment.

---

### 🚀 Technologies Used

- **Backend API:** PHP (vanilla, no framework)
- **Frontend Client:** HTML, Bootstrap, jQuery (PHP-rendered)
- **Database:** MySQL
- **Web Server:** Apache or Nginx
- **PHP Version:** 7.4+

---

## 📁 Directory Structure

```
/dfcu
  ├── api/                 # Payment API endpoints
    ├── logs/              # API Request logs
  ├── client/              # Web client (HTML + JS + PHP)
  ├── sql/                 # Database schema
  ├── collection.json      # Postman Collection for quick import
  ├── documentation.txt    # API Documentation link
  └── README.md
  
```

---

## 💠 Step-by-Step Deployment Instructions

### 1. ✅ Prerequisites

Ensure your server has the following:

- PHP 7.4+
- MySQL 5.7+
- Apache or Nginx
- `mod_rewrite` enabled (for Apache for clean url - API only)

---

### 2. 🗃️ Database Setup

- Create a new database, e.g., `dfcu`.

- Use the following schema:

```sql
CREATE TABLE transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  payer_account VARCHAR(20) NOT NULL,
  payee_account VARCHAR(20) NOT NULL,
  amount DECIMAL(15,2) NOT NULL,
  currency VARCHAR(5) NOT NULL,
  payer_reference VARCHAR(255),
  transaction_reference VARCHAR(64) NOT NULL UNIQUE,
  status VARCHAR(20) DEFAULT 'PENDING',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

### 3. ⚙️ API Configuration

- Place the `api/` folder in your web root (e.g., `/var/www/html/dfcu/api`).
- Update `database.php` inside `api/` directory:

```php
<?php
$host = 'localhost';
$db   = 'dfcu';
$user = 'your_db_user';
$pass = 'your_db_password';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
?>
```

- Expose these endpoints: these are api endpoints:

  - `POST /payment/initiate`
  - `GET /payment/verify/{transaction_reference}`


---

### 4. 🌐 Web Client Setup

- Open `client/index.php` and set the API base URL:

```php
$site_url = 'http://yourdomain.com/dfcu/api';
```

- Place the `client/` folder in your web root (e.g., `/var/www/html/dfcu/client`).

- Add the logo or ribbon image as needed in `logo.png`.
- Add the favicon  as needed in `favicon.ico`.

---

### 5. 🔒 Permissions & Security

- Ensure `logs/` directory is writable by the web server.
- All API inputs are validated backend.
- Disable directory indexing via `.htaccess`.
- Use HTTPS in production.

---

### 6. 🔀 Sample API Requests

**Initiate Payment**

```http
POST /payment/initiate
Content-Type: application/json

{
  "payer": "1000123456",
  "payee": "1000654321",
  "amount": 150000,
  "currency": "UGX",
  "payer_reference": "School Fees Jan"
}
```

**Verify Payment**
- Check the payment status of the transaction at this point before giving value

```http
GET /payment/verify/182441b72f7490efc066a14cbb643b17e30d
```
