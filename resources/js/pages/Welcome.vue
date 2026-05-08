<script setup lang="ts">
import { defineAsyncComponent, onMounted, ref, nextTick } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import {
    ArrowRight, BookOpen, Bot, Calendar, ClipboardCheck,
    GraduationCap, LayoutGrid, Shield, Sparkles, Users, Zap,
    Mic, Volume2, Brain, Target, ChevronDown,
} from 'lucide-vue-next'
import { dashboard, login, register } from '@/routes'
import { useMagneticCard } from '@/composables/useMagneticCard'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

const LandingHeroScene = defineAsyncComponent(
    () => import('@/components/3d/LandingHeroScene.vue'),
)

withDefaults(defineProps<{ canRegister: boolean }>(), { canRegister: true })

const features = [
    { icon: GraduationCap, title: 'Student Management', desc: 'Complete profiles, admissions, enrollments and promotions', color: 'from-blue-500 to-cyan-500', glowColor: 'rgba(59, 130, 246, 0.15)' },
    { icon: ClipboardCheck, title: 'Attendance System', desc: 'Daily & period-wise tracking with instant reports', color: 'from-green-500 to-emerald-500', glowColor: 'rgba(16, 185, 129, 0.15)' },
    { icon: LayoutGrid, title: 'Exam & Grading', desc: 'Mark entry, auto-grading, report cards with rankings', color: 'from-purple-500 to-violet-500', glowColor: 'rgba(139, 92, 246, 0.15)' },
    { icon: Bot, title: 'AI Personal Teacher', desc: 'Voice-enabled chatbot that explains, solves & quizzes', color: 'from-orange-500 to-amber-500', glowColor: 'rgba(249, 115, 22, 0.15)' },
    { icon: Calendar, title: 'Smart Timetable', desc: 'Conflict detection, period management, room allocation', color: 'from-pink-500 to-rose-500', glowColor: 'rgba(236, 72, 153, 0.15)' },
    { icon: Shield, title: 'Role-Based Access', desc: 'Dedicated portals for admin, teacher, student & parent', color: 'from-indigo-500 to-blue-500', glowColor: 'rgba(99, 102, 241, 0.15)' },
]

const stats = [
    { value: '21+', label: 'Modules', icon: '📦' },
    { value: '190+', label: 'Features', icon: '⚡' },
    { value: '4', label: 'User Portals', icon: '🚪' },
    { value: 'AI', label: 'Powered', icon: '🧠' },
]

const roles = [
    { icon: Shield, title: 'Admin Panel', desc: 'Full control over academics, fees, staff & settings', gradient: 'from-blue-500 to-blue-600', bg: 'bg-blue-500/10', iconColor: 'text-blue-400', glowColor: 'rgba(59, 130, 246, 0.12)' },
    { icon: Users, title: 'Teacher Portal', desc: 'Manage classes, marks, lessons & assignments', gradient: 'from-emerald-500 to-green-600', bg: 'bg-emerald-500/10', iconColor: 'text-emerald-400', glowColor: 'rgba(16, 185, 129, 0.12)' },
    { icon: GraduationCap, title: 'Student Portal', desc: 'AI routines, chatbot, results & learning materials', gradient: 'from-violet-500 to-purple-600', bg: 'bg-violet-500/10', iconColor: 'text-violet-400', glowColor: 'rgba(139, 92, 246, 0.12)' },
    { icon: Sparkles, title: 'Parent Portal', desc: 'Track attendance, results, notices & child progress', gradient: 'from-amber-500 to-orange-600', bg: 'bg-amber-500/10', iconColor: 'text-amber-400', glowColor: 'rgba(245, 158, 11, 0.12)' },
]

const aiFeatures = [
    { icon: Brain, text: 'Explains concepts step-by-step' },
    { icon: Mic, text: 'Voice input & output — talk to your teacher' },
    { icon: Target, text: 'AI-generated weekly study routines' },
    { icon: Sparkles, text: 'Knows your grades — focuses on weak areas' },
]

// Magnetic cards
const featureCards = Array.from({ length: 6 }, () => useMagneticCard(6))
const roleCards = Array.from({ length: 4 }, () => useMagneticCard(10))

// Stat count-up
const statRefs = ref<HTMLElement[]>([])
const animatedStats = ref(stats.map(() => '0'))

function animateCountUp(_el: HTMLElement, target: string, index: number) {
    if (target === 'AI') {
        animatedStats.value[index] = 'AI'
        return
    }
    const num = parseInt(target)
    const hasPlus = target.includes('+')
    gsap.to({ val: 0 }, {
        val: num,
        duration: 2.5,
        ease: 'power2.out',
        onUpdate: function () {
            animatedStats.value[index] = Math.round(this.targets()[0].val) + (hasPlus ? '+' : '')
        },
    })
}

// Chat message animation
const chatVisible = ref(false)
const msg1Visible = ref(false)
const msg2Visible = ref(false)
const typingVisible = ref(false)

// Section heading animations
const heroLoaded = ref(false)

onMounted(async () => {
    await nextTick()
    heroLoaded.value = true

    // Feature cards — scale + rotate on entry
    gsap.fromTo('.feature-card', {
        y: 80,
        opacity: 0,
        scale: 0.9,
        rotateX: 5,
    }, {
        y: 0,
        opacity: 1,
        scale: 1,
        rotateX: 0,
        duration: 0.7,
        stagger: 0.1,
        ease: 'power3.out',
        scrollTrigger: {
            trigger: '#features-section',
            start: 'top 80%',
        },
    })

    // Section headings slide up
    gsap.fromTo('.section-heading', {
        y: 30,
        opacity: 0,
    }, {
        y: 0,
        opacity: 1,
        duration: 0.6,
        ease: 'power2.out',
        scrollTrigger: {
            trigger: '#features-section',
            start: 'top 85%',
        },
    })

    // Role cards — stagger with perspective tilt
    gsap.fromTo('.role-card', {
        y: 50,
        opacity: 0,
        rotateY: -15,
        scale: 0.92,
    }, {
        y: 0,
        opacity: 1,
        rotateY: 0,
        scale: 1,
        duration: 0.8,
        stagger: 0.12,
        ease: 'power3.out',
        scrollTrigger: {
            trigger: '#roles-section',
            start: 'top 80%',
        },
    })

    // AI section
    gsap.fromTo('#ai-text', {
        x: -80,
        opacity: 0,
    }, {
        x: 0,
        opacity: 1,
        duration: 0.9,
        ease: 'power3.out',
        scrollTrigger: {
            trigger: '#ai-section',
            start: 'top 75%',
        },
    })

    // AI chat — sequenced message reveal
    ScrollTrigger.create({
        trigger: '#ai-section',
        start: 'top 70%',
        once: true,
        onEnter: () => {
            chatVisible.value = true
            setTimeout(() => { msg1Visible.value = true }, 300)
            setTimeout(() => { typingVisible.value = true }, 900)
            setTimeout(() => { typingVisible.value = false; msg2Visible.value = true }, 1800)
        },
    })

    // Stats count-up
    ScrollTrigger.create({
        trigger: '#stats-band',
        start: 'top 85%',
        once: true,
        onEnter: () => {
            stats.forEach((stat, i) => animateCountUp(statRefs.value[i], stat.value, i))
        },
    })

    // CTA section
    gsap.fromTo('#cta-section', {
        y: 40,
        opacity: 0,
    }, {
        y: 0,
        opacity: 1,
        duration: 0.7,
        ease: 'power2.out',
        scrollTrigger: {
            trigger: '#cta-section',
            start: 'top 85%',
        },
    })
})
</script>

<template>
    <Head title="ClassMatrix - Smart School Management">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
    </Head>

    <div class="min-h-screen bg-[#050d1e] text-white overflow-x-hidden">
        <!-- ======================== NAVBAR ======================== -->
        <nav class="fixed top-0 z-50 w-full border-b border-white/[0.06] bg-[#050d1e]/60 backdrop-blur-2xl">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-6">
                <Link href="/" class="flex items-center gap-3 group">
                    <img src="/logo.png" alt="ClassMatrix" class="size-9 transition-transform duration-300 group-hover:scale-110" />
                    <span class="text-xl font-bold text-gradient">ClassMatrix</span>
                </Link>
                <div class="flex items-center gap-3">
                    <template v-if="$page.props.auth.user">
                        <Link :href="dashboard()" class="inline-flex items-center gap-2 rounded-lg bg-brand-blue px-5 py-2 text-sm font-medium text-white transition-all hover:brightness-110 hover:shadow-lg hover:shadow-brand-blue/25">
                            Dashboard <ArrowRight class="size-4" />
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="login()" class="rounded-lg px-4 py-2 text-sm font-medium text-white/60 transition-all hover:text-white hover:bg-white/5">
                            Log in
                        </Link>
                        <Link v-if="canRegister" :href="register()" class="inline-flex items-center gap-2 rounded-lg bg-brand-blue px-5 py-2 text-sm font-medium text-white transition-all hover:brightness-110 hover:shadow-lg hover:shadow-brand-blue/25">
                            Get Started <ArrowRight class="size-4" />
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- ======================== HERO ======================== -->
        <section class="relative min-h-screen overflow-hidden pt-16">
            <!-- 3D Scene -->
            <div class="absolute inset-0">
                <LandingHeroScene />
            </div>

            <!-- Drifting glow orbs -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                <div class="absolute top-[15%] left-[20%] h-[500px] w-[500px] rounded-full bg-brand-blue/[0.07] blur-[150px] animate-drift-1" />
                <div class="absolute bottom-[20%] right-[15%] h-[400px] w-[400px] rounded-full bg-brand-cyan/[0.05] blur-[130px] animate-drift-2" />
                <div class="absolute top-[50%] left-[50%] -translate-x-1/2 -translate-y-1/2 h-[350px] w-[350px] rounded-full bg-brand-violet/[0.04] blur-[120px] animate-drift-3" />
            </div>

            <!-- Vignette overlay -->
            <div class="absolute inset-0 pointer-events-none" style="background: radial-gradient(ellipse at center, transparent 50%, #050d1e 100%);" />

            <!-- Content -->
            <div class="relative z-10 mx-auto max-w-7xl px-6 pb-24 pt-28 text-center lg:pt-36">
                <!-- Badge -->
                <div :class="['mx-auto mb-8 inline-flex items-center gap-2 rounded-full border border-brand-blue/20 bg-brand-blue/[0.08] px-5 py-2 text-sm font-medium text-brand-cyan backdrop-blur-md transition-all duration-700', heroLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4']">
                    <Sparkles class="size-3.5 animate-scale-pulse" /> AI-Powered School Management
                </div>

                <!-- Logo -->
                <div :class="['mx-auto mb-8 flex justify-center transition-all duration-700 delay-100', heroLoaded ? 'opacity-100 translate-y-0 scale-100' : 'opacity-0 translate-y-6 scale-90']">
                    <div class="relative">
                        <img src="/logo.png" alt="ClassMatrix" class="size-28 drop-shadow-2xl lg:size-36 relative z-10" />
                        <!-- Logo glow -->
                        <div class="absolute inset-0 -m-4 rounded-full bg-brand-blue/15 blur-2xl animate-scale-pulse" />
                    </div>
                </div>

                <!-- Headline -->
                <h1 :class="['mx-auto max-w-4xl text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-7xl transition-all duration-700 delay-200', heroLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8']">
                    The Smartest Way to
                    <span class="text-gradient"> Manage Your School</span>
                </h1>

                <!-- Subtitle -->
                <p :class="['mx-auto mt-6 max-w-2xl text-lg text-white/45 leading-relaxed transition-all duration-700 delay-300', heroLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6']">
                    ClassMatrix brings together academics, administration, communication,
                    and AI-powered learning into one beautiful, intelligent platform.
                </p>

                <!-- CTAs -->
                <div :class="['mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row transition-all duration-700 delay-[400ms]', heroLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6']">
                    <Link v-if="!$page.props.auth.user" :href="register()" class="group inline-flex items-center gap-2 rounded-xl bg-brand-blue px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-brand-blue/25 transition-all duration-300 hover:shadow-brand-blue/40 hover:shadow-2xl hover:brightness-110 hover:-translate-y-0.5">
                        Start Free <ArrowRight class="size-5 transition-transform duration-300 group-hover:translate-x-1" />
                    </Link>
                    <Link v-if="!$page.props.auth.user" :href="login()" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/[0.04] px-8 py-3.5 text-base font-semibold text-white backdrop-blur-md transition-all duration-300 hover:bg-white/[0.08] hover:border-white/20 hover:-translate-y-0.5">
                        Sign In
                    </Link>
                    <Link v-else :href="dashboard()" class="group inline-flex items-center gap-2 rounded-xl bg-brand-blue px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-brand-blue/25 transition-all duration-300 hover:shadow-brand-blue/40 hover:shadow-2xl hover:brightness-110 hover:-translate-y-0.5">
                        Go to Dashboard <ArrowRight class="size-5 transition-transform duration-300 group-hover:translate-x-1" />
                    </Link>
                </div>

                <!-- Scroll indicator -->
                <div :class="['mt-24 transition-all duration-700 delay-[600ms]', heroLoaded ? 'opacity-30' : 'opacity-0']">
                    <div class="flex flex-col items-center gap-2">
                        <span class="text-xs uppercase tracking-widest text-white/30">Scroll to explore</span>
                        <ChevronDown class="size-5 animate-bounce" />
                    </div>
                </div>
            </div>
        </section>

        <!-- ======================== STATS BAND ======================== -->
        <section id="stats-band" class="relative border-y border-white/[0.06] bg-[#070e20]/80 backdrop-blur-2xl py-14">
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-40 w-[600px] rounded-full bg-brand-blue/[0.04] blur-[80px]" />
            </div>
            <div class="relative mx-auto grid max-w-4xl grid-cols-2 gap-8 px-6 sm:grid-cols-4">
                <div v-for="(stat, i) in stats" :key="stat.label" ref="statRefs" class="text-center group">
                    <div class="text-2xl mb-2">{{ stat.icon }}</div>
                    <div class="text-3xl font-extrabold text-gradient sm:text-4xl">{{ animatedStats[i] }}</div>
                    <div class="mt-1 text-xs text-white/35 uppercase tracking-widest sm:text-sm">{{ stat.label }}</div>
                </div>
            </div>
        </section>

        <!-- ======================== FEATURES ======================== -->
        <section id="features-section" class="relative py-32 overflow-hidden">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 h-px w-2/3 bg-linear-to-r from-transparent via-brand-blue/15 to-transparent" />
            <!-- Section background glow -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[600px] w-[600px] rounded-full bg-brand-blue/[0.03] blur-[150px] pointer-events-none" />

            <div class="relative mx-auto max-w-7xl px-6">
                <div class="section-heading text-center mb-20">
                    <div class="mx-auto mb-4 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/[0.04] px-4 py-1.5 text-xs font-medium text-white/50 uppercase tracking-widest">
                        Comprehensive Suite
                    </div>
                    <h2 class="text-3xl font-bold sm:text-5xl">Everything Your School Needs</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-white/35 text-lg">From attendance to AI tutoring, ClassMatrix covers every aspect of school management.</p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(feature, i) in features"
                        :key="feature.title"
                        :ref="(el) => { if (el) featureCards[i].target.value = (el as HTMLElement) }"
                        :style="featureCards[i].cardStyle.value"
                        class="feature-card gradient-border group relative overflow-hidden rounded-2xl border border-white/[0.06] bg-white/[0.02] p-7 transition-all duration-500 will-change-transform hover:border-white/[0.12] hover:bg-white/[0.05]"
                    >
                        <!-- Icon with glow -->
                        <div :class="['mb-5 inline-flex rounded-xl bg-linear-to-br p-3.5 text-white shadow-lg', feature.color]">
                            <component :is="feature.icon" class="size-5 icon-glow" />
                        </div>
                        <h3 class="text-lg font-semibold mb-2">{{ feature.title }}</h3>
                        <p class="text-sm text-white/35 leading-relaxed">{{ feature.desc }}</p>

                        <!-- Full-card hover glow -->
                        <div class="absolute inset-0 rounded-2xl opacity-0 transition-opacity duration-500 group-hover:opacity-100 pointer-events-none" :style="{ boxShadow: `inset 0 0 60px ${feature.glowColor}` }" />
                        <!-- Corner accent -->
                        <div :class="['absolute -bottom-4 -right-4 size-32 rounded-full bg-linear-to-br opacity-0 blur-3xl transition-opacity duration-700 group-hover:opacity-25', feature.color]" />
                    </div>
                </div>
            </div>
        </section>

        <!-- ======================== ROLE PORTALS ======================== -->
        <section id="roles-section" class="relative py-32">
            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 h-px w-2/3 bg-linear-to-r from-transparent via-brand-violet/15 to-transparent" />

            <div class="mx-auto max-w-7xl px-6">
                <div class="section-heading text-center mb-20">
                    <div class="mx-auto mb-4 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/[0.04] px-4 py-1.5 text-xs font-medium text-white/50 uppercase tracking-widest">
                        Role-Based Experience
                    </div>
                    <h2 class="text-3xl font-bold sm:text-5xl">Dedicated Portal for Everyone</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-white/35 text-lg">Each user gets a tailored experience designed for their specific needs.</p>
                </div>

                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4" style="perspective: 1200px">
                    <div
                        v-for="(role, i) in roles"
                        :key="role.title"
                        :ref="(el) => { if (el) roleCards[i].target.value = (el as HTMLElement) }"
                        :style="roleCards[i].cardStyle.value"
                        class="role-card group relative overflow-hidden rounded-2xl border border-white/[0.06] bg-white/[0.02] backdrop-blur-sm p-7 text-center transition-all duration-500 will-change-transform hover:border-white/[0.15] hover:bg-white/[0.06] hover:-translate-y-2"
                    >
                        <!-- Top gradient line -->
                        <div :class="['absolute top-0 left-0 right-0 h-0.5 bg-linear-to-r opacity-0 transition-all duration-500 group-hover:opacity-100', role.gradient]" />

                        <!-- Icon container with glow -->
                        <div class="relative mx-auto mb-5">
                            <div :class="['flex size-16 items-center justify-center rounded-2xl transition-all duration-300 group-hover:scale-110', role.bg]">
                                <component :is="role.icon" :class="['size-8 transition-all duration-300', role.iconColor]" />
                            </div>
                            <!-- Icon background glow on hover -->
                            <div class="absolute inset-0 -m-2 rounded-2xl opacity-0 transition-opacity duration-500 group-hover:opacity-100 blur-xl" :style="{ backgroundColor: role.glowColor }" />
                        </div>

                        <h3 class="font-semibold text-white text-lg mb-2">{{ role.title }}</h3>
                        <p class="text-sm text-white/35 leading-relaxed">{{ role.desc }}</p>

                        <!-- Bottom glow -->
                        <div class="absolute -bottom-6 left-1/2 -translate-x-1/2 size-32 rounded-full opacity-0 blur-3xl transition-opacity duration-700 group-hover:opacity-100" :style="{ backgroundColor: role.glowColor }" />
                    </div>
                </div>
            </div>
        </section>

        <!-- ======================== AI SHOWCASE ======================== -->
        <section id="ai-section" class="relative py-32 overflow-hidden">
            <!-- Background -->
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-1/2 left-[15%] -translate-y-1/2 h-[500px] w-[500px] rounded-full bg-brand-cyan/[0.04] blur-[150px] animate-drift-2" />
                <div class="absolute top-1/2 right-[15%] -translate-y-1/2 h-[400px] w-[400px] rounded-full bg-brand-blue/[0.04] blur-[130px] animate-drift-1" />
            </div>

            <div class="relative mx-auto max-w-7xl px-6">
                <div class="grid items-center gap-16 lg:grid-cols-2">
                    <!-- Text -->
                    <div id="ai-text">
                        <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-brand-cyan/20 bg-brand-cyan/[0.08] px-5 py-2 text-sm font-medium text-brand-cyan backdrop-blur-md">
                            <Bot class="size-4" /> AI-Powered Learning
                        </div>
                        <h2 class="text-3xl font-bold sm:text-5xl leading-tight">
                            Your Personal <br />
                            <span class="bg-linear-to-r from-brand-cyan to-brand-blue bg-clip-text text-transparent">AI Teacher</span>
                        </h2>
                        <p class="mt-5 text-white/35 leading-relaxed text-lg">
                            Students get a personal AI tutor that understands their curriculum,
                            knows their strengths and weaknesses, and adapts to their learning style.
                        </p>
                        <ul class="mt-10 space-y-4">
                            <li v-for="(item, idx) in aiFeatures" :key="item.text" class="flex items-center gap-4 text-sm text-white/55" :style="{ transitionDelay: `${idx * 100}ms` }">
                                <div class="flex size-9 items-center justify-center rounded-xl bg-brand-cyan/[0.08] border border-brand-cyan/10">
                                    <component :is="item.icon" class="size-4 text-brand-cyan" />
                                </div>
                                {{ item.text }}
                            </li>
                        </ul>
                    </div>

                    <!-- Chat mockup -->
                    <div class="flex justify-center">
                        <div :class="['relative w-full max-w-md transition-all duration-700', chatVisible ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-12']">
                            <!-- Orb glow behind -->
                            <div class="absolute -top-10 left-1/2 -translate-x-1/2 z-0">
                                <div class="relative size-20">
                                    <div class="absolute inset-0 rounded-full bg-brand-cyan/15 blur-2xl animate-scale-pulse" />
                                    <div class="absolute inset-3 rounded-full bg-brand-cyan/25 blur-lg animate-scale-pulse" style="animation-delay: 0.5s" />
                                    <div class="absolute inset-6 rounded-full bg-brand-cyan/40 blur-md" />
                                </div>
                            </div>

                            <!-- Chat card -->
                            <div class="relative rounded-2xl border border-white/[0.08] bg-white/[0.03] p-6 backdrop-blur-2xl shadow-2xl shadow-black/20">
                                <!-- Header -->
                                <div class="mb-5 flex items-center gap-3 border-b border-white/[0.06] pb-4">
                                    <div class="relative">
                                        <div class="flex size-10 items-center justify-center rounded-full bg-linear-to-br from-brand-cyan to-brand-blue">
                                            <Bot class="size-5 text-white" />
                                        </div>
                                        <div class="absolute -bottom-0.5 -right-0.5 size-3 rounded-full border-2 border-[#0a1532] bg-emerald-400" />
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold">AI Personal Teacher</div>
                                        <div class="text-xs text-emerald-400/80">Online</div>
                                    </div>
                                    <div class="ml-auto flex gap-1.5">
                                        <button class="rounded-lg bg-white/[0.04] p-2 transition-colors hover:bg-white/[0.08]">
                                            <Mic class="size-4 text-white/30" />
                                        </button>
                                        <button class="rounded-lg bg-white/[0.04] p-2 transition-colors hover:bg-white/[0.08]">
                                            <Volume2 class="size-4 text-white/30" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Messages -->
                                <div class="space-y-4 min-h-[140px]">
                                    <!-- User message -->
                                    <div v-if="msg1Visible" class="flex gap-3 animate-msg-in">
                                        <div class="size-7 shrink-0 rounded-full bg-brand-violet/15 flex items-center justify-center">
                                            <GraduationCap class="size-4 text-brand-violet" />
                                        </div>
                                        <div class="rounded-2xl rounded-tl-md bg-white/[0.06] px-4 py-2.5 text-sm text-white/65">
                                            Can you explain photosynthesis?
                                        </div>
                                    </div>

                                    <!-- Typing indicator -->
                                    <div v-if="typingVisible" class="flex gap-3 animate-msg-in">
                                        <div class="size-7 shrink-0 rounded-full bg-brand-cyan/15 flex items-center justify-center">
                                            <Bot class="size-4 text-brand-cyan" />
                                        </div>
                                        <div class="rounded-2xl rounded-tl-md bg-white/[0.04] px-4 py-3">
                                            <div class="flex gap-1">
                                                <span class="size-1.5 rounded-full bg-white/40 animate-bounce" style="animation-delay: 0ms" />
                                                <span class="size-1.5 rounded-full bg-white/40 animate-bounce" style="animation-delay: 150ms" />
                                                <span class="size-1.5 rounded-full bg-white/40 animate-bounce" style="animation-delay: 300ms" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- AI response -->
                                    <div v-if="msg2Visible" class="flex gap-3 animate-msg-in">
                                        <div class="size-7 shrink-0 rounded-full bg-brand-cyan/15 flex items-center justify-center">
                                            <Bot class="size-4 text-brand-cyan" />
                                        </div>
                                        <div class="max-w-xs rounded-2xl rounded-tl-md bg-linear-to-br from-brand-blue/[0.08] to-brand-cyan/[0.08] border border-brand-cyan/[0.08] px-4 py-2.5 text-sm text-white/65 leading-relaxed">
                                            Of course! Think of it like a recipe:
                                            <strong class="text-white/85">CO2 + Water + Sunlight = Glucose + Oxygen</strong>.
                                            Plants use chlorophyll to capture sunlight energy!
                                        </div>
                                    </div>
                                </div>

                                <!-- Input -->
                                <div class="mt-5 flex items-center gap-2 rounded-xl border border-white/[0.06] bg-white/[0.02] px-4 py-2.5">
                                    <span class="text-sm text-white/15">Ask anything...</span>
                                    <div class="ml-auto flex size-8 items-center justify-center rounded-lg bg-brand-blue transition-all hover:brightness-110">
                                        <ArrowRight class="size-4 text-white" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ======================== CTA ======================== -->
        <section id="cta-section" class="relative py-32">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[500px] w-[500px] rounded-full bg-brand-blue/[0.04] blur-[150px]" />
            </div>

            <div class="relative mx-auto max-w-3xl px-6 text-center">
                <div class="relative inline-block mb-8">
                    <img src="/logo.png" alt="ClassMatrix" class="mx-auto size-20 drop-shadow-lg relative z-10" />
                    <div class="absolute inset-0 -m-6 rounded-full bg-brand-blue/10 blur-2xl animate-scale-pulse" />
                </div>
                <h2 class="text-3xl font-bold sm:text-5xl leading-tight">Ready to Transform<br />Your School?</h2>
                <p class="mx-auto mt-5 max-w-lg text-white/35 text-lg">Join ClassMatrix today and experience the future of school management.</p>
                <div class="mt-10">
                    <Link v-if="!$page.props.auth.user" :href="register()" class="group inline-flex items-center gap-2 rounded-xl bg-brand-blue px-10 py-4 text-lg font-semibold text-white shadow-lg shadow-brand-blue/25 transition-all duration-300 hover:shadow-brand-blue/40 hover:shadow-2xl hover:brightness-110 hover:-translate-y-0.5">
                        Create Free Account <ArrowRight class="size-5 transition-transform duration-300 group-hover:translate-x-1" />
                    </Link>
                    <Link v-else :href="dashboard()" class="group inline-flex items-center gap-2 rounded-xl bg-brand-blue px-10 py-4 text-lg font-semibold text-white shadow-lg shadow-brand-blue/25 transition-all duration-300 hover:shadow-brand-blue/40 hover:shadow-2xl hover:brightness-110 hover:-translate-y-0.5">
                        Go to Dashboard <ArrowRight class="size-5 transition-transform duration-300 group-hover:translate-x-1" />
                    </Link>
                </div>
            </div>
        </section>

        <!-- ======================== FOOTER ======================== -->
        <footer class="relative border-t border-white/[0.06] bg-[#030810] py-10">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 h-px w-1/2 bg-linear-to-r from-transparent via-brand-blue/10 to-transparent" />
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-6 sm:flex-row">
                <div class="flex items-center gap-2.5 text-sm text-white/25">
                    <img src="/logo.png" alt="" class="size-5 opacity-50" />
                    ClassMatrix &mdash; Manage. Connect. Succeed.
                </div>
                <div class="text-xs text-white/15">&copy; 2026 ClassMatrix. All rights reserved.</div>
            </div>
        </footer>
    </div>
</template>
