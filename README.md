# U-SIIRS — UTeM Smart Infrastructure Issue Reporting System

> A web-based platform that empowers UTeM students and staff to report campus infrastructure issues and track their resolution in real time.

---

## Overview

U-SIIRS is a lightweight, framework-free PHP web application built for Universiti Teknikal Malaysia Melaka (UTeM). It provides a structured channel for the university community to submit infrastructure and maintenance reports — broken facilities, electrical faults, cleanliness issues, and more — while giving administrators the tools to triage, prioritize, and resolve them efficiently.

---

## Features

### For Users
- **Submit Reports** — Describe an issue with a title, category, location, photo attachment, and priority level
- **Track Status** — Follow each report through `Pending → In Progress → Resolved`
- **Edit & Manage** — Update or withdraw reports from a personal dashboard
- **In-App Notifications** — Get notified when an admin updates your report status
- **Contact Form** — Reach the admin team directly for general inquiries

### For Admins
- **Admin Dashboard** — Overview of all submitted reports with filtering by status and priority
- **Status Management** — Update report status with remarks logged in a full audit trail
- **User Management** — View, ban, or unban registered users
- **Contact Messages** — Read and manage incoming contact form submissions

### Security
- PDO prepared statements on every database query
- `password_hash()` / `password_verify()` (bcrypt) for all credentials
- CSRF tokens on every form
- File uploads validated by MIME type (`getimagesize`) and renamed to safe filenames
- PHP execution blocked in the uploads directory via `.htaccess`
- Access restricted to `@utem.edu.my` and `@student.utem.edu.my` email addresses

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP (Core / PDO) |
| Database | MySQL / MariaDB |
| Frontend | HTML, CSS, Vanilla JavaScript |
| Server | Apache (via XAMPP) |
| No frameworks | — |

---

## Project Structure

```
u-siirs/
├── index.php               # Landing / login redirect
├── login.php               # User login
├── register.php            # User registration
├── dashboard.php           # User home
├── submit-report.php       # New report form
├── edit-report.php         # Edit existing report
├── my-report.php           # Personal report list
├── report-details.php      # Single report view
├── contact.php             # Contact form
├── profile.php             # Profile page
├── settings.php            # Account settings
├── logout.php
│
├── admin/
│   ├── login.php
│   ├── dashboard.php       # Admin overview
│   ├── reports.php         # All reports list
│   ├── report-view.php     # Single report + status update
│   ├── update-status.php   # Status change handler
│   ├── manage-users.php    # User list + ban/unban
│   ├── contact-messages.php
│   ├── delete-report.php
│   └── logout.php
│
├── config/
│   └── database.php        # DB credentials
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── navbar.php
│   ├── auth-check.php
│   └── admin-check.php
│
├── assets/
│   ├── css/style.css
│   ├── js/app.js
│   ├── images/
│   └── uploads/            # User-uploaded report photos (must be writable)
│
└── sql/
    └── u_siirs.sql         # Full schema + seed data
```

---

## Getting Started

### Requirements
- [XAMPP](https://www.apachefriends.org/) (Apache + PHP 7.4+ or PHP 8.x, MySQL/MariaDB)

### Installation

1. **Clone or download** this repository into your XAMPP `htdocs` directory:
   ```bash
   git clone https://github.com/<your-username>/u-siirs.git C:/xampp/htdocs/u-siirs
   # macOS/Linux: /Applications/XAMPP/htdocs/u-siirs
   ```

2. **Start Apache and MySQL** from the XAMPP Control Panel.

3. **Import the database** — open `http://localhost/phpmyadmin`, click the **Import** tab, choose `sql/u_siirs.sql`, and click **Go**. This creates the `u_siirs` database with all tables and a default admin account.

4. *(Optional)* If your MySQL root account uses a password, update `config/database.php`:
   ```php
   $DB_PASS = 'your_password_here';
   ```

5. **On Linux/macOS**, make the uploads directory writable:
   ```bash
   chmod 775 assets/uploads
   ```

6. **Visit the app** at `http://localhost/u-siirs/`

### Default Admin Account

| Field | Value |
|---|---|
| URL | `http://localhost/u-siirs/admin/login.php` |
| Email | `admin@utem.edu.my` |
| Password | `Admin@123` |

> ⚠️ **Change the default password immediately after your first login.**

---

## Database Schema

| Table | Purpose |
|---|---|
| `users` | Registered UTeM users |
| `admins` | Admin accounts |
| `reports` | Infrastructure issue reports |
| `report_attachments` | Photos attached to reports |
| `status_updates` | Audit log of all status changes |
| `notifications` | In-app notifications for users |
| `contact_messages` | Contact form submissions |

---

## Troubleshooting

| Problem | Fix |
|---|---|
| *Database connection failed* | Ensure MySQL is running; check credentials in `config/database.php` |
| *Invalid CSRF token* | Clear cookies / restart browser — sessions may have expired |
| *Images not showing* | Confirm files exist in `assets/uploads` and Apache can read them |
| *Login redirect loop* | Clear the `PHPSESSID` cookie for `localhost` |
| *Path issues* | Keep the folder named `u-siirs` — `BASE_URL` auto-detects from the folder name |

---

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

---

## License

This project is developed for academic purposes at UTeM. All rights reserved by the respective contributors.
