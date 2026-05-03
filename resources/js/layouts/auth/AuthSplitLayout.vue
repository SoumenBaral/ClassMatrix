<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BookOpen, Bot, Calendar, GraduationCap, Shield, Users, Zap } from 'lucide-vue-next';
import { home } from '@/routes';

defineProps<{ title?: string; description?: string }>();

const features = [
    { icon: GraduationCap, text: 'Complete student & staff management' },
    { icon: Calendar, text: 'Timetable, attendance & exam tracking' },
    { icon: Bot, text: 'AI-powered study routines & personal teacher' },
    { icon: Shield, text: 'Role-based access for every stakeholder' },
    { icon: Users, text: 'Parent, teacher & student portals' },
    { icon: BookOpen, text: 'Online learning with quizzes & assignments' },
];
</script>

<template>
    <div class="relative grid min-h-dvh flex-col items-center justify-center px-6 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
        <!-- Left panel -->
        <div class="relative hidden h-full flex-col justify-between overflow-hidden bg-gradient-to-br from-blue-950 via-indigo-950 to-purple-950 p-10 text-white lg:flex">
            <!-- Subtle grid pattern -->
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.3) 1px, transparent 0); background-size: 32px 32px;" />
            <!-- Glow effects -->
            <div class="absolute top-20 left-20 h-64 w-64 rounded-full bg-blue-500/20 blur-3xl" />
            <div class="absolute bottom-20 right-20 h-48 w-48 rounded-full bg-cyan-500/15 blur-3xl" />

            <Link :href="home()" class="relative z-20 flex items-center gap-3">
                <img src="/logo.png" alt="ClassMatrix" class="size-10" />
                <span class="text-xl font-bold">ClassMatrix</span>
            </Link>

            <div class="relative z-20 space-y-8">
                <div>
                    <h2 class="text-3xl font-bold leading-tight">
                        The smartest way to<br><span class="text-cyan-400">manage your school</span>
                    </h2>
                    <p class="mt-3 text-sm leading-relaxed text-white/60 max-w-md">
                        One platform for academics, administration, communication,
                        and AI-powered learning.
                    </p>
                </div>

                <div class="grid gap-2.5">
                    <div v-for="(f, i) in features" :key="i"
                        class="flex items-center gap-3 rounded-xl bg-white/5 px-4 py-2.5 backdrop-blur-sm border border-white/5">
                        <div class="flex size-7 items-center justify-center rounded-lg bg-white/10">
                            <component :is="f.icon" class="size-3.5 text-cyan-400" />
                        </div>
                        <span class="text-sm text-white/80">{{ f.text }}</span>
                    </div>
                </div>
            </div>

            <p class="relative z-20 text-xs text-white/30">Manage. Connect. Succeed.</p>
        </div>

        <!-- Right panel -->
        <div class="flex items-center justify-center py-12 lg:p-8">
            <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[420px]">
                <!-- Mobile logo -->
                <div class="flex flex-col items-center gap-3 lg:hidden">
                    <Link :href="home()" class="flex items-center gap-2">
                        <img src="/logo.png" alt="ClassMatrix" class="size-12" />
                    </Link>
                    <span class="text-lg font-bold text-gradient">ClassMatrix</span>
                </div>

                <div class="flex flex-col space-y-2 text-center">
                    <h1 class="text-2xl font-semibold tracking-tight" v-if="title">{{ title }}</h1>
                    <p class="text-sm text-muted-foreground" v-if="description">{{ description }}</p>
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>
