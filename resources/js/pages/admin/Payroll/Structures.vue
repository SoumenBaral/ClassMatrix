<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type Structure = {
    id: number; designation: string; basic: string; hra: string;
    da: string; ta: string; pf: string; tax: string;
};

const props = defineProps<{ structures: Structure[] }>();

const showDialog = ref(false);
const editing = ref<Structure | null>(null);

const form = useForm({
    designation: '', basic: 0, hra: 0, da: 0, ta: 0, pf: 0, tax: 0,
});

function openCreate() {
    editing.value = null;
    form.reset();
    showDialog.value = true;
}

function openEdit(s: Structure) {
    editing.value = s;
    form.designation = s.designation;
    form.basic = Number(s.basic);
    form.hra = Number(s.hra);
    form.da = Number(s.da);
    form.ta = Number(s.ta);
    form.pf = Number(s.pf);
    form.tax = Number(s.tax);
    showDialog.value = true;
}

function submit() {
    if (editing.value) {
        form.put(`/admin/salary-structures/${editing.value.id}`, { onSuccess: () => { showDialog.value = false; } });
    } else {
        form.post('/admin/salary-structures', { onSuccess: () => { showDialog.value = false; form.reset(); } });
    }
}

function destroy(id: number) {
    if (confirm('Delete this salary structure?')) router.delete(`/admin/salary-structures/${id}`);
}

function gross(s: Structure): number {
    return [s.basic, s.hra, s.da, s.ta].reduce((sum, v) => sum + Number(v), 0);
}
function net(s: Structure): number {
    return gross(s) - Number(s.pf) - Number(s.tax);
}
</script>

<template>
    <Head title="Salary Structures" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Salary Structures" description="Define salary breakdowns by designation." />
            <Button @click="openCreate"><Plus class="mr-2 size-4" /> Add Structure</Button>
        </div>

        <div class="rounded-lg border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="px-4 py-3 text-left font-medium">Designation</th>
                        <th class="px-4 py-3 text-right font-medium">Basic</th>
                        <th class="px-4 py-3 text-right font-medium">HRA</th>
                        <th class="px-4 py-3 text-right font-medium">DA</th>
                        <th class="px-4 py-3 text-right font-medium">TA</th>
                        <th class="px-4 py-3 text-right font-medium">PF</th>
                        <th class="px-4 py-3 text-right font-medium">Tax</th>
                        <th class="px-4 py-3 text-right font-medium">Gross</th>
                        <th class="px-4 py-3 text-right font-medium">Net</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="s in structures" :key="s.id" class="border-b last:border-0 hover:bg-muted/30">
                        <td class="px-4 py-2 font-medium">{{ s.designation }}</td>
                        <td class="px-4 py-2 text-right font-mono">{{ Number(s.basic).toLocaleString() }}</td>
                        <td class="px-4 py-2 text-right font-mono">{{ Number(s.hra).toLocaleString() }}</td>
                        <td class="px-4 py-2 text-right font-mono">{{ Number(s.da).toLocaleString() }}</td>
                        <td class="px-4 py-2 text-right font-mono">{{ Number(s.ta).toLocaleString() }}</td>
                        <td class="px-4 py-2 text-right font-mono text-red-500">{{ Number(s.pf).toLocaleString() }}</td>
                        <td class="px-4 py-2 text-right font-mono text-red-500">{{ Number(s.tax).toLocaleString() }}</td>
                        <td class="px-4 py-2 text-right font-mono font-medium">{{ gross(s).toLocaleString() }}</td>
                        <td class="px-4 py-2 text-right font-mono font-bold text-green-600">{{ net(s).toLocaleString() }}</td>
                        <td class="px-4 py-2 text-right">
                            <div class="flex justify-end gap-1">
                                <Button variant="ghost" size="sm" @click="openEdit(s)"><Pencil class="size-3" /></Button>
                                <Button variant="ghost" size="sm" class="text-destructive" @click="destroy(s.id)"><Trash2 class="size-3" /></Button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="!structures.length" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">No salary structures defined.</div>
    </div>

    <Dialog v-model:open="showDialog">
        <DialogContent>
            <DialogHeader><DialogTitle>{{ editing ? 'Edit' : 'Add' }} Salary Structure</DialogTitle></DialogHeader>
            <form @submit.prevent="submit" class="space-y-4">
                <div><Label>Designation</Label><Input v-model="form.designation" placeholder="e.g. Senior Teacher" /><InputError :message="form.errors.designation" /></div>
                <div class="grid grid-cols-2 gap-3">
                    <div><Label>Basic</Label><Input type="number" v-model.number="form.basic" min="0" /></div>
                    <div><Label>HRA</Label><Input type="number" v-model.number="form.hra" min="0" /></div>
                    <div><Label>DA</Label><Input type="number" v-model.number="form.da" min="0" /></div>
                    <div><Label>TA</Label><Input type="number" v-model.number="form.ta" min="0" /></div>
                    <div><Label>PF (deduction)</Label><Input type="number" v-model.number="form.pf" min="0" /></div>
                    <div><Label>Tax (deduction)</Label><Input type="number" v-model.number="form.tax" min="0" /></div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showDialog = false">Cancel</Button>
                    <Button type="submit" :disabled="form.processing">{{ editing ? 'Update' : 'Create' }}</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
