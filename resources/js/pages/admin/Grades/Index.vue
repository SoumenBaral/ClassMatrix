<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Star } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type GradeRange = { grade: string; min_pct: number; max_pct: number; gpa: number | null };
type GradeScheme = { id: number; name: string; is_default: boolean; ranges: GradeRange[] };

const props = defineProps<{ schemes: GradeScheme[] }>();

const showDialog = ref(false);
const editing = ref<GradeScheme | null>(null);

const form = useForm({
    name: '',
    is_default: false,
    ranges: [{ grade: '', min_pct: 0, max_pct: 100, gpa: null as number | null }] as GradeRange[],
});

function openCreate() {
    editing.value = null;
    form.reset();
    form.ranges = [
        { grade: 'A+', min_pct: 90, max_pct: 100, gpa: 4.0 },
        { grade: 'A', min_pct: 80, max_pct: 89.99, gpa: 3.7 },
        { grade: 'B+', min_pct: 70, max_pct: 79.99, gpa: 3.3 },
        { grade: 'B', min_pct: 60, max_pct: 69.99, gpa: 3.0 },
        { grade: 'C', min_pct: 50, max_pct: 59.99, gpa: 2.5 },
        { grade: 'D', min_pct: 33, max_pct: 49.99, gpa: 1.5 },
        { grade: 'F', min_pct: 0, max_pct: 32.99, gpa: 0.0 },
    ];
    showDialog.value = true;
}

function openEdit(scheme: GradeScheme) {
    editing.value = scheme;
    form.name = scheme.name;
    form.is_default = scheme.is_default;
    form.ranges = scheme.ranges.map(r => ({ ...r }));
    showDialog.value = true;
}

function addRange() {
    form.ranges.push({ grade: '', min_pct: 0, max_pct: 0, gpa: null });
}

function removeRange(idx: number) {
    form.ranges.splice(idx, 1);
}

function submit() {
    if (editing.value) {
        form.put(`/admin/grades/${editing.value.id}`, { onSuccess: () => { showDialog.value = false; } });
    } else {
        form.post('/admin/grades', { onSuccess: () => { showDialog.value = false; form.reset(); } });
    }
}

function destroy(scheme: GradeScheme) {
    if (confirm(`Delete "${scheme.name}"?`)) {
        router.delete(`/admin/grades/${scheme.id}`);
    }
}
</script>

<template>
    <Head title="Grade Schemes" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Grade Schemes" description="Define grading systems for report cards." />
            <Button @click="openCreate"><Plus class="mr-2 size-4" /> Add Scheme</Button>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <Card v-for="scheme in schemes" :key="scheme.id" :class="{ 'ring-2 ring-primary': scheme.is_default }">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <div class="flex items-center gap-2">
                        <CardTitle class="text-base">{{ scheme.name }}</CardTitle>
                        <Badge v-if="scheme.is_default" variant="default"><Star class="size-3 mr-1" /> Default</Badge>
                    </div>
                    <div class="flex gap-1">
                        <Button variant="ghost" size="sm" @click="openEdit(scheme)"><Pencil class="size-3" /></Button>
                        <Button variant="ghost" size="sm" class="text-destructive" @click="destroy(scheme)"><Trash2 class="size-3" /></Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="py-1 text-left font-medium">Grade</th>
                                <th class="py-1 text-center font-medium">Min %</th>
                                <th class="py-1 text-center font-medium">Max %</th>
                                <th class="py-1 text-center font-medium">GPA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="r in scheme.ranges" :key="r.grade" class="border-b last:border-0">
                                <td class="py-1 font-bold">{{ r.grade }}</td>
                                <td class="py-1 text-center text-muted-foreground">{{ r.min_pct }}</td>
                                <td class="py-1 text-center text-muted-foreground">{{ r.max_pct }}</td>
                                <td class="py-1 text-center">{{ r.gpa ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>
        </div>

        <div v-if="!schemes.length" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">
            No grade schemes defined yet.
        </div>
    </div>

    <Dialog v-model:open="showDialog">
        <DialogContent class="max-w-lg max-h-[90vh] overflow-y-auto">
            <DialogHeader><DialogTitle>{{ editing ? 'Edit' : 'Create' }} Grade Scheme</DialogTitle></DialogHeader>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <Label>Name</Label>
                    <Input v-model="form.name" placeholder="e.g. Standard (A-F)" />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_default" v-model="form.is_default" class="rounded" />
                    <Label for="is_default">Set as default scheme</Label>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <Label>Grade Ranges</Label>
                        <Button type="button" variant="outline" size="sm" @click="addRange">+ Add</Button>
                    </div>
                    <div v-for="(range, idx) in form.ranges" :key="idx" class="grid grid-cols-5 gap-2 items-end">
                        <div>
                            <Label v-if="idx === 0" class="text-xs">Grade</Label>
                            <Input v-model="range.grade" placeholder="A+" class="h-8 text-xs" />
                        </div>
                        <div>
                            <Label v-if="idx === 0" class="text-xs">Min %</Label>
                            <Input type="number" v-model.number="range.min_pct" min="0" max="100" step="0.01" class="h-8 text-xs" />
                        </div>
                        <div>
                            <Label v-if="idx === 0" class="text-xs">Max %</Label>
                            <Input type="number" v-model.number="range.max_pct" min="0" max="100" step="0.01" class="h-8 text-xs" />
                        </div>
                        <div>
                            <Label v-if="idx === 0" class="text-xs">GPA</Label>
                            <Input type="number" v-model.number="range.gpa" min="0" max="10" step="0.1" class="h-8 text-xs" />
                        </div>
                        <Button type="button" variant="ghost" size="sm" class="text-destructive h-8" @click="removeRange(idx)">
                            <Trash2 class="size-3" />
                        </Button>
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="showDialog = false">Cancel</Button>
                    <Button type="submit" :disabled="form.processing">{{ editing ? 'Update' : 'Create' }}</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
