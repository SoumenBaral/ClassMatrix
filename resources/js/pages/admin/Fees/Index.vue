<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type Category = { id: number; name: string; description: string | null };
type ClassLevel = { id: number; name: string };
type FeeStructure = {
    id: number; amount: string; frequency: string;
    class_level: ClassLevel; category: Category;
};

const props = defineProps<{
    structures: FeeStructure[];
    categories: Category[];
    classLevels: ClassLevel[];
}>();

// Fee structure form
const showFeeDialog = ref(false);
const feeForm = useForm({
    class_level_id: '' as any,
    fee_category_id: '' as any,
    amount: 0,
    frequency: 'monthly',
});

function submitFee() {
    feeForm.post('/admin/fees', { onSuccess: () => { showFeeDialog.value = false; feeForm.reset(); } });
}

function destroyFee(id: number) {
    if (confirm('Remove this fee structure?')) router.delete(`/admin/fees/${id}`);
}

// Category form
const showCatDialog = ref(false);
const catForm = useForm({ name: '', description: '' });

function submitCategory() {
    catForm.post('/admin/fee-categories', { onSuccess: () => { showCatDialog.value = false; catForm.reset(); } });
}

function destroyCategory(id: number) {
    if (confirm('Delete this category?')) router.delete(`/admin/fee-categories/${id}`);
}

const frequencies = ['monthly', 'quarterly', 'yearly', 'one-time'];
</script>

<template>
    <Head title="Fee Structure" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Fee Structure" description="Define fees per class and category." />
            <div class="flex gap-2">
                <Button variant="outline" @click="showCatDialog = true"><Plus class="mr-1 size-4" /> Category</Button>
                <Button @click="showFeeDialog = true"><Plus class="mr-1 size-4" /> Add Fee</Button>
            </div>
        </div>

        <!-- Categories -->
        <div class="flex flex-wrap gap-2">
            <div v-for="cat in categories" :key="cat.id" class="flex items-center gap-1 rounded-lg border px-3 py-1 text-sm">
                {{ cat.name }}
                <button @click="destroyCategory(cat.id)" class="ml-1 text-muted-foreground hover:text-destructive"><Trash2 class="size-3" /></button>
            </div>
        </div>

        <!-- Fee table -->
        <div class="rounded-lg border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="px-4 py-3 text-left font-medium">Class</th>
                        <th class="px-4 py-3 text-left font-medium">Category</th>
                        <th class="px-4 py-3 text-right font-medium">Amount</th>
                        <th class="px-4 py-3 text-center font-medium">Frequency</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="s in structures" :key="s.id" class="border-b last:border-0 hover:bg-muted/30">
                        <td class="px-4 py-2 font-medium">{{ s.class_level.name }}</td>
                        <td class="px-4 py-2">{{ s.category.name }}</td>
                        <td class="px-4 py-2 text-right font-mono">{{ Number(s.amount).toLocaleString() }}</td>
                        <td class="px-4 py-2 text-center"><Badge variant="outline" class="capitalize">{{ s.frequency }}</Badge></td>
                        <td class="px-4 py-2 text-right">
                            <Button variant="ghost" size="sm" class="text-destructive" @click="destroyFee(s.id)"><Trash2 class="size-3" /></Button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="!structures.length" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">No fee structures defined.</div>
    </div>

    <!-- Add Fee -->
    <Dialog v-model:open="showFeeDialog">
        <DialogContent>
            <DialogHeader><DialogTitle>Add Fee Structure</DialogTitle></DialogHeader>
            <form @submit.prevent="submitFee" class="space-y-4">
                <div>
                    <Label>Class</Label>
                    <select v-model="feeForm.class_level_id" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                        <option value="">Select...</option>
                        <option v-for="c in classLevels" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                    <InputError :message="feeForm.errors.class_level_id" />
                </div>
                <div>
                    <Label>Category</Label>
                    <select v-model="feeForm.fee_category_id" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                        <option value="">Select...</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                    <InputError :message="feeForm.errors.fee_category_id" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><Label>Amount</Label><Input type="number" v-model.number="feeForm.amount" min="0" step="0.01" /><InputError :message="feeForm.errors.amount" /></div>
                    <div>
                        <Label>Frequency</Label>
                        <select v-model="feeForm.frequency" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option v-for="f in frequencies" :key="f" :value="f" class="capitalize">{{ f }}</option>
                        </select>
                    </div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showFeeDialog = false">Cancel</Button>
                    <Button type="submit" :disabled="feeForm.processing">Save</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Add Category -->
    <Dialog v-model:open="showCatDialog">
        <DialogContent>
            <DialogHeader><DialogTitle>Add Fee Category</DialogTitle></DialogHeader>
            <form @submit.prevent="submitCategory" class="space-y-4">
                <div><Label>Name</Label><Input v-model="catForm.name" placeholder="e.g. Tuition" /><InputError :message="catForm.errors.name" /></div>
                <div><Label>Description</Label><Input v-model="catForm.description" placeholder="Optional" /></div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showCatDialog = false">Cancel</Button>
                    <Button type="submit" :disabled="catForm.processing">Create</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
