<script setup lang="ts">
withDefaults(
    defineProps<{
        as?: string
        blur?: 'sm' | 'md' | 'lg' | 'xl'
    }>(),
    {
        as: 'div',
        blur: 'xl',
    },
)

const blurMap = {
    sm: 'backdrop-blur-sm',
    md: 'backdrop-blur-md',
    lg: 'backdrop-blur-lg',
    xl: 'backdrop-blur-xl',
}
</script>

<template>
    <component
        :is="as"
        :class="[
            'rounded-xl border border-white/20 bg-white/10',
            blurMap[blur],
        ]"
    >
        <slot />
    </component>
</template>
