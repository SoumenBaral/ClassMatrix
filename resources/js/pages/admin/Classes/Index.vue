<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Users } from 'lucide-vue-next';
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

type Section = {
    id: number;
    name: string;
    capacity: number;
    class_teacher_id: number | null;
    room_no: string | null;
    students_count: number;
};

type ClassLevel = {
    id: number;
    name: string;
    numeric_order: number;
    sections: Section[];
};

type Teacher = {
    id: number;
    name: string;
};

const props = defineProps<{
    classLevels: ClassLevel[];
    teachers: Teacher[];
    currentAcademicYear: { id: number; name: string } | null;
}>();

// Class dialog
const showClassDialog = ref(false);
const editingClass = ref<ClassLevel | null>(null);
const classForm = useForm({ name: '', numeric_order: 0 });

function openCreateClass() {
    editingClass.value = null;
    classForm.reset();
    classForm.numeric_order = props.classLevels.length + 1;
    showClassDialog.value = true;
}

function openEditClass(cl: ClassLevel) {
    editingClass.value = cl;
    classForm.name = cl.name;
    classForm.numeric_order = cl.numeric_order;
    showClassDialog.value = true;
}

function submitClass() {
    if (editingClass.value) {
        classForm.put(`/admin/classes/${editingClass.value.id}`, {
            onSuccess: () => { showClassDialog.value = false; },
        });
    } else {
        classForm.post('/admin/classes', {
            onSuccess: () => { showClassDialog.value = false; classForm.reset(); },
        });
    }
}

function destroyClass(cl: ClassLevel) {
    if (confirm(`Delete "${cl.name}"?`)) {
        router.delete(`/admin/classes/${cl.id}`);
    }
}

// Section dialog
const showSectionDialog = ref(false);
const editingSection = ref<Section | null>(null);
const sectionClassId = ref<number>(0);
const sectionForm = useForm({
    class_level_id: 0,
    name: '',
    capacity: 40,
    class_teacher_id: null as number | null,
    room_no: '',
});

function openCreateSection(classLevelId: number) {
    editingSection.value = null;
    sectionClassId.value = classLevelId;
    sectionForm.reset();
    sectionForm.class_level_id = classLevelId;
    sectionForm.capacity = 40;
    showSectionDialog.value = true;
}

function openEditSection(section: Section) {
    editingSection.value = section;
    sectionForm.name = section.name;
    sectionForm.capacity = section.capacity;
    sectionForm.class_teacher_id = section.class_teacher_id;
    sectionForm.room_no = section.room_no ?? '';
    showSectionDialog.value = true;
}

function submitSection() {
    if (editingSection.value) {
        sectionForm.put(`/admin/sections/${editingSection.value.id}`, {
            onSuccess: () => { showSectionDialog.value = false; },
        });
    } else {
        sectionForm.post('/admin/sections', {
            onSuccess: () => { showSectionDialog.value = false; sectionForm.reset(); },
        });
    }
}

function destroySection(section: Section) {
    if (confirm(`Delete section "${section.name}"?`)) {
        router.delete(`/admin/sections/${section.id}`);
    }
}
</script>

<template>
    <Head title="Classes & Sections" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Classes & Sections" :description="`Academic Year: ${currentAcademicYear?.name ?? 'Not set'}`" />
            <Button @click="openCreateClass">
                <Plus class="mr-2 size-4" />
                Add Class
            </Button>
        </div>

        <div class="space-y-4">
            <Card v-for="cl in classLevels" :key="cl.id">
                <CardHeader class="flex flex-row items-center justify-between">
                    <div class="flex items-center gap-3">
                        <CardTitle class="text-base">{{ cl.name }}</CardTitle>
                        <Badge variant="outline">Order: {{ cl.numeric_order }}</Badge>
                    </div>
                    <div class="flex gap-2">
                        <Button variant="outline" size="sm" @click="openCreateSection(cl.id)">
                            <Plus class="mr-1 size-3" />
                            Add Section
                        </Button>
                        <Button variant="outline" size="sm" @click="openEditClass(cl)">
                            <Pencil class="size-3" />
                        </Button>
                        <Button variant="outline" size="sm" class="text-destructive" @click="destroyClass(cl)">
                            <Trash2 class="size-3" />
                        </Button>
                    </div>
                </CardHeader>
                <CardContent v-if="cl.sections.length">
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div v-for="section in cl.sections" :key="section.id"
                             class="flex items-center justify-between rounded-lg border p-3">
                            <div>
                                <div class="font-medium">Section {{ section.name }}</div>
                                <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                    <Users class="size-3" />
                                    {{ section.students_count }}/{{ section.capacity }}
                                    <span v-if="section.room_no">| Room {{ section.room_no }}</span>
                                </div>
                            </div>
                            <div class="flex gap-1">
                                <Button variant="ghost" size="sm" @click="openEditSection(section)">
                                    <Pencil class="size-3" />
                                </Button>
                                <Button variant="ghost" size="sm" class="text-destructive" @click="destroySection(section)">
                                    <Trash2 class="size-3" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
                <CardContent v-else>
                    <p class="text-sm text-muted-foreground">No sections created for this class.</p>
                </CardContent>
            </Card>
        </div>

        <div v-if="!classLevels.length" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">
            No classes created yet. Click "Add Class" to get started.
        </div>
    </div>

    <!-- Class Dialog -->
    <Dialog v-model:open="showClassDialog">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ editingClass ? 'Edit' : 'Create' }} Class</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submitClass" class="space-y-4">
                <div>
                    <Label for="class-name">Name</Label>
                    <Input id="class-name" v-model="classForm.name" placeholder="e.g. Grade 1" />
                    <InputError :message="classForm.errors.name" />
                </div>
                <div>
                    <Label for="class-order">Order</Label>
                    <Input id="class-order" type="number" v-model.number="classForm.numeric_order" min="1" />
                    <InputError :message="classForm.errors.numeric_order" />
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showClassDialog = false">Cancel</Button>
                    <Button type="submit" :disabled="classForm.processing">
                        {{ editingClass ? 'Update' : 'Create' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Section Dialog -->
    <Dialog v-model:open="showSectionDialog">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ editingSection ? 'Edit' : 'Create' }} Section</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submitSection" class="space-y-4">
                <div>
                    <Label for="section-name">Name</Label>
                    <Input id="section-name" v-model="sectionForm.name" placeholder="e.g. A" />
                    <InputError :message="sectionForm.errors.name" />
                </div>
                <div>
                    <Label for="section-capacity">Capacity</Label>
                    <Input id="section-capacity" type="number" v-model.number="sectionForm.capacity" min="1" max="200" />
                    <InputError :message="sectionForm.errors.capacity" />
                </div>
                <div>
                    <Label for="section-room">Room No</Label>
                    <Input id="section-room" v-model="sectionForm.room_no" placeholder="e.g. 101" />
                </div>
                <div>
                    <Label for="section-teacher">Class Teacher</Label>
                    <select id="section-teacher" v-model="sectionForm.class_teacher_id"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                        <option :value="null">None</option>
                        <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                            {{ teacher.name }}
                        </option>
                    </select>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showSectionDialog = false">Cancel</Button>
                    <Button type="submit" :disabled="sectionForm.processing">
                        {{ editingSection ? 'Update' : 'Create' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
