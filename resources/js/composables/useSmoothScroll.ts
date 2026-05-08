import { onMounted, onUnmounted } from 'vue'
import Lenis from 'lenis'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import { usePreferredReducedMotion } from '@vueuse/core'

gsap.registerPlugin(ScrollTrigger)

export function useSmoothScroll() {
    const prefersReduced = usePreferredReducedMotion()
    let lenis: Lenis | null = null

    onMounted(() => {
        // Skip smooth scroll for reduced motion preference
        if (prefersReduced.value === 'reduce') return

        lenis = new Lenis({
            lerp: 0.1,
            smoothWheel: true,
        })

        // Sync Lenis scroll position with GSAP ScrollTrigger
        lenis.on('scroll', ScrollTrigger.update)
        gsap.ticker.add((time) => lenis?.raf(time * 1000))
        gsap.ticker.lagSmoothing(0)
    })

    onUnmounted(() => {
        if (lenis) {
            lenis.destroy()
            lenis = null
        }
    })

    return {
        /** Access the Lenis instance (null if reduced motion) */
        getLenis: () => lenis,
        /** GSAP reference for creating ScrollTrigger animations */
        gsap,
        ScrollTrigger,
    }
}
