<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Star } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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

type AcademicYear = {
    id: number;
    name: string;
    start_date: string;
    end_date: string;
    is_current: boolean;
    terms_count: number;
    sections_count: number;
};

const props = defineProps<{
    academicYears: AcademicYear[];
}>();

const showDialog = ref(false);
const editing = ref<AcademicYear | null>(null);

const form = useForm({
    name: '',
    start_date: '',
    end_date: '',
    is_current: false,
});

function openCreate() {
    editing.value = null;
    form.reset();
    showDialog.value = true;
}

function openEdit(year: AcademicYear) {
    editing.value = year;
    form.name = year.name;
    form.start_date = year.start_date;
    form.end_date = year.end_date;
    form.is_current = year.is_current;
    showDialog.value = true;
}

function submit() {
    if (editing.value) {
        form.put(`/admin/academic-years/${editing.value.id}`, {
            onSuccess: () => { showDialog.value = false; },
        });
    } else {
        form.post('/admin/academic-years', {
            onSuccess: () => { showDialog.value = false; form.reset(); },
        });
    }
}

function destroy(year: AcademicYear) {
    if (confirm(`Delete "${year.name}"? This will also delete all related terms and sections.`)) {
        router.delete(`/admin/academic-years/${year.id}`);
    }
}

function setCurrent(year: AcademicYear) {
    router.post(`/admin/academic-years/${year.id}/set-current`);
}
</script>

<template>
    <Head title="Academic Years" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Academic Years" description="Manage academic years and terms." />
            <Button @click="openCreate">
                <Plus class="mr-2 size-4" />
                Add Academic Year
            </Button>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Card v-for="year in academicYears" :key="year.id" :class="{ 'ring-2 ring-primary': year.is_current }">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-base">{{ year.name }}</CardTitle>
                    <Badge v-if="year.is_current" variant="default">Current</Badge>
                    <Badge v-else variant="outline">Inactive</Badge>
                </CardHeader>
                <CardContent class="space-y-3">
                    <div class="text-sm text-muted-foreground">
                        {{ year.start_date }} to {{ year.end_date }}
                    </div>
                    <div class="flex gap-4 text-sm">
                        <span>{{ year.terms_count }} Terms</span>
                        <span>{{ year.sections_count }} Sections</span>
                    </div>
                    <div class="flex gap-2 pt-2">
                        <Button v-if="!year.is_current" variant="outline" size="sm" @click="setCurrent(year)">
                            <Star class="mr-1 size-3" />
                            Set Current
                        </Button>
                        <Button variant="outline" size="sm" @click="openEdit(year)">
                            <Pencil class="mr-1 size-3" />
                            Edit
                        </Button>
                        <Button variant="outline" size="sm" class="text-destructive" @click="destroy(year)">
                            <Trash2 class="mr-1 size-3" />
                            Delete
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div v-if="!academicYears.length" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">
            No academic years created yet. Click "Add Academic Year" to get started.
        </div>
    </div>

    <Dialog v-model:open="showDialog">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ editing ? 'Edit' : 'Create' }} Academic Year</DialogTitle>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <Label for="name">Name</Label>
                    <Input id="name" v-model="form.name" placeholder="e.g. 2026-2027" />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <Label for="start_date">Start Date</Label>
                        <Input id="start_date" type="date" v-model="form.start_date" />
                        <InputError :message="form.errors.start_date" />
                    </div>
                    <div>
                        <Label for="end_date">End Date</Label>
                        <Input id="end_date" type="date" v-model="form.end_date" />
                        <InputError :message="form.errors.end_date" />
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_current" v-model="form.is_current" class="rounded" />
                    <Label for="is_current">Set as current academic year</Label>
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
