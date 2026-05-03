<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Bell } from 'lucide-vue-next';
import { ref } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';

type Notice = { id: number; title: string; body: string; published_at: string; creator: string };

const props = defineProps<{ notices: Notice[] }>();

const showNotice = ref(false);
const viewing = ref<Notice | null>(null);

function openNotice(notice: Notice) {
    viewing.value = notice;
    showNotice.value = true;
}
</script>

<template>
    <Head title="Notices" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <Heading title="School Notices" description="Announcements and updates from school." />

        <div v-if="notices.length" class="space-y-3">
            <Card v-for="notice in notices" :key="notice.id"
                class="cursor-pointer hover:bg-accent/50 transition-colors"
                @click="openNotice(notice)">
                <CardHeader class="pb-2">
                    <div class="flex items-start justify-between">
                        <CardTitle class="text-base">{{ notice.title }}</CardTitle>
                        <span class="text-xs text-muted-foreground shrink-0 ml-4">{{ notice.published_at }}</span>
                    </div>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground line-clamp-2">{{ notice.body }}</p>
                    <p class="text-xs text-muted-foreground mt-2">By {{ notice.creator }}</p>
                </CardContent>
            </Card>
        </div>

        <div v-else class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed p-12 text-center">
            <Bell class="size-12 text-muted-foreground/20 mb-4" />
            <p class="text-muted-foreground">No notices at this time.</p>
        </div>
    </div>

    <Dialog v-model:open="showNotice">
        <DialogContent class="max-w-lg">
            <DialogHeader>
                <DialogTitle>{{ viewing?.title }}</DialogTitle>
            </DialogHeader>
            <div class="space-y-3">
                <div class="flex gap-2 text-xs text-muted-foreground">
                    <span>{{ viewing?.published_at }}</span>
                    <span>By {{ viewing?.creator }}</span>
                </div>
                <div class="whitespace-pre-wrap text-sm leading-relaxed">{{ viewing?.body }}</div>
            </div>
        </DialogContent>
    </Dialog>
</template>
