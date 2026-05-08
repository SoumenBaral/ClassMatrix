import { ref, computed, onMounted, onUnmounted, type Ref } from 'vue'
import { usePreferredReducedMotion } from '@vueuse/core'

export interface Scene3DOptions {
    /** Max device pixel ratio — cap to 2 for perf on retina */
    maxDpr?: number
    /** Clear color for the canvas */
    clearColor?: string
    /** Pause rendering when tab is hidden */
    pauseOnHidden?: boolean
}

const defaults: Required<Scene3DOptions> = {
    maxDpr: 2,
    clearColor: '#0a1532',
    pauseOnHidden: true,
}

export function use3DScene(options: Scene3DOptions = {}) {
    const config = { ...defaults, ...options }

    const prefersReduced = usePreferredReducedMotion()
    const reducedMotion = computed(() => prefersReduced.value === 'reduce')
    const isVisible = ref(true)
    const shouldRender = ref(true)

    // Clamp DPR to maxDpr for performance
    const dpr: [number, number] = [1, config.maxDpr]

    function onVisibilityChange() {
        isVisible.value = document.visibilityState === 'visible'
        shouldRender.value = isVisible.value && !reducedMotion.value
    }

    onMounted(() => {
        if (config.pauseOnHidden) {
            document.addEventListener('visibilitychange', onVisibilityChange)
        }
    })

    onUnmounted(() => {
        if (config.pauseOnHidden) {
            document.removeEventListener('visibilitychange', onVisibilityChange)
        }
    })

    return {
        /** True if user prefers reduced motion — show gradient fallback */
        reducedMotion: reducedMotion as Ref<boolean>,
        /** Whether the tab is visible */
        isVisible,
        /** Whether the 3D scene should actively render */
        shouldRender,
        /** Clamped DPR tuple for TresCanvas :dpr prop */
        dpr,
        /** Clear color for TresCanvas */
        clearColor: config.clearColor,
    }
}
