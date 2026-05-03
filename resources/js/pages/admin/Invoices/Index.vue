<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Eye, FileText, Plus, Search } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type Invoice = {
    id: number; invoice_no: string; student_name: string; admission_no: string;
    total_amount: string; discount_amount: string; paid_amount: string; balance: number;
    due_date: string; status: string; payments_count: number; created_at: string;
};
type PaginatedData = { data: Invoice[]; links: { url: string | null; label: string; active: boolean }[] };

const props = defineProps<{
    invoices: PaginatedData;
    filters: { status?: string; search?: string };
    stats: { total: string; collected: string; pending: string; overdue: number };
}>();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

function filter() {
    router.get('/admin/invoices', { search: search.value || undefined, status: status.value || undefined }, {
        preserveState: true, preserveScroll: true,
    });
}

// Generate dialog
const showGenerateDialog = ref(false);
const genForm = useForm({ section_id: '' as any, due_date: '', notes: '' });

function submitGenerate() {
    genForm.post('/admin/invoices/generate', { onSuccess: () => { showGenerateDialog.value = false; genForm.reset(); } });
}

const statusColors: Record<string, string> = {
    unpaid: 'secondary', partial: 'outline', paid: 'default', overdue: 'destructive', cancelled: 'secondary',
};

const statCards = [
    { label: 'Total Billed', value: props.stats.total, color: 'text-blue-600' },
    { label: 'Collected', value: props.stats.collected, color: 'text-green-600' },
    { label: 'Pending', value: props.stats.pending, color: 'text-orange-600' },
    { label: 'Overdue', value: props.stats.overdue, color: 'text-red-600' },
];
</script>

<template>
    <Head title="Invoices" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Invoices" description="Manage student fee invoices and payments." />
            <Button @click="showGenerateDialog = true"><Plus class="mr-2 size-4" /> Generate Invoices</Button>
        </div>

        <!-- Stats -->
        <div class="grid gap-4 sm:grid-cols-4">
            <Card v-for="s in statCards" :key="s.label">
                <CardContent class="pt-4">
                    <div class="text-sm text-muted-foreground">{{ s.label }}</div>
                    <div :class="['text-2xl font-bold', s.color]">{{ typeof s.value === 'number' ? s.value : Number(s.value).toLocaleString() }}</div>
                </CardContent>
            </Card>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <Label>Search</Label>
                <div class="relative">
                    <Search class="absolute left-2.5 top-2.5 size-4 text-muted-foreground" />
                    <Input v-model="search" @keyup.enter="filter" placeholder="Name or admission no..." class="pl-9 w-56" />
                </div>
            </div>
            <div>
                <Label>Status</Label>
                <select v-model="status" @change="filter" class="flex h-9 w-40 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                    <option value="">All</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="partial">Partial</option>
                    <option value="paid">Paid</option>
                    <option value="overdue">Overdue</option>
                </select>
            </div>
        </div>

        <!-- Invoices table -->
        <div class="rounded-lg border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="px-4 py-3 text-left font-medium">Invoice #</th>
                        <th class="px-4 py-3 text-left font-medium">Student</th>
                        <th class="px-4 py-3 text-right font-medium">Total</th>
                        <th class="px-4 py-3 text-right font-medium">Paid</th>
                        <th class="px-4 py-3 text-right font-medium">Balance</th>
                        <th class="px-4 py-3 text-center font-medium">Due Date</th>
                        <th class="px-4 py-3 text-center font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="inv in invoices.data" :key="inv.id" class="border-b last:border-0 hover:bg-muted/30">
                        <td class="px-4 py-2 font-mono text-xs">{{ inv.invoice_no }}</td>
                        <td class="px-4 py-2">
                            <div class="font-medium">{{ inv.student_name }}</div>
                            <div class="text-xs text-muted-foreground">{{ inv.admission_no }}</div>
                        </td>
                        <td class="px-4 py-2 text-right font-mono">{{ Number(inv.total_amount).toLocaleString() }}</td>
                        <td class="px-4 py-2 text-right font-mono text-green-600">{{ Number(inv.paid_amount).toLocaleString() }}</td>
                        <td class="px-4 py-2 text-right font-mono" :class="inv.balance > 0 ? 'text-red-600 font-bold' : ''">{{ inv.balance.toLocaleString() }}</td>
                        <td class="px-4 py-2 text-center text-muted-foreground">{{ inv.due_date }}</td>
                        <td class="px-4 py-2 text-center">
                            <Badge :variant="(statusColors[inv.status] as any)" class="capitalize">{{ inv.status }}</Badge>
                        </td>
                        <td class="px-4 py-2 text-right">
                            <Button variant="ghost" size="sm" as-child>
                                <a :href="`/admin/invoices/${inv.id}`"><Eye class="size-3" /></a>
                            </Button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="invoices.links.length > 3" class="flex justify-center gap-1">
            <template v-for="link in invoices.links" :key="link.label">
                <button v-if="link.url" @click="router.get(link.url)" :class="['rounded-md px-3 py-1 text-sm', link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted']" v-html="link.label" />
            </template>
        </div>
    </div>

    <!-- Generate Dialog -->
    <Dialog v-model:open="showGenerateDialog">
        <DialogContent>
            <DialogHeader><DialogTitle>Generate Invoices</DialogTitle></DialogHeader>
            <form @submit.prevent="submitGenerate" class="space-y-4">
                <p class="text-sm text-muted-foreground">This will generate invoices for all active students in the selected section based on the fee structure.</p>
                <div>
                    <Label>Section</Label>
                    <Input v-model="genForm.section_id" type="number" placeholder="Section ID" />
                    <InputError :message="genForm.errors.section_id" />
                </div>
                <div>
                    <Label>Due Date</Label>
                    <Input type="date" v-model="genForm.due_date" />
                    <InputError :message="genForm.errors.due_date" />
                </div>
                <div><Label>Notes</Label><Input v-model="genForm.notes" placeholder="Optional" /></div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showGenerateDialog = false">Cancel</Button>
                    <Button type="submit" :disabled="genForm.processing">Generate</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
