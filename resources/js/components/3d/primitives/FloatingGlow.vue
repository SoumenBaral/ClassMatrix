<script setup lang="ts">
import { ref } from 'vue'
import { Levioso } from '@tresjs/cientos'

withDefaults(
    defineProps<{
        color?: string
        size?: number
        speed?: number
        intensity?: number
    }>(),
    {
        color: '#3b82f6',
        size: 0.5,
        speed: 1.5,
        intensity: 0.8,
    },
)
</script>

<template>
    <Levioso :speed="speed" :float-intensity="intensity">
        <TresMesh>
            <TresSphereGeometry :args="[size, 16, 16]" />
            <TresMeshBasicMaterial
                :color="color"
                :transparent="true"
                :opacity="0.3"
            />
        </TresMesh>
        <!-- Outer glow layer -->
        <TresMesh>
            <TresSphereGeometry :args="[size * 1.8, 16, 16]" />
            <TresMeshBasicMaterial
                :color="color"
                :transparent="true"
                :opacity="0.08"
            />
        </TresMesh>
    </Levioso>
</template>
