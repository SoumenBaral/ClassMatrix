---
name: classmatrix-3d-frontend
description: Polish and perfect the ClassMatrix frontend — an AI-powered school management system on Laravel 13 + Inertia v3 + Vue 3 + Tailwind v4. Use this skill for any UI polish, landing page improvements, dashboard enhancements, 3D treatments, animations, or visual refinements. NO new models or migrations — only improve what already exists.
---

# ClassMatrix Frontend — Polish & Perfect

This skill guide is about making the EXISTING ClassMatrix features look and feel premium. We do NOT add new models, migrations, or backend features. We only polish, animate, and visually upgrade what's already built.

---

## Golden Rule

**Polish what exists. Don't add what doesn't.**

- NO new database tables or migrations
- NO new Eloquent models
- NO new backend features or API endpoints (unless purely for existing data display)
- YES to visual upgrades, animations, 3D treatments, better layouts
- YES to improving UX flow, adding micro-interactions, fixing responsive issues
- YES to making the landing page a showstopper

---

## Core Philosophy

**3D for emotion. 2D for work.**

| Use 3D / heavy animation | Keep clean & fast |
|---|---|
| Landing page (the showstopper) | Attendance, Marks entry |
| Auth (login/register) | Invoice tables, Forms |
| Dashboard welcome banners | Data grids, Reports |
| AI Chat avatar | Daily teacher workflow |
| Empty states / 404 | Settings pages |
| Quiz completion / Results reveal | Timetable grid |

If a student uses a page 50 times a day, it stays clean. If it's marketing or first-impression, go all in.

**Never violate:**
- Heavy 3D on a daily-use work page is a regression, not an upgrade.
- Always ship a 2D fallback for `prefers-reduced-motion`.
- Mobile mid-range Android is the real test device, not a MacBook.

---

## What Already Exists (DO NOT recreate)

### Backend (64 models, 190+ routes — all done)
- Full admin panel (19 controllers, 42 pages)
- Student panel (7 controllers: Dashboard, Timetable, Assignments, Quizzes, Lessons, Results, Attendance, AI Chat, Routines)
- Parent panel (Dashboard, Children, Attendance, Results, Notices)
- Teacher panel (Dashboard — needs page expansion, not model changes)
- Auth system with role-based registration & login

### 3D Foundation (Phase 1 — done)
- `@tresjs/core`, `@tresjs/cientos`, `three`, `gsap`, `lenis`, `@vueuse/motion`, `lottie-web` installed
- `use3DScene.ts` — TresJS wrapper with DPR cap, tab-visibility pause, reduced-motion fallback
- `useSmoothScroll.ts` — Lenis + GSAP ScrollTrigger sync
- `useMagneticCard.ts` — mouse-follow tilt with computed `cardStyle`
- `components/3d/HeroScene.vue` — skeleton scene
- `components/3d/LandingHeroScene.vue` — knowledge constellation (9 nodes, connections, particles, mouse-reactive)
- `components/3d/primitives/FloatingGlow.vue` — reusable glow sphere
- `components/ui/MagneticCard.vue`, `GlassPanel.vue`
- Brand tokens: `brand-navy`, `brand-blue`, `brand-cyan`, `brand-violet`, `glow-*`
- Reduced-motion media query, `.glass`, `.glow-*` utilities

### Landing Page (Phase 2 — done, needs polish)
- Dark immersive theme (`#050d1e` background)
- 3D knowledge constellation hero with mouse parallax
- GSAP scroll-driven animations (feature cards, role cards, AI section)
- Stats band with count-up animation
- Magnetic tilt on feature cards and role portal cards
- AI showcase with mock chat + pulsing orb
- Glass navbar with backdrop blur

---

## Polish Priorities (what to work on next)

### Priority 1 — Landing Page: Make It a Showstopper

The landing page is built but needs to go from "good" to "jaw-dropping":

**Hero Section Upgrades:**
- Add smooth scroll-driven camera movement in the 3D constellation (GSAP ScrollTrigger scrubbing camera position as user scrolls)
- Add a subtle animated gradient mesh behind the 3D scene (CSS `conic-gradient` with `@property` animated angle — pure CSS, no WebGL cost)
- Improve glow orbs — make them drift slowly with CSS keyframe animation instead of static pulse
- Add a typing effect on the headline for first-time visitors
- Smooth parallax on the logo as user scrolls

**Feature Section Upgrades:**
- Stagger animation should feel more dramatic — cards should scale from 0.9 + rotate slightly on entry
- Add gradient border on hover (not just corner glow)
- Each card icon should have a subtle pulse glow matching its gradient color

**Role Portal Section:**
- Cards should have a glass effect (frosted backdrop-blur) not just border
- On hover: card lifts higher + slight rotation + background glow intensifies
- Consider adding a small animated icon per role (Lottie or CSS animation)

**AI Showcase Section:**
- The pulsing orb should be a proper 3D TresJS element (concentric translucent spheres with Levioso float)
- Chat mockup messages should animate in one-by-one on scroll into view
- Add a subtle typing indicator animation on the AI response

**Footer:**
- Add subtle gradient line above footer
- Consider constellation-style dot pattern as background

### Priority 2 — Student Dashboard Banner

Replace the current CSS gradient banner in `Dashboard.vue` with a lazy-loaded 3D scene:

```
Component: resources/js/components/3d/StudentDashboardBanner.vue
Height: 300-350px

Scene:
├── 3-5 floating books (Levioso, colors from enrolled subjects)
├── 1 atom structure (3 orbital TorusGeometry rings + SphereGeometry nucleus)
├── 2-3 math symbols (extruded ∑, π)
├── Ambient particles (60 points, soft blue)
└── Mouse-reactive group rotation

Fallback: Gradient banner with CSS floating colored dots
Load: defineAsyncComponent, <1MB, cap DPR to 2
```

Stat cards below get `useMagneticCard` tilt on hover.

### Priority 3 — Microinteractions Pass (All Panels)

Apply across every page without adding 3D scenes:

- **Card hover tilt** — `useMagneticCard` on every clickable card
- **Button depth** — shadow + translateY on press, glow on hover
- **Page transitions** — GSAP fade/slide with ~300ms via Inertia events
- **Sidebar items** — subtle x-translate + accent bar slide on hover
- **Modal entry** — scale from 0.96 + backdrop blur ramp
- **Form fields** — focus ring with glow animation
- **Skeleton screens** — shimmer gradient instead of plain pulse
- **Toast notifications** — slide from edge with soft drop shadow

### Priority 4 — AI Chat Avatar

Build `resources/js/components/3d/AIAvatar.vue`:
- Pulsing orb — concentric translucent spheres, slowly rotating
- **Idle:** gentle breathing pulse (scale 1 ↔ 1.05, 4s)
- **Listening:** faster pulse + cyan glow ramp
- **Speaking:** sphere distorts subtly to audio amplitude via `AnalyserNode`
- **Error:** red tint + slower pulse
- Place in chat header, lazy-loaded

### Priority 5 — Celebration Moments

- **Quiz completion:** confetti burst + score reveal animation (`canvas-confetti` library — tiny, not Three.js)
- **Results page:** graduation cap CSS 3D float-in on load, confetti on high scores
- **Empty states:** friendly illustrations + CTA text (SVG for frequent pages, small TresJS mesh for rare pages like Library)

### Priority 6 — Auth Flow Visual Upgrade

- **Login page:** Replace static gradient panel with slow-rotating low-poly scene + ambient particles (reuse LandingHeroScene at lower intensity)
- **Register role selection:** 2 tilted glass cards with hover lift + rotate
- **Forms stay 2D and fast** — 3D is decoration around forms, not in them

---

## Visual Identity

**Brand tokens** (already in `app.css @theme`):
```css
--color-brand-navy: #0a1532;
--color-brand-blue: #3b82f6;
--color-brand-cyan: #06b6d4;
--color-brand-violet: #8b5cf6;
--color-glow-blue: rgba(59, 130, 246, 0.5);
--color-glow-cyan: rgba(6, 182, 212, 0.4);
```

**Depth layer model** (every immersive page):
1. **Background (z=-1):** 3D scene or gradient mesh
2. **Glass (z=0):** frosted cards (`backdrop-blur-xl bg-white/10 border border-white/20`)
3. **Content (z=1):** text, forms, primary actions
4. **Accent (z=2):** glow orbs, micro-interactions

**Chosen 3D motif:** Knowledge constellation (connected subject nodes) — used on landing hero. Reuse across auth and dashboard banners for cohesion. Do NOT introduce a different 3D world per page.

---

## Student Page Polish Treatment

These pages are BUILT. Polish only — better animations, hover effects, visual refinements.

| Page | What to Polish |
|------|---------------|
| **Dashboard** | Add 3D banner (Priority 2), magnetic stat cards, stagger animations on quick actions |
| **Timetable** | Current day/period glow effect, smooth tab transitions on mobile |
| **Assignments** | Magnetic card tilt, smoother filter tab transitions, badge micro-animations |
| **Assignment Detail** | Slide-in animation for sidebar, progress bar animation on grade reveal |
| **Quizzes** | Card hover effects, status badge pulse for "available" quizzes |
| **Quiz Taking** | Timer glow on urgency, smooth question transitions, progress bar animation |
| **Quiz Result** | Confetti on good scores, score counter animation, answer review slide-in |
| **Lessons** | Subject tab transitions, card hover with color banner expansion |
| **Lesson Detail** | Video player loading state, material cards hover effect |
| **Results** | Exam tab transitions, progress bar fill animations, grade color pop |
| **Attendance** | Calendar cell hover tooltip, month navigation slide transition |
| **AI Chat** | 3D avatar (Priority 4), message slide-in animations, typing indicator |
| **Routines** | Time block tilt (CSS 3D transforms), goal/tip card hover effects |

---

## Performance Rules (non-negotiable)

- Hero scene total assets under **2MB**
- Cap pixel ratio: `dpr={[1, 2]}` on every TresCanvas
- Pause rendering when tab hidden — `document.visibilityState`
- Lazy-load ALL 3D components: `defineAsyncComponent(() => import(...))`
- Honor `prefers-reduced-motion` — gradient fallback, never force animation
- First Contentful Paint under 2s on landing page
- 60fps on mid-range hardware; below 45fps → simplify or swap for CSS/Lottie

---

## Anti-Patterns

- ❌ Adding new database tables or models
- ❌ 3D scene on daily-work pages (attendance, timetable, marks entry)
- ❌ Different 3D motif on every page (breaks identity)
- ❌ WebGL in modal dialogs
- ❌ Skipping the reduced-motion fallback
- ❌ Heavy parallax on long forms
- ❌ Adding features that need new backend endpoints beyond displaying existing data
- ❌ "Improving" by adding complexity — simpler is better if it looks better

---

## Working Style

When polishing:

1. **Read the existing page first** — understand what's there before changing anything
2. **Polish incrementally** — one section at a time, verify it looks right
3. **Test the reduced-motion fallback** — every change
4. **Mobile check** — every layout change
5. **Don't break functionality** — visual-only changes, never alter data flow
6. **Reuse composables** — `useMagneticCard`, `use3DScene`, `useSmoothScroll` are ready to use
7. **Keep bundle small** — if a visual improvement adds >50KB, it better be worth it
