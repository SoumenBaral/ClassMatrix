<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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

type Department = {
    id: number;
    name: string;
    code: string;
    head: { id: number; name: string } | null;
    subjects_count: number;
    staff_count: number;
};

type Teacher = { id: number; name: string };

const props = defineProps<{
    departments: Department[];
    teachers: Teacher[];
}>();

const showDialog = ref(false);
const editing = ref<Department | null>(null);

const form = useForm({
    name: '',
    code: '',
    head_id: null as number | null,
});

function openCreate() {
    editing.value = null;
    form.reset();
    showDialog.value = true;
}

function openEdit(dept: Department) {
    editing.value = dept;
    form.name = dept.name;
    form.code = dept.code;
    form.head_id = dept.head?.id ?? null;
    showDialog.value = true;
}

function submit() {
    if (editing.value) {
        form.put(`/admin/departments/${editing.value.id}`, {
            onSuccess: () => { showDialog.value = false; },
        });
    } else {
        form.post('/admin/departments', {
            onSuccess: () => { showDialog.value = false; form.reset(); },
        });
    }
}

function destroy(dept: Department) {
    if (confirm(`Delete "${dept.name}"?`)) {
        router.delete(`/admin/departments/${dept.id}`);
    }
}
</script>

<template>
    <Head title="Departments" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Departments" description="Manage academic departments." />
            <Button @click="openCreate">
                <Plus class="mr-2 size-4" />
                Add Department
            </Button>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Card v-for="dept in departments" :key="dept.id">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <div>
                        <CardTitle class="text-base">{{ dept.name }}</CardTitle>
                        <p class="text-xs text-muted-foreground mt-1">
                            Code: <code class="rounded bg-muted px-1 py-0.5">{{ dept.code }}</code>
                        </p>
                    </div>
                    <div class="flex gap-1">
                        <Button variant="ghost" size="sm" @click="openEdit(dept)">
                            <Pencil class="size-3" />
                        </Button>
                        <Button variant="ghost" size="sm" class="text-destructive" @click="destroy(dept)">
                            <Trash2 class="size-3" />
                        </Button>
                    </div>
                </CardHeader>
                <CardContent class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Head</span>
                        <span class="font-medium">{{ dept.head?.name ?? 'Not assigned' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Subjects</span>
                        <span class="font-medium">{{ dept.subjects_count }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Staff</span>
                        <span class="font-medium">{{ dept.staff_count }}</span>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div v-if="!departments.length" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">
            No departments created yet.
        </div>
    </div>

    <Dialog v-model:open="showDialog">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ editing ? 'Edit' : 'Create' }} Department</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <Label for="dept-name">Name</Label>
                    <Input id="dept-name" v-model="form.name" placeholder="e.g. Science" />
                    <InputError :message="form.errors.name" />
                </div>
                <div>
                    <Label for="dept-code">Code</Label>
                    <Input id="dept-code" v-model="form.code" placeholder="e.g. SCI" />
                    <InputError :message="form.errors.code" />
                </div>
                <div>
                    <Label for="dept-head">Department Head</Label>
                    <select id="dept-head" v-model="form.head_id"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                        <option :value="null">None</option>
                        <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                            {{ teacher.name }}
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
