<script setup lang="ts">
import { useMagneticCard } from '@/composables/useMagneticCard'

const props = withDefaults(
    defineProps<{
        maxTilt?: number
    }>(),
    { maxTilt: 8 },
)

const { target, cardStyle } = useMagneticCard(props.maxTilt)
</script>

<template>
    <div
        ref="target"
        :style="cardStyle"
        class="transition-transform duration-200 will-change-transform"
    >
        <slot />
    </div>
</template>
