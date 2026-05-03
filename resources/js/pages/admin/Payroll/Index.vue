<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import Heading from '@/components/Heading.vue';

type Payslip = {
    id: number; staff_name: string; employee_no: string; designation: string;
    basic: string; gross: string; net: string; status: string; paid_at: string | null;
};

const props = defineProps<{
    payslips: Payslip[];
    month: number;
    year: number;
}>();

const month = ref(props.month);
const year = ref(props.year);

function navigate() {
    router.get('/admin/payroll', { month: month.value, year: year.value }, { preserveState: true });
}

const genForm = useForm({ month: props.month, year: props.year });

function generate() {
    genForm.month = month.value;
    genForm.year = year.value;
    genForm.post('/admin/payroll/generate', { preserveScroll: true });
}

function markPaid(id: number) {
    router.post(`/admin/payslips/${id}/paid`, {}, { preserveScroll: true });
}

const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
</script>

<template>
    <Head title="Payroll" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Payroll" description="Generate and manage staff payslips." />
            <Button @click="generate" :disabled="genForm.processing">Generate Payslips</Button>
        </div>

        <div class="flex items-end gap-4">
            <div>
                <Label>Month</Label>
                <select v-model.number="month" @change="navigate" class="flex h-9 w-36 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                    <option v-for="(m, i) in months" :key="i" :value="i + 1">{{ m }}</option>
                </select>
            </div>
            <div>
                <Label>Year</Label>
                <select v-model.number="year" @change="navigate" class="flex h-9 w-28 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                    <option v-for="y in [2024,2025,2026,2027]" :key="y" :value="y">{{ y }}</option>
                </select>
            </div>
        </div>

        <div class="rounded-lg border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="px-4 py-3 text-left font-medium">Employee</th>
                        <th class="px-4 py-3 text-left font-medium">Designation</th>
                        <th class="px-4 py-3 text-right font-medium">Basic</th>
                        <th class="px-4 py-3 text-right font-medium">Gross</th>
                        <th class="px-4 py-3 text-right font-medium">Net</th>
                        <th class="px-4 py-3 text-center font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in payslips" :key="p.id" class="border-b last:border-0 hover:bg-muted/30">
                        <td class="px-4 py-2">
                            <div class="font-medium">{{ p.staff_name }}</div>
                            <div class="text-xs text-muted-foreground">{{ p.employee_no }}</div>
                        </td>
                        <td class="px-4 py-2 text-muted-foreground">{{ p.designation }}</td>
                        <td class="px-4 py-2 text-right font-mono">{{ Number(p.basic).toLocaleString() }}</td>
                        <td class="px-4 py-2 text-right font-mono">{{ Number(p.gross).toLocaleString() }}</td>
                        <td class="px-4 py-2 text-right font-mono font-bold">{{ Number(p.net).toLocaleString() }}</td>
                        <td class="px-4 py-2 text-center">
                            <Badge :variant="p.status === 'paid' ? 'default' : 'secondary'" class="capitalize">{{ p.status }}</Badge>
                        </td>
                        <td class="px-4 py-2 text-right">
                            <Button v-if="p.status !== 'paid'" variant="outline" size="sm" @click="markPaid(p.id)">Mark Paid</Button>
                            <span v-else class="text-xs text-muted-foreground">{{ p.paid_at }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="!payslips.length" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">
            No payslips for {{ months[month - 1] }} {{ year }}. Click "Generate Payslips" to create them.
        </div>
    </div>
</template>
