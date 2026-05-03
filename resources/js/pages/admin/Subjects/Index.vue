<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type Subject = {
    id: number;
    name: string;
    code: string;
    type: string;
    department: { id: number; name: string } | null;
    class_levels_count: number;
};

type Department = { id: number; name: string };

const props = defineProps<{
    subjects: Subject[];
    departments: Department[];
}>();

const showDialog = ref(false);
const editing = ref<Subject | null>(null);

const form = useForm({
    name: '',
    code: '',
    type: 'theory',
    department_id: null as number | null,
});

function openCreate() {
    editing.value = null;
    form.reset();
    showDialog.value = true;
}

function openEdit(subject: Subject) {
    editing.value = subject;
    form.name = subject.name;
    form.code = subject.code;
    form.type = subject.type;
    form.department_id = subject.department?.id ?? null;
    showDialog.value = true;
}

function submit() {
    if (editing.value) {
        form.put(`/admin/subjects/${editing.value.id}`, {
            onSuccess: () => { showDialog.value = false; },
        });
    } else {
        form.post('/admin/subjects', {
            onSuccess: () => { showDialog.value = false; form.reset(); },
        });
    }
}

function destroy(subject: Subject) {
    if (confirm(`Delete "${subject.name}"?`)) {
        router.delete(`/admin/subjects/${subject.id}`);
    }
}

const typeColors: Record<string, string> = {
    theory: 'default',
    practical: 'secondary',
    lab: 'outline',
};
</script>

<template>
    <Head title="Subjects" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Subjects" description="Manage subjects across all classes." />
            <Button @click="openCreate">
                <Plus class="mr-2 size-4" />
                Add Subject
            </Button>
        </div>

        <div class="rounded-lg border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="px-4 py-3 text-left font-medium">Name</th>
                        <th class="px-4 py-3 text-left font-medium">Code</th>
                        <th class="px-4 py-3 text-left font-medium">Type</th>
                        <th class="px-4 py-3 text-left font-medium">Department</th>
                        <th class="px-4 py-3 text-left font-medium">Classes</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="subject in subjects" :key="subject.id" class="border-b last:border-0 hover:bg-muted/30">
                        <td class="px-4 py-3 font-medium">{{ subject.name }}</td>
                        <td class="px-4 py-3">
                            <code class="rounded bg-muted px-1.5 py-0.5 text-xs">{{ subject.code }}</code>
                        </td>
                        <td class="px-4 py-3">
                            <Badge :variant="(typeColors[subject.type] as any) ?? 'default'">
                                {{ subject.type }}
                            </Badge>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ subject.department?.name ?? '-' }}
                        </td>
                        <td class="px-4 py-3">{{ subject.class_levels_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-1">
                                <Button variant="ghost" size="sm" @click="openEdit(subject)">
                                    <Pencil class="size-3" />
                                </Button>
                                <Button variant="ghost" size="sm" class="text-destructive" @click="destroy(subject)">
                                    <Trash2 class="size-3" />
                                </Button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="!subjects.length" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">
            No subjects created yet.
        </div>
    </div>

    <Dialog v-model:open="showDialog">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ editing ? 'Edit' : 'Create' }} Subject</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <Label for="subject-name">Name</Label>
                    <Input id="subject-name" v-model="form.name" placeholder="e.g. Mathematics" />
                    <InputError :message="form.errors.name" />
                </div>
                <div>
                    <Label for="subject-code">Code</Label>
                    <Input id="subject-code" v-model="form.code" placeholder="e.g. MAT" />
                    <InputError :message="form.errors.code" />
                </div>
                <div>
                    <Label for="subject-type">Type</Label>
                    <select id="subject-type" v-model="form.type"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                        <option value="theory">Theory</option>
                        <option value="practical">Practical</option>
                        <option value="lab">Lab</option>
                    </select>
                    <InputError :message="form.errors.type" />
                </div>
                <div>
                    <Label for="subject-dept">Department</Label>
                    <select id="subject-dept" v-model="form.department_id"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                        <option :value="null">None</option>
                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                            {{ dept.name }}
                        </option>
                    </select>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showDialog = false">Cancel</Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ editing ? 'Update' : 'Create' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
