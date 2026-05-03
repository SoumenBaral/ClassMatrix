<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Eye } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type Notice = {
    id: number; title: string; body: string; target: string;
    published_at: string | null; expires_at: string | null;
    is_published: boolean; creator: string; created_at: string;
};

type PaginatedData = {
    data: Notice[];
    links: { url: string | null; label: string; active: boolean }[];
};

const props = defineProps<{ notices: PaginatedData }>();

const showDialog = ref(false);
const editing = ref<Notice | null>(null);
const showViewDialog = ref(false);
const viewing = ref<Notice | null>(null);

const form = useForm({
    title: '',
    body: '',
    target: 'all',
    published_at: '',
    expires_at: '',
});

const targets = [
    { value: 'all', label: 'Everyone' },
    { value: 'students', label: 'Students' },
    { value: 'teachers', label: 'Teachers' },
    { value: 'parents', label: 'Parents' },
];

function openCreate() {
    editing.value = null;
    form.reset();
    showDialog.value = true;
}

function openEdit(notice: Notice) {
    editing.value = notice;
    form.title = notice.title;
    form.body = notice.body;
    form.target = notice.target;
    form.published_at = notice.published_at ?? '';
    form.expires_at = notice.expires_at ?? '';
    showDialog.value = true;
}

function submit() {
    if (editing.value) {
        form.put(`/admin/notices/${editing.value.id}`, {
            onSuccess: () => { showDialog.value = false; },
        });
    } else {
        form.post('/admin/notices', {
            onSuccess: () => { showDialog.value = false; form.reset(); },
        });
    }
}

function destroy(notice: Notice) {
    if (confirm(`Delete "${notice.title}"?`)) {
        router.delete(`/admin/notices/${notice.id}`);
    }
}

function view(notice: Notice) {
    viewing.value = notice;
    showViewDialog.value = true;
}
</script>

<template>
    <Head title="Notices" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Notices" description="Create and manage school announcements." />
            <Button @click="openCreate">
                <Plus class="mr-2 size-4" /> New Notice
            </Button>
        </div>

        <div class="space-y-3">
            <Card v-for="notice in notices.data" :key="notice.id">
                <CardHeader class="flex flex-row items-start justify-between pb-2">
                    <div class="space-y-1">
                        <CardTitle class="text-base">{{ notice.title }}</CardTitle>
                        <div class="flex items-center gap-2 text-xs text-muted-foreground">
                            <span>By {{ notice.creator }}</span>
                            <span>{{ notice.created_at }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Badge :variant="notice.is_published ? 'default' : 'secondary'">
                            {{ notice.is_published ? 'Published' : 'Draft' }}
                        </Badge>
                        <Badge variant="outline" class="capitalize">{{ notice.target }}</Badge>
                    </div>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground line-clamp-2">{{ notice.body }}</p>
                    <div class="mt-3 flex gap-2">
                        <Button variant="outline" size="sm" @click="view(notice)">
                            <Eye class="mr-1 size-3" /> View
                        </Button>
                        <Button variant="outline" size="sm" @click="openEdit(notice)">
                            <Pencil class="mr-1 size-3" /> Edit
                        </Button>
                        <Button variant="outline" size="sm" class="text-destructive" @click="destroy(notice)">
                            <Trash2 class="mr-1 size-3" /> Delete
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Pagination -->
        <div v-if="notices.links.length > 3" class="flex justify-center gap-1">
            <template v-for="link in notices.links" :key="link.label">
                <button
                    v-if="link.url"
                    @click="router.get(link.url)"
                    :class="['rounded-md px-3 py-1 text-sm', link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted']"
                    v-html="link.label"
                />
            </template>
        </div>

        <div v-if="!notices.data.length" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">
            No notices yet. Create your first notice.
        </div>
    </div>

    <!-- Create/Edit Dialog -->
    <Dialog v-model:open="showDialog">
        <DialogContent class="max-w-lg">
            <DialogHeader>
                <DialogTitle>{{ editing ? 'Edit' : 'Create' }} Notice</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <Label>Title</Label>
                    <Input v-model="form.title" placeholder="Notice title" />
                    <InputError :message="form.errors.title" />
                </div>
                <div>
                    <Label>Content</Label>
                    <textarea v-model="form.body" rows="5" placeholder="Write your notice..."
                        class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" />
                    <InputError :message="form.errors.body" />
                </div>
                <div>
                    <Label>Audience</Label>
                    <select v-model="form.target"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                        <option v-for="t in targets" :key="t.value" :value="t.value">{{ t.label }}</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <Label>Publish At</Label>
                        <Input type="datetime-local" v-model="form.published_at" />
                    </div>
                    <div>
                        <Label>Expires At</Label>
                        <Input type="datetime-local" v-model="form.expires_at" />
                    </div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showDialog = false">Cancel</Button>
                    <Button type="submit" :disabled="form.processing">{{ editing ? 'Update' : 'Publish' }}</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- View Dialog -->
    <Dialog v-model:open="showViewDialog">
        <DialogContent class="max-w-lg">
            <DialogHeader>
                <DialogTitle>{{ viewing?.title }}</DialogTitle>
            </DialogHeader>
            <div class="space-y-3">
                <div class="flex gap-2 text-xs text-muted-foreground">
                    <span>By {{ viewing?.creator }}</span>
                    <span>{{ viewing?.created_at }}</span>
                    <Badge variant="outline" class="capitalize">{{ viewing?.target }}</Badge>
                </div>
                <div class="whitespace-pre-wrap text-sm">{{ viewing?.body }}</div>
            </div>
        </DialogContent>
    </Dialog>
</template>
