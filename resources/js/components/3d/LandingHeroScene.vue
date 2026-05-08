<script setup lang="ts">
import { ref, onMounted, onUnmounted, shallowRef } from 'vue'
import { TresCanvas } from '@tresjs/core'
import { Levioso } from '@tresjs/cientos'
import { use3DScene } from '@/composables/use3DScene'
import * as THREE from 'three'

const { reducedMotion, dpr, clearColor } = use3DScene({ clearColor: '#050d1e' })

// Mouse tracking for parallax
const mouseX = ref(0)
const mouseY = ref(0)
const targetRotX = ref(0)
const targetRotY = ref(0)

function onMouseMove(e: MouseEvent) {
    mouseX.value = (e.clientX / window.innerWidth - 0.5) * 2
    mouseY.value = (e.clientY / window.innerHeight - 0.5) * 2
}

onMounted(() => {
    window.addEventListener('mousemove', onMouseMove)
    rafId = requestAnimationFrame(animate)
})
onUnmounted(() => {
    window.removeEventListener('mousemove', onMouseMove)
    cancelAnimationFrame(rafId)
})

// Subject nodes — Knowledge Constellation
const subjectNodes = [
    { pos: [0, 0, 0] as [number, number, number], color: '#3b82f6', size: 0.4, label: 'Core' },
    { pos: [2.4, 1.3, -0.5] as [number, number, number], color: '#06b6d4', size: 0.24, label: 'Math' },
    { pos: [-2.2, 1.6, 0.3] as [number, number, number], color: '#8b5cf6', size: 0.24, label: 'Science' },
    { pos: [1.6, -1.5, 0.8] as [number, number, number], color: '#f59e0b', size: 0.22, label: 'English' },
    { pos: [-1.9, -1.1, -0.6] as [number, number, number], color: '#10b981', size: 0.22, label: 'History' },
    { pos: [0.4, 2.4, -0.3] as [number, number, number], color: '#ec4899', size: 0.2, label: 'Art' },
    { pos: [-0.6, -2.5, 0.4] as [number, number, number], color: '#f97316', size: 0.2, label: 'Music' },
    { pos: [3.0, -0.4, -0.2] as [number, number, number], color: '#14b8a6', size: 0.22, label: 'PE' },
    { pos: [-3.0, 0.3, 0.6] as [number, number, number], color: '#a855f7', size: 0.22, label: 'Tech' },
]

// Connections (index pairs)
const connections = [
    [0, 1], [0, 2], [0, 3], [0, 4], [0, 5], [0, 6], [0, 7], [0, 8],
    [1, 5], [2, 8], [3, 7], [4, 6], [1, 3], [2, 4], [5, 6], [7, 3], [8, 4],
]

function buildLineGeometry(from: [number, number, number], to: [number, number, number]) {
    const geometry = new THREE.BufferGeometry()
    const positions = new Float32Array([...from, ...to])
    geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3))
    return geometry
}

const lineGeometries = connections.map(([a, b]) =>
    buildLineGeometry(subjectNodes[a].pos, subjectNodes[b].pos),
)

// Constellation group ref
const constellationRef = shallowRef<THREE.Group | null>(null)

// Center node pulse ref
const centerNodeRef = shallowRef<THREE.Mesh | null>(null)
let pulsePhase = 0

// Floating particles — more and varied
const particleCount = 150
const particlePositions = new Float32Array(particleCount * 3)
const particleSizes = new Float32Array(particleCount)
for (let i = 0; i < particleCount; i++) {
    particlePositions[i * 3] = (Math.random() - 0.5) * 16
    particlePositions[i * 3 + 1] = (Math.random() - 0.5) * 12
    particlePositions[i * 3 + 2] = (Math.random() - 0.5) * 10
    particleSizes[i] = Math.random() * 0.04 + 0.01
}
const particleGeometry = new THREE.BufferGeometry()
particleGeometry.setAttribute('position', new THREE.BufferAttribute(particlePositions, 3))

// Animation loop
let rafId = 0
let time = 0
function animate() {
    time += 0.016
    pulsePhase += 0.02

    // Smooth mouse follow
    targetRotY.value += (mouseX.value * 0.3 - targetRotY.value) * 0.04
    targetRotX.value += (mouseY.value * 0.2 - targetRotX.value) * 0.04
    if (constellationRef.value) {
        constellationRef.value.rotation.y = targetRotY.value
        constellationRef.value.rotation.x = targetRotX.value
    }

    // Center node breathing pulse
    if (centerNodeRef.value) {
        const scale = 1 + Math.sin(pulsePhase) * 0.08
        centerNodeRef.value.scale.set(scale, scale, scale)
    }

    rafId = requestAnimationFrame(animate)
}
</script>

<template>
    <!-- Gradient fallback for reduced motion -->
    <div
        v-if="reducedMotion"
        class="h-full w-full bg-linear-to-br from-[#050d1e] via-[#0a1e3d] to-[#050d1e]"
    >
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-[20%] left-[30%] size-4 rounded-full bg-brand-blue/30 blur-sm" />
            <div class="absolute top-[35%] right-[25%] size-3 rounded-full bg-brand-cyan/30 blur-sm" />
            <div class="absolute bottom-[30%] left-[20%] size-3.5 rounded-full bg-brand-violet/30 blur-sm" />
            <div class="absolute top-[60%] right-[35%] size-2.5 rounded-full bg-amber-400/20 blur-sm" />
            <div class="absolute bottom-[25%] left-[50%] size-4 rounded-full bg-brand-blue/20 blur-sm" />
            <div class="absolute top-[45%] left-[45%] size-5 rounded-full bg-brand-blue/15 blur-md" />
        </div>
    </div>

    <TresCanvas
        v-else
        :clear-color="clearColor"
        :dpr="dpr"
        window-size
        alpha
        class="!absolute inset-0"
    >
        <TresPerspectiveCamera :position="[0, 0, 7]" :fov="50" />

        <!-- Lighting — richer -->
        <TresAmbientLight :intensity="0.3" />
        <TresDirectionalLight :position="[5, 5, 5]" :intensity="0.9" color="#3b82f6" />
        <TresDirectionalLight :position="[-5, 3, -5]" :intensity="0.5" color="#06b6d4" />
        <TresPointLight :position="[0, 0, 4]" :intensity="0.8" color="#3b82f6" :distance="12" />
        <TresPointLight :position="[3, 2, 2]" :intensity="0.3" color="#8b5cf6" :distance="8" />
        <TresPointLight :position="[-3, -1, 2]" :intensity="0.3" color="#06b6d4" :distance="8" />

        <!-- Constellation group -->
        <TresGroup ref="constellationRef">
            <!-- Connection lines with varied opacity -->
            <TresLineSegments
                v-for="(geo, i) in lineGeometries"
                :key="'line-' + i"
                :geometry="geo"
            >
                <TresLineBasicMaterial
                    :color="i < 8 ? '#3b82f6' : '#6b9fff'"
                    :transparent="true"
                    :opacity="i < 8 ? 0.2 : 0.08"
                />
            </TresLineSegments>

            <!-- Center node — pulsing -->
            <TresMesh ref="centerNodeRef" :position="[0, 0, 0]">
                <TresIcosahedronGeometry :args="[0.4, 2]" />
                <TresMeshStandardMaterial
                    color="#3b82f6"
                    :metalness="0.7"
                    :roughness="0.15"
                    emissive="#3b82f6"
                    :emissive-intensity="0.5"
                />
            </TresMesh>
            <!-- Center glow -->
            <TresMesh :position="[0, 0, 0]">
                <TresSphereGeometry :args="[1.0, 16, 16]" />
                <TresMeshBasicMaterial color="#3b82f6" :transparent="true" :opacity="0.04" />
            </TresMesh>
            <TresMesh :position="[0, 0, 0]">
                <TresSphereGeometry :args="[0.65, 16, 16]" />
                <TresMeshBasicMaterial color="#3b82f6" :transparent="true" :opacity="0.06" />
            </TresMesh>

            <!-- Outer subject nodes -->
            <Levioso
                v-for="(node, i) in subjectNodes.slice(1)"
                :key="'node-' + i"
                :speed="1.0 + i * 0.12"
                :rotation-intensity="0.15"
                :float-intensity="0.3 + i * 0.06"
            >
                <TresMesh :position="node.pos">
                    <TresIcosahedronGeometry :args="[node.size, 2]" />
                    <TresMeshStandardMaterial
                        :color="node.color"
                        :metalness="0.6"
                        :roughness="0.2"
                        :emissive="node.color"
                        :emissive-intensity="0.4"
                    />
                </TresMesh>
                <!-- Per-node glow halo -->
                <TresMesh :position="node.pos">
                    <TresSphereGeometry :args="[node.size * 2.2, 12, 12]" />
                    <TresMeshBasicMaterial :color="node.color" :transparent="true" :opacity="0.05" />
                </TresMesh>
            </Levioso>
        </TresGroup>

        <!-- Floating particles — denser -->
        <TresPoints :geometry="particleGeometry">
            <TresPointsMaterial
                color="#6b9fff"
                :size="0.025"
                :transparent="true"
                :opacity="0.45"
                :size-attenuation="true"
            />
        </TresPoints>

        <!-- Faint secondary particle layer — larger, dimmer, depth feel -->
        <TresPoints :geometry="particleGeometry">
            <TresPointsMaterial
                color="#a78bfa"
                :size="0.05"
                :transparent="true"
                :opacity="0.12"
                :size-attenuation="true"
            />
        </TresPoints>
    </TresCanvas>
</template>
