<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Link2, Search, Unlink, UserPlus } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type Child = { id: number; name: string; admission_no: string; relation: string; is_primary: boolean };
type ParentUser = { id: number; name: string; email: string; phone: string | null; children: Child[] };
type StudentOption = { id: number; name: string; admission_no: string };
type PaginatedData = { data: ParentUser[]; links: { url: string | null; label: string; active: boolean }[] };

const props = defineProps<{
    parents: PaginatedData;
    students: StudentOption[];
    filters: { search?: string };
}>();

const search = ref(props.filters.search ?? '');

function filter() {
    router.get('/admin/parent-links', { search: search.value || undefined }, { preserveState: true });
}

// Link dialog
const showLinkDialog = ref(false);
const linkingParentId = ref(0);
const linkingParentName = ref('');
const studentSearch = ref('');

const linkForm = useForm({
    parent_id: 0,
    student_id: '' as any,
    relation: 'parent',
    is_primary: false,
});

function openLink(parent: ParentUser) {
    linkingParentId.value = parent.id;
    linkingParentName.value = parent.name;
    linkForm.reset();
    linkForm.parent_id = parent.id;
    studentSearch.value = '';
    showLinkDialog.value = true;
}

function submitLink() {
    linkForm.post('/admin/parent-links', {
        onSuccess: () => { showLinkDialog.value = false; },
    });
}

function unlink(parentId: number, studentId: number, studentName: string) {
    if (confirm(`Unlink "${studentName}" from this parent?`)) {
        router.delete('/admin/parent-links', {
            data: { parent_id: parentId, student_id: studentId },
        });
    }
}

const relations = [
    { value: 'father', label: 'Father' },
    { value: 'mother', label: 'Mother' },
    { value: 'guardian', label: 'Guardian' },
    { value: 'other', label: 'Other' },
];

const relationColors: Record<string, string> = {
    father: 'default',
    mother: 'secondary',
    guardian: 'outline',
    other: 'outline',
};

// Filter students in dialog
function filteredStudents() {
    if (!studentSearch.value) return props.students.slice(0, 20);
    const q = studentSearch.value.toLowerCase();
    return props.students.filter(s =>
        s.name.toLowerCase().includes(q) || s.admission_no.toLowerCase().includes(q)
    ).slice(0, 20);
}
</script>

<template>
    <Head title="Parent-Student Links" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <Heading title="Parent-Student Links" description="Link parent accounts to their children's student profiles." />

        <div class="flex items-end gap-4">
            <div class="relative">
                <Search class="absolute left-2.5 top-2.5 size-4 text-muted-foreground" />
                <Input v-model="search" @keyup.enter="filter" placeholder="Search parent name or email..." class="pl-9 w-64" />
            </div>
        </div>

        <!-- Parent cards -->
        <div class="space-y-4">
            <Card v-for="parent in parents.data" :key="parent.id">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <div>
                        <CardTitle class="text-base">{{ parent.name }}</CardTitle>
                        <p class="text-xs text-muted-foreground mt-0.5">{{ parent.email }} <span v-if="parent.phone"> | {{ parent.phone }}</span></p>
                    </div>
                    <Button variant="outline" size="sm" @click="openLink(parent)">
                        <UserPlus class="mr-1 size-3" /> Link Child
                    </Button>
                </CardHeader>
                <CardContent>
                    <div v-if="parent.children.length" class="flex flex-wrap gap-3">
                        <div v-for="child in parent.children" :key="child.id"
                            class="flex items-center gap-3 rounded-lg border p-3 pr-2">
                            <div>
                                <div class="font-medium text-sm">{{ child.name }}</div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <code class="text-xs text-muted-foreground">{{ child.admission_no }}</code>
                                    <Badge :variant="(relationColors[child.relation] as any)" class="text-xs capitalize">{{ child.relation }}</Badge>
                                    <Badge v-if="child.is_primary" variant="default" class="text-xs">Primary</Badge>
                                </div>
                            </div>
                            <button @click="unlink(parent.id, child.id, child.name)"
                                class="p-1.5 rounded-md text-muted-foreground hover:text-destructive hover:bg-muted transition-colors" title="Unlink">
                                <Unlink class="size-3.5" />
                            </button>
                        </div>
                    </div>
                    <div v-else class="flex items-center gap-2 text-sm text-muted-foreground">
                        <Link2 class="size-4" />
                        No children linked yet. Click "Link Child" to connect a student.
                    </div>
                </CardContent>
            </Card>
        </div>

        <div v-if="!parents.data.length" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">
            No parent accounts found. Parents need to register first.
        </div>

        <!-- Pagination -->
        <div v-if="parents.links.length > 3" class="flex justify-center gap-1">
            <template v-for="link in parents.links" :key="link.label">
                <button v-if="link.url" @click="router.get(link.url)"
                    :class="['rounded-md px-3 py-1 text-sm', link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted']"
                    v-html="link.label" />
            </template>
        </div>
    </div>

    <!-- Link Dialog -->
    <Dialog v-model:open="showLinkDialog">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>Link Child to {{ linkingParentName }}</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submitLink" class="space-y-4">
                <div>
                    <Label>Search Student</Label>
                    <Input v-model="studentSearch" placeholder="Type student name or admission no..." class="mb-2" />
                    <div class="max-h-48 overflow-y-auto rounded-lg border">
                        <button v-for="s in filteredStudents()" :key="s.id" type="button"
                            @click="linkForm.student_id = s.id; studentSearch = s.name"
                            :class="[
                                'flex w-full items-center justify-between px-3 py-2 text-sm text-left hover:bg-accent transition-colors',
                                linkForm.student_id === s.id ? 'bg-accent font-medium' : '',
                            ]">
                            <span>{{ s.name }}</span>
                            <code class="text-xs text-muted-foreground">{{ s.admission_no }}</code>
                        </button>
                        <div v-if="!filteredStudents().length" class="px-3 py-4 text-sm text-muted-foreground text-center">
                            No students found.
                        </div>
                    </div>
                    <InputError :message="linkForm.errors.student_id" />
                </div>

                <div>
                    <Label>Relation</Label>
                    <select v-model="linkForm.relation"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                        <option v-for="r in relations" :key="r.value" :value="r.value">{{ r.label }}</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_primary" v-model="linkForm.is_primary" class="rounded" />
                    <Label for="is_primary">Primary contact for this student</Label>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="showLinkDialog = false">Cancel</Button>
                    <Button type="submit" :disabled="linkForm.processing || !linkForm.student_id">Link Student</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
