# ClassMatrix — School Management System Blueprint

**Stack:** Laravel 11+ · Inertia.js v2 · Vue 3 (Composition API) · Tailwind CSS v4 · MySQL · Redis · OpenAI API

---

## 1. Architecture

```
┌──────────────────────────────────────────────────────┐
│  Vue 3 SPA (Pages + Components + Pinia + Tailwind)   │
└────────────────────────┬─────────────────────────────┘
                         │ Inertia (no REST glue)
┌────────────────────────▼─────────────────────────────┐
│  Laravel (Controllers → Services → Models)           │
│  Auth · Policies · Form Requests · Events · Jobs     │
└────────┬───────────┬───────────┬───────────┬─────────┘
         ▼           ▼           ▼           ▼
       MySQL       Redis       S3/Disk     OpenAI
      (data)    (cache,queue, (files)     (AI routines,
                 sessions)                 insights)
```

**Principles:**
- Thin controllers — business logic in **Services** or **Actions**
- **Policies** for authorization, **Form Requests** for validation
- **Events/Listeners** to decouple side effects (emails, notifications, AI triggers)
- Queue all heavy work (PDFs, bulk SMS, AI calls, Excel exports)

---

## 2. Modules

### Core (Phase 1-4)
| # | Module | Key Features |
|---|--------|-------------|
| 1 | **Multi-Role Auth** | Super Admin, Admin, Principal, Teacher, Student, Parent, Accountant, Librarian, Driver |
| 2 | **Academic Setup** | Academic Year, Term, Department, Class/Grade, Section, Subject |
| 3 | **Admissions** | Application form, approval workflow, ID card generation |
| 4 | **Students** | Profile, guardians, medical info, documents, promotion |
| 5 | **Staff/HR** | Profile, department, designation, qualifications, leave |
| 6 | **Attendance** | Daily/period-wise (students), biometric/manual (staff) |
| 7 | **Timetable** | Class & teacher timetable, room allocation, conflict detection |
| 8 | **Exams** | Types, schedules, seating, marks entry, grade calculation |
| 9 | **Report Cards** | Customizable templates, GPA/percentage, rank, PDF export |

### Operations (Phase 5-6)
| # | Module | Key Features |
|---|--------|-------------|
| 10 | **Fees** | Fee structure, invoices, partial payments, due reminders, receipts, scholarships |
| 11 | **Payroll** | Salary structure, allowances, deductions, payslips, tax |
| 12 | **Library** | Books, issue/return, fines, reservations, barcode |
| 13 | **Transport** | Routes, vehicles, drivers, stops, student allocation |
| 14 | **Hostel** | Buildings, rooms, allocation, mess |
| 15 | **Inventory** | Stock, issue/return, maintenance |

### Engagement (Phase 7)
| # | Module | Key Features |
|---|--------|-------------|
| 16 | **Communication** | Notices, in-app messaging, email/SMS, push notifications |
| 17 | **Online Learning** | Lessons, video, materials, assignments, quizzes |
| 18 | **Calendar** | School calendar, holidays, events, parent-teacher meetings |

### Intelligence (Phase 8)
| # | Module | Key Features |
|---|--------|-------------|
| 19 | **AI Routines** | Personalized weekly study/work routines via OpenAI |
| 20 | **Reports & Analytics** | Attendance %, performance trends, fee collection, AI insights |
| 21 | **Settings** | School info, branding, grading scheme, module toggles, API keys |

### Bonus Differentiators
- Mobile-friendly Parent Portal with push notifications
- Live Classes (Zoom/Google Meet API)
- Behavior/Discipline tracking
- Health records & vaccination tracker
- Visitor management with QR check-in
- Multi-language (i18n) + RTL support
- Multi-tenancy for SaaS

---

## 3. Non-Functional Requirements

| Concern | Target |
|---------|--------|
| **Performance** | <300ms dashboard, eager-load relations, Redis cache |
| **Security** | RBAC, 2FA, rate limiting, CSRF, encrypted PII, signed file URLs |
| **Scalability** | Queue heavy jobs, Redis cache, indexed columns |
| **Auditability** | Activity log on every sensitive CRUD |
| **Reliability** | Daily backups, soft deletes on critical models, DB transactions on financials |
| **Accessibility** | WCAG AA, keyboard nav |
| **AI Safety** | No PII sent to OpenAI, rate-limited, cost-capped, response validation |

---

## 4. Database Schema

> Convention: snake_case, plural tables. UUIDs for public IDs, bigint auto-increment for PKs.

### 4.1 Auth & Tenancy
```
users
  id, name, email, phone, password, email_verified_at,
  user_type [admin|teacher|student|parent|staff],
  status [active|inactive|suspended],
  remember_token, two_factor_secret, two_factor_recovery_codes,
  timestamps, soft_deletes

schools                                -- only if multi-tenant
  id, name, code, address, phone, email, logo,
  primary_color, settings (json), timezone, currency, status

roles, permissions, model_has_roles, model_has_permissions, role_has_permissions
  -- via spatie/laravel-permission
```

### 4.2 Academic Structure
```
academic_years         id, school_id, name, start_date, end_date, is_current
terms                  id, academic_year_id, name, start_date, end_date

departments            id, name, code, head_id (→ users)
class_levels           id, name, numeric_order
sections               id, class_level_id, academic_year_id, name,
                       capacity, class_teacher_id, room_no

subjects               id, name, code, department_id, type [theory|practical|lab]
class_subjects         id, class_level_id, subject_id, full_marks, pass_marks
teacher_subjects       id, teacher_id, section_id, subject_id, academic_year_id
```

### 4.3 Students & Guardians
```
students
  id, user_id, admission_no (unique), roll_no, dob, gender,
  blood_group, religion, nationality, mother_tongue,
  current_section_id, admission_date, previous_school, photo,
  address_permanent, address_current, emergency_contact,
  medical_notes, status [active|alumni|withdrawn]

student_enrollments
  id, student_id, academic_year_id, section_id, roll_no,
  promotion_status, remarks

guardians              id, name, relation, phone, email, occupation, photo, address
student_guardian       student_id, guardian_id, is_primary, can_pickup
```

### 4.4 Staff
```
staff
  id, user_id, employee_no, designation, department_id,
  joining_date, qualification, experience_years, photo,
  date_of_birth, gender, address, emergency_contact,
  bank_account, status
```

### 4.5 Attendance
```
student_attendances    id, student_id, section_id, date,
                       status [present|absent|late|excused|half_day],
                       period_id (nullable), remarks, marked_by

staff_attendances      id, staff_id, date, check_in, check_out,
                       status, working_hours, remarks
```
> Index: `(student_id, date)`, `(section_id, date)`

### 4.6 Exams & Grading
```
exam_types             id, name
exams                  id, exam_type_id, academic_year_id, term_id,
                       name, start_date, end_date, status
exam_schedules         id, exam_id, class_level_id, subject_id,
                       exam_date, start_time, end_time,
                       full_marks, pass_marks, room

marks                  id, exam_schedule_id, student_id,
                       marks_obtained, grade, remarks, entered_by

grade_schemes          id, name, is_default
grade_ranges           id, grade_scheme_id, grade, min_pct, max_pct, gpa
```

### 4.7 Timetable
```
periods                id, name, start_time, end_time, order, is_break
timetables             id, section_id, day_of_week, period_id,
                       subject_id, teacher_id, room
```

### 4.8 Fees & Payments
```
fee_categories         id, name
fee_structures         id, class_level_id, fee_category_id,
                       academic_year_id, amount,
                       frequency [monthly|quarterly|yearly|one-time]

invoices               id, student_id, academic_year_id, term_id,
                       invoice_no, total_amount, paid_amount, due_date,
                       status [unpaid|partial|paid|overdue], generated_at
invoice_items          id, invoice_id, fee_structure_id, description, amount

payments               id, invoice_id, amount, method [cash|card|bank|online],
                       transaction_id, gateway, paid_at, received_by, receipt_no

discounts              id, name, type [percent|flat], value
student_discounts      id, student_id, discount_id, valid_from, valid_to, reason
```
> Always wrap payment writes in DB transactions with `lockForUpdate()`.

### 4.9 Library
```
book_categories        id, name
books                  id, title, author, isbn, publisher, year,
                       category_id, shelf, total_copies, available_copies
book_issues            id, book_id, user_id, issued_at, due_date,
                       returned_at, fine, status, issued_by
```

### 4.10 Transport
```
vehicles               id, registration_no, type, capacity, driver_id, helper_id
routes                 id, name, vehicle_id, fare, distance_km
route_stops            id, route_id, name, pickup_time, drop_time, order
student_transport      id, student_id, route_id, stop_id, academic_year_id
```

### 4.11 Hostel
```
hostels                id, name, type [boys|girls], warden_id, total_rooms
rooms                  id, hostel_id, room_no, capacity, type [ac|non-ac], rent
hostel_allocations     id, room_id, student_id, allocated_at, vacated_at, fee
```

### 4.12 Communication
```
notices                id, title, body, target [all|students|teachers|parents|class],
                       target_id, attachment, published_at, expires_at, created_by
messages               id, sender_id, receiver_id, subject, body, read_at, parent_id
notifications          -- Laravel default notifications table
```

### 4.13 Online Learning
```
lessons                id, subject_id, section_id, title, content,
                       video_url, order, published_at, created_by
lesson_materials       id, lesson_id, name, file_path, type

assignments            id, teacher_id, section_id, subject_id, title,
                       description, due_date, total_marks, attachment
assignment_submissions id, assignment_id, student_id, submitted_at,
                       file, comment, marks, feedback, graded_at, graded_by

quizzes                id, lesson_id, title, duration_minutes, total_marks
quiz_questions         id, quiz_id, question, type [mcq|short|true_false], marks
quiz_options           id, question_id, text, is_correct
quiz_attempts          id, quiz_id, student_id, started_at, submitted_at, score
quiz_answers           id, attempt_id, question_id, answer, is_correct, marks
```

### 4.14 HR & Payroll
```
salary_structures      id, designation, basic, hra, da, ta,
                       other_allowances (json), pf, tax, other_deductions (json)
payslips               id, staff_id, month, year, basic, allowances (json),
                       deductions (json), gross, net, generated_at, paid_at

leaves                 id, staff_id, type [casual|sick|earned|unpaid],
                       from_date, to_date, days, reason, status, approved_by
leave_balances         id, staff_id, year, type, total, used
```

### 4.15 Calendar
```
events                 id, title, description, start_at, end_at,
                       type [holiday|exam|meeting|cultural], audience,
                       color, location, created_by
holidays               id, name, date, recurring
```

### 4.16 Inventory
```
item_categories        id, name
inventory_items        id, name, category_id, unit, quantity, min_stock, location
stock_movements        id, item_id, type [in|out|adjustment], quantity,
                       reference, performed_by, performed_at
asset_allocations      id, item_id, allocated_to, allocated_at,
                       returned_at, condition_notes
```

### 4.17 AI & Routines
```
routine_preferences
  id, user_id (unique), wake_up_time, sleep_time,
  focus_minutes, peak_focus [morning|afternoon|evening],
  blocked_times (json), learning_goals (json),
  subject_priorities (json), include_weekend, timestamps

routines
  id, user_id, academic_year_id, week_start_date,
  status [generating|ready|failed],
  weekly_goals (json), study_tips (json), ai_summary (text),
  blocks (json),                    -- all time blocks as JSON array
  model_used, total_tokens, cost_usd (decimal 8,6),
  timestamps
  index: (user_id, week_start_date)

ai_logs
  id, user_id, feature, model,
  prompt_tokens, completion_tokens,
  cost_usd (decimal 8,6), latency_ms,
  status [success|failed], error (text),
  timestamps
  index: (feature, created_at)
```

### 4.18 Settings & Logs
```
system_settings        key, value, group, type
activity_logs          -- via spatie/laravel-activitylog
```

---

## 5. Folder Structure

```
app/
├── Actions/                      # Single-purpose (CreateInvoiceAction, etc.)
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   ├── Teacher/
│   │   ├── Student/
│   │   ├── Parent/
│   │   └── Auth/
│   ├── Requests/                 # Form Request validation
│   ├── Resources/                # API resources (mobile)
│   └── Middleware/
├── Models/
├── Policies/
├── Services/
│   ├── AttendanceService.php
│   ├── ExamService.php
│   ├── FeeService.php
│   ├── ReportCardService.php
│   └── RoutineService.php        # AI routine generation
├── Jobs/
│   └── GenerateRoutineJob.php
├── Events/
├── Listeners/
├── Notifications/
├── Observers/
└── Support/                      # Enums, helpers, value objects

resources/js/
├── Pages/
│   ├── Admin/
│   ├── Teacher/
│   ├── Student/
│   │   └── Routines/             # Show.vue, Preferences.vue
│   ├── Parent/
│   └── Auth/
├── Layouts/
│   ├── AdminLayout.vue
│   ├── TeacherLayout.vue
│   ├── StudentLayout.vue
│   └── GuestLayout.vue
├── Components/
│   ├── UI/                       # Button, Input, Modal, Dropdown
│   ├── Tables/
│   ├── Charts/
│   └── Routines/                 # RoutineCalendar.vue
├── Composables/                  # useToast, usePermission, useRoutineListener
├── Stores/                       # Pinia
└── Utils/
```

---

## 6. Packages

### Backend (Composer)
| Package | Purpose |
|---------|---------|
| `inertiajs/inertia-laravel` | Inertia adapter |
| `tightenco/ziggy` | Laravel routes in Vue |
| `spatie/laravel-permission` | Roles & permissions (RBAC) |
| `spatie/laravel-medialibrary` | Photos, attachments, documents |
| `spatie/laravel-activitylog` | Audit trail |
| `spatie/laravel-backup` | Scheduled backups |
| `spatie/laravel-query-builder` | Filter/sort/include on lists |
| `barryvdh/laravel-dompdf` | Report cards, receipts, payslips |
| `maatwebsite/excel` | Bulk import/export |
| `openai-php/laravel` | OpenAI API for AI features |
| `laravel/sanctum` | API auth (mobile app) |
| `laravel/horizon` | Queue dashboard |
| `laravel/scout` + Meilisearch | Global search |
| `laravel/reverb` | Real-time broadcasting |
| `intervention/image` | Image resize/crop |
| `stancl/tenancy` | If multi-tenant SaaS |

### Frontend (npm)
| Package | Purpose |
|---------|---------|
| `@inertiajs/vue3` | Inertia Vue adapter |
| `pinia` | State management |
| `@headlessui/vue` + `@heroicons/vue` | Accessible UI primitives |
| `vee-validate` + `zod` | Form validation |
| `vue3-apexcharts` | Charts & analytics |
| `@fullcalendar/vue3` | Calendar + routine view |
| `@vueuse/core` | Composition utilities |
| `dayjs` | Date formatting |
| `vue-toastification` | Toast notifications |
| `@tanstack/vue-table` | Data tables |

---

## 7. Auth & Authorization

1. **Single `users` table** with `user_type` + related profile tables (`students`, `staff`)
2. **Spatie Roles:** `super-admin`, `admin`, `principal`, `teacher`, `class-teacher`, `accountant`, `librarian`, `student`, `parent`
3. **Policies** per model — always `Gate::authorize()` in controllers
4. **Middleware** to auto-redirect to role-specific dashboard
5. **2FA** for admins/accountants via Fortify
6. **Sanctum** tokens for mobile API

---

## 8. AI Routine Feature

### What It Does

**Students:** AI generates a personalized weekly study routine based on class, subjects, exam scores (weak areas get more time), upcoming exams, school timetable, and personal preferences.

**Teachers:** AI generates a weekly work routine based on teaching load, pending assessments, struggling students, and free periods.

### How It Works

```
User clicks "Generate" → Controller dispatches queued job
→ RoutineService builds prompt with student context
→ Calls OpenAI with JSON Schema (structured output)
→ Validates response → Saves to DB → Broadcasts event
→ Vue auto-reloads via Reverb
```

### Config

```env
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-4o
OPENAI_FALLBACK_MODEL=gpt-4o-mini
```

```php
// config/services.php
'openai' => [
    'key'            => env('OPENAI_API_KEY'),
    'model'          => env('OPENAI_MODEL', 'gpt-4o'),
    'fallback_model' => env('OPENAI_FALLBACK_MODEL', 'gpt-4o-mini'),
    'max_tokens'     => 4096,
],
```

### RoutineService (Single File — All AI Logic)

`app/Services/RoutineService.php`

```php
<?php

namespace App\Services;

use App\Models\Routine;
use App\Models\AiLog;
use App\Models\User;
use App\Events\RoutineReady;
use Carbon\Carbon;
use OpenAI\Laravel\Facades\OpenAI;

class RoutineService
{
    public function generate(User $user, Carbon $weekStart): Routine
    {
        $routine = Routine::create([
            'user_id'          => $user->id,
            'academic_year_id' => current_academic_year()->id,
            'week_start_date'  => $weekStart,
            'status'           => 'generating',
        ]);

        $model = config('services.openai.model');
        $startMs = hrtime(true);

        try {
            $response = OpenAI::chat()->create([
                'model'           => $model,
                'max_tokens'      => config('services.openai.max_tokens'),
                'response_format' => [
                    'type'        => 'json_schema',
                    'json_schema' => [
                        'name'   => 'weekly_routine',
                        'strict' => true,
                        'schema' => self::jsonSchema(),
                    ],
                ],
                'messages' => [
                    ['role' => 'system',  'content' => $this->systemPrompt($user)],
                    ['role' => 'user',    'content' => $this->userPrompt($user, $weekStart)],
                ],
            ]);

            $data    = json_decode($response->choices[0]->message->content, true);
            $usage   = $response->usage;
            $cost    = $this->cost($model, $usage->promptTokens, $usage->completionTokens);
            $latency = (int) ((hrtime(true) - $startMs) / 1e6);

            $routine->update([
                'status'       => 'ready',
                'weekly_goals' => $data['weekly_goals'],
                'study_tips'   => $data['study_tips'],
                'ai_summary'   => $data['summary'],
                'blocks'       => $data['days'],
                'model_used'   => $model,
                'total_tokens' => $usage->totalTokens,
                'cost_usd'     => $cost,
            ]);

            AiLog::create([
                'user_id'           => $user->id,
                'feature'           => 'routine',
                'model'             => $model,
                'prompt_tokens'     => $usage->promptTokens,
                'completion_tokens' => $usage->completionTokens,
                'cost_usd'          => $cost,
                'latency_ms'        => $latency,
                'status'            => 'success',
            ]);

            RoutineReady::dispatch($routine);
        } catch (\Throwable $e) {
            $routine->update(['status' => 'failed']);
            AiLog::create([
                'user_id'           => $user->id,
                'feature'           => 'routine',
                'model'             => $model,
                'prompt_tokens'     => 0,
                'completion_tokens' => 0,
                'cost_usd'          => 0,
                'latency_ms'        => (int) ((hrtime(true) - $startMs) / 1e6),
                'status'            => 'failed',
                'error'             => $e->getMessage(),
            ]);
            throw $e;
        }

        return $routine;
    }

    protected function systemPrompt(User $user): string
    {
        $role = $user->isStudent()
            ? 'You are an expert academic coach designing study routines for school students.'
            : 'You are an education coordinator designing work routines for teachers.';

        return "{$role}

Rules:
- Never schedule during blocked times, school hours, or sleep
- Give more time to weak subjects and upcoming exams
- Mix activities: study, revision, practice, breaks, exercise, free time
- Use 30-60 minute study blocks
- Include meals and 30+ min daily exercise
- Don't repeat the same subject more than 2 hours in a row
- Respond ONLY with the JSON schema provided";
    }

    protected function userPrompt(User $user, Carbon $weekStart): string
    {
        $student     = $user->student;
        $prefs       = $user->routinePreference;
        $performance = json_encode($student->recentPerformanceSummary());
        $exams       = $student->upcomingExams(30)
            ->map(fn ($e) => "{$e->subject->name} on {$e->exam_date->format('M d')}")
            ->join(', ') ?: 'None';
        $subjects    = $student->section->subjects->pluck('name')->join(', ');
        $schoolHours = $student->section->timetableSummary();

        return "Generate routine for week of {$weekStart->format('Y-m-d (l)')}.

STUDENT: {$student->section->classLevel->name}
SUBJECTS: {$subjects}
PERFORMANCE (%): {$performance}
UPCOMING EXAMS: {$exams}
SCHOOL: {$schoolHours}
WAKE: {$prefs->wake_up_time} | SLEEP: {$prefs->sleep_time}
BLOCKED: " . json_encode($prefs->blocked_times ?? []) . "
PEAK FOCUS: {$prefs->peak_focus} | BLOCK: {$prefs->focus_minutes}min
PRIORITIES: " . json_encode($prefs->subject_priorities ?? []) . "
GOALS: " . ($prefs->learning_goals ? implode('; ', $prefs->learning_goals) : 'None');
    }

    public static function jsonSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'weekly_goals' => ['type' => 'array', 'items' => ['type' => 'string']],
                'study_tips'   => ['type' => 'array', 'items' => ['type' => 'string']],
                'summary'      => ['type' => 'string'],
                'days' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'day' => ['type' => 'string', 'enum' => ['mon','tue','wed','thu','fri','sat','sun']],
                            'blocks' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'start'    => ['type' => 'string'],
                                        'end'      => ['type' => 'string'],
                                        'type'     => ['type' => 'string', 'enum' => [
                                            'study','revision','practice','homework','reading',
                                            'break','exercise','meal','sleep','school','extracurricular','free',
                                        ]],
                                        'subject'  => ['type' => ['string','null']],
                                        'title'    => ['type' => 'string'],
                                        'priority' => ['type' => 'integer'],
                                    ],
                                    'required' => ['start','end','type','title'],
                                    'additionalProperties' => false,
                                ],
                            ],
                        ],
                        'required' => ['day','blocks'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            'required' => ['weekly_goals','study_tips','summary','days'],
            'additionalProperties' => false,
        ];
    }

    protected function cost(string $model, int $in, int $out): float
    {
        $rates = [
            'gpt-4o'      => ['in' => 2.50, 'out' => 10.00],
            'gpt-4o-mini' => ['in' => 0.15, 'out' => 0.60],
        ];
        $r = $rates[$model] ?? $rates['gpt-4o'];
        return round(($in / 1e6) * $r['in'] + ($out / 1e6) * $r['out'], 6);
    }
}
```

### Queue Job

```php
// app/Jobs/GenerateRoutineJob.php
class GenerateRoutineJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 2;
    public int $timeout = 90;

    public function __construct(public User $user, public Carbon $weekStart) {}

    public function handle(RoutineService $service): void
    {
        $routine = $service->generate($this->user, $this->weekStart);
        $this->user->notify(new RoutineReady($routine));
    }

    public function failed(\Throwable $e): void
    {
        $this->user->notify(new RoutineFailed());
    }
}
```

### Real-time (Reverb)

```php
// app/Events/RoutineReady.php
class RoutineReady implements ShouldBroadcast
{
    public function __construct(public Routine $routine) {}
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("user.{$this->routine->user_id}");
    }
}
```

```js
// Composables/useRoutineListener.js
export function useRoutineListener(userId) {
  let channel
  onMounted(() => {
    channel = window.Echo.private(`user.${userId}`)
      .listen('RoutineReady', () => router.reload({ only: ['routine'] }))
  })
  onUnmounted(() => channel?.stopListening('RoutineReady'))
}
```

### Vue Page

```vue
<!-- Pages/Student/Routines/Show.vue -->
<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import StudentLayout from '@/Layouts/StudentLayout.vue'
import RoutineCalendar from '@/Components/Routines/RoutineCalendar.vue'
import { useRoutineListener } from '@/Composables/useRoutineListener'

const props = defineProps({ routine: Object, authUserId: Number })
useRoutineListener(props.authUserId)

const isGenerating = computed(() => props.routine?.status === 'generating')

function generate() {
  router.post(route('routines.generate'), {}, { preserveScroll: true })
}
</script>

<template>
  <StudentLayout>
    <div class="max-w-7xl mx-auto p-6 space-y-6">
      <header class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">My Weekly Routine</h1>
        <button @click="generate" :disabled="isGenerating"
          class="px-4 py-2 bg-indigo-600 text-white rounded-lg disabled:opacity-50">
          {{ isGenerating ? 'Generating...' : routine ? 'Regenerate' : 'Generate Routine' }}
        </button>
      </header>

      <div v-if="isGenerating" class="bg-indigo-50 rounded-lg p-6 text-center animate-pulse">
        AI is building your routine (10-30s)...
      </div>

      <template v-else-if="routine?.status === 'ready'">
        <p class="text-gray-600">{{ routine.ai_summary }}</p>
        <div class="grid md:grid-cols-2 gap-4">
          <div class="bg-white rounded-xl p-5 shadow-sm">
            <h2 class="font-semibold mb-2">Goals</h2>
            <ul class="text-sm space-y-1">
              <li v-for="g in routine.weekly_goals">- {{ g }}</li>
            </ul>
          </div>
          <div class="bg-white rounded-xl p-5 shadow-sm">
            <h2 class="font-semibold mb-2">Tips</h2>
            <ul class="text-sm space-y-1">
              <li v-for="t in routine.study_tips">- {{ t }}</li>
            </ul>
          </div>
        </div>
        <RoutineCalendar :days="routine.blocks" />
      </template>

      <div v-else class="border-2 border-dashed rounded-xl p-12 text-center text-gray-500">
        Set your preferences, then generate your first routine.
      </div>
    </div>
  </StudentLayout>
</template>
```

### AI Safeguards

- **Validate response:** 7 days, no time overlaps, no blocks during sleep/school, subjects match enrollment
- **Privacy:** Send "Class 10-A student" not real names to OpenAI
- **Rate limit:** `throttle:3,60` + max 1 generation/day/user
- **Fallback:** If gpt-4o fails, retry with gpt-4o-mini
- **Cost cap:** Monitor `ai_logs`, set monthly per-user token quota

### AI Cost (1,000 students/week)

| Model | Cost/week |
|-------|-----------|
| gpt-4o | ~$12.50 |
| gpt-4o-mini | ~$0.75 |

Skip regeneration if nothing changed (no new marks, exams, or preference updates).

---

## 9. Development Roadmap

| Phase | Weeks | Modules |
|-------|-------|---------|
| **1 — Foundation** | 1-3 | Auth, roles, school setup, academic year, classes, sections, subjects, dashboard |
| **2 — People** | 4-6 | Student admission, staff onboarding, profiles, ID cards, bulk imports |
| **3 — Daily Ops** | 7-10 | Attendance, timetable, notices, messaging, calendar |
| **4 — Academics** | 11-14 | Exams, marks entry, grading, report cards (PDF), promotion |
| **5 — Finance** | 15-17 | Fee structure, invoices, payments, receipts, payroll, payslips |
| **6 — Auxiliary** | 18-22 | Library, transport, hostel, inventory, HR/leave |
| **7 — Engagement** | 23-26 | Online learning, assignments, quizzes, live classes |
| **8 — Intelligence** | 27-30 | AI routines, reports & analytics, performance tuning, security audit |

---

## 10. Best Practices

### Code
- **Form Requests** for ALL validation — never in controllers
- **Enums** for statuses (PHP 8.1+ native)
- **DB transactions** for multi-table writes (payments, enrollment, promotion)
- **Eager load** relations — N+1 kills attendance/marks lists
- **Indexes** on `(student_id, date)`, `(section_id, academic_year_id)`, `invoice_no`, `admission_no`
- **Soft deletes** on Students, Staff, Invoices
- **Queue** PDFs, bulk SMS/email, AI calls, Excel exports

### Security
- Hash passwords (bcrypt default)
- **Encrypt PII** (medical notes, bank accounts) with `Crypt`
- Signed URLs for photos, certificates, report cards
- Rate limit login (`throttle:5,1`) and payment endpoints
- CSP headers, sanitize rich text with HTMLPurifier
- Activity log on marks, fees, role changes
- **Never expose OpenAI API key** to frontend

### Performance
- Cache academic year, classes, sections, fee structures in Redis
- Redis for sessions + cache + queue + broadcasting
- Paginate all lists
- `chunk()` / `lazy()` for bulk operations

### UX
- Optimistic UI for attendance toggling
- Skeleton loaders, not spinners
- Filters in URL query string
- Keyboard shortcuts for marks entry & attendance
- Bulk actions (promote, notify, export)
- "Last updated by X at Y" on records

---

## 11. What Makes ClassMatrix the Best

1. **Speed** — every page <500ms (Inertia + eager loading + Redis)
2. **Beautiful UI** — clean Tailwind, dark mode, mobile-first
3. **AI-Powered** — personalized study routines, not just data entry
4. **Bulk everything** — admins handle 500 actions at once
5. **Smart automation** — auto-generate invoices, auto-mark absentees, one-click promotion
6. **Real parent engagement** — push notifications for attendance, marks, fees, homework
7. **Customizable report cards** — every school's template is different
8. **Global search** — students, staff, invoices, books via Meilisearch
9. **Full audit trail** — marks, fees, role changes (schools fight over these)
10. **10-minute setup** — onboarding wizard gets schools running fast

---

## 12. Multi-Tenancy Decision

| Approach | When |
|----------|------|
| **Single school** (no tenancy) | Building for one school |
| **Single DB + `school_id`** | Few schools (~50) |
| **DB-per-tenant** (`stancl/tenancy`) | SaaS for hundreds of schools |

Decide before writing the first migration.

---

## 13. Deployment

- **Server:** Ubuntu 24.04 LTS, Nginx + PHP-FPM 8.3+
- **Queue:** Supervisor for workers + Reverb
- **CI/CD:** GitHub Actions → Forge / Coolify
- **HTTPS:** Let's Encrypt
- **Backups:** `spatie/laravel-backup` → S3 daily
- **Monitoring:** Sentry (errors), Horizon (queues)
- **Cron:** `* * * * * php artisan schedule:run`

---

## 14. Immediate Next Steps

1. Decide: single school or multi-tenant?
2. `composer require inertiajs/inertia-laravel spatie/laravel-permission tightenco/ziggy openai-php/laravel`
3. Set up roles, permissions, school settings, academic year
4. Build Phase 1 end-to-end before moving on
5. Write feature tests as you go — financials and AI especially
