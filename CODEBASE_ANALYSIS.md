# PA-ETOS Codebase Analysis

**Audited:** August 2026
**Scope:** Entire `paetos/` repository (corporate website + student hostel accommodation portal)
**Type:** Static code review (no runtime execution)

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Repository Structure](#2-repository-structure)
3. [Part A: Corporate Marketing Website](#3-part-a-corporate-marketing-website)
4. [Part B: Hostel Accommodation Portal](#4-part-b-hostel-accommodation-portal)
5. [Data Model](#5-data-model)
6. [User Flows](#6-user-flows)
7. [Backend API Surface](#7-backend-api-surface)
8. [Security Analysis](#8-security-analysis)
9. [Code Quality & Bugs](#9-code-quality--bugs)
10. [Deployment & Runtime Findings](#10-deployment--runtime-findings)
11. [Recommendations](#11-recommendations)
12. [Appendix: File Inventory](#12-appendix-file-inventory)

---

## 1. Executive Summary

The repository contains two applications bundled together:

1. **A static corporate marketing site** (`index.html`, `gallery.html`, `paetoshostel.html` + `img/`) for **PA-ETOS LIMITED**, a construction and engineering firm in Abuja, Nigeria (est. 1991). This is a presentational brochure site built with Tailwind CSS (CDN) and vanilla JS. It contains no server-side logic.

2. **A PHP + MySQL hostel accommodation portal** (`portal/`) used to manage student room bookings at the **PA-ETOS Hostel, Veritas University Abuja**. This is the substantive application: student registration/login, payment-receipt upload, admin payment confirmation, room/bunk assignment, CSV bulk import, and a printable room-allocation slip.

**Overall assessment:** The marketing site is fine for its purpose. The portal is functional but built as a rapid prototype on top of a purchased Bootstrap admin template (DexignLab "Jobick"). It works end-to-end for the core happy path, but it has **critical security vulnerabilities** (unauthenticated admin registration, unauthenticated password reset to a known default, several unauthenticated read/write endpoints, widespread stored XSS), **dead code, inconsistent naming, and schema drift** between the code and the shipped SQL dump.

**Critical issues in short:**
- Anyone can register as a full admin (`admin_register.php`, hardcoded `role='admin'`).
- Anyone can reset any account's password to the hardcoded value `welcome` with only an email (`php/reset.php`).
- Database credentials are hardcoded in `php/config.php` and a copy of the DB seed (with real BLOB passport photos and payment receipts) is committed in `paetos.sql`.
- Several admin/AJAX endpoints have **no authentication** (`assign_room.php`, `update_user.php`, `fetch_user_d.php`, `upload_csv.php`, `update_student_room.php`, `revoke_room.php`, `fetch_assigned_room.php`, `fetch_user.php`, and all `fetch_*`/room-management scripts).
- Student-supplied data is echoed into `innerHTML` unescaped in at least 8 pages (stored/reflected XSS).

The positive side: prepared statements (MySQLi) are used consistently — no classic concatenated SQL injection was found — and passwords are hashed with `password_hash()`.

---

## 2. Repository Structure

```
paetos/
├── index.html                  # Corporate home page (Tailwind, one-page marketing site)
├── gallery.html                # Photo gallery of past projects
├── paetoshostel.html           # Promo page for PA-ETOS Hostel @ Veritas University
├── img/                        # Marketing-site imagery (projects, logo, ETOS/, gallery/)
├── cgi-bin/                    # Empty, unused
└── portal/                     # Hostel accommodation application (PHP + MySQL)
    ├── .git/                   # Separate git repo (origin: github.com/lifeofrence/paetos-main-master)
    ├── .htaccess               # display_errors On + broken rewrite rules
    ├── index.html              # Student login (alias of login.html)
    ├── login.html              # Student login + public "Check Room Allocation"
    ├── register.html           # Student self-registration
    ├── admin_login.html        # Admin login
    ├── admin_register.html     # Admin self-registration (!! public)
    ├── dashboard.php           # Student dashboard (payment-status-aware buttons)
    ├── book-hostel.php         # Booking + payment receipt upload
    ├── check-room.php          # View allocation + print slip
    ├── profile.php             # Student profile (read-only)
    ├── edit-profile.php        # Profile editing + passport upload
    ├── forgot-password.php     # Self-service password reset (secret Q&A)
    ├── password-reset.php      # Admin password-reset UI (!! no auth gate)
    ├── admin-dashboard.php     # Admin dashboard (stat cards)
    ├── admin-sidebar.php       # Admin layout shell (headers/sidebar)
    ├── admin-navbar.php        # Legacy admin navbar (malformed)
    ├── manage-hostel.php       # Add/edit rooms & room categories
    ├── confirm-payments.php    # Approve/reject payments + assign room
    ├── assigned_room.php       # Assigned-rooms table + CSV upload/export (!! no auth)
    ├── list-student.php        # Read-only student list
    ├── edit-student.php        # Inline student editing
    ├── admin_payments.php      # Legacy payments page (dead code — checks $_SESSION['admin_id'])
    ├── fetch_rooms.php         # JSON: rooms by category
    ├── fetch_room_categories.php# JSON: room categories
    ├── assign_room.php         # JSON: write room assignment to `users` (!! no auth)
    ├── navbar.php / footer.php # Layout fragments
    ├── paetos.sql              # phpMyAdmin dump (12 tables + live seed data)
    ├── sample_template.csv     # CSV import template
    ├── error_log / debug.log   # Production logs (committed — leaks paths)
    ├── php/                    # ~40 backend scripts (see §7)
    ├── css/  js/  images/      # Template assets
    ├── vendor/                 # ~30 bundled JS/CSS libraries (jQuery, Chartist, DataTables, etc.)
    ├── ajax/                   # Template demo fragments (commented-out HTML)
    └── Edge/ OPR/ Trident/ sidebar-img/
                              # Template browser-demo copies (duplicates)
```

---

## 3. Part A: Corporate Marketing Website

### 3.1 Purpose
A public-facing brochure site. Its job is purely informational: present the company's services, projects, history, team, and contact details.

### 3.2 Pages
| File | Content |
|------|---------|
| `index.html` (1187 lines) | Sticky nav, 3-slide hero carousel (5s auto-advance, arrows + dots), About section (mission, history timeline 1991→present), Core Values grid (6 cards), Services (Electrical / Civil / Building engineering) with a "Learn More" modal, 8 specialization tiles, two "Projects" sections (one with generic text, one with real project photos such as "DUALIZATION OF TINAPA ODUKPANI JUNCTION 2", "IYAMHO POLICE BARRACK"), Management Team (6 executives), Contact section with embedded Google Map iframe + a contact form, and a footer with social links + back-to-top button. |
| `gallery.html` (416 lines) | Header + responsive photo grid (20+ images from `img/gallery/`). |
| `paetoshostel.html` (482 lines) | Hero banner, "Modern Student Accommodation" section, 6 hostel feature cards, project gallery grid (images from `img/ETOS/`), CTA linking to the portal. |

### 3.3 Implementation notes
- **Tailwind CSS v3 via CDN** (`cdn.tailwindcss.com`) with a custom `tailwind.config` defining brand colors (`brand-red #B22222`, `brand-gold #D4AF37`). Using the CDN build means the runtime compiler ships to every visitor and classes are generated client-side — fine for a brochure, but not ideal for production performance.
- **Font Awesome 6.4.0** via CDN; **Google Fonts** (Poppins) imported in `<style>`.
- **Vanilla JS** for: mobile menu toggle, smooth scrolling with 80px offset, scroll-spy nav highlighting, hero slider, back-to-top.
- Duplicate `id="about"` on two `<section>`s in `index.html` (invalid but harmless for anchors).
- The contact form (`index.html` ~line 900) has **no action handler** — it never sends anything. "Send Message" does nothing.
- `gallery.html` has a duplicated `<!DOCTYPE html>` (line 1–2) and no JS for the gallery (static grid only).
- `index.html` references some images that may not exist (`img/carousel-1.png`, `img/carousel-2.png` do exist; several project images are referenced with inline paths that match files, e.g. `img/road.jpg`). Some referenced assets (e.g., `img/carousel-3.jpg`) are present. No broken-image check was performed at runtime.

### 3.4 SEO / meta
- Good: canonical links, description, keywords, favicon, title on all pages.
- Minor: `paetoshostel.html` and `gallery.html` share the **same canonical URL** (`https://www.pa-etos.com/gallery`) — this can confuse search engines.
- Copyright footer says 2025 (stale).

---

## 4. Part B: Hostel Accommodation Portal

### 4.1 Purpose
A private system for Veritas University students to register, book a hostel room, upload proof of payment, and (once an admin approves) see their allocated room/bunk and print an official allocation slip. Admins manage rooms, categories, students, payments, and CSV imports.

### 4.2 Tech stack
- **Backend:** PHP 8.x (MySQLi, prepared statements), session-based auth, no framework, no Composer, no PDO (one file uses `$pdo` that is never defined — see §9).
- **Database:** MySQL/MariaDB (dump generated on MariaDB 10.4.24 / PHP 8.1.6).
- **Frontend:** Bootstrap 4.6 + jQuery (bundled in `vendor/global/global.min.js`), DataTables, SweetAlert, Select2, etc. — all from the **DexignLab "Jobick" admin template**. Heavy reliance on `innerHTML` and `fetch()`/`$.ajax` to JSON endpoints.
- **Deployment:** cPanel shared hosting (`/home/doncassa/public_html/paetosltd.ng/portal/`), domain **paetosltd.ng** (per `error_log` and `.htaccess`).

### 4.3 Architecture pattern
Classic multi-page PHP app with "ajax-style" JSON endpoints:

```
Browser (jQuery/fetch)
        │
        ├─ Form POST → PHP page handler (login.php, register.php, reset.php …)
        │
        └─ fetch/$.ajax → JSON endpoint (fetch_*.php, add_room.php, upload_csv.php …)
```

Layout is assembled by **including layout fragments inside pages** (`admin-sidebar.php`, `footer.php`). The fragment files themselves output a full `<html><head><body>` document, so any page that includes more than one fragment emits multiple nested document skeletons. This is the single biggest maintainability smell and is the direct cause of the "headers already sent" errors in the logs (see §10).

### 4.4 The "two dashboards" split
- **Student pages** check `$_SESSION['user_id']` + `$_SESSION['timeout']` (`dashboard.php`, `book-hostel.php`, `check-room.php`, `profile.php`, `edit-profile.php`).
- **Admin pages** check `$_SESSION['user_id']` + `$_SESSION['role'] === 'admin'` (`admin-dashboard.php` does **not**, but `admin-sidebar.php`/`admin-navbar.php` don't gate either — only `confirm-payments.php`, `manage-hostel.php`, `list-student.php`, `edit-student.php` self-gate).
- `assigned_room.php` and `password-reset.php` have **no auth gate at all**.

### 4.5 Noteworthy implementation details
- Session timeout is a manual rolling window: `$_SESSION['timeout'] = time() + 1800` refreshed on each page load. Session is never regenerated after login (fixation risk).
- "Remember me" writes a cookie containing the **plaintext email** with no `HttpOnly`/`Secure` flags and is **never consumed** anywhere — dead functionality.
- All state-changing writes rely on `POST` without CSRF tokens.
- Payment receipts and passport photos are stored **as raw BLOBs in the database** (not files on disk). `download_payment.php` serves the blob with a generic `application/octet-stream` and a bogus `basename()` filename.
- `assign_room.php` (root) writes room assignments into a `users` table that **does not exist in the shipped schema** (`paetos.sql` has no `users` table) — this endpoint is broken against the shipped DB.

---

## 5. Data Model

Source of truth: `portal/paetos.sql` (phpMyAdmin dump, 447 lines). Note the dump is **not** fully consistent with the running code (missing `assign_room`, `room`, `room_category`, `users`, `secret_question`/`secret_answer` columns — see §9).

### 5.1 Tables (as shipped)
| Table | Purpose | Key columns | Notes |
|-------|---------|-------------|-------|
| `admin` | Admin accounts | id, username, email, password(bcrypt), role (`'admin'`) | 2 seeded accounts: `ada@d.com`, `clinton@gmail.com` (hashes only) |
| `adminlog` | Admin login audit | adminid, ip, logintime | Empty; never written to by the app |
| `userregistration` | Student accounts | id, regNo, firstName…lastName, gender, contactNo, email, password(bcrypt), userImage(BLOB), regDate | 3 seeded students; **no `secret_question`/`secret_answer` columns** yet the reset flow queries them → runtime error |
| `registration` | Legacy/alternative student registration | roomno, seater, feespm, foodstatus, course, guardian*, addresses | Unused by current code |
| `payments` | Payment receipts | id, userId, paymentInfo(BLOB), status, room, bed, bankName, payers_name, uploadDate | Status values used in code: `Pending`, `Approved`, `rejected`, `NULL`, `Confirmed` |
| `rooms` | Legacy room inventory | seater, room_no, fees | Unused by current code (`room` is used instead) |
| `courses` | Static course list | course_code, course_sn, course_fn | Unused |
| `feedback` | Hostel feedback | AccessibilityWarden, RedressalProblem, Room, Mess, OverallRating… | Unused |
| `complaints` / `complainthistory` | Complaint tracking | complaintType, complaintDetails, complaintStatus… | Unused by current UI (sidebar links removed) |
| `states` | Nigerian states | State | 1 row only (incomplete) |
| `userlog` | Student login audit | userId, userEmail, userIp, city, country, loginTime | Unused by the app |
| `password_resets` | (Planned) token-based resets | email, token, expires | Empty; unused — app uses secret Q&A instead |

### 5.2 Tables used by code but missing from the dump
| Table | Referenced by | Why it matters |
|-------|---------------|----------------|
| `assign_room` | `check-room.php`, `assigned_room.php`, `search_room_allocation.php`, `fetch_assigned_room.php`, `upload_csv.php`, `update_student_room.php`, `revoke_room.php` | Central to the whole allocation flow. Not in `paetos.sql`. A helper script (`create_assign_room_table.php`) exists to create it. |
| `room` | `room.php`, `add_room.php`, `update_room.php`, `fetch_rooms.php` | Room inventory. Not in the dump. |
| `room_category` | `room_category.php`, `add_category.php`, `update_room_category.php`, `fetch_room_categories.php` | Room categories/rates. Not in the dump. |
| `users` | `assign_room.php` (root) | Endpoint is dead against the shipped schema. |
| `userregistration.secret_question` / `secret_answer` | `forgot-password.php`, `php/forgot_password_handler.php` | Reset flow breaks if the columns don't exist. |

> **Bottom line:** The shipped `paetos.sql` is a partial snapshot from a different point in development than the PHP code. A fresh install from this file will not run the app correctly.

### 5.3 Seed data exposure
The dump embeds **real student records** (names, emails, matric numbers, phone numbers), **admin hashes**, and **binary BLOBs**: three passport photos and three payment receipts (two JPEGs and one PDF). Anyone who obtains this file gets the full dataset. The file should never live in a public repository.

---

## 6. User Flows

### 6.1 Student flow
```
register.html
   └─ POST php/register.php          (creates userregistration row; stores passport BLOB; password_hash)
login.html / index.html
   └─ POST php/login.php             (password_verify; sets user_id + timeout; redirects to dashboard.php)
dashboard.php                        (button state driven by payment status)
   ├─ "Upload Payment" / "Submit Proof" → book-hostel.php
   │     └─ POST php/payment_info.php   (stores receipt BLOB, status='Pending', bankName, payers_name)
   ├─ "Pending Approved" / "Check Your Room" → check-room.php
   │     └─ queries assign_room by matric_no → prints allocation slip
   ├─ profile.php (read-only) / edit-profile.php (passport upload via php/update_passport.php)
forgot-password.php                   (secret Q&A reset; also php/forgot_password_handler.php)
```

### 6.2 Admin flow
```
admin_register.html ── POST php/admin_register.php   (!!! creates an admin for anyone)
admin_login.html ──── POST php/admin_login.php       (sets user_id + role; redirects admin-dashboard.php)
admin-dashboard.php                                  (stat cards via fetch_*.php)
   ├─ manage-hostel.php       → add/edit rooms + categories (php/add_room.php, update_room.php, …)
   ├─ confirm-payments.php    → approve payment + assign room/bed
   │     └─ fetch_user_d.php?id=…  (loads student + receipt)  →  assign_room.php (root) writes `users`
   ├─ assigned_room.php       → view/edit/CSV-upload allocation records (php/fetch_assigned_room.php,
   │                            php/update_student_room.php, php/upload_csv.php, php/revoke_room.php)
   ├─ list-student.php        → view (php/fetch_user.php)
   ├─ edit-student.php        → inline edit (php/update_user.php)
   └─ password-reset.php      → force-reset any account (php/reset.php)  → password becomes 'welcome'
```

---

## 7. Backend API Surface

All endpoints under `portal/php/` unless noted. **R** = read (GET/POST returning data), **W** = write, 🔓 = **no authentication**.

| Endpoint | Type | Auth | Purpose |
|----------|------|------|---------|
| `config.php` | – | – | DB connection (hardcoded creds) |
| `login.php` | W | public | Student login (JSON) |
| `register.php` | W | public | Student registration (JSON) |
| `logout.php` / `admin_logout.php` | W | session | Destroy session + cookie |
| `admin_login.php` | W | public | Admin login (server-side redirect) |
| `admin_register.php` | W | public 🔓 | **Creates admin accounts for anyone** |
| `reset.php` | W | public 🔓 | **Reset any account password to `welcome`** |
| `forgot-password.php` | W | public | Secret-Q&A reset (leaks DB error) |
| `forgot_password_handler.php` | W | public | Secret-Q&A reset to hardcoded `12345678` |
| `fetch_user_details.php` | R | session | Returns logged-in student row + payments (functions) |
| `fetch_user.php` | R | none 🔓 | All students' PII (JSON) |
| `fetch_user_d.php` | R | none 🔓 | One student's PII + base64 receipt by `id` |
| `fetch_user_count.php` | R | none | COUNT students |
| `fetch_admin_info.php` | R | session | Admin row |
| `fetch_payment.php` | R | none | Payments JOIN students (paginated) |
| `fetch_roomCategory.php` | R | none | COUNT room_category |
| `fetch_roomnumbers.php` | R | none | COUNT rooms |
| `fetch_roomspace.php` | R | none | "available space" (bizarre query, see §9) |
| `fetch_roomassign.php` | R | none | COUNT assign_room with matric |
| `fetch_roompayment.php` | R | none | COUNT assign_room rows (misnamed) |
| `fetch_assigned_room.php` | R | none 🔓 | All allocation records (PII) |
| `fetch_roomCategory.php` / `fetch_roomnumbers.php` / `fetch_roomspace.php` | R | none | Dashboard stats |
| `add_room.php` / `update_room.php` | W | none 🔓 | Create/update room |
| `add_category.php` / `update_room_category.php` | W | none 🔓 | Create/update category |
| `room.php` / `room_category.php` | R | none | JSON lists |
| `upload_payment.php` | W | session | Receipt upload (legacy; sets status='Pending') |
| `payment_info.php` | W | session | Receipt upload + bank/payer (no ownership check 🔓) |
| `process_payment_action.php` | R | none 🔓 | **Uses undefined `$pdo` → fatal error** |
| `confirm_payment.php` | W | `admin_id` (never set) | Legacy: status→'Confirmed', sets room/bed |
| `download_payment.php` | R | none | Streams receipt blob |
| `view_payment.php` | R | none | Streams receipt as PDF (any `id`) |
| `user_payment.php` | R | `userId` (never set) | Broken session key |
| `button_status.php` | R | session | Returns button text/link |
| `update_user.php` | W | none 🔓 | Update any student's profile |
| `update_passport.php` | W | session | Passport BLOB update (no validation) |
| `upload_csv.php` | W | none 🔓 | Bulk import/update allocation records |
| `update_student_room.php` | W | none 🔓 | Edit allocation record |
| `revoke_room.php` | W | none 🔓 | Delete allocation record |
| `search_room_allocation.php` | R | public | Public room-lookup (matric/phone/parent) |
| `create_assign_room_table.php` | W | none 🔓 | Creates `assign_room` table |
| `insert_sample_data.php` | W | none 🔓 | Seeds sample allocation records |
| `sidebar.php` / `mis.php` / `all.php` / `functions.php` | – | – | Prototype/library leftovers (self-executing, placeholder creds) |
| `test_debug.php`, `test_json.php`, `test_log.php`, `test_output.php`, `test_search_*.php` | R | none | Debug scripts committed to production |
| `admin_login.php` etc. | – | – | See above |

Root-level endpoints: `fetch_rooms.php` (🔓, unguarded `$_GET['category_id']`), `fetch_room_categories.php` (🔓), `assign_room.php` (🔓, writes to non-existent `users` table).

---

## 8. Security Analysis

### 8.1 Critical
| # | Issue | Location | Impact |
|---|-------|----------|--------|
| C1 | **Public admin self-registration** | `php/admin_register.php:17` (`$role = 'admin'`), linked from `admin_login.html:64` | Anyone can create an admin account and take over the system |
| C2 | **Unauthenticated password reset to known default** | `php/reset.php:17,39,66` (`$defaultPassword = 'welcome'`); exposed via `password-reset.php` (no auth gate) | Full account takeover for every admin & student with only an email |
| C3 | **Hardcoded DB credentials committed** | `php/config.php:6-13` (`doncassa_pat` / `!JD-E17mJ%;9b!^{`); plus production `error_log` showing `root`/`root` | Database compromise if repo/backup leaks; creds also present in the analyzed snapshot |
| C4 | **Live seed data + BLOBs in the repo** | `paetos.sql` | Student PII, passports, payment receipts committed |
| C5 | **Unauthenticated write endpoints** | `assign_room.php` (root), `update_user.php`, `update_student_room.php`, `revoke_room.php`, `upload_csv.php`, `add_room.php`, `update_room.php`, `add_category.php`, `update_room_category.php` | Arbitrary data modification/insertion/deletion by unauthenticated callers |
| C6 | **Unauthenticated PII read endpoints** | `fetch_user.php`, `fetch_user_d.php` (id → PII + receipt), `fetch_assigned_room.php` | Complete student directory + payment receipts exposed |
| C7 | **No auth gate on `assigned_room.php` / `password-reset.php` pages** | Page level | Admin-only functionality reachable anonymously |

### 8.2 High
| # | Issue | Location |
|---|-------|----------|
| H1 | **Stored/reflected XSS via `innerHTML`** on student-supplied fields (names, bank, payer, department, matric) | `confirm-payments.php:312-344`, `assigned_room.php:247-268`, `manage-hostel.php:464-480`, `list-student.php:362-377`, `edit-student.php:373-379`, `book-hostel.php:98,341`, `profile.php:325-347`, `edit-profile.php:413-471`, `dashboard.php:135,164-166,205-210` |
| H2 | **File-upload weaknesses** — MIME sniffing only (`payment_info.php:30-34`), no server-side validation at all (`update_passport.php`, `register.php` BLOB) | Rogue file types, no size limits, potential malware carriage |
| H3 | **Verbose errors to visitors** | `.htaccess:4` `php_flag display_errors On`; `forgot-password.php:60` appends `$e->getMessage()`; DB errors echoed in `upload_csv.php:76,91`, `register.php:59`, `add_room.php:22`, `update_room.php:21` |
| H4 | **IDOR on payment upload** — `payment_info.php` reads `$_POST['id']` and attaches receipt to any `userId` | A student can attach (or replay) receipts on other accounts |
| H5 | **Secret Q&A stored in plaintext** + two alternative resets with hardcoded passwords (`reset.php` → `welcome`, `forgot_password_handler.php` → `12345678`) | Enables brute-force/guessing of secret answers; second reset path ignores the user's new password field entirely |

### 8.3 Medium
| # | Issue | Location |
|---|-------|----------|
| M1 | No CSRF tokens on any state-changing request | all POST handlers |
| M2 | No `session_regenerate_id()` after login; session cookies lack `HttpOnly`/`Secure` flags | `login.php`, `admin_login.php` |
| M3 | "Remember me" stores plaintext email, never consumed | `login.php:41`, `admin_login.php:37` |
| M4 | CORS wide-open on room search | `search_room_allocation.php:3` (`Access-Control-Allow-Origin: *`) |
| M5 | Debug/test scripts shipped to production | `test_*.php`, `insert_sample_data.php`, `create_assign_room_table.php`, `fetch_rooms.php` unguarded param |
| M6 | CSV injection risk in client-side CSV export | `assigned_room.php:400-538` (no `=`/`+`/`-`/`@` escaping) |
| M7 | Admin/student session namespaces overlap (both use `user_id`); role not enforced on many pages | throughout |

### 8.4 What is done correctly
- **Prepared statements** (MySQLi `bind_param`) are used for essentially every query — no classic SQLi found.
- Passwords hashed with `password_hash()` / `PASSWORD_BCRYPT` at registration and verified with `password_verify()`.
- The public secret-Q&A reset (`forgot-password.php`) is properly structured (validation, prepared queries, hashed update) — it just needs rate-limiting and output-escaping fixes.
- Logout scripts clear sessions and cookies correctly.
- `admin-sidebar.php` escapes `$_SESSION` output with `htmlspecialchars()` (line 147-148).

---

## 9. Code Quality & Bugs

### 9.1 Schema/code drift (highest-impact maintainability issue)
- Code queries `assign_room`, `room`, `room_category`, `users`, and `userregistration.secret_question/secret_answer` — none of these are in the shipped `paetos.sql`.
- `assign_room.php` (root) writes to `users` — a table that does not exist anywhere in the codebase schema.
- Two different room-capacity columns are used inconsistently: `add_room.php` inserts into `available_space`, while `update_room.php` writes `full_capacity` and `available_space`. `fetch_roomspace.php` sums `room.full_capacity` but `fetch_rooms.php` selects `available_space` as capacity.

### 9.2 Broken / dead code
| Item | File |
|------|------|
| `$pdo` used but never defined (fatal) | `process_payment_action.php:16-17` |
| `submitPaymentInfo()` referenced inline but never defined | `book-hostel.php:363` |
| Reject button has no handler | `confirm-payments.php:255` |
| `.revoke-btn` handler for buttons never rendered | `assigned_room.php:285-308` |
| `admin_payments.php` always redirects (checks never-set `$_SESSION['admin_id']`) | `admin_payments.php:6` |
| `user_payment.php` checks never-set `$_SESSION['userId']` | `user_payment.php:11` |
| `downloadCSVTemplate()` exports real data, not a template | `assigned_room.php:436` |
| Passport input has two overlapping change listeners; the `.update-flie` block targets selectors that don't exist on the page | `edit-profile.php:341 & 361`, `528-608` |
| Edit-profile form has no submit handler — text fields can't be saved | `edit-profile.php:408-481` |
| `functions.php` / `all.php` contain self-executing demo code with placeholder creds — including them executes against the DB | `functions.php:23`, `all.php` |
| Duplicate `<!DOCTYPE html>` | `gallery.html:1-2` |
| Duplicate `id="about"` sections | `index.html` |
| `fetch_roomspace.php` counts `assign_room WHERE level IS NULL` as "available space" | `fetch_roomspace.php:13-21` |
| `fetch_roompayment.php` counts all `assign_room` rows as "payments" | `fetch_roompayment.php:6` |
| Dashboard shows hardcoded `775` "Total Bed Spaces" | `admin-dashboard.php:67` |
| `navbar.php`/`admin-sidebar.php` emit full `<html><head><body>` inside included fragments → nested document skeletons | layout includes |
| Blank line/BOM before `<?php` in some pages causes "headers already sent" | see §10 |

### 9.3 Data-quality bugs
- `register.html:69-71` — both gender options have `value="Female"` (male students stored as Female).
- `register.html:119` — malformed option value (`What was the name of your first pet>`, missing quote).
- `forgot_password_handler.php` — after reset it returns "Password reset successfully" but the reset password is the hardcoded `12345678`, and it `echo json_encode($data)` before processing (line 9) — JSON output before JSON response.
- `index.html` contact form and social links are non-functional.

### 9.4 Style / consistency
- PHP uses MySQLi everywhere except one stray PDO reference.
- Mixed conventions: `snake_case`, `camelCase`, and `$camelCase` SQL column names (`userId` vs `user_id`, `uploadDate` vs `upload_date`).
- Mixed status strings: `'Pending'`, `'Approved'`, `'rejected'`, `'Confirmed'`, and `NULL` are all handled across different files (`dashboard.php:30-48`, `button_status.php:33-47` use different case/value sets).
- Two "forgot password" flows (`forgot-password.php` vs `php/forgot_password_handler.php`).
- No dependency manifest — all vendors are committed binaries with no version pinning or license inventory.
- Committed runtime artifacts: `error_log`, `debug.log`, `php_debug.log` (referenced), template demo copies (`Edge/`, `OPR/`, `Trident/`).

---

## 10. Deployment & Runtime Findings

Evidence from committed logs (`portal/error_log`, `portal/php/error_log`, `portal/debug.log`):

1. **Production path:** `/home/doncassa/public_html/paetosltd.ng/portal/` on cPanel + PHP 8.1 (`ea-php81`), MySQL on port 8889.
2. **DB connection was failing in production:** repeated `mysqli_sql_exception: Access denied for user 'root'@'localhost'` — the deployed `config.php` at that time used `root`/`root` (the repo now contains `doncassa_pat` with a different password). Either way, credentials are in the repo and port 8889 is MAMP-specific; shared-hosting MySQL normally runs on 3306.
3. **"headers already sent" cascade:** triggered because some pages (e.g., `assigned_room.php`) emit output before `session_start()` in included fragments — e.g. `Cannot modify header information - headers already sent by (output started at .../assigned_room.php:1)`.
4. **Include-path breakage:** `include(/admin-sidebar.php)` and `include(../admin-sidebar.php)` failures show the codebase has been moved between directories and absolute-ish includes are fragile.
5. **`.htaccess` misconfiguration:** `RewriteRule . index.php [L]` rewrites everything to a file that doesn't exist (`index.php`), so unknown paths 500. `RewriteBase /paetosltd.ng/portal/` hardcodes the deployment folder.

---

## 11. Recommendations

Prioritized roadmap. **P0 = fix immediately (security), P1 = next sprint, P2 = longer-term.**

### P0 — Critical security fixes
1. **Remove public admin registration.** Delete `admin_register.html`/`admin_register.php`, or gate it behind an invite token / existing admin session. Seed the admin account from SQL only.
2. **Delete or authenticate `php/reset.php` and `password-reset.php`.** Never use hardcoded default passwords; require an authenticated admin session **and** verification (token/email link). Rotate the passwords of all existing admin/student accounts afterwards, since `welcome`/`12345678` may already be in use.
3. **Move DB credentials out of the repo.** Use environment variables / a gitignored `config.local.php`; use a least-privilege DB user; use port 3306 for shared hosting. **Rotate the exposed MySQL password immediately** and remove `error_log`/`debug.log`/`paetos.sql` from version control.
4. **Add authentication to every backend endpoint.** Build one small shared guard (e.g., `require_admin()` / `require_login()` functions in `functions.php`) and call it at the top of all `fetch_*`, `add_*`, `update_*`, `delete_*`, `upload_*` scripts. Apply it to the `assigned_room.php` and `password-reset.php` pages.
5. **Add ownership checks** — `payment_info.php` must use `$_SESSION['user_id']`, never a posted `id`; `update_user.php` should only allow admins (or a user editing their own row).
6. **Escape all output.** Replace `innerHTML` rendering of user data with `textContent`/`text()`, or add a shared `esc()` helper on the PHP side and always escape echoed values with `htmlspecialchars()`.

### P1 — Correctness and hardening
7. **Reconcile schema and code.** Generate one authoritative `schema.sql` containing `admin`, `userregistration` (+ `secret_question`, `secret_answer`), `payments`, `assign_room`, `room`, `room_category`, and drop the unused legacy tables. Align `users`-vs-`assign_room` in the assignment flow.
8. **Harden file uploads:** server-side MIME sniffing (`finfo_file`), size limits, re-encode images, random filenames if stored on disk, and validate PDF magic bytes. Reject anything that is not an image/PDF regardless of client `type`.
9. **Disable `display_errors` in production** (`.htaccess`), sanitize `$e->getMessage()` before echoing, and remove the `RewriteRule . index.php` line.
10. **Password reset rework:** use `password_resets` token table with expiry + emailed links (or OTP), rate-limit attempts, and stop storing/echoing secret answers in plaintext. Make `forgot_password_handler.php` consistent with `forgot-password.php`.
11. **Session hygiene:** `session_regenerate_id(true)` on login, set cookie flags (`HttpOnly`, `Secure`, `SameSite`), separate `admin`/`student` session keys or use an explicit role check everywhere, and either implement Remember Me properly (random token in `password_resets`-style table) or remove it.
12. **Add CSRF tokens** to all POST forms and a small middleware check.
13. **Fix the XSS-adjacent data bugs:** gender dropdown, malformed option, dead Reject/revoke handlers, `fetch_roomspace.php` query, hardcoded dashboard stat.

### P2 — Maintainability
14. **Standardize a layout approach.** Convert `admin-sidebar.php` to output only fragments (no `<html>/<head>`), or switch to a single `template.php` with `<?= $content ?>`. This removes the nested-document and headers-already-sent class of bugs.
15. **Centralize helpers:** one `db.php` connection + `esc()`, `require_login()`, `require_admin()`, `json_response()`, `redirect()`; delete `all.php`, `mis.php`, `functions.php` self-executing code and `test_*.php`.
16. **Status enum alignment:** use one canonical status set (e.g., `pending` → `approved` → `rejected`) and update `dashboard.php`/`button_status.php`/admin pages to share it.
17. **Frontend cleanup:** pin Tailwind via a build step (or a downloaded CSS file) instead of the CDN JIT compiler; remove the unused template vendors (Edge/OPR/Trident/sidebar-img, ajax fragments, most of `vendor/`); add a proper gallery lightbox.
18. **Monitoring & logging:** move logs outside the web root, add basic structured logging, and add a `.gitignore` for `*.log`, `paetos.sql`, `config.local.php`, and temp files.
19. **Regression tests:** at minimum, scripted smoke tests for login, registration, payment upload, admin approve+assign, and room lookup; plus a `php -l` lint pass in CI.
20. **Document the deployment:** README with setup (import schema, create `.env`, base URL), and update the canonical URL / copyright year on the marketing pages.

---

## 12. Appendix: File Inventory

### Marketing site (root)
| File | Size (approx) | Status |
|------|---------------|--------|
| `index.html` | 1187 lines | Working; minor issues (duplicate #about, non-functional contact form) |
| `gallery.html` | 416 lines | Working; duplicate `<!DOCTYPE>` |
| `paetoshostel.html` | 482 lines | Working; CTA → `https://paetosltd.ng/portal/` |
| `img/` (~40 files) | – | Project photos, logos, gallery images |
| `cgi-bin/` | empty | Unused |

### Portal — PHP backend (`portal/php/`, 40 files)
Auth & session: `config.php`, `login.php`, `register.php`, `logout.php`, `admin_login.php`, `admin_register.php`, `admin_logout.php`, `reset.php`, `forgot-password.php`, `forgot_password_handler.php`, `button_status.php`
Fetch/JSON (read): `fetch_user.php`, `fetch_user_d.php`, `fetch_user_details.php`, `fetch_user_count.php`, `fetch_admin_info.php`, `fetch_payment.php`, `fetch_assigned_room.php`, `fetch_roomCategory.php`, `fetch_roomnumbers.php`, `fetch_roomspace.php`, `fetch_roomassign.php`, `fetch_roompayment.php`, `room.php`, `room_category.php`, `user_payment.php`, `view_payment.php`, `download_payment.php`
Write: `add_room.php`, `update_room.php`, `add_category.php`, `update_room_category.php`, `upload_payment.php`, `payment_info.php`, `confirm_payment.php`, `process_payment_action.php`, `update_user.php`, `update_passport.php`, `upload_csv.php`, `update_student_room.php`, `revoke_room.php`, `search_room_allocation.php`, `create_assign_room_table.php`, `insert_sample_data.php`
Leftovers/debug: `all.php`, `mis.php`, `functions.php`, `sidebar.php`, `test_debug.php`, `test_json.php`, `test_log.php`, `test_output.php`, `test_search_debug.php`, `test_search_fixed.php`, `test_search_specific.php`, `error_log`

### Portal — pages (root of `portal/`)
Student: `index.html`, `login.html`, `register.html`, `dashboard.php`, `book-hostel.php`, `check-room.php`, `profile.php`, `edit-profile.php`, `forgot-password.php`
Admin: `admin_login.html`, `admin_register.html`, `admin-dashboard.php`, `manage-hostel.php`, `confirm-payments.php`, `assigned_room.php`, `list-student.php`, `edit-student.php`, `password-reset.php`, `admin_payments.php`
Shared: `admin-sidebar.php`, `admin-navbar.php`, `navbar.php`, `footer.php`
JSON endpoints: `fetch_rooms.php`, `fetch_room_categories.php`, `assign_room.php`
Assets: `paetos.sql` (447 lines), `sample_template.csv`, `.htaccess`, `debug.log`, `error_log`, `css/`, `js/`, `images/`, `vendor/` (~30 libs), `ajax/`, `Edge/`, `OPR/`, `Trident/`, `sidebar-img/`

---

*Generated by automated static analysis of the repository at `paetos/`. Line numbers refer to the files as of August 2026.*
