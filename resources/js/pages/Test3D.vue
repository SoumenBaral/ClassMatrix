<script setup lang="ts">
import { defineAsyncComponent } from 'vue'
import { Head } from '@inertiajs/vue3'
import { useMagneticCard } from '@/composables/useMagneticCard'

const HeroScene = defineAsyncComponent(
    () => import('@/components/3d/HeroScene.vue'),
)

const { target: card1, cardStyle: card1Style } = useMagneticCard(10)
const { target: card2, cardStyle: card2Style } = useMagneticCard(10)
const { target: card3, cardStyle: card3Style } = useMagneticCard(10)
</script>

<template>
    <Head title="3D Test" />

    <div class="min-h-screen bg-brand-navy text-white">
        <!-- Hero 3D Scene -->
        <section class="relative h-[70vh] overflow-hidden">
            <HeroScene class="absolute inset-0" />

            <!-- Content overlay -->
            <div class="relative z-10 flex h-full flex-col items-center justify-center px-6 text-center">
                <h1 class="text-5xl font-bold text-gradient mb-4">
                    3D Pipeline Test
                </h1>
                <p class="text-lg text-white/70 max-w-md">
                    TresJS scene with floating icosahedron, reduced-motion fallback, and magnetic cards below.
                </p>
            </div>
        </section>

        <!-- Magnetic Cards Test -->
        <section class="px-8 py-16 max-w-5xl mx-auto">
            <h2 class="text-2xl font-semibold mb-8 text-center text-white/90">
                Magnetic Card Tilt
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div
                    ref="card1"
                    :style="card1Style"
                    class="glass rounded-2xl p-6 transition-transform duration-200 will-change-transform"
                >
                    <div class="w-10 h-10 rounded-lg bg-brand-blue/20 flex items-center justify-center mb-4">
                        <span class="text-brand-blue text-xl">&#x2728;</span>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">TresJS</h3>
                    <p class="text-sm text-white/60">Declarative Three.js for Vue 3 with Composition API.</p>
                </div>

                <div
                    ref="card2"
                    :style="card2Style"
                    class="glass rounded-2xl p-6 transition-transform duration-200 will-change-transform"
                >
                    <div class="w-10 h-10 rounded-lg bg-brand-cyan/20 flex items-center justify-center mb-4">
                        <span class="text-brand-cyan text-xl">&#x26A1;</span>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">GSAP</h3>
                    <p class="text-sm text-white/60">Scroll-driven animations and timeline control.</p>
                </div>

                <div
                    ref="card3"
                    :style="card3Style"
                    class="glass rounded-2xl p-6 transition-transform duration-200 will-change-transform"
                >
                    <div class="w-10 h-10 rounded-lg bg-brand-violet/20 flex items-center justify-center mb-4">
                        <span class="text-brand-violet text-xl">&#x1F3A8;</span>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Lenis</h3>
                    <p class="text-sm text-white/60">Butter-smooth scrolling synced with ScrollTrigger.</p>
                </div>
            </div>
        </section>

        <!-- Glass Panel Test -->
        <section class="px-8 py-16 max-w-3xl mx-auto text-center">
            <div class="glass rounded-2xl p-8">
                <h2 class="text-xl font-semibold mb-3">Glass Panel</h2>
                <p class="text-white/60">
                    Frosted glass effect with backdrop-blur. This is the z=0 layer in the depth model.
                </p>
                <div class="mt-6 flex justify-center gap-4">
                    <button class="px-5 py-2.5 bg-brand-blue rounded-lg font-medium glow-blue transition-all hover:scale-105">
                        Glow Blue
                    </button>
                    <button class="px-5 py-2.5 bg-brand-cyan rounded-lg font-medium glow-cyan transition-all hover:scale-105">
                        Glow Cyan
                    </button>
                    <button class="px-5 py-2.5 bg-brand-violet rounded-lg font-medium glow-violet transition-all hover:scale-105">
                        Glow Violet
                    </button>
                </div>
            </div>
        </section>
    </div>
</template>
