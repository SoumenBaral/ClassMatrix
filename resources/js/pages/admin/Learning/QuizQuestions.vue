<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type Option = { id: number; text: string; is_correct: boolean };
type Question = { id: number; question: string; type: string; marks: number; order: number; options: Option[] };
type QuizInfo = { id: number; title: string; total_marks: number };

const props = defineProps<{ quiz: QuizInfo; questions: Question[] }>();

const showDialog = ref(false);
const form = useForm({
    question: '', type: 'mcq', marks: 1, order: 0,
    options: [
        { text: '', is_correct: false },
        { text: '', is_correct: false },
        { text: '', is_correct: false },
        { text: '', is_correct: false },
    ] as { text: string; is_correct: boolean }[],
});

function openCreate() {
    form.reset();
    form.marks = 1;
    form.order = props.questions.length;
    form.options = [
        { text: '', is_correct: false },
        { text: '', is_correct: false },
        { text: '', is_correct: false },
        { text: '', is_correct: false },
    ];
    showDialog.value = true;
}

function addOption() { form.options.push({ text: '', is_correct: false }); }
function removeOption(idx: number) { form.options.splice(idx, 1); }

function submit() {
    form.post(`/admin/quizzes/${props.quiz.id}/questions`, {
        onSuccess: () => { showDialog.value = false; form.reset(); },
    });
}

function destroy(id: number) { if (confirm('Delete question?')) router.delete(`/admin/quiz-questions/${id}`); }

const typeLabels: Record<string, string> = { mcq: 'MCQ', short: 'Short Answer', true_false: 'True/False' };
</script>

<template>
    <Head :title="`Questions - ${quiz.title}`" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center gap-3">
            <Button variant="ghost" size="sm" as-child><a href="/admin/quizzes"><ArrowLeft class="size-4" /></a></Button>
            <Heading :title="quiz.title" :description="`${questions.length} questions | ${quiz.total_marks} total marks`" />
            <div class="flex-1" />
            <Button @click="openCreate"><Plus class="mr-2 size-4" /> Add Question</Button>
        </div>

        <div class="space-y-3">
            <Card v-for="(q, idx) in questions" :key="q.id">
                <CardHeader class="flex flex-row items-start justify-between pb-2">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <Badge variant="outline" class="text-xs">Q{{ idx + 1 }}</Badge>
                            <Badge variant="secondary" class="text-xs">{{ typeLabels[q.type] }}</Badge>
                            <Badge variant="outline" class="text-xs">{{ q.marks }} marks</Badge>
                        </div>
                        <p class="text-sm font-medium">{{ q.question }}</p>
                    </div>
                    <Button variant="ghost" size="sm" class="text-destructive" @click="destroy(q.id)"><Trash2 class="size-3" /></Button>
                </CardHeader>
                <CardContent v-if="q.options.length">
                    <div class="grid gap-1 sm:grid-cols-2">
                        <div v-for="opt in q.options" :key="opt.id"
                            :class="['rounded-md border px-3 py-1.5 text-sm', opt.is_correct ? 'border-green-400 bg-green-50 dark:bg-green-950/30' : '']">
                            {{ opt.text }}
                            <Badge v-if="opt.is_correct" variant="default" class="ml-2 text-xs">Correct</Badge>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
        <div v-if="!questions.length" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">No questions yet. Add questions to this quiz.</div>
    </div>

    <Dialog v-model:open="showDialog">
        <DialogContent class="max-w-lg max-h-[90vh] overflow-y-auto">
            <DialogHeader><DialogTitle>Add Question</DialogTitle></DialogHeader>
            <form @submit.prevent="submit" class="space-y-4">
                <div><Label>Question</Label>
                    <textarea v-model="form.question" rows="3" class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" /><InputError :message="form.errors.question" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><Label>Type</Label>
                        <select v-model="form.type" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option value="mcq">MCQ</option><option value="short">Short Answer</option><option value="true_false">True/False</option>
                        </select>
                    </div>
                    <div><Label>Marks</Label><Input type="number" v-model.number="form.marks" min="0.5" step="0.5" /></div>
                </div>

                <div v-if="form.type !== 'short'" class="space-y-2">
                    <div class="flex items-center justify-between">
                        <Label>Options</Label>
                        <Button type="button" variant="outline" size="sm" @click="addOption" v-if="form.type === 'mcq'">+ Option</Button>
                    </div>
                    <div v-for="(opt, idx) in form.options" :key="idx" class="flex items-center gap-2">
                        <input type="checkbox" :checked="opt.is_correct" @change="opt.is_correct = !opt.is_correct" class="rounded" />
                        <Input v-model="opt.text" placeholder="Option text" class="flex-1 h-8" />
                        <button v-if="form.options.length > 2" type="button" @click="removeOption(idx)" class="text-muted-foreground hover:text-destructive"><Trash2 class="size-3" /></button>
                    </div>
                </div>

                <DialogFooter><Button type="button" variant="outline" @click="showDialog = false">Cancel</Button><Button type="submit" :disabled="form.processing">Add</Button></DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
