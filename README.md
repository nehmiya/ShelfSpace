# 📚 ShelfSpace — Modern Library Management System

> A sleek, Netflix-inspired library management web application built with PHP, MySQL, and vanilla CSS — no frameworks, no fluff.

---

## 🎯 Overview

**ShelfSpace** is a full-stack library management system that allows users to browse, borrow, and return books, while giving admins full control over the catalog, categories, and borrowing activity. The UI is dark-mode first, designed with a premium glass-morphism aesthetic and smooth micro-animations.

---

## 🛠️ Tech Stack

| Layer        | Technology                          |
|--------------|--------------------------------------|
| **Backend**  | PHP 8+ (procedural + PDO)            |
| **Database** | MySQL via PDO with prepared statements |
| **Frontend** | Vanilla HTML5 + Vanilla CSS          |
| **Typography** | Google Fonts — Inter               |
| **Server**   | MAMP (Apache on localhost)           |

> ❌ No Bootstrap. No Tailwind. No jQuery. No JS frameworks.

---

## ✨ Features

### 👤 Authentication & Access Control
- Secure **user registration** with email validation and bcrypt password hashing (`password_hash`)
- **Login / Logout** with PHP session management (`session_regenerate_id` on login for session fixation protection)
- **Role-based access control** — two roles: `admin` and `user`
- Protected routes via a shared `auth_check.php` partial that redirects unauthenticated users
- Admin-only pages enforced by a `require_admin()` helper function

### 📖 Book Browsing (index.php)
- Responsive **book grid** displaying all library books with cover image, title, author, category, and availability badge
- **Live search** by title or author using client-side JavaScript (no page reload)
- **Category filter** dropdown to narrow down books
- One-click **Borrow** button — uses a PDO transaction to atomically insert a borrowing record and mark the book as `Borrowed`
- Fallback cover image via [ui-avatars.com](https://ui-avatars.com) when no cover URL is provided

### 📋 User Dashboard (dashboard.php)
- Shows **currently borrowed books** in a card grid with borrow date
- **Return book** button per card — uses a PDO transaction to stamp `returned_at` and reset book status to `Available`
- **Recent return history** table showing the last 10 returned books
- Personalised greeting with the user's name and role badge

### 🛡️ Admin Panel (admin.php)
- Live **stat cards**: Total Books, Active Users, Books Currently Borrowed, Total Categories
- **Recent active borrowings** table showing who borrowed what and when
- Quick-access buttons to **Add Book** and **Manage Categories**

### ➕ Add Book (add_book.php) — Admin Only
- Form to create a new book entry with: Title, Author, ISBN, Category, and optional Cover Image URL
- **ISBN uniqueness** check before insertion
- Blocks submission if no categories exist (with a helpful message)
- Clears POST data after successful submission to prevent re-submission

### 🗂️ Category Management (categories.php) — Admin Only
- Add new book categories
- View all existing categories with their **book count**
- Delete categories — protected from deletion if they still contain books (referential integrity at the app level, backed by a `RESTRICT` FK constraint)

---

## 📁 Project Structure

```
library/
├── index.php              # Book catalog — browse, search, filter, borrow
├── dashboard.php          # User dashboard — active borrows & return history
├── login.php              # Login form + session handling
├── register.php           # New user registration
├── logout.php             # Session destroy & redirect
├── admin.php              # Admin dashboard with stats & recent activity
├── add_book.php           # Admin-only: Add a new book
├── categories.php         # Admin-only: Manage book categories
├── config.php             # DB connection (PDO), sanitize helper, auto-admin seed
├── database.sql           # MySQL schema — tables + default admin seed
│
├── partials/
│   ├── header.php         # HTML <head>, navbar (role-aware nav links), <main> open
│   ├── footer.php         # </main>, <footer>, script includes
│   └── auth_check.php     # Session guard + require_admin() function
│
└── assets/
    ├── style.css          # Design system: CSS variables, layout, components
    ├── animations.css     # Hover effects, card transitions, staggered fade-ins
    └── script.js          # Client-side search & category filter logic
```

---

## 🗄️ Database Schema

```sql
-- Four tables, clean relational design

users
  ├── id            INT AUTO_INCREMENT PK
  ├── name          VARCHAR(255)
  ├── email         VARCHAR(255) UNIQUE
  ├── password      VARCHAR(255)       -- bcrypt hash
  ├── role          ENUM('admin','user') DEFAULT 'user'
  └── created_at    TIMESTAMP

categories
  ├── id            INT AUTO_INCREMENT PK
  └── category_name VARCHAR(255) UNIQUE

books
  ├── id            INT AUTO_INCREMENT PK
  ├── category_id   INT FK → categories(id) ON DELETE RESTRICT
  ├── title         VARCHAR(255)
  ├── author        VARCHAR(255)
  ├── isbn          VARCHAR(255) UNIQUE
  ├── status        ENUM('Available','Borrowed') DEFAULT 'Available'
  ├── cover_image   VARCHAR(255) NULL
  └── added_on      TIMESTAMP

borrowings
  ├── id            INT AUTO_INCREMENT PK
  ├── book_id       INT FK → books(id) ON DELETE CASCADE
  ├── user_id       INT FK → users(id) ON DELETE CASCADE
  ├── borrowed_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  └── returned_at   TIMESTAMP NULL   -- NULL = currently borrowed
```

> A `returned_at IS NULL` check distinguishes active borrows from returned ones — no separate status enum needed.

---

## 🎨 Design System

### Color Palette (CSS Custom Properties)

| Variable             | Value       | Usage                          |
|----------------------|-------------|--------------------------------|
| `--bg-color`         | `#0f172a`   | Page background (dark slate)   |
| `--card-bg`          | `#1e293b`   | Card / panel backgrounds       |
| `--text-primary`     | `#f8fafc`   | Main text                      |
| `--text-secondary`   | `#94a3b8`   | Labels, muted text             |
| `--accent-primary`   | `#3b82f6`   | Blue — links, highlights       |
| `--accent-gradient`  | `135deg, #3b82f6 → #8b5cf6` | Brand gradient (blue→purple) |
| `--success`          | `#10b981`   | Available badge, return dates  |
| `--danger`           | `#ef4444`   | Error states, borrowed badge   |
| `--border-color`     | `rgba(255,255,255,0.1)` | Subtle borders          |

### Typography
- **Font**: [Inter](https://fonts.google.com/specimen/Inter) — loaded from Google Fonts
- Weights used: 300, 400, 500, 600, 700

### Animations (`animations.css`)
- `fadeIn` keyframe with `translateY(10px → 0)` on page/main content load
- **Staggered card entrance** — each book card has an `animation-delay` (0.1s increments, up to 8 cards)
- Book cover **zoom on hover** (`scale(1.05)`) with `overflow: hidden` clip on container
- Button **lift effect** on hover (`translateY(-2px)`) with glow shadow for `.btn-primary`
- Card **elevation effect** on hover (`translateY(-5px)` + deeper shadow)
- Glassmorphism **sticky navbar** with `backdrop-filter: blur(10px)`

---

## 🔐 Security Practices

| Threat               | Mitigation                                                      |
|----------------------|-----------------------------------------------------------------|
| SQL Injection        | All queries use PDO prepared statements with parameterized inputs |
| XSS                  | All output runs through `sanitize()` → `htmlspecialchars()`    |
| Session Fixation     | `session_regenerate_id(true)` called on successful login        |
| CSRF (basic)         | Actions require POST, protected by session auth guard           |
| Privilege Escalation | `require_admin()` guard on all admin pages, server-side        |
| Password Storage     | `password_hash()` with `PASSWORD_DEFAULT` (bcrypt)             |
| Error Exposure       | PDO exceptions caught with `die()` in config, not exposed in pages |

---

## ⚙️ Setup & Installation

### Prerequisites
- [MAMP](https://www.mamp.info/) (or any LAMP/WAMP stack)
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10+

### Steps

1. **Clone / copy** the project into `C:\MAMP\htdocs\library\`

2. **Start MAMP** — ensure Apache and MySQL are running

3. **Import the database schema**:
   - Open phpMyAdmin at `http://localhost/phpmyadmin`
   - Run the contents of [`database.sql`](./database.sql)
   - This creates the `shelfspace` database, all tables, and seeds a default admin

4. **Verify config** in [`config.php`](./config.php):
   ```php
   $host     = 'localhost';
   $dbname   = 'shelfspace';
   $username = 'root';
   $password = 'root'; // Update if your MySQL password differs
   ```

5. **Access the app** at: `http://localhost/library/`

### Default Admin Credentials
| Field    | Value                    |
|----------|--------------------------|
| Email    | `admin@shelfspace.com`   |
| Password | `admin123`               |

> ⚠️ Change the admin password immediately in a production environment.

---

## 🗺️ Page Map & User Flows

```
[Guest]
  └──> login.php       ─── POST ──> session created ──> index.php
  └──> register.php    ─── POST ──> success message ──> login.php

[Logged-in User]
  └──> index.php       ─── Browse all books, search, filter, borrow
  └──> dashboard.php   ─── View active borrows, return books, history
  └──> logout.php      ─── Destroys session ──> login.php

[Admin] (all of the above, plus:)
  └──> admin.php       ─── Stats dashboard + recent borrowing feed
  └──> add_book.php    ─── Add a new book to the catalog
  └──> categories.php  ─── Add / delete book categories
```

---

## 📝 Key Code Patterns

### PDO Transactions (Borrow / Return)
Both borrow and return operations wrap two SQL statements in a transaction to guarantee atomicity — if either fails, both are rolled back:

```php
$pdo->beginTransaction();
try {
    $pdo->prepare("UPDATE books SET status = 'Borrowed' WHERE id = ?")->execute([$book_id]);
    $pdo->prepare("INSERT INTO borrowings (book_id, user_id) VALUES (?, ?)")->execute([$book_id, $user_id]);
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
}
```

### Auth Guard Partial
Every protected page includes the same two lines at the top:
```php
require_once 'config.php';
require_once 'partials/auth_check.php';
// admin-only pages also call:
require_admin();
```

### Client-Side Search & Filter (`script.js`)
Uses `data-title`, `data-author`, and `data-category` HTML attributes on each book card for zero-latency filtering without AJAX:
```js
document.getElementById('searchInput').addEventListener('input', filterBooks);
document.getElementById('categoryFilter').addEventListener('change', filterBooks);
```

---

## 🚀 Possible Enhancements

- [ ] Borrow due-dates & overdue notifications
- [ ] Admin ability to view all users and their borrow history
- [ ] Pagination for large book catalogs
- [ ] Book edit / delete functionality for admins
- [ ] Upload cover images from disk (instead of URL only)
- [ ] Email notifications on borrow/return
- [ ] Search with server-side filtering via AJAX for scalability

---

*Built with ❤️ — ShelfSpace © 2026*
