import { ref, computed, watch, type Ref } from 'vue'
import { useMouseInElement, usePreferredReducedMotion } from '@vueuse/core'

export function useMagneticCard(maxTilt = 8) {
    const target = ref<HTMLElement | null>(null)
    const prefersReduced = usePreferredReducedMotion()

    const { elementX, elementY, elementWidth, elementHeight, isOutside } =
        useMouseInElement(target as Ref<HTMLElement>)

    const tiltX = ref(0)
    const tiltY = ref(0)

    watch(
        [elementX, elementY, isOutside],
        () => {
            if (prefersReduced.value === 'reduce' || isOutside.value) {
                tiltX.value = 0
                tiltY.value = 0
                return
            }

            const px = elementX.value / elementWidth.value - 0.5
            const py = elementY.value / elementHeight.value - 0.5
            tiltX.value = -py * maxTilt
            tiltY.value = px * maxTilt
        },
        { immediate: true },
    )

    const cardStyle = computed(() => ({
        transform: `perspective(1000px) rotateX(${tiltX.value}deg) rotateY(${tiltY.value}deg)`,
    }))

    return {
        /** Ref to bind to the card element */
        target,
        tiltX,
        tiltY,
        /** Ready-to-use :style binding with perspective + rotate */
        cardStyle,
    }
}
