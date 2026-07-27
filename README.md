# Transparency_ke — Reviewer-focused README

Purpose
-------
This repository implements a Government of Kenya portal (frontend HTML/CSS/JS + PHP backend + MariaDB schema). This README is written for an automated reviewer (ChartGPT / ChatGPT) or a human reviewer to quickly understand architecture, how to run the project locally, and which areas need careful security and correctness checks.

Quick summary
-------------
- Audience: Citizens and government representatives (gov reps).
- Main features: Project listings, citizen feedback/inquiries, gov rep registration and messaging, dashboards and charts.
- Stack:
  - Languages: HTML, CSS, JavaScript, PHP, SQL
  - Runtime: PHP 8.x + MariaDB / MySQL
  - Notable files: `index.html`, `CHANGES_README.md`, `government_portal .sql`, `migration_v2.sql`, `config.example.php`, `db_connect.php`, `register_user.php`, `login_user.php`, frontend JS (`main.js`, `Chart.js`, `projects-list.js`, etc.)

Security & immediate deployment notes (important)
------------------------------------------------
- Secrets: Database credentials were originally in repo history. `config.example.php` is provided; copy to `config.php` and fill credentials. Rotate any exposed DB passwords before reuse.
- Migration: Run `migration_v2.sql` after loading the DB dump; it adds institutions, messages, notifications, and more. Inspect the step that sets `is_platform_admin` — change the email before running.
- Authentication/Authorization: CHANGES_README.md documents that several endpoints were previously missing auth checks and were hardened in the effective code. Confirm all sensitive endpoints require a logged-in, approved government session (e.g., `get_all_inquiries.php`, `submit_reply.php`, `manage_gov_reps.php`).
- Password storage: Passwords use PHP's password_hash (good). Verify login flow uses password_verify.
- Input validation: Some endpoints perform server-side filtering and validation (see `register_user.php`). Check for SQL injection, proper prepared statements, and escaping where raw SQL or HTML rendering is involved.
- Session handling: Confirm sessions are secure (cookie flags, session fixation protections).
- CSRF & XSS: Review forms and endpoints for CSRF protections and output escaping in pages that render user content (inquiries, messages, feedback).
- Database constraints: The original dump references a `categories` table (CHANGES_README calls this out) — ensure foreign keys are valid and migration order is correct.

How to run locally (short)
--------------------------
Prereqs: PHP 8+, Composer (only if you add PHP deps), MariaDB/MySQL, phpMyAdmin or mysql client.

1. Clone
   git clone https://github.com/Antoney-tct/Transparency_ke.git
2. Create database
   - Import the dump:
     - Using cli: mysql -u root -p government_portal < "government_portal .sql"
     - Or import via phpMyAdmin
3. Copy config
   - cp config.example.php config.php
   - Edit config.php with your DB host/user/pass/name.
4. Run DB migration
   - Inspect `migration_v2.sql` and edit the platform admin email if needed.
   - Run once: mysql -u root -p government_portal < migration_v2.sql
5. Serve with built-in PHP server (dev)
   - php -S localhost:8000
   - Open http://localhost:8000/index.html
6. Test key flows:
   - Register a citizen and a government rep (government accounts will be created as pending approval).
   - Approve a gov rep using the admin flow (check `manage_gov_reps.php`).
   - Submit an inquiry and reply; validate messaging thread appears.

Files of interest (quick map)
----------------------------
- db_connect.php — loads `config.php` and opens the DB connection.
- config.example.php — template; copy to config.php (NOT in git).
- government_portal .sql — DB dump with tables and seed data.
- migration_v2.sql — structural migration (institutions, messages, notifications, constraints).
- CHANGES_README.md — security and design changes summary (must-read before deployment).
- register_user.php, login_user.php — authentication and signup logic (server-side validation).
- get_* and save_* PHP endpoints — API endpoints used by frontend (e.g., `get_projects.php`, `get_inquiries.php`, `submit_inquiry.php`, `submit_reply.php`).
- Frontend: index.html, Projects.html, user_dashboard.html, gov-rep-dashboard.html and many page templates + JS (`main.js`, `Chart.js`, `projects-list.js`) and CSS files.

Review checklist (recommended for ChartGPT)
------------------------------------------
1. Secret exposure
   - Confirm no live credentials remain in tracked files or recent commit history; if present, rotate credentials.
2. Authentication & authorization
   - Verify all endpoints that return or change private data require a valid session and proper role checks.
   - Test `register_user.php` and `login_user.php` flows and edge cases (pending/rejected accounts blocked).
3. SQL safety
   - Ensure prepared statements used everywhere user input goes into SQL (I saw prepared statements in `register_user.php` — check others).
4. Password policy & hashing
   - Password hashing: use password_hash/password_verify.
   - Check password policy enforcement and reset flows.
5. Session & cookie security
   - Verify session cookie flags (Secure, HttpOnly, SameSite).
6. XSS & output escaping
   - Check places that echo user-entered text into HTML — escape output.
7. CSRF
   - Review critical POST endpoints for CSRF protections (tokens or same-site cookie enforcement).
8. Migration safety
   - Confirm `migration_v2.sql` safe-to-run semantics (adds only). Back up DB before running.
9. Client/Server contract
   - CHANGES_README indicates frontend wasn't updated to new shapes (messages array, notifications). Identify frontend areas to align with new API responses.
10. Logging & error handling
   - Ensure no sensitive data is leaked in error messages or logs.

Example calls to exercise endpoints
----------------------------------
- Register (citizen):
  curl -X POST -d "name=Alice&email=alice@example.com&password=StrongP@ssw0rd&userType=citizen&nationalId=12345&phone=0712345678" http://localhost:8000/register_user.php
- Login (example):
  curl -X POST -d "email=aouko178@gmail.com&password=..." http://localhost:8000/login_user.php

Notes for reviewers
-------------------
- CHANGES_README.md already documents remediation steps that were applied to the backend (auth checks, new tables, notifications, etc.). Use it as the source of truth for what changed; verify the live code matches those claims.
- The repo contains many static HTML files (views). The backend migration introduced new response shapes (threaded `messages`, new `notifications` table). Frontend pages such as `Reply-inquiries.html` and `reply inquiry.js` likely need updates to consume the new response shapes.
- The SQL dump references a `categories` FK that may not exist; check integrity before attempting a fresh DB restore.

Next steps I can take (if you want)
-----------------------------------
- Commit this README.md to the repo.
- Run an automated security scan and produce a vulnerability report (SQLi/XSS/CSRF).
- Update frontend JS to match the new API response shapes (`messages` array, notifications).
- Add a simple admin UI for `manage_gov_reps.php` to approve/reject government accounts.

Questions for the repo owner / reviewer
--------------------------------------
- Which environment will host this (shared hosting, VPS, Docker)? I can provide a deployment checklist for that target.
- Do you want me to commit README.md now and open a PR, or would you prefer manual edits before commit?
- Should I also add a lightweight Postman / curl collection demonstrating main flows (auth, inquiries, replies, notifications)?
