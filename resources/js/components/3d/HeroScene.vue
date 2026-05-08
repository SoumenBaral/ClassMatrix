<script setup lang="ts">
import { TresCanvas } from '@tresjs/core'
import { Levioso, ContactShadows } from '@tresjs/cientos'
import { use3DScene } from '@/composables/use3DScene'

const { reducedMotion, dpr, clearColor } = use3DScene()
</script>

<template>
    <!-- Gradient fallback for reduced motion / no WebGL -->
    <div
        v-if="reducedMotion"
        class="h-full w-full bg-linear-to-br from-brand-navy via-[#1e3a5f] to-brand-navy"
    />

    <TresCanvas
        v-else
        :clear-color="clearColor"
        :dpr="dpr"
        window-size
        alpha
    >
        <TresPerspectiveCamera :position="[0, 0, 8]" />
        <TresAmbientLight :intensity="0.5" />
        <TresDirectionalLight :position="[5, 5, 5]" :intensity="1" />

        <Levioso :speed="2" :rotation-intensity="0.5" :float-intensity="1.2">
            <!-- Placeholder: swap with campus model in Phase 2 -->
            <TresMesh>
                <TresIcosahedronGeometry :args="[2, 1]" />
                <TresMeshStandardMaterial
                    color="#3b82f6"
                    :metalness="0.7"
                    :roughness="0.2"
                />
            </TresMesh>
        </Levioso>

        <ContactShadows
            :opacity="0.4"
            :blur="2"
            :position-y="-2.5"
        />
    </TresCanvas>
</template>
