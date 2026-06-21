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
# 🚀 Extended Documentation — U-SIIRS

---

# 🧠 System Architecture

U-SIIRS follows a modular and layered architecture to ensure scalability, maintainability, and clear separation of concerns.

## 🔹 Architecture Layers

### 1. Presentation Layer (Frontend)

* Built using HTML5, CSS3, and JavaScript
* Handles user interaction
* Provides responsive UI for different devices

### 2. Application Layer (Backend Logic)

* Developed using Core PHP
* Handles business logic
* Processes user inputs
* Controls workflow between frontend and database

### 3. Data Layer (Database)

* MySQL / MariaDB
* Stores all system data including users, reports, and logs

---

# 🔄 System Modules

## 1. Authentication Module

Handles:

* User registration
* Login/logout
* Session management
* Role-based access control (User/Admin)

## 2. Report Management Module

Handles:

* Report submission
* Editing and deletion
* Status updates
* Report categorization

## 3. Notification Module

Handles:

* Real-time system notifications
* Status update alerts
* User engagement tracking

## 4. Admin Control Module

Handles:

* Dashboard analytics
* Report filtering
* User management
* System monitoring

## 5. Contact & Communication Module

Handles:

* User inquiries
* Admin responses
* Communication logs

---

# 📊 Use Case Description

## 👨‍🎓 User Use Cases

### UC1: Register Account

Actor: User
Description: User creates a new account using UTeM email

### UC2: Login

Actor: User
Description: User logs into system securely

### UC3: Submit Report

Actor: User
Description: Submit infrastructure issue

### UC4: Track Report

Actor: User
Description: Monitor report progress

### UC5: Receive Notification

Actor: User
Description: Get updates from system

---

## 👨‍💼 Admin Use Cases

### UC6: Manage Reports

Actor: Admin
Description: View and update reports

### UC7: Manage Users

Actor: Admin
Description: Ban/unban users

### UC8: Respond to Contact Messages

Actor: Admin
Description: Reply to user inquiries

---

# 🧩 ERD Explanation

## 🔑 Entities Overview

### Users

* Stores all registered users
* Includes authentication credentials

### Reports

* Core entity of system
* Stores issue details

### Attachments

* Stores uploaded images

### Notifications

* Stores alerts sent to users

### Status Updates

* Tracks report lifecycle

---

# 🔗 Relationships

* One User → Many Reports
* One Report → Many Attachments
* One Report → Many Status Updates
* One User → Many Notifications

---

# ⚙️ Input Validation Rules

## User Input Validation

* Email must match:

```regex
^[a-zA-Z0-9._%+-]+@(utem\.edu\.my|student\.utem\.edu\.my)$
```

* Password:

  * Minimum 8 characters
  * Must include uppercase, lowercase, number

## Report Validation

* Title: Required, max 255 chars
* Description: Required
* Image:

  * Max size: 2MB
  * Allowed: JPG, PNG

---

# ⚡ Performance Optimization

* Use of indexed database queries
* Optimized SQL joins
* Lazy loading images
* Reduced HTTP requests
* Minified CSS/JS

---

# 🔐 Advanced Security Practices

* Session timeout control
* Regenerate session ID after login
* XSS protection using:

```php
htmlspecialchars()
```

* Input sanitization
* Secure headers (future enhancement)

---

# 🧪 System Testing Strategy

## 1. Unit Testing

* Individual PHP functions

## 2. Integration Testing

* Database + backend interaction

## 3. System Testing

* Full workflow validation

## 4. User Acceptance Testing (UAT)

* Real users testing system usability

---

# 🎨 UI/UX Design Principles

* Minimalist design
* Clear navigation
* Responsive layout
* Accessibility-friendly colors
* Consistent component styling

---

# 📈 Future Scalability Plan

* Convert to MVC framework (Laravel)
* REST API integration
* Cloud deployment (AWS)
* Mobile app integration

---

# 🤖 AI Integration (Future)

* Auto-priority classification
* Issue detection from images
* Smart recommendation system

---

# 📊 Data Analytics Potential

System data can be used to:

* Identify high-risk areas
* Predict maintenance needs
* Improve campus planning

---

# 🧾 Logging & Audit Strategy

Every action logged:

* Report updates
* Admin actions
* User activities

Benefits:

* Transparency
* Accountability
* Debugging support

---

# 🧭 Navigation Flow

User Journey:

```text
Home → Login → Dashboard → Submit Report → Track → Notification
```

Admin Journey:

```text
Login → Dashboard → Reports → Update → Resolve
```

---

# 🧱 Maintainability Strategy

* Modular file structure
* Reusable components
* Clean coding standards
* Commented code

---

# 🌐 Deployment Considerations

* Apache server configuration
* Database backup strategy
* Error logging enabled
* Production mode security

---

# 📌 Limitations

* No mobile app yet
* Manual admin assignment
* No real-time push notification
* Limited analytics dashboard

---

# 🏁 Conclusion (Extended)

U-SIIRS is a practical, scalable, and efficient system designed to modernize infrastructure issue reporting at UTeM. It enhances transparency, improves response efficiency, and ensures a structured communication channel between users and administrators.

The system not only solves current reporting inefficiencies but also lays the foundation for future smart campus solutions.

---

## 🔚 End of Extended Documentation
# 🔧 Additional Technical Implementation Details

# 🧩 Backend Logic (Core PHP Examples)

## 🔐 Authentication (Login Example)
```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    header("Location: dashboard.php");
} else {
    $error = "Invalid credentials";
}