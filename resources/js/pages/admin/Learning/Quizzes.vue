<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Trash2, Eye, ListChecks, BarChart3 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type Section = { id: number; name: string };
type Subject = { id: number; name: string };
type Quiz = {
    id: number; title: string; subject: string; section: string;
    duration_minutes: number; total_marks: number;
    questions_count: number; attempts_count: number;
    available_from: string | null; available_until: string | null;
};
type PaginatedData = { data: Quiz[]; links: { url: string | null; label: string; active: boolean }[] };

const props = defineProps<{ quizzes: PaginatedData; sections: Section[]; subjects: Subject[] }>();

const showDialog = ref(false);
const form = useForm({
    title: '', section_id: '' as any, subject_id: '' as any,
    duration_minutes: 30, available_from: '', available_until: '',
});

function openCreate() { form.reset(); form.duration_minutes = 30; showDialog.value = true; }
function submit() { form.post('/admin/quizzes', { onSuccess: () => { showDialog.value = false; form.reset(); } }); }
function destroy(id: number) { if (confirm('Delete quiz and all questions?')) router.delete(`/admin/quizzes/${id}`); }
</script>

<template>
    <Head title="Quizzes" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Quizzes" description="Create online quizzes with auto-grading." />
            <Button @click="openCreate"><Plus class="mr-2 size-4" /> New Quiz</Button>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Card v-for="quiz in quizzes.data" :key="quiz.id">
                <CardHeader class="pb-2">
                    <CardTitle class="text-base">{{ quiz.title }}</CardTitle>
                    <p class="text-xs text-muted-foreground">{{ quiz.subject }} | {{ quiz.section }}</p>
                </CardHeader>
                <CardContent class="space-y-3">
                    <div class="flex flex-wrap gap-2 text-sm">
                        <Badge variant="outline">{{ quiz.duration_minutes }} min</Badge>
                        <Badge variant="outline">{{ quiz.total_marks }} marks</Badge>
                        <Badge variant="secondary">{{ quiz.questions_count }} Q</Badge>
                        <Badge variant="secondary">{{ quiz.attempts_count }} attempts</Badge>
                    </div>
                    <div v-if="quiz.available_from" class="text-xs text-muted-foreground">
                        {{ quiz.available_from }} — {{ quiz.available_until ?? 'No end' }}
                    </div>
                    <div class="flex gap-2 pt-1">
                        <Button variant="outline" size="sm" as-child>
                            <a :href="`/admin/quizzes/${quiz.id}/questions`"><ListChecks class="mr-1 size-3" /> Questions</a>
                        </Button>
                        <Button variant="outline" size="sm" as-child>
                            <a :href="`/admin/quizzes/${quiz.id}/results`"><BarChart3 class="mr-1 size-3" /> Results</a>
                        </Button>
                        <Button variant="ghost" size="sm" class="text-destructive" @click="destroy(quiz.id)"><Trash2 class="size-3" /></Button>
                    </div>
                </CardContent>
            </Card>
        </div>
        <div v-if="!quizzes.data.length" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">No quizzes yet.</div>
    </div>

    <Dialog v-model:open="showDialog">
        <DialogContent><DialogHeader><DialogTitle>Create Quiz</DialogTitle></DialogHeader>
            <form @submit.prevent="submit" class="space-y-4">
                <div><Label>Title</Label><Input v-model="form.title" /><InputError :message="form.errors.title" /></div>
                <div class="grid grid-cols-2 gap-4">
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
                <div><Label>Duration (minutes)</Label><Input type="number" v-model.number="form.duration_minutes" min="1" /></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><Label>Available From</Label><Input type="datetime-local" v-model="form.available_from" /></div>
                    <div><Label>Available Until</Label><Input type="datetime-local" v-model="form.available_until" /></div>
                </div>
                <DialogFooter><Button type="button" variant="outline" @click="showDialog = false">Cancel</Button><Button type="submit" :disabled="form.processing">Create</Button></DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
