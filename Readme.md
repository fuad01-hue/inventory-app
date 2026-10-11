# Inventory Management Application (`inventory-app`)

A robust, modular, and secure inventory management web application built with pure PHP and MySQL. Designed with a clean directory architecture, robust validation helpers, and comprehensive stock movement tracking.

---

## 📁 Project Directory Structure

The project is structured following a clean separation of concerns:

```text
inventory-app/
├── config/
│   └── database.php             # Database connection configuration (PDO)
├── helpers/
│   ├── auth.php                 # Authentication & session management functions
│   ├── escape.php               # Data sanitization and XSS prevention utilities
│   ├── functions.php            # Core business logic and general helper utilities
│   └── validation.php           # Input validation and error handling routines
├── public/
│   ├── categories/
│   │   ├── hapus_kategori.php   # Delete category module
│   │   └── kategori.php         # Manage & view product categories
│   ├── products/
│   │   ├── create.php           # Add new product form & handler
│   │   ├── edit.php             # Update existing product details
│   │   ├── hapus.php            # Delete product module
│   │   └── tampilan.php         # View list of products
│   ├── stock/
│   │   ├── in.php               # Record stock input (restock)
│   │   ├── out.php              # Record stock output (sales/usage)
│   │   └── riwayat.php          # Audit trail / history of stock movements
│   ├── suppliers/
│   │   ├── create.php           # Add new supplier form & handler
│   │   └── edit.php             # Update supplier details
│   │   └── hapus.php            # Delete supplier module
│   │   └── tampilan.php         # View list of suppliers
│   ├── create_admin.php         # Initial admin account setup script
│   ├── dashboard.php            # Main administrative dashboard / overview
│   ├── login.php                # Authentication login interface
│   └── logout.php               # Session termination script
├── views/
│   ├── footer.php               # Global HTML footer component
│   └── header.php               # Global HTML header & navigation component
```

---

## 🗄️ Database Schema (`inventory_db`)

The application connects to a MySQL/MariaDB database named `inventory_db` containing 7 core tables:

1. **`users`**
   * Stores system user credentials, roles, and login access.
2. **`user_profiles`**
   * Contains extended profile information linked to each user record (one-to-one relationship).
3. **`categories`**
   * Classifies products into distinct groups or categories.
4. **`suppliers`**
   * Stores supplier contact details, company names, and vendor information.
5. **`products`**
   * The core inventory catalog containing product names, SKU, pricing, and current stock levels.
6. **`product_suppliers`**
   * Pivot/junction table establishing relationships between products and their respective suppliers (many-to-many).
7. **`stock_movements`**
   * Logs every stock transaction (incoming or outgoing) with timestamps and quantity changes for complete inventory auditing.

---

## 🚀 Installation & Setup

1. **Prerequisites**
   * Local web server environment with PHP (v8.0 or higher) and MySQL/MariaDB (e.g., XAMPP).
   * A web browser.

2. **Database Setup**
   * Open **phpMyAdmin** (`http://localhost/phpmyadmin/`).
   * Create a new database named **`inventory_db`**.
   * Import or structure the 7 required tables (`categories`, `products`, `product_suppliers`, `stock_movements`, `suppliers`, `users`, `user_profiles`).

3. **Configure Database Connection**
   * Open `config/database.php` and verify your credentials:
     ```php
     $host = '127.0.0.1';
     $db   = 'inventory_db';
     $user = 'root';
     $pass = '';
     ```

4. **Running the Application**
   * Place the `inventory-app` folder inside your server's root directory (e.g., `C:\xampp\htdocs\inventory-app`).
   * Start Apache and MySQL services via the XAMPP Control Panel.
   * Access the application in your browser:
     > `http://localhost/inventory-app/public/login.php`

---

## 🔐 Security & Features
* **Authentication Protection:** Secure login/logout checks via `helpers/auth.php`.
* **Data Sanitization:** Input escaping utility (`helpers/escape.php`) preventing SQL Injection and Cross-Site Scripting (XSS).
* **Modular Layout:** Consistent UI components utilizing modular `header.php` and `footer.php`.
* **Stock Auditing:** Dedicated logs for tracking stock inflows and outflows via `stock/riwayat.php`.

---

## 📜 License
Developed for educational and operational inventory management purposes.