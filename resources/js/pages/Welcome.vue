<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight, BookOpen, Bot, Calendar, ClipboardCheck,
    GraduationCap, LayoutGrid, Shield, Sparkles, Users, Zap,
} from 'lucide-vue-next';
import { dashboard, login, register } from '@/routes';

withDefaults(defineProps<{ canRegister: boolean }>(), { canRegister: true });

const features = [
    { icon: GraduationCap, title: 'Student Management', desc: 'Complete profiles, admissions, enrollments and promotions', color: 'from-blue-500 to-cyan-500' },
    { icon: ClipboardCheck, title: 'Attendance System', desc: 'Daily & period-wise tracking with instant reports', color: 'from-green-500 to-emerald-500' },
    { icon: LayoutGrid, title: 'Exam & Grading', desc: 'Mark entry, auto-grading, report cards with rankings', color: 'from-purple-500 to-violet-500' },
    { icon: Bot, title: 'AI Personal Teacher', desc: 'Voice-enabled chatbot that explains, solves & quizzes', color: 'from-orange-500 to-amber-500' },
    { icon: Calendar, title: 'Smart Timetable', desc: 'Conflict detection, period management, room allocation', color: 'from-pink-500 to-rose-500' },
    { icon: Shield, title: 'Role-Based Access', desc: 'Dedicated portals for admin, teacher, student & parent', color: 'from-indigo-500 to-blue-500' },
];

const stats = [
    { value: '21+', label: 'Modules' },
    { value: '190+', label: 'Features' },
    { value: '4', label: 'User Portals' },
    { value: 'AI', label: 'Powered' },
];

const roles = [
    { icon: Shield, title: 'Admin Panel', desc: 'Full control over academics, fees, staff & settings', color: 'text-blue-500' },
    { icon: Users, title: 'Teacher Portal', desc: 'Manage classes, marks, lessons & assignments', color: 'text-green-500' },
    { icon: GraduationCap, title: 'Student Portal', desc: 'AI routines, chatbot, results & learning materials', color: 'text-purple-500' },
    { icon: Sparkles, title: 'Parent Portal', desc: 'Track attendance, results, notices & child progress', color: 'text-amber-500' },
];
</script>

<template>
    <Head title="ClassMatrix - Smart School Management">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
    </Head>

    <div class="min-h-screen bg-background">
        <!-- Navbar -->
        <nav class="fixed top-0 z-50 w-full border-b border-border/50 bg-background/80 backdrop-blur-xl">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-6">
                <Link href="/" class="flex items-center gap-3">
                    <img src="/logo.png" alt="ClassMatrix" class="size-9" />
                    <span class="text-xl font-bold text-gradient">ClassMatrix</span>
                </Link>
                <div class="flex items-center gap-3">
                    <template v-if="$page.props.auth.user">
                        <Link :href="dashboard()" class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2 text-sm font-medium text-primary-foreground transition-all hover:opacity-90 glow-blue">
                            Dashboard <ArrowRight class="size-4" />
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="login()" class="rounded-lg px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
                            Log in
                        </Link>
                        <Link v-if="canRegister" :href="register()" class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2 text-sm font-medium text-primary-foreground transition-all hover:opacity-90 glow-blue">
                            Get Started <ArrowRight class="size-4" />
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero -->
        <section class="relative overflow-hidden pt-16">
            <div class="absolute inset-0 -z-10">
                <div class="absolute top-0 left-1/4 h-96 w-96 rounded-full bg-blue-500/10 blur-3xl" />
                <div class="absolute top-20 right-1/4 h-80 w-80 rounded-full bg-cyan-500/10 blur-3xl" />
                <div class="absolute bottom-0 left-1/2 h-72 w-72 -translate-x-1/2 rounded-full bg-purple-500/5 blur-3xl" />
            </div>

            <div class="mx-auto max-w-7xl px-6 pb-20 pt-24 text-center lg:pt-32">
                <div class="mx-auto mb-8 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-4 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800 dark:bg-blue-950/50 dark:text-blue-300 animate-fade-in-up">
                    <Sparkles class="size-3.5" /> AI-Powered School Management
                </div>

                <div class="mx-auto mb-8 flex justify-center animate-fade-in-up" style="animation-delay: 100ms">
                    <img src="/logo.png" alt="ClassMatrix" class="size-28 drop-shadow-2xl lg:size-36" />
                </div>

                <h1 class="mx-auto max-w-4xl text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl animate-fade-in-up" style="animation-delay: 200ms">
                    The Smartest Way to
                    <span class="text-gradient"> Manage Your School</span>
                </h1>

                <p class="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground leading-relaxed animate-fade-in-up" style="animation-delay: 300ms">
                    ClassMatrix brings together academics, administration, communication,
                    and AI-powered learning into one beautiful, intelligent platform.
                </p>

                <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row animate-fade-in-up" style="animation-delay: 400ms">
                    <Link v-if="!$page.props.auth.user" :href="register()" class="inline-flex items-center gap-2 rounded-xl bg-primary px-8 py-3.5 text-base font-semibold text-primary-foreground shadow-lg transition-all hover:opacity-90 glow-blue">
                        Start Free <ArrowRight class="size-5" />
                    </Link>
                    <Link v-if="!$page.props.auth.user" :href="login()" class="inline-flex items-center gap-2 rounded-xl border border-border bg-background px-8 py-3.5 text-base font-semibold text-foreground shadow-sm transition-all hover:bg-accent">
                        Sign In
                    </Link>
                    <Link v-else :href="dashboard()" class="inline-flex items-center gap-2 rounded-xl bg-primary px-8 py-3.5 text-base font-semibold text-primary-foreground shadow-lg transition-all hover:opacity-90 glow-blue">
                        Go to Dashboard <ArrowRight class="size-5" />
                    </Link>
                </div>

                <div class="mx-auto mt-16 grid max-w-xl grid-cols-4 gap-8 animate-fade-in-up" style="animation-delay: 500ms">
                    <div v-for="stat in stats" :key="stat.label" class="text-center">
                        <div class="text-2xl font-extrabold text-gradient sm:text-3xl">{{ stat.value }}</div>
                        <div class="mt-1 text-xs text-muted-foreground sm:text-sm">{{ stat.label }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section class="border-t border-border/50 bg-muted/30 py-24">
            <div class="mx-auto max-w-7xl px-6">
                <div class="text-center">
                    <h2 class="text-3xl font-bold sm:text-4xl">Everything Your School Needs</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-muted-foreground">From attendance to AI tutoring, ClassMatrix covers every aspect of school management.</p>
                </div>
                <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="feature in features" :key="feature.title"
                        class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-500/5">
                        <div :class="['mb-4 inline-flex rounded-xl bg-gradient-to-br p-3 text-white', feature.color]">
                            <component :is="feature.icon" class="size-5" />
                        </div>
                        <h3 class="text-lg font-semibold">{{ feature.title }}</h3>
                        <p class="mt-2 text-sm text-muted-foreground leading-relaxed">{{ feature.desc }}</p>
                        <div class="absolute -bottom-1 -right-1 size-24 rounded-full bg-gradient-to-br opacity-0 blur-2xl transition-opacity duration-500 group-hover:opacity-10" :class="feature.color" />
                    </div>
                </div>
            </div>
        </section>

        <!-- Role Portals -->
        <section class="py-24">
            <div class="mx-auto max-w-7xl px-6">
                <div class="text-center">
                    <h2 class="text-3xl font-bold sm:text-4xl">Dedicated Portal for Everyone</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-muted-foreground">Each user gets a tailored experience designed for their specific needs.</p>
                </div>
                <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="role in roles" :key="role.title"
                        class="rounded-2xl border border-border bg-card p-6 text-center transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                        <div class="mx-auto mb-4 flex size-14 items-center justify-center rounded-2xl bg-muted">
                            <component :is="role.icon" :class="['size-7', role.color]" />
                        </div>
                        <h3 class="font-semibold">{{ role.title }}</h3>
                        <p class="mt-2 text-sm text-muted-foreground">{{ role.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- AI Section -->
        <section class="border-t border-border/50 bg-gradient-to-br from-blue-950 via-indigo-950 to-purple-950 py-24 text-white">
            <div class="mx-auto max-w-7xl px-6">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div>
                        <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-sm font-medium backdrop-blur-sm">
                            <Bot class="size-4" /> AI-Powered Learning
                        </div>
                        <h2 class="text-3xl font-bold sm:text-4xl">Your Personal <span class="text-cyan-400">AI Teacher</span></h2>
                        <p class="mt-4 text-white/70 leading-relaxed">Students get a personal AI tutor that understands their curriculum, knows their strengths and weaknesses, and adapts to their learning style.</p>
                        <ul class="mt-8 space-y-3">
                            <li v-for="item in ['Explains concepts step-by-step', 'Voice input & output — talk to your teacher', 'AI-generated weekly study routines', 'Knows your grades — focuses on weak areas']" :key="item" class="flex items-center gap-3 text-sm text-white/80">
                                <div class="flex size-6 items-center justify-center rounded-full bg-cyan-500/20"><Zap class="size-3 text-cyan-400" /></div>
                                {{ item }}
                            </li>
                        </ul>
                    </div>
                    <div class="flex justify-center">
                        <div class="relative">
                            <div class="absolute -inset-4 rounded-3xl bg-gradient-to-r from-blue-500/20 to-cyan-500/20 blur-2xl" />
                            <div class="relative rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur-sm">
                                <div class="space-y-4">
                                    <div class="flex gap-3">
                                        <div class="size-8 shrink-0 rounded-full bg-blue-500/20 p-1.5"><GraduationCap class="size-5 text-blue-400" /></div>
                                        <div class="rounded-2xl rounded-tl-md bg-white/10 px-4 py-2.5 text-sm">Can you explain photosynthesis?</div>
                                    </div>
                                    <div class="flex gap-3">
                                        <div class="size-8 shrink-0 rounded-full bg-cyan-500/20 p-1.5"><Bot class="size-5 text-cyan-400" /></div>
                                        <div class="max-w-xs rounded-2xl rounded-tl-md bg-gradient-to-br from-blue-500/20 to-cyan-500/20 px-4 py-2.5 text-sm leading-relaxed">
                                            Of course! Think of it like a recipe: <strong>CO2 + Water + Sunlight = Glucose + Oxygen</strong>. Plants use chlorophyll to capture sunlight energy!
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="py-24">
            <div class="mx-auto max-w-3xl px-6 text-center">
                <img src="/logo.png" alt="ClassMatrix" class="mx-auto mb-6 size-16" />
                <h2 class="text-3xl font-bold sm:text-4xl">Ready to Transform Your School?</h2>
                <p class="mx-auto mt-4 max-w-lg text-muted-foreground">Join ClassMatrix today and experience the future of school management.</p>
                <div class="mt-8">
                    <Link v-if="!$page.props.auth.user" :href="register()" class="inline-flex items-center gap-2 rounded-xl bg-primary px-8 py-3.5 text-base font-semibold text-primary-foreground shadow-lg transition-all hover:opacity-90 glow-blue">
                        Create Free Account <ArrowRight class="size-5" />
                    </Link>
                    <Link v-else :href="dashboard()" class="inline-flex items-center gap-2 rounded-xl bg-primary px-8 py-3.5 text-base font-semibold text-primary-foreground shadow-lg transition-all hover:opacity-90 glow-blue">
                        Go to Dashboard <ArrowRight class="size-5" />
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-border/50 bg-muted/30 py-8">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-6 sm:flex-row">
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <img src="/logo.png" alt="" class="size-5" />
                    ClassMatrix &mdash; Manage. Connect. Succeed.
                </div>
                <div class="text-xs text-muted-foreground">&copy; 2026 ClassMatrix. All rights reserved.</div>
            </div>
        </footer>
    </div>
</template>
