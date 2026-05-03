# ClassMatrix — Development Session Log

**Project:** ClassMatrix — School Management System
**Stack:** Laravel 13 · Inertia.js v3 · Vue 3 (Composition API) · Tailwind CSS v4 · MySQL · OpenAI API
**Started:** 2026-05-03
**Developer:** Shumen Baral

---

## Project Overview

ClassMatrix is a full-featured school management system with AI-powered features. It includes admin, teacher, student, and parent panels — each with role-based access, dedicated dashboards, and tailored functionality.

---

## What Was Built (All 8 Phases + Extras)

### Phase 1 — Foundation (Complete)
- Multi-role auth system (Super Admin, Admin, Teacher, Student, Parent)
- Spatie roles & permissions (40+ permissions, 8 roles)
- Academic structure: Academic Years, Terms, Departments, Class Levels, Sections, Subjects
- Admin dashboard with stats cards
- Database seeder with sample data (admin, teacher, grades 1-12, subjects, departments)

### Phase 2 — Skipped (Student/Staff profiles built in management section later)

### Phase 3 — Daily Operations (Complete)
- **Attendance:** Daily student attendance marking with bulk save, status toggles (Present/Absent/Late/Excused/Half Day), monthly attendance reports with percentage
- **Timetable:** Periods management, interactive timetable grid (days × periods), click-to-assign subject & teacher, teacher conflict detection
- **Notices:** Create/edit/view announcements, audience targeting (all/students/teachers/parents), publish scheduling, pagination
- **Calendar & Events:** Monthly calendar grid with events & holidays, color-coded event types, double-click to add events

### Phase 4 — Examinations & Grading (Complete)
- **Exams:** Exam types (Unit Test, Mid-Term, Final), exam CRUD with status tracking
- **Exam Schedules:** Per-exam schedule — assign subjects to classes with date, time, marks, room
- **Marks Entry:** 3-step filter (exam → subject → section), inline marks input, auto-grade calculation from grade scheme
- **Results:** Full results table with all subjects as columns, total/percentage/grade/GPA, ranked by percentage
- **Grade Schemes:** Configurable A-F grade schemes with GPA, default scheme seeded

### Phase 5 — Finance & HR (Complete)
- **Fee Structure:** Fee categories, class-level fee structures with frequency (monthly/quarterly/yearly/one-time)
- **Invoices:** Bulk invoice generation per section, stats cards (total/collected/pending/overdue), search & filter
- **Invoice Detail:** Items breakdown, payment history, record payment with `lockForUpdate()` on invoice
- **Payroll:** Monthly payslip generation from salary structures, mark paid
- **Salary Structures:** Designation-based salary breakdown (basic/HRA/DA/TA/PF/Tax), auto gross/net
- **Leaves:** Leave request list, approve/reject workflow, leave balance tracking

### Phase 6 — Auxiliary Modules (Complete)
- **Library:** Book catalog with search, categories, copy tracking (total/available), pagination
- **Book Issues:** Issue/return workflow, overdue highlighting, fine collection on return
- **Transport:** Vehicles, routes with ordered stops (pickup/drop times), student allocation
- **Hostel:** Hostels (boys/girls), rooms with occupancy tracking, warden assignment, AC/non-AC
- **Inventory:** Items with low-stock alerts, stock in/out/adjustment, categories, min-stock warnings

### Phase 7 — Online Learning (Complete)
- **Lessons:** Section/subject filters, CRUD, video URL support, publish status, content editor
- **Assignments:** Due date tracking, overdue highlighting, submission count
- **Submissions:** Per-assignment grading (marks + feedback), graded/pending status
- **Quizzes:** Card-based list, duration/marks/question count, availability window
- **Quiz Questions:** MCQ/Short/True-False types, options with correct answer, marks per question
- **Quiz Results:** Ranked attempt table, score/percentage

### Phase 8 — AI & Student Panel (Complete)
- **AI Routine Generator:** OpenAI-powered weekly/monthly study routine generation with student context (performance, subjects, preferences)
- **AI Personal Teacher (Chatbot):**
  - Full chat interface with message history
  - Voice input (browser Speech-to-Text — free, no API cost)
  - Voice output (browser Text-to-Speech — free)
  - Auto-speak toggle for hands-free mode
  - Student context injected (class, subjects, exam scores)
  - Friendly error messages when API fails (rate limit, key issues)
  - Retry with backoff on rate limits
- **Student Dashboard:** Stats (attendance %, assignments, routine status, AI chats), recent results
- **Routine Preferences:** Wake/sleep time, focus duration, peak focus, subject priorities, learning goals
- **Routine View:** Day tabs, color-coded time blocks, goals & tips cards

### Auth & User Management (Complete)
- **Registration redesigned:** 2-step flow — role selection (Student/Parent only) → details form
- **Parent registration:** Requires child's admission number to register, auto-links on signup
- **Split auth layout:** Beautiful gradient branding panel + form panel
- **Login redirect:** Role-based → admin/teacher/student/parent dashboards
- **Admin User Management:** Super Admin creates admins, Admin creates teachers, toggle status, reset password
- **Student Management:** Full CRUD, search/filter by section/status, assign class/section/roll, auto admission number
- **Staff/Teacher Management:** Full CRUD, search/filter by department, designation/qualification/experience, auto employee number
- **Parent Management:** View/edit/delete, children count badge, link to Parent Links
- **Parent-Student Links:** Admin links parent accounts to student profiles (father/mother/guardian/other)
- **Admin sidebar Management section:** Students | Teachers & Staff | Parents | Parent Links | All Users
- **Registration:** Only Student/Parent can self-register. Parent MUST provide valid child admission number or registration fails
- **Validation:** Server rejects admin/teacher in public signup. Custom error: "No student found with this admission number"

### Parent Panel (Complete)
- **Dashboard:** Children cards with attendance % and recent marks
- **My Children:** Detailed profiles (class, section, DOB, gender, blood group, attendance stats)
- **Attendance:** Select child + month → summary cards + daily records table with color-coded status
- **Results:** Select child → exam results grouped by exam, marks/grade/pass-fail per subject
- **Notices:** School announcements targeted to parents, click to read full notice

### Teacher Panel (Partial)
- **Dashboard:** Teaching sections, class teacher sections, pending assignments
- Sidebar with nav links (pages point to dashboard for now — needs expansion)

### UI/UX Overhaul (Complete)
- **Brand identity** matched to logo.png — deep navy sidebar, blue/cyan primary, gradient accents
- **Landing page** — Full redesign with fixed glassmorphism navbar, animated hero with floating glow orbs, logo showcase, gradient heading, stats counter, 6 feature cards with hover effects, role portal cards, AI showcase section with mock chat, CTA, footer
- **Auth pages** — Split layout with dark gradient left panel (blue→indigo→purple), dot pattern + glow effects, feature list with glass cards, logo.png integration
- **Admin dashboard** — Gradient stat cards with animated count-up numbers, hover lift effect, colored bottom bars, quick action cards with arrow reveal on hover
- **Student dashboard** — Full-width gradient welcome banner with dot pattern, colored stat cards, staggered fade-in animations on results, hover cards with colored border highlights
- **Logo component** — Uses logo.png image instead of SVG, gradient "ClassMatrix" text, tagline "Manage. Connect. Succeed"
- **Global CSS** — `.text-gradient` (blue→cyan), `.card-hover` (lift+shadow), `.glow-blue` (button glow), `.animate-count-up`, `.animate-fade-in-up` (staggered), custom styled scrollbar, antialiased text
- **Color system** — Light mode: blue primary (#3B82F6), dark navy sidebar. Dark mode: matching dark theme with blue accents
- **Font** — Inter (clean, modern, professional)

---

## Project Stats

| Metric | Count |
|--------|-------|
| Total Routes | **190** |
| Models | **64** |
| Vue Pages | **61** |
| Migrations | **31** |
| Controllers | **38** |
| Enums | **16** |
| AI Services | **2** |
| PHP Files | **138** |

---

## Database Tables (31 migrations)

### Auth & Core
users, password_reset_tokens, sessions, cache, jobs, permission_tables

### Academic
academic_years, terms, departments, class_levels, sections, subjects, class_subjects, teacher_subjects

### People & Management
students, student_enrollments, guardians, student_guardian, staff, parent_student

### Daily Operations
periods, timetables, student_attendances, staff_attendances, notices, events, holidays

### Examinations
exam_types, exams, exam_schedules, marks, grade_schemes, grade_ranges

### Finance
fee_categories, fee_structures, discounts, student_discounts, invoices, invoice_items, payments, salary_structures, payslips, leaves, leave_balances

### Auxiliary
book_categories, books, book_issues, vehicles, routes, route_stops, student_transport, hostels, rooms, hostel_allocations, item_categories, inventory_items, stock_movements, asset_allocations

### Online Learning
lessons, lesson_materials, assignments, assignment_submissions, quizzes, quiz_questions, quiz_options, quiz_attempts, quiz_answers

### AI
routine_preferences, routines, ai_chats, ai_messages, ai_logs

### Settings
system_settings

---

## Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@classmatrix.com | password |
| Teacher | teacher@classmatrix.com | password |
| Student | Register as student | — |
| Parent | Register with child's admission no | — |

---

## Tech Decisions Made

1. **Single school** (no multi-tenancy) — can add later with `stancl/tenancy`
2. **OpenAI API** for AI features (gpt-4o for routines, gpt-4o-mini for chat)
3. **Browser Speech API** for voice (zero cost — no API calls for voice)
4. **Spatie permissions** for RBAC
5. **Reka UI** (Radix Vue port) for accessible UI components
6. **Inertia.js v3** with Wayfinder for route generation
7. **SQLite → MySQL** switched during development
8. **DB transactions** on all financial writes with `lockForUpdate()`
9. **Soft deletes** on users, students, staff, invoices

---

## Environment Setup

```
APP_URL=http://ClassMatrix.test
DB_CONNECTION=mysql
DB_DATABASE=ClassMatrix
OPENAI_API_KEY=sk-proj-... (rate limited — needs billing upgrade)
OPENAI_MODEL=gpt-4o
OPENAI_CHAT_MODEL=gpt-4o-mini
```

---

## Known Issues / Pending

1. **OpenAI rate limit** — API key is on free tier, needs billing/credits to work
2. **Teacher panel** — Dashboard exists but other pages (marks entry, attendance, assignments) still point to dashboard
3. **No file uploads yet** — Student photos, assignment attachments, lesson materials use string paths but no actual upload handling
4. **No PDF generation** — Report cards, receipts, payslips need `barryvdh/laravel-dompdf`
5. **No search (Meilisearch)** — Global search not implemented
6. **No real-time (Reverb)** — Broadcasting configured as `log`, no WebSocket for live updates
7. **No email/SMS** — Notifications configured as `log` mailer
8. **No mobile API** — Sanctum installed but no API routes for mobile app

---

## What To Do Next (Priority Order)

### Immediate
1. **Fix OpenAI** — Add billing to OpenAI account so AI teacher works
2. **Teacher panel pages** — Build attendance marking, marks entry, lesson management for teachers
3. **File uploads** — Student photos, assignment files using `spatie/laravel-medialibrary`

### Short-term
4. **PDF report cards** — Install `barryvdh/laravel-dompdf`, build report card templates
5. **Student panel expansion** — View timetable, assignments, quiz taking, attendance history
6. **Bulk operations** — Bulk student import (Excel), bulk promotion to next year
7. **Global search** — Install Meilisearch, add search across students/staff/books/invoices

### Medium-term
8. **Real-time notifications** — Install Reverb, broadcast attendance/marks/notices
9. **Email notifications** — Fee due reminders, exam schedules, leave approvals
10. **Behavior/Discipline tracking** — Incidents, actions, parent notifications
11. **Visitor management** — QR check-in system

### Long-term
12. **Multi-tenancy** — `stancl/tenancy` for SaaS model
13. **Mobile app API** — Sanctum API routes for Flutter/React Native app
14. **Analytics dashboard** — Charts for attendance trends, fee collection, performance
15. **i18n** — Multi-language support

---

## Folder Structure Summary

```
app/
├── Actions/Fortify/          # Auth actions (CreateNewUser with role-based registration)
├── Concerns/                 # Validation rule traits
├── Enums/                    # 16 enums (UserType, AttendanceStatus, etc.)
├── Http/
│   ├── Controllers/
│   │   ├── Admin/            # 19 admin controllers (all modules + user/student/staff/parent management)
│   │   ├── Student/          # 3 student controllers (Dashboard, Routine, Chat)
│   │   ├── Teacher/          # 1 teacher controller (Dashboard)
│   │   └── ParentController  # Parent dashboard + children/attendance/results/notices
│   ├── Middleware/            # 6 middleware (Admin, Student, Teacher, Parent, Inertia, Appearance)
│   └── Responses/            # LoginResponse (role-based redirect)
├── Models/                   # 64 Eloquent models
├── Services/AI/              # RoutineService, ChatService (OpenAI with retry + error handling)
└── helpers.php               # current_academic_year() helper

resources/
├── css/app.css               # Brand colors (navy/blue/cyan), animations, scrollbar, effects
└── js/
    ├── pages/
    │   ├── admin/            # 42 admin pages (all modules + student/staff/parent/user management)
    │   ├── student/          # 6 student pages (dashboard, routines, chat)
    │   ├── teacher/          # 1 teacher page (dashboard)
    │   ├── parent/           # 5 parent pages (dashboard, children, attendance, results, notices)
    │   ├── auth/             # 7 auth pages (login with new design, register with role selection)
    │   ├── settings/         # 3 settings pages (profile, security, appearance)
    │   └── Welcome.vue       # Landing page (full redesign with logo, features, AI showcase)
    ├── components/           # Sidebar per role (Admin/Student/Teacher/Parent), UI components, logo
    ├── composables/          # useVoice (speech I/O), useAppearance, useCurrentUrl, etc.
    ├── layouts/              # AppLayout, StudentLayout, TeacherLayout, ParentLayout, AuthSplitLayout
    └── types/                # TypeScript type definitions

routes/
├── web.php                   # Main routes + role-based redirect (admin/student/teacher/parent)
├── admin.php                 # Admin routes (145+)
├── student.php               # Student routes (10)
├── teacher.php               # Teacher routes (1)
├── parent.php                # Parent routes (5)
└── settings.php              # Settings routes
```

---

## Commands

```bash
# Start development
composer dev

# Fresh database with seed data
php artisan migrate:fresh --seed

# Build frontend
npx vite build

# Run tests
php artisan test
```
