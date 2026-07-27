# Transparency_ke — fixes applied

Tested against a real MariaDB 10.11 instance end to end (loaded your actual
`government_portal .sql` dump, ran the migration on top of it, verified your
existing data survives and your account comes out as platform admin).

## Deploy order — do this exactly in this order

1. **Rotate your InfinityFree DB password now**, before anything else. The
   old one is permanently burned since it sat in public git history.
2. Upload every file in this folder to your host, overwriting the old ones.
3. On the server, copy `config.example.php` to `config.php` and fill in
   your **new** DB password. `config.php` is gitignored — don't commit it.
4. Run `migration_v2.sql` once, via phpMyAdmin, against your live database.
   Back up first (Export tab) — the script only adds tables/columns, it
   doesn't delete anything, but back up anyway.
5. Open `migration_v2.sql` and check the line that sets
   `is_platform_admin = 1` — it's set to `aouko178@gmail.com`. Change it if
   that's not the account you want as super-admin.
6. Push the code to GitHub. `db_connect.php` no longer contains secrets, so
   this repo is safe to be public again.

## What actually changed

**Security**
- `db_connect.php` — credentials moved out of the repo into gitignored
  `config.php`. `config.example.php` is the safe-to-commit template.
- `get_all_inquiries.php` and `submit_reply.php` had **zero auth checks** —
  anyone could pull every citizen's name/email/message, or post a fake
  government reply, just by hitting the URL. Both now require a logged-in,
  approved government session.
- `manage_user.php` reset-password action hardcoded every reset to the same
  password (`Transparency@123`) — readable straight from your source code.
  Now generates a random one-time password per reset.

**Institutions & admin structure**
- New `institutions` table — real entities (name, type, region, verified
  flag) instead of a free-text `department` string.
- `government_representatives` gained `institution_id`, `status`
  (pending/approved/rejected), and `is_platform_admin`.
- `register_user.php` — government signups now resolve or create an
  institution row and start as `pending`. They cannot log in until approved.
- `login_user.php` — blocks pending/rejected accounts with a clear message;
  approved reps carry `institution_id` and admin flag into their session.
- `manage_gov_reps.php` (new) — platform-admin-only endpoint to list
  pending reps and approve/reject them. First approval also marks the
  institution `verified`.
- Dead code: `Govrep_formhandler.php` was a second, weaker registration
  handler nothing actually called. Neutralized with a deprecation message —
  safe to delete outright once you confirm nothing links to it.

**Messaging**
- New `messages` table replaces the old one-shot `replies` table. Every
  inquiry is now a real thread — citizen and gov rep can go back and forth.
  Your existing replies were migrated in automatically.
- `submit_inquiry.php` — accepts an optional `institution_id` so a citizen's
  message routes to the right department instead of a shared free-for-all.
- `submit_reply.php` — writes to the new thread, and a regular rep can only
  reply to inquiries routed to their own institution (platform admins can
  answer anything).
- `reply_to_inquiry.php` (new) — lets the **citizen** continue the
  conversation. Previously they had no way to respond to a reply.
- `get_user_messages.php` — now returns the full thread per inquiry, not a
  single reply.
- New `notifications` table + `get_notifications.php` +
  `mark_notification_read.php` — the bell icon can now show real unread
  counts instead of numbers only tracked in the browser DOM.

## What I did NOT touch — your next step

I rebuilt the backend and database layer and tested it against real data.
I did **not** rewrite the frontend JS/HTML to consume the new response
shapes yet:

- `Reply-inquiries.html` / `reply inquiry.js` should render the `messages`
  array per inquiry (threaded) instead of the old single-reply view.
- `notification.js` should fetch `get_notifications.php` on load instead of
  manipulating badge counts client-side only.
- `GovernmentSign_up.html` should show "pending approval" messaging instead
  of implying instant access.
- You'll want a simple admin page that calls `manage_gov_reps.php`
  (`list_pending`, `approve`, `reject`) so you're not doing approvals via
  raw SQL.
- The citizen inquiry form needs an institution dropdown (`SELECT * FROM
  institutions WHERE verified = 1`) wired to the new `institution_id` field.

Say the word and I'll build those next — the backend is the part that was
actually risky, this part is comparatively quick.

## Also worth knowing
Your original `government_portal .sql` dump references a `categories`
table in a foreign key constraint that doesn't exist anywhere in the dump —
that FK would fail if you ever tried to restore that exact file fresh. Not
something I introduced; flagging it since I hit it while testing.

---

## Round 2 — frontend wiring + bugs found by actually testing

I ran a full local PHP + MariaDB stack and drove every flow with real
requests (register → pending → blocked login → admin approve → login
succeeds → citizen submits inquiry → gov rep replies → citizen gets a
notification → citizen replies back → full thread). That surfaced three
real bugs — two pre-existing, one my own — all now fixed and re-verified:

1. **`register_user.php` / `login_user.php`** — both also write to a
   `users` table used only by the admin dashboard's "all users" list. That
   table wasn't in the SQL dump you gave me. If it's missing or drifted on
   your live server the same way, **every registration and every login was
   reporting failure to the user even when the actual account action
   succeeded** — because that secondary, non-essential write was wrapped in
   the same try/catch as the real one. Both are now isolated so a dashboard
   sync failure can't masquerade as your account not working.
2. **My own bug**, caught by testing: in `manage_gov_reps.php` and
   `submit_reply.php`, I ran a new prepared statement on the same DB
   connection before closing the previous one — MySQL calls this "commands
   out of sync." Approvals and gov rep replies were silently failing.
   Fixed and re-verified with real requests.

**Frontend work done this round:**
- `Reply-inquiries.html` (admin inbox) — renders the real threaded
  `messages` array instead of the old single-reply view, shows which
  institution an inquiry belongs to, and has a working notification bell
  wired to `get_notifications.php` / `mark_notification_read.php`.
- `User_inquirery.html` (citizen side) — the "My Inquiries" tab no longer
  lets anyone type in any email to view someone else's inquiries (see
  below); it's session-based now, shows the full thread, and citizens can
  reply back. The new-inquiry form has an institution dropdown pulling from
  `get_institutions.php`.
- `gov-rep-approvals.html` (new page) — platform admin can approve/reject
  pending gov reps without touching SQL. Linked from the sidebar on
  `Reply-inquiries.html`, only visible to platform admins.
- `check_session.php` (new) — lightweight endpoint pages call to confirm
  who's actually logged in server-side.

**Another real vulnerability found and fixed:** `get_user_inquiries.php`
took a plain `?email=` query parameter with no authentication — anyone
could type in any citizen's email address and read their private
correspondence with government, replies included. It's now locked to the
logged-in citizen's own session and ignores any email passed in the URL.

All of this was tested against a real database with real HTTP requests,
not just read for correctness — the zip you're getting is the version that
actually passed those tests.

