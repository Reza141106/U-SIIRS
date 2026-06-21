# 🏫 U-SIIRS — UTeM Smart Infrastructure Issue Reporting System

> A web-based platform that empowers UTeM students and staff to report campus infrastructure issues and track their resolution in real time.

---

## 📖 Overview

**U-SIIRS (UTeM Smart Infrastructure Issue Reporting System)** is a lightweight, framework-free PHP web application developed for **Universiti Teknikal Malaysia Melaka (UTeM)**.

The system provides a centralized platform where students and staff can report infrastructure issues such as:

- Broken facilities
- Electrical faults
- Water leakage
- Cleanliness issues
- Internet/network problems
- Safety hazards

Administrators can efficiently manage reports, update statuses, communicate with users, and maintain a complete audit trail of all actions.

---

## 🎯 Objectives

- Provide a centralized infrastructure issue reporting platform.
- Improve maintenance response efficiency.
- Increase transparency through real-time tracking.
- Reduce communication delays.
- Maintain proper documentation and audit trails.
- Enhance campus infrastructure management.

---

# ✨ Features

## 👨‍🎓 User Features

### 📝 Submit Reports

Users can submit reports including:

- Issue title
- Description
- Category
- Location
- Priority level
- Photo attachment

### 📊 Track Report Status

Monitor report progress in real time:

```text
Pending
   ↓
In Progress
   ↓
Resolved
```

### ✏️ Edit & Manage Reports

Users can:

- Edit reports
- Withdraw reports
- View report history
- Check administrator remarks

### 🔔 Notifications

Receive notifications whenever:

- Report status changes
- Remarks are added
- Issues are resolved

### 📩 Contact Administrators

Users can send inquiries through the built-in contact form.

---

## 👨‍💼 Administrator Features

### 📈 Dashboard

View:

- Total reports
- Pending reports
- Reports in progress
- Resolved reports
- User statistics

### 🗂 Report Management

Administrators can:

- View all reports
- Search reports
- Filter reports
- Update statuses
- Add remarks

### 👥 User Management

Administrators can:

- View registered users
- Ban users
- Unban users
- Monitor activity

### 📬 Contact Message Management

Administrators can:

- Read contact messages
- Manage inquiries
- Track communications

### 📜 Audit Trail

Every report update is logged automatically for transparency and accountability.

---

# 🔒 Security Features

## Database Security

- PDO Prepared Statements
- SQL Injection Protection
- Parameterized Queries

## Password Security

```php
password_hash()
password_verify()
```

- Passwords are hashed using bcrypt.
- Plain text passwords are never stored.

## CSRF Protection

All forms use CSRF tokens:

```php
$_SESSION['csrf_token']
```

## File Upload Protection

Uploaded files are:

- MIME type validated
- Verified using `getimagesize()`
- Renamed securely
- Stored in a protected uploads directory

## Upload Directory Protection

```apache
php_flag engine off
Options -ExecCGI
```

## Email Restrictions

Only official UTeM email addresses are allowed:

```text
@utem.edu.my
@student.utem.edu.my
```

---

# 🏗 System Workflow

## User Workflow

```text
Register/Login
      ↓
Submit Report
      ↓
Admin Reviews Report
      ↓
Status Updated
      ↓
User Receives Notification
      ↓
Issue Resolved
```

## Administrator Workflow

```text
Receive Report
      ↓
Review Report
      ↓
Assign Priority
      ↓
Update Status
      ↓
Add Remarks
      ↓
Close Report
```

---

# 💻 Technology Stack

| Layer | Technology |
|---------|------------|
| Backend | PHP (Core PHP) |
| Database | MySQL / MariaDB |
| Frontend | HTML5 |
| Styling | CSS3 |
| Scripting | Vanilla JavaScript |
| Server | Apache |
| Development Environment | XAMPP |
| Database Access | PDO |

---

# 📂 Project Structure

```text
u-siirs/
│
├── admin/
│   ├── login.php
│   ├── dashboard.php
│   ├── reports.php
│   ├── report-view.php
│   ├── update-status.php
│   ├── manage-users.php
│   ├── contact-messages.php
│   ├── delete-report.php
│   └── logout.php
│
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│   └── uploads/
│
├── config/
│   └── database.php
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── navbar.php
│   ├── auth-check.php
│   └── admin-check.php
│
├── sql/
│   └── u_siirs.sql
│
├── index.php
├── login.php
├── register.php
├── dashboard.php
├── submit-report.php
├── my-report.php
├── report-details.php
├── contact.php
├── profile.php
├── settings.php
└── logout.php
```

---

# 🗄 Database Schema

| Table | Purpose |
|---------|---------|
| users | Registered users |
| admins | Administrator accounts |
| reports | Infrastructure reports |
| report_attachments | Uploaded report images |
| status_updates | Status history records |
| notifications | User notifications |
| contact_messages | Contact form submissions |

---

# ⚙️ Installation Guide

## 1. Clone Repository

```bash
git clone https://github.com/YOUR_USERNAME/u-siirs.git
```

Move the project into:

```text
C:\xampp\htdocs\u-siirs
```

---

## 2. Start XAMPP

Open XAMPP Control Panel and start:

- Apache
- MySQL

---

## 3. Import Database

Open:

```text
http://localhost/phpmyadmin
```

Import:

```text
sql/u_siirs.sql
```

---

## 4. Configure Database

Edit:

```php
config/database.php
```

Example:

```php
$DB_HOST = 'localhost';
$DB_NAME = 'u_siirs';
$DB_USER = 'root';
$DB_PASS = '';
```

---

## 5. Configure Upload Permissions

Linux/macOS:

```bash
chmod 775 assets/uploads
```

---

## 6. Launch Application

Open:

```text
http://localhost/u-siirs/
```

---

# 🔑 Default Admin Account

| Field | Value |
|---------|---------|
| URL | http://localhost/u-siirs/admin/login.php |
| Email | admin@utem.edu.my |
| Password | Admin@123 |

> ⚠️ Change the default password immediately after first login.

---

# 📷 Screenshots

## Login Page

![Login Page](docs/screenshots/login.png)

## User Dashboard

![Dashboard](docs/screenshots/dashboard.png)

## Submit Report

![Report Form](docs/screenshots/report-form.png)

## Admin Dashboard

![Admin Dashboard](docs/screenshots/admin-dashboard.png)

---

# 🧪 Testing Checklist

- [x] User Registration
- [x] User Login
- [x] Report Submission
- [x] Image Upload
- [x] Report Editing
- [x] Status Tracking
- [x] Notification System
- [x] Contact Form
- [x] Admin Dashboard
- [x] User Ban/Unban
- [x] CSRF Validation

---

# 🔮 Future Enhancements

- Email notifications
- Google Maps integration
- QR code issue reporting
- Push notifications
- Analytics dashboard
- AI-based priority recommendation
- Dark mode
- Mobile application
- Maintenance staff assignment module

---

# 👨‍💻 Development Team

Developed for:

**Universiti Teknikal Malaysia Melaka (UTeM)**

Faculty of Information and Communication Technology (FTMK)

---

# 📄 License

This project was developed for academic and educational purposes.

All rights belong to the respective contributors and Universiti Teknikal Malaysia Melaka (UTeM).

---

# ⭐ Support

If you find this project useful:

⭐ Star the repository

🍴 Fork the repository

🐛 Report issues

💡 Suggest improvements

---

## U-SIIRS

**Smart Reporting • Faster Resolution • Better Campus Infrastructure**
# 🏫 U-SIIRS — UTeM Smart Infrastructure Issue Reporting System

> A web-based platform that empowers UTeM students and staff to report campus infrastructure issues and track their resolution in real time.

---

## 📖 Overview

**U-SIIRS (UTeM Smart Infrastructure Issue Reporting System)** is a lightweight, framework-free PHP web application developed for **Universiti Teknikal Malaysia Melaka (UTeM)**.

The system provides a centralized platform where students and staff can report infrastructure issues such as:

- Broken facilities
- Electrical faults
- Water leakage
- Cleanliness issues
- Internet/network problems
- Safety hazards

Administrators can efficiently manage reports, update statuses, communicate with users, and maintain a complete audit trail of all actions.

---

## 🎯 Objectives

- Provide a centralized infrastructure issue reporting platform.
- Improve maintenance response efficiency.
- Increase transparency through real-time tracking.
- Reduce communication delays.
- Maintain proper documentation and audit trails.
- Enhance campus infrastructure management.

---

# ✨ Features

## 👨‍🎓 User Features

### 📝 Submit Reports

Users can submit reports including:

- Issue title
- Description
- Category
- Location
- Priority level
- Photo attachment

### 📊 Track Report Status

Monitor report progress in real time:

```text
Pending
   ↓
In Progress
   ↓
Resolved
```

### ✏️ Edit & Manage Reports

Users can:

- Edit reports
- Withdraw reports
- View report history
- Check administrator remarks

### 🔔 Notifications

Receive notifications whenever:

- Report status changes
- Remarks are added
- Issues are resolved

### 📩 Contact Administrators

Users can send inquiries through the built-in contact form.

---

## 👨‍💼 Administrator Features

### 📈 Dashboard

View:

- Total reports
- Pending reports
- Reports in progress
- Resolved reports
- User statistics

### 🗂 Report Management

Administrators can:

- View all reports
- Search reports
- Filter reports
- Update statuses
- Add remarks

### 👥 User Management

Administrators can:

- View registered users
- Ban users
- Unban users
- Monitor activity

### 📬 Contact Message Management

Administrators can:

- Read contact messages
- Manage inquiries
- Track communications

### 📜 Audit Trail

Every report update is logged automatically for transparency and accountability.

---

# 🔒 Security Features

## Database Security

- PDO Prepared Statements
- SQL Injection Protection
- Parameterized Queries

## Password Security

```php
password_hash()
password_verify()
```

- Passwords are hashed using bcrypt.
- Plain text passwords are never stored.

## CSRF Protection

All forms use CSRF tokens:

```php
$_SESSION['csrf_token']
```

## File Upload Protection

Uploaded files are:

- MIME type validated
- Verified using `getimagesize()`
- Renamed securely
- Stored in a protected uploads directory

## Upload Directory Protection

```apache
php_flag engine off
Options -ExecCGI
```

## Email Restrictions

Only official UTeM email addresses are allowed:

```text
@utem.edu.my
@student.utem.edu.my
```

---

# 🏗 System Workflow

## User Workflow

```text
Register/Login
      ↓
Submit Report
      ↓
Admin Reviews Report
      ↓
Status Updated
      ↓
User Receives Notification
      ↓
Issue Resolved
```

## Administrator Workflow

```text
Receive Report
      ↓
Review Report
      ↓
Assign Priority
      ↓
Update Status
      ↓
Add Remarks
      ↓
Close Report
```

---

# 💻 Technology Stack

| Layer | Technology |
|---------|------------|
| Backend | PHP (Core PHP) |
| Database | MySQL / MariaDB |
| Frontend | HTML5 |
| Styling | CSS3 |
| Scripting | Vanilla JavaScript |
| Server | Apache |
| Development Environment | XAMPP |
| Database Access | PDO |

---

# 📂 Project Structure

```text
u-siirs/
│
├── admin/
│   ├── login.php
│   ├── dashboard.php
│   ├── reports.php
│   ├── report-view.php
│   ├── update-status.php
│   ├── manage-users.php
│   ├── contact-messages.php
│   ├── delete-report.php
│   └── logout.php
│
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│   └── uploads/
│
├── config/
│   └── database.php
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── navbar.php
│   ├── auth-check.php
│   └── admin-check.php
│
├── sql/
│   └── u_siirs.sql
│
├── index.php
├── login.php
├── register.php
├── dashboard.php
├── submit-report.php
├── my-report.php
├── report-details.php
├── contact.php
├── profile.php
├── settings.php
└── logout.php
```

---

# 🗄 Database Schema

| Table | Purpose |
|---------|---------|
| users | Registered users |
| admins | Administrator accounts |
| reports | Infrastructure reports |
| report_attachments | Uploaded report images |
| status_updates | Status history records |
| notifications | User notifications |
| contact_messages | Contact form submissions |

---

# ⚙️ Installation Guide

## 1. Clone Repository

```bash
git clone https://github.com/YOUR_USERNAME/u-siirs.git
```

Move the project into:

```text
C:\xampp\htdocs\u-siirs
```

---

## 2. Start XAMPP

Open XAMPP Control Panel and start:

- Apache
- MySQL

---

## 3. Import Database

Open:

```text
http://localhost/phpmyadmin
```

Import:

```text
sql/u_siirs.sql
```

---

## 4. Configure Database

Edit:

```php
config/database.php
```

Example:

```php
$DB_HOST = 'localhost';
$DB_NAME = 'u_siirs';
$DB_USER = 'root';
$DB_PASS = '';
```

---

## 5. Configure Upload Permissions

Linux/macOS:

```bash
chmod 775 assets/uploads
```

---

## 6. Launch Application

Open:

```text
http://localhost/u-siirs/
```

---

# 🔑 Default Admin Account

| Field | Value |
|---------|---------|
| URL | http://localhost/u-siirs/admin/login.php |
| Email | admin@utem.edu.my |
| Password | Admin@123 |

> ⚠️ Change the default password immediately after first login.

---

# 📷 Screenshots

## Login Page

![Login Page](docs/screenshots/login.png)

## User Dashboard

![Dashboard](docs/screenshots/dashboard.png)

## Submit Report

![Report Form](docs/screenshots/report-form.png)

## Admin Dashboard

![Admin Dashboard](docs/screenshots/admin-dashboard.png)

---

# 🧪 Testing Checklist

- [x] User Registration
- [x] User Login
- [x] Report Submission
- [x] Image Upload
- [x] Report Editing
- [x] Status Tracking
- [x] Notification System
- [x] Contact Form
- [x] Admin Dashboard
- [x] User Ban/Unban
- [x] CSRF Validation

---

# 🔮 Future Enhancements

- Email notifications
- Google Maps integration
- QR code issue reporting
- Push notifications
- Analytics dashboard
- AI-based priority recommendation
- Dark mode
- Mobile application
- Maintenance staff assignment module

---

# 👨‍💻 Development Team

Developed for:

**Universiti Teknikal Malaysia Melaka (UTeM)**

Faculty of Information and Communication Technology (FTMK)

---

# 📄 License

This project was developed for academic and educational purposes.

All rights belong to the respective contributors and Universiti Teknikal Malaysia Melaka (UTeM).

---

# ⭐ Support

If you find this project useful:

⭐ Star the repository

🍴 Fork the repository

🐛 Report issues

💡 Suggest improvements

---

## U-SIIRS

**Smart Reporting • Faster Resolution • Better Campus Infrastructure**
# 🏫 U-SIIRS — UTeM Smart Infrastructure Issue Reporting System

> A web-based platform that empowers UTeM students and staff to report campus infrastructure issues and track their resolution in real time.

---

## 📖 Overview

**U-SIIRS (UTeM Smart Infrastructure Issue Reporting System)** is a lightweight, framework-free PHP web application developed for **Universiti Teknikal Malaysia Melaka (UTeM)**.

The system provides a centralized platform where students and staff can report infrastructure issues such as:

- Broken facilities
- Electrical faults
- Water leakage
- Cleanliness issues
- Internet/network problems
- Safety hazards

Administrators can efficiently manage reports, update statuses, communicate with users, and maintain a complete audit trail of all actions.

---

## 🎯 Objectives

- Provide a centralized infrastructure issue reporting platform.
- Improve maintenance response efficiency.
- Increase transparency through real-time tracking.
- Reduce communication delays.
- Maintain proper documentation and audit trails.
- Enhance campus infrastructure management.

---

# ✨ Features

## 👨‍🎓 User Features

### 📝 Submit Reports

Users can submit reports including:

- Issue title
- Description
- Category
- Location
- Priority level
- Photo attachment

### 📊 Track Report Status

Monitor report progress in real time:

```text
Pending
   ↓
In Progress
   ↓
Resolved
```

### ✏️ Edit & Manage Reports

Users can:

- Edit reports
- Withdraw reports
- View report history
- Check administrator remarks

### 🔔 Notifications

Receive notifications whenever:

- Report status changes
- Remarks are added
- Issues are resolved

### 📩 Contact Administrators

Users can send inquiries through the built-in contact form.

---

## 👨‍💼 Administrator Features

### 📈 Dashboard

View:

- Total reports
- Pending reports
- Reports in progress
- Resolved reports
- User statistics

### 🗂 Report Management

Administrators can:

- View all reports
- Search reports
- Filter reports
- Update statuses
- Add remarks

### 👥 User Management

Administrators can:

- View registered users
- Ban users
- Unban users
- Monitor activity

### 📬 Contact Message Management

Administrators can:

- Read contact messages
- Manage inquiries
- Track communications

### 📜 Audit Trail

Every report update is logged automatically for transparency and accountability.

---

# 🔒 Security Features

## Database Security

- PDO Prepared Statements
- SQL Injection Protection
- Parameterized Queries

## Password Security

```php
password_hash()
password_verify()
```

- Passwords are hashed using bcrypt.
- Plain text passwords are never stored.

## CSRF Protection

All forms use CSRF tokens:

```php
$_SESSION['csrf_token']
```

## File Upload Protection

Uploaded files are:

- MIME type validated
- Verified using `getimagesize()`
- Renamed securely
- Stored in a protected uploads directory

## Upload Directory Protection

```apache
php_flag engine off
Options -ExecCGI
```

## Email Restrictions

Only official UTeM email addresses are allowed:

```text
@utem.edu.my
@student.utem.edu.my
```

---

# 🏗 System Workflow

## User Workflow

```text
Register/Login
      ↓
Submit Report
      ↓
Admin Reviews Report
      ↓
Status Updated
      ↓
User Receives Notification
      ↓
Issue Resolved
```

## Administrator Workflow

```text
Receive Report
      ↓
Review Report
      ↓
Assign Priority
      ↓
Update Status
      ↓
Add Remarks
      ↓
Close Report
```

---

# 💻 Technology Stack

| Layer | Technology |
|---------|------------|
| Backend | PHP (Core PHP) |
| Database | MySQL / MariaDB |
| Frontend | HTML5 |
| Styling | CSS3 |
| Scripting | Vanilla JavaScript |
| Server | Apache |
| Development Environment | XAMPP |
| Database Access | PDO |

---

# 📂 Project Structure

```text
u-siirs/
│
├── admin/
│   ├── login.php
│   ├── dashboard.php
│   ├── reports.php
│   ├── report-view.php
│   ├── update-status.php
│   ├── manage-users.php
│   ├── contact-messages.php
│   ├── delete-report.php
│   └── logout.php
│
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│   └── uploads/
│
├── config/
│   └── database.php
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── navbar.php
│   ├── auth-check.php
│   └── admin-check.php
│
├── sql/
│   └── u_siirs.sql
│
├── index.php
├── login.php
├── register.php
├── dashboard.php
├── submit-report.php
├── my-report.php
├── report-details.php
├── contact.php
├── profile.php
├── settings.php
└── logout.php
```

---

# 🗄 Database Schema

| Table | Purpose |
|---------|---------|
| users | Registered users |
| admins | Administrator accounts |
| reports | Infrastructure reports |
| report_attachments | Uploaded report images |
| status_updates | Status history records |
| notifications | User notifications |
| contact_messages | Contact form submissions |

---

# ⚙️ Installation Guide

## 1. Clone Repository

```bash
git clone https://github.com/YOUR_USERNAME/u-siirs.git
```

Move the project into:

```text
C:\xampp\htdocs\u-siirs
```

---

## 2. Start XAMPP

Open XAMPP Control Panel and start:

- Apache
- MySQL

---

## 3. Import Database

Open:

```text
http://localhost/phpmyadmin
```

Import:

```text
sql/u_siirs.sql
```

---

## 4. Configure Database

Edit:

```php
config/database.php
```

Example:

```php
$DB_HOST = 'localhost';
$DB_NAME = 'u_siirs';
$DB_USER = 'root';
$DB_PASS = '';
```

---

## 5. Configure Upload Permissions

Linux/macOS:

```bash
chmod 775 assets/uploads
```

---

## 6. Launch Application

Open:

```text
http://localhost/u-siirs/
```

---

# 🔑 Default Admin Account

| Field | Value |
|---------|---------|
| URL | http://localhost/u-siirs/admin/login.php |
| Email | admin@utem.edu.my |
| Password | Admin@123 |

> ⚠️ Change the default password immediately after first login.

---

# 📷 Screenshots

## Login Page

![Login Page](docs/screenshots/login.png)

## User Dashboard

![Dashboard](docs/screenshots/dashboard.png)

## Submit Report

![Report Form](docs/screenshots/report-form.png)

## Admin Dashboard

![Admin Dashboard](docs/screenshots/admin-dashboard.png)

---

# 🧪 Testing Checklist

- [x] User Registration
- [x] User Login
- [x] Report Submission
- [x] Image Upload
- [x] Report Editing
- [x] Status Tracking
- [x] Notification System
- [x] Contact Form
- [x] Admin Dashboard
- [x] User Ban/Unban
- [x] CSRF Validation

---

# 🔮 Future Enhancements

- Email notifications
- Google Maps integration
- QR code issue reporting
- Push notifications
- Analytics dashboard
- AI-based priority recommendation
- Dark mode
- Mobile application
- Maintenance staff assignment module

---

# 👨‍💻 Development Team

Developed for:

**Universiti Teknikal Malaysia Melaka (UTeM)**

Faculty of Information and Communication Technology (FTMK)

---

# 📄 License

This project was developed for academic and educational purposes.

All rights belong to the respective contributors and Universiti Teknikal Malaysia Melaka (UTeM).

---

# ⭐ Support

If you find this project useful:

⭐ Star the repository

🍴 Fork the repository

🐛 Report issues

💡 Suggest improvements

---

## U-SIIRS

**Smart Reporting • Faster Resolution • Better Campus Infrastructure**