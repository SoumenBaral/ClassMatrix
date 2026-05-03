<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Video, FileIcon } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type Section = { id: number; name: string };
type Subject = { id: number; name: string };
type Lesson = {
    id: number; title: string; subject: string; section: string;
    video_url: string | null; published: boolean; published_at: string | null;
    materials_count: number; creator: string; order: number;
};
type PaginatedData = { data: Lesson[]; links: { url: string | null; label: string; active: boolean }[] };

const props = defineProps<{
    lessons: PaginatedData; sections: Section[]; subjects: Subject[];
    filters: { section_id?: string; subject_id?: string };
}>();

const sectionId = ref(props.filters.section_id ?? '');
const subjectId = ref(props.filters.subject_id ?? '');

watch([sectionId, subjectId], () => {
    router.get('/admin/lessons', {
        section_id: sectionId.value || undefined,
        subject_id: subjectId.value || undefined,
    }, { preserveState: true });
});

const showDialog = ref(false);
const editing = ref<Lesson | null>(null);
const form = useForm({
    title: '', section_id: '' as any, subject_id: '' as any,
    content: '', video_url: '', order: 0, published_at: '',
});

function openCreate() { editing.value = null; form.reset(); showDialog.value = true; }
function openEdit(l: Lesson) {
    editing.value = l; form.title = l.title; form.video_url = l.video_url ?? '';
    form.order = l.order; form.published_at = l.published_at ?? '';
    showDialog.value = true;
}
function submit() {
    if (editing.value) form.put(`/admin/lessons/${editing.value.id}`, { onSuccess: () => { showDialog.value = false; } });
    else form.post('/admin/lessons', { onSuccess: () => { showDialog.value = false; form.reset(); } });
}
function destroy(id: number) { if (confirm('Delete lesson?')) router.delete(`/admin/lessons/${id}`); }
</script>

<template>
    <Head title="Lessons" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Lessons" description="Create and manage lesson content." />
            <Button @click="openCreate"><Plus class="mr-2 size-4" /> New Lesson</Button>
        </div>

        <div class="flex flex-wrap items-end gap-4">
            <div>
                <Label>Section</Label>
                <select v-model="sectionId" class="flex h-9 w-56 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                    <option value="">All sections</option>
                    <option v-for="s in sections" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
            </div>
            <div>
                <Label>Subject</Label>
                <select v-model="subjectId" class="flex h-9 w-48 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                    <option value="">All subjects</option>
                    <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
            </div>
        </div>

        <div class="rounded-lg border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="px-4 py-3 text-left font-medium">#</th>
                        <th class="px-4 py-3 text-left font-medium">Title</th>
                        <th class="px-4 py-3 text-left font-medium">Subject</th>
                        <th class="px-4 py-3 text-left font-medium">Section</th>
                        <th class="px-4 py-3 text-center font-medium">Media</th>
                        <th class="px-4 py-3 text-center font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="lesson in lessons.data" :key="lesson.id" class="border-b last:border-0 hover:bg-muted/30">
                        <td class="px-4 py-2 text-muted-foreground">{{ lesson.order }}</td>
                        <td class="px-4 py-2 font-medium">{{ lesson.title }}</td>
                        <td class="px-4 py-2 text-muted-foreground">{{ lesson.subject }}</td>
                        <td class="px-4 py-2 text-muted-foreground">{{ lesson.section }}</td>
                        <td class="px-4 py-2 text-center">
                            <div class="flex justify-center gap-1">
                                <Video v-if="lesson.video_url" class="size-4 text-blue-500" />
                                <span v-if="lesson.materials_count" class="flex items-center gap-0.5 text-xs text-muted-foreground"><FileIcon class="size-3" />{{ lesson.materials_count }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <Badge :variant="lesson.published ? 'default' : 'secondary'">{{ lesson.published ? 'Published' : 'Draft' }}</Badge>
                        </td>
                        <td class="px-4 py-2 text-right">
                            <div class="flex justify-end gap-1">
                                <Button variant="ghost" size="sm" @click="openEdit(lesson)"><Pencil class="size-3" /></Button>
                                <Button variant="ghost" size="sm" class="text-destructive" @click="destroy(lesson.id)"><Trash2 class="size-3" /></Button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="lessons.links.length > 3" class="flex justify-center gap-1">
            <template v-for="link in lessons.links" :key="link.label">
                <button v-if="link.url" @click="router.get(link.url)" :class="['rounded-md px-3 py-1 text-sm', link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted']" v-html="link.label" />
            </template>
        </div>
        <div v-if="!lessons.data.length" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">No lessons found.</div>
    </div>

    <Dialog v-model:open="showDialog">
        <DialogContent class="max-w-lg"><DialogHeader><DialogTitle>{{ editing ? 'Edit' : 'Create' }} Lesson</DialogTitle></DialogHeader>
            <form @submit.prevent="submit" class="space-y-4">
                <div><Label>Title</Label><Input v-model="form.title" /><InputError :message="form.errors.title" /></div>
                <div v-if="!editing" class="grid grid-cols-2 gap-4">
                    <div><Label>Section</Label>
                        <select v-model="form.section_id" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option value="">Select...</option><option v-for="s in sections" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select><InputError :message="form.errors.section_id" />
                    </div>
                    <div><Label>Subject</Label>
                        <select v-model="form.subject_id" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option value="">Select...</option><option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select><InputError :message="form.errors.subject_id" />
                    </div>
                </div>
                <div><Label>Content</Label>
                    <textarea v-model="form.content" rows="4" class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" placeholder="Lesson content..." />
                </div>
                <div><Label>Video URL</Label><Input v-model="form.video_url" placeholder="https://youtube.com/..." /></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><Label>Order</Label><Input type="number" v-model.number="form.order" min="0" /></div>
                    <div><Label>Publish Date</Label><Input type="date" v-model="form.published_at" /></div>
                </div>
                <DialogFooter><Button type="button" variant="outline" @click="showDialog = false">Cancel</Button><Button type="submit" :disabled="form.processing">{{ editing ? 'Update' : 'Create' }}</Button></DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
