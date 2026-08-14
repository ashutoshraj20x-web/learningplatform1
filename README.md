# LearnHub — Full-Stack PHP & MySQL Backend Setup Guide

Welcome to the **LearnHub** backend documentation! This project transforms the static educational frontend into a fully dynamic platform powered by **PHP 8.x**, a **MySQL Database**, REST API endpoints, and a comprehensive **Admin Management Panel**.

---

## 🌟 Key Features

1. **Frontend Preservation**: 100% of your existing styling, Tailwind CSS, animations, video progress tracking, and UI layouts are preserved and now fetch dynamic data from PHP APIs.
2. **Admin Control Portal (`admin/`)**:
   - 🔒 **Secure Authentication**: Password hashing (Bcrypt), session management, and CSRF protection.
   - ▶ **Unit-wise Video Lectures**: Add, edit, reorder, or delete YouTube video lectures under any subject's unit.
   - ▤ **Unit-wise Notes**: Upload or replace `.pdf` notes for each unit with automatic link generation.
   - ⌘ **Practicals & Labs**: Manage experiment source codes (Java, C, DSA) and upload experiment PDFs for Digital Electronics Lab.
   - ? **PYQs (Previous Year Questions)**: Upload and manage subject-wise and year-wise (2021–2026) exam PDFs.
   - ✓ **Test Series**: Create subject mock tests with MCQs, 4 options, correct answer keys, and detailed explanations.
   - &lt;/&gt; **Coding Contests**: Manage programming quizzes in Java, C, Python, C++, and SQL with code snippets.
   - ⚙ **Settings**: Change admin password and profile details.
3. **Interactive Testing Engine**: Students can take live MCQs with timers, receive instant scoring, and review step-by-step solutions.

---

## 🚀 Step-by-Step Local Setup Guide (Windows with XAMPP)

### Step 1: Open XAMPP Control Panel
1. Open **XAMPP Control Panel** on your Windows PC.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.

---

### Step 2: Copy Project to `htdocs`
Copy the `learnhub` folder into your XAMPP `htdocs` directory:
- Source: `C:\Users\Ashutosh Raj\.gemini\antigravity\scratch\learnhub`
- Destination: `C:\xampp\htdocs\learnhub`

*(Alternatively, you can create a shortcut or run directly using the PHP CLI server as explained in Method 2 below).*

---

### Step 3: Import the MySQL Database
1. Open your browser and go to: **[http://localhost/phpmyadmin/](http://localhost/phpmyadmin/)**
2. Click on **New** in the left sidebar to create a database.
3. Name the database **`learnhub`** (collation: `utf8mb4_unicode_ci` or default) and click **Create**.
4. With `learnhub` selected, click on the **Import** tab in the top menu.
5. Click **Choose File** and select:
   `C:\xampp\htdocs\learnhub\database\schema.sql` (or from your project folder).
6. Scroll down and click **Import** (or **Go**).
7. *Success!* All tables and seed data (subjects, units, sample lectures, practicals, pyqs, tests, and contests) are now imported!

---

### Step 4: Access the Website & Admin Panel

- **🌐 Student Website**: Open **[http://localhost/learnhub/](http://localhost/learnhub/)**
- **🔒 Admin Management Portal**: Open **[http://localhost/learnhub/admin/login.php](http://localhost/learnhub/admin/login.php)**

#### Default Admin Credentials:
| Field | Default Value |
|---|---|
| **Username** | `admin` |
| **Password** | `admin123` |

*(You can change the username and password anytime inside Admin Panel $\rightarrow$ Settings).*

---

## ⚡ Method 2: Running via PHP Built-in Server

If you prefer running from the command line:
1. Ensure MySQL is running in XAMPP.
2. Open PowerShell or Command Prompt in the `learnhub` folder:
   ```powershell
   cd "C:\Users\Ashutosh Raj\.gemini\antigravity\scratch\learnhub"
   & "C:\xampp\php\php.exe" -S localhost:8000
   ```
3. Open **[http://localhost:8000/](http://localhost:8000/)** in your browser.

---

## 📂 Project Directory Structure

```
learnhub/
├── config/
│   └── db.php                  # Database connection (PDO) & global utilities
├── database/
│   └── schema.sql              # Complete MySQL database schema + preloaded seed data
├── uploads/                    # Storage for admin-uploaded PDFs
│   ├── notes/                  # Unit-wise Notes PDFs
│   ├── practicals/             # DE Lab experiment PDFs
│   └── pyqs/                   # Previous Year Question PDFs
├── api/                        # REST API layer consumed by frontend
│   ├── get_subjects.php        # Returns subjects, units & video lectures
│   ├── get_notes.php           # Returns unit-wise PDF notes
│   ├── get_practicals.php      # Returns practical codes & PDFs
│   ├── get_pyqs.php            # Returns subject & year-wise PYQs
│   ├── get_tests.php           # Returns test series & question bank
│   ├── get_contests.php        # Returns coding contests & question bank
│   └── submit_quiz.php         # Calculates test/contest score & explanations
├── admin/                      # Admin Panel
│   ├── auth_check.php          # Session verification & admin UI layout
│   ├── login.php               # Admin login page
│   ├── logout.php              # Logout handler
│   ├── index.php               # Admin dashboard overview with counters
│   ├── subjects_lectures.php   # Unit-wise video lectures management
│   ├── notes.php               # Unit-wise PDF notes management (upload/replace)
│   ├── practicals.php          # Lab experiments code & DE PDF management
│   ├── pyqs.php                # PYQ PDF uploads management
│   ├── test_series.php         # Subject-wise MCQ Test Series Question Bank
│   ├── coding_contest.php      # Language-wise Coding Contest Question Bank
│   └── settings.php            # Profile & password settings
├── index.php                   # Main student frontend (fully dynamic + interactive tests)
└── README.md                   # Setup guide and documentation
```

---

## 🛡️ Database Connection Settings (`config/db.php`)

If your MySQL has a different username or password, you can easily adjust them in `config/db.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'learnhub');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default for XAMPP is empty
```

---

Developed for **LearnHub — RRSDCE BEGUSARAI** (DSE 3rd Sem).
Developed by **Ashutosh Raj**.
